<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014-2016 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Settings extends Display_Featured_Image_Genesis_Helper {

	/**
	 * The common plugin class.
	 * @var $commmon Display_Featured_Image_Genesis_Common
	 */
	protected $common;

	/**
	 * The plugin admin page.
	 * @var $page string
	 */
	protected $page = 'displayfeaturedimagegenesis';

	/**
	 * The plugin setting.
	 * @var $setting string
	 */
	protected $setting;

	/**
	 * Public post types on the site.
	 * @var $post_types array
	 */
	protected $post_types;

	/**
	 * The plugin settings fields.
	 * @var $fields array
	 */
	protected $fields;

	/**
	 * add a submenu page under Appearance
	 * @return submenu Display Featured image settings page
	 * @since  1.4.0
	 */
	public function do_submenu_page() {

		$this->common     = new Display_Featured_Image_Genesis_Common();
		$this->setting    = $this->get_display_setting();
		$this->post_types = $this->get_content_types();

		add_theme_page(
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		$sections     = $this->register_sections();
		$this->fields = $this->register_fields();
		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );
	}

	/**
	 * create settings form
	 * @return form Display Featured Image for Genesis settings
	 *
	 * @since  1.4.0
	 */
	public function do_settings_form() {
		if ( $this->terms_need_updating() ) {
			$this->update_delete_term_meta();
		}
		$page_title = get_admin_page_title();
		echo '<div class="wrap">';
			printf( '<h1>%s</h1>', esc_attr( $page_title ) );
			if ( $this->terms_need_updating() ) {
				$this->term_meta_notice();
			}
			$active_tab = $this->get_active_tab();
			echo $this->do_tabs( $active_tab );
			echo '<form action="options.php" method="post">';
				settings_fields( 'displayfeaturedimagegenesis' );
				do_settings_sections( 'displayfeaturedimagegenesis_' . $active_tab );
				wp_nonce_field( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce', false );
				submit_button();
				settings_errors();
			echo '</form>';
		echo '</div>';
	}

	/**
	 * Output tabs.
	 * @return string
	 * @since 2.5.0
	 */
	protected function do_tabs( $active_tab ) {
		$tabs = array(
			'main' => array( 'id' => 'main', 'tab' => __( 'Main', 'display-featured-image-genesis' ) ),
			'cpt'  => array( 'id' => 'cpt', 'tab' => __( 'Content Types', 'display-featured-image-genesis' ) ),
		);
		$output  = '<div class="nav-tab-wrapper">';
		$output .= sprintf( '<h2 id="settings-tabs" class="screen-reader-text">%s</h2>', __( 'Settings Tabs', 'display-featured-image-genesis' ) );
		$output .= '<ul>';
		foreach ( $tabs as $tab ) {
			$class   = $active_tab === $tab['id'] ? ' nav-tab-active' : '';
			$output .= sprintf( '<li><a href="themes.php?page=%s&tab=%s" class="nav-tab%s">%s</a></li>', $this->page, $tab['id'], $class, $tab['tab'] );
		}
		$output .= '</ul>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Settings for options screen
	 * @return array settings for backstretch image options
	 *
	 * @since 1.1.0
	 */
	public function register_settings() {
		register_setting( 'displayfeaturedimagegenesis', 'displayfeaturedimagegenesis', array( $this, 'do_validation_things' ) );
	}

	/**
	 * Register plugin settings page sections
	 *
	 * @since 2.3.0
	 */
	protected function register_sections() {
		return array(
			'main' => array(
				'id'    => 'main',
				'title' => __( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
			),
			'cpt' => array(
				'id'    => 'cpt',
				'title' => __( 'Featured Images for Custom Content Types', 'display-featured-image-genesis' ),
			),
		);
	}

	/**
	 * Register plugin settings fields
	 * @return array           all settings fields
	 *
	 * @since 2.3.0
	 */
	protected function register_fields() {

		return array_merge( $this->define_main_fields(), $this->define_cpt_fields() );
	}

	protected function define_main_fields() {
		return array(
			array(
				'id'       => 'less_header',
				'title'    => __( 'Height' , 'display-featured-image-genesis' ),
				'callback' => 'do_number',
				'section'  => 'main',
				'args'     => array( 'setting' => 'less_header', 'label' => __( 'pixels to remove', 'display-featured-image-genesis' ), 'min' => 0, 'max' => 400 ),
			),
			array(
				'id'       => 'max_height',
				'title'    => __( 'Maximum Height' , 'display-featured-image-genesis' ),
				'callback' => 'do_number',
				'section'  => 'main',
				'args'     => array( 'setting' => 'max_height', 'label' => __( 'pixels', 'display-featured-image-genesis' ), 'min' => 100, 'max' => 1000 ),
			),
			array(
				'id'       => 'default',
				'title'    => __( 'Default Featured Image', 'display-featured-image-genesis' ),
				'callback' => 'set_default_image',
				'section'  => 'main',
			),
			array(
				'id'       => 'always_default',
				'title'    => __( 'Always Use Default', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'always_default', 'label' => __( 'Always use the default image, even if a featured image is set.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'exclude_front',
				'title'    => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'exclude_front', 'label' => __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'keep_titles',
				'title'    => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'keep_titles', 'label' => __( 'Do not move the titles to overlay the backstretch Featured Image.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'move_excerpts',
				'title'    => __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'move_excerpts', 'label' => __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'is_paged',
				'title'    => __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'is_paged', 'label' => __( 'Show featured image on pages 2+ of blogs and archives.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'feed_image',
				'title'    => __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'feed_image', 'label' => __( 'Optionally, add the featured image to your RSS feed.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'thumbnails',
				'title'    => __( 'Archive Thumbnails', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'thumbnails', 'label' => __( 'Use term/post type fallback images for content archives?', 'display-featured-image-genesis' ) ),
			),
		);
	}

	protected function define_cpt_fields() {
		$fields = array(
			array(
				'id'       => 'post_types][search',
				'title'    => __( 'Search Results', 'display-featured-image-genesis' ),
				'callback' => 'set_cpt_image',
				'section'  => 'cpt',
				'args'     => array( 'post_type' => 'search' ),
			),
			array(
				'id'       => 'post_types][fourohfour',
				'title'    => __( '404 Page', 'display-featured-image-genesis' ),
				'callback' => 'set_cpt_image',
				'section'  => 'cpt',
				'args'     => array( 'post_type' => 'fourohfour' ),
			),
			array(
				'id'       => 'skip',
				'title'    => __( 'Skip Content Types', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox_array',
				'section'  => 'cpt',
				'args'     => array( 'setting' => 'skip' ),
			),
		);

		if ( $this->post_types ) {

			foreach ( $this->post_types as $post ) {
				$object = get_post_type_object( $post );
				$fields[] = array(
					'id'       => 'post_types][' . esc_attr( $object->name ),
					'title'    => esc_attr( $object->label ),
					'callback' => 'set_cpt_image',
					'section'  => 'cpt',
					'args'     => array( 'post_type' => $object ),
				);
			}
		}
		return $fields;
	}

	/**
	 * Section description
	 * @return string description
	 *
	 * @since 1.1.0
	 */
	public function main_section_description() {
		$description = __( 'The Display Featured Image for Genesis plugin has just a few optional settings. Check the Help tab for more information. ', 'display-featured-image-genesis' );
		$this->print_section_description( $description );
	}

	/**
	 * Section description
	 * @return string description
	 *
	 * @since 1.1.0
	 */
	public function cpt_section_description() {
		$description = __( 'Optional: set a custom image for search results and 404 (no results found) pages.', 'display-featured-image-genesis' );
		if ( $this->post_types ) {
			$description .= __( ' Additionally, since you have custom post types with archives, you might like to set a featured image for each of them.', 'display-featured-image-genesis' );
		}
		$this->print_section_description( $description );
	}

	/**
	 * Description for less_header setting.
	 * @return string description
	 *
	 * @since 2.3.0
	 */
	protected function less_header_description() {
		return __( 'Changing this number will reduce the backstretch image height by this number of pixels. Default is zero.', 'display-featured-image-genesis' );
	}

	/**
	 * Description for the max_height setting.
	 * @return string|void description
	 * @since 2.6.0
	 */
	protected function max_height_description() {
		return __( 'Optionally, set a max-height value for the header image; it will be added to your CSS.', 'display-featured-image-genesis' );
	}

	/**
	 * Default image uploader
	 *
	 * @return  image
	 *
	 * @since  1.2.1
	 */
	public function set_default_image() {

		$id   = $this->setting['default'] ? $this->setting['default'] : '';
		$name = 'displayfeaturedimagegenesis[default]';
		if ( ! empty( $id ) ) {
			echo wp_kses_post( $this->render_image_preview( $id, 'default' ) );
		}
		$this->render_buttons( $id, $name );
		$this->do_description( 'default_image' );

	}

	/**
	 * Description for default image setting
	 * @return string
	 *
	 * @since 2.3.0
	 */
	protected function default_image_description() {
		$large = $this->common->minimum_backstretch_width();
		return sprintf(
			esc_html__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
		);
	}

	/**
	 * Custom Post Type image uploader
	 *
	 * @return  image
	 *
	 * @since  2.0.0
	 */
	public function set_cpt_image( $args ) {

		$post_type = is_object( $args['post_type'] ) ? $args['post_type']->name : $args['post_type'];
		if ( empty( $this->setting['post_type'][ $post_type ] ) ) {
			$this->setting['post_type'][ $post_type ] = $id = '';
		}

		if ( is_object( $args['post_type'] ) ) {
			$fallback_args = array(
				'setting'      => "fallback][{$post_type}",
				'label'        => sprintf( __( 'Always use a fallback image for %s.', 'display-featured-image-genesis' ), esc_attr( $args['post_type']->label ) ),
				'setting_name' => 'fallback',
				'name'         => $post_type,
			);
			echo '<p>';
			$this->do_checkbox( $fallback_args );
			echo '</p>';
		}
		$id   = $this->setting['post_type'][ $post_type ];
		$name = 'displayfeaturedimagegenesis[post_type][' . esc_attr( $post_type ) . ']';
		if ( $id ) {
			echo wp_kses_post( $this->render_image_preview( $id, $post_type ) );
		}

		$this->render_buttons( $id, $name );

		if ( empty( $id ) || ! is_object( $args['post_type'] ) ) {
			return;
		}
		$description = sprintf( __( 'View your <a href="%1$s" target="_blank">%2$s</a> archive.', 'display-featured-image-genesis' ),
			esc_url( get_post_type_archive_link( $post_type ) ),
			esc_attr( $args['post_type']->label )
		);
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * validate all inputs
	 * @param  string $new_value various settings
	 * @return string            number or URL
	 *
	 * @since  1.4.0
	 */
	public function do_validation_things( $new_value ) {

		$action = 'displayfeaturedimagegenesis_save-settings';
		$nonce  = 'displayfeaturedimagegenesis_nonce';
		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $action, $nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		check_admin_referer( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce' );
		$new_value = array_merge( $this->setting, $new_value );

		// validate all checkbox fields
		foreach ( $this->fields as $field ) {
			if ( 'do_checkbox' === $field['callback'] ) {
				$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
			} elseif ( 'do_number' === $field['callback'] ) {
				$new_value[ $field['id'] ] = $this->check_value( $new_value[ $field['id'] ], $this->setting[ $field['id'] ], $field['args']['min'], $field['args']['max'] );
			}
		}

		// extra variables to pass through to image validation
		$size_to_check = $this->common->minimum_backstretch_width();

		// validate default image
		$new_value['default'] = $this->validate_image( $new_value['default'], $this->setting['default'], __( 'Default', 'display-featured-image-genesis' ), $size_to_check );

		// search/404
		$size_to_check = get_option( 'medium_size_w' );
		$custom_pages  = array(
			array(
				'id'    => 'search',
				'label' => __( 'Search Results', 'display-featured-image-genesis' ),
			),
			array(
				'id'    => 'fourohfour',
				'label' => __( '404 Page', 'display-featured-image-genesis' ),
			),
		);
		foreach ( $custom_pages as $page ) {
			$setting_to_check = isset( $this->setting['post_type'][ $page['id'] ] ) ? $this->setting['post_type'][ $page['id'] ] : '';
			if ( isset( $new_value['post_type'][ $page ['id'] ] ) ) {
				$new_value['post_type'][ $page ['id'] ] = $this->validate_image( $new_value['post_type'][ $page['id'] ], $setting_to_check, $page['label'], $size_to_check );
			}
		}

		foreach ( $this->post_types as $post_type ) {

			$object    = get_post_type_object( $post_type );
			$old_value = isset( $this->setting['post_type'][ $object->name ] ) ? $this->setting['post_type'][ $object->name ] : '';
			$label     = $object->label;

			if ( isset( $new_value['post_type'][ $post_type ] ) ) {
				$new_value['post_type'][ $post_type ] = $this->validate_image( $new_value['post_type'][ $post_type ], $old_value, $label, $size_to_check );
			}
			if ( isset( $new_value['fallback'][ $post_type ] ) ) {
				$new_value['fallback'][ $post_type ] = $this->one_zero( $new_value['fallback'][ $post_type ] );
			}
		}
		$post_types = $this->get_content_types_built_in();
		foreach ( $post_types as $post_type ) {
			$new_value['skip'][ $post_type ] = isset( $new_value['skip'][ $post_type ] ) ? $this->one_zero( $new_value['skip'][ $post_type ] ) : 0;
		}

		return $new_value;
	}

	/**
	 * Check the numeric value against the allowed range. If it's within the range, return it; otherwise, return the old value.
	 * @param $new_value int new submitted value
	 * @param $old_value int old setting value
	 * @param $min int minimum value
	 * @param $max int maximum value
	 *
	 * @return int
	 */
	protected function check_value( $new_value, $old_value, $min, $max ) {
		if ( $new_value >= $min && $new_value <= $max ) {
			return (int) $new_value;
		}
		return $old_value;
	}

	/**
	 * For 4.4, output a notice explaining that old term options can be updated to term_meta.
	 * Options are to update all terms or to ignore, and do by hand.
	 * @since 2.4.0
	 */
	protected function term_meta_notice() {
		$screen = get_current_screen();
		if ( 'appearance_page_displayfeaturedimagegenesis' !== $screen->id ) {
			return;
		}
		$terms = $this->get_affected_terms();
		if ( empty( $terms ) ) {
			return;
		}
		$message  = sprintf( '<p>%s</p>', __( 'WordPress 4.4 introduces term metadata for categories, tags, and other taxonomies. This is your opportunity to optionally update all impacted terms on your site to use the new metadata.', 'display-featured-image-genesis' ) );
		$message .= sprintf( '<p>%s</p>', __( 'This <strong>will modify</strong> your database (potentially many entries at once), so if you\'d rather do it yourself, you can. Here\'s a list of the affected terms:', 'display-featured-image-genesis' ) );
		$message .= '<ul style="margin-left:24px;">';
		foreach ( $terms as $term ) {
			$message .= edit_term_link( $term->name, '<li>', '</li>', $term, false );
		}
		$message .= '</ul>';
		$message .= sprintf( '<p>%s</p>', __( 'To get rid of this notice, you can 1) update your terms by hand; 2) click the update button (please check your terms afterward); or 3) click the dismiss button.', 'display-featured-image-genesis' ) );
		$faq      = sprintf( __( 'For more information, please visit the plugin\'s <a href="%s" target="_blank">Frequently Asked Questions</a> on WordPress.org.', 'display-featured-image-genesis' ), esc_url( 'https://wordpress.org/plugins/display-featured-image-genesis/faq/' ) );
		$message .= sprintf( '<p>%s</p>', $faq );
		echo '<div class="updated">' . wp_kses_post( $message );
		echo '<form action="" method="post">';
		wp_nonce_field( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce', false );
		$buttons = array(
			array(
				'value' => __( 'Update My Terms', 'display-featured-image-genesis' ),
				'name'  => 'displayfeaturedimagegenesis_termmeta',
				'class' => 'button-primary',
			),
			array(
				'value' => __( 'Dismiss (I\'ve got this!)', 'display-featured-image-genesis' ),
				'name'  => 'displayfeaturedimagegenesis_termmetadismiss',
				'class' => 'button-secondary',
			),
		);
		echo '<p>';
		foreach ( $buttons as $button ) {
			printf( '<input type="submit" class="%s" name="%s" value="%s" style="margin-right:12px;" />',
				esc_attr( $button['class'] ),
				esc_attr( $button['name'] ),
				esc_attr( $button['value'] )
			);
		}
		echo '</p>';
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Update and/or delete term_meta and wp_options
	 * @since 2.4.0
	 */
	protected function update_delete_term_meta() {

		if ( isset( $_POST['displayfeaturedimagegenesis_termmeta'] ) ) {
			if ( ! check_admin_referer( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce' ) ) {
				return;
			}
			$terms = $this->get_affected_terms();
			foreach ( $terms as $term ) {
				$term_id = $term->term_id;
				$option  = get_option( "displayfeaturedimagegenesis_{$term_id}" );
				if ( false !== $option ) {
					$image_id = (int) displayfeaturedimagegenesis_check_image_id( $option['term_image'] );
					update_term_meta( $term_id, 'displayfeaturedimagegenesis', $image_id );
					delete_option( "displayfeaturedimagegenesis_{$term_id}" );
				}
			}
		}

		if ( isset( $_POST['displayfeaturedimagegenesis_termmeta'] ) || isset( $_POST['displayfeaturedimagegenesis_termmetadismiss'] ) ) {
			if ( ! check_admin_referer( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce' ) ) {
				return;
			}
			update_option( 'displayfeaturedimagegenesis_updatedterms', true );
		}
	}

	/**
	 * Get IDs of terms with featured images
	 * @param  array  $term_ids empty array
	 * @return array           all terms with featured images
	 * @since 2.4.0
	 */
	protected function get_affected_terms( $affected_terms = array() ) {
		$args = array(
			'public'  => true,
			'show_ui' => true,
		);
		$taxonomies = get_taxonomies( $args, 'objects' );

		foreach ( $taxonomies as $tax ) {
			$args   = array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			);
			$terms  = get_terms( $tax->name, $args );
			foreach ( $terms as $term ) {
				$term_id = $term->term_id;
				$option  = get_option( "displayfeaturedimagegenesis_{$term_id}", false );
				if ( false !== $option ) {
					$affected_terms[] = $term;
				}
			}
		}
		return $affected_terms;
	}

	/**
	 * Check whether terms need to be updated
	 * @return boolean true if on 4.4 and wp_options for terms exist; false otherwise
	 *
	 * @since 2.4.0
	 */
	protected function terms_need_updating() {
		$updated = get_option( 'displayfeaturedimagegenesis_updatedterms', false );
		if ( ! $updated && function_exists( 'get_term_meta' ) ) {
			return true;
		}
		return false;
	}
}
