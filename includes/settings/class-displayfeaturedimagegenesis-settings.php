<?php

/**
 * Class Display_Featured_Image_Genesis_Settings
 * @package   DisplayFeaturedImageGenesis
 * @copyright 2017 Robin Cornett
 */
class Display_Featured_Image_Genesis_Settings extends Display_Featured_Image_Genesis_Helper {

	/**
	 * The plugin admin page.
	 * @var $page string
	 */
	protected $page = 'displayfeaturedimagegenesis';

	/**
	 * The plugin setting.
	 * @var $setting array
	 */
	protected $setting;

	/**
	 * The plugin settings fields.
	 * @var $fields array
	 */
	protected $fields;

	/**
	 * add a submenu page under Appearance
	 * @since  1.4.0
	 */
	public function do_submenu_page() {

		$this->setting = $this->get_display_setting();

		add_theme_page(
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( "load-appearance_page_{$this->page}", array( $this, 'build_settings_page' ) );
	}

	/**
	 * Build out the settings page sections/fields.
	 */
	public function build_settings_page() {
		$sections     = include 'sections.php';
		$this->fields = $this->get_fields();
		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );

		include_once 'class-displayfeaturedimagegenesis-helptabs.php';
		$help = new Display_Featured_Image_Genesis_HelpTabs();
		$help->help();
	}

	/**
	 * Get the settings fields.
	 * @return array
	 */
	protected function get_fields() {
		$fields = array();
		$files  = array(
			'main',
			'style',
			'cpt',
			'advanced',
		);
		foreach ( $files as $file ) {
			$fields = array_merge( $fields, include "fields-{$file}.php" );
		}

		return $fields;
	}

	/**
	 * create settings form
	 *
	 * @since  1.4.0
	 */
	public function do_settings_form() {
		$page_title = get_admin_page_title();
		echo '<div class="wrap">';
			printf( '<h1>%s</h1>', esc_attr( $page_title ) );
			$this->check_and_maybe_update_terms();
			$active_tab = $this->get_active_tab();
			echo $this->do_tabs( $active_tab );
			echo '<form action="options.php" method="post">';
				settings_fields( $this->page );
				do_settings_sections( $this->page . '_' . $active_tab );
				wp_nonce_field( $this->page . '_save-settings', $this->page . '_nonce', false );
				submit_button();
				settings_errors();
			echo '</form>';
		echo '</div>';
	}

	/**
	 * Check if term images need to be updated because they were added before WP 4.4 and this plugin 2.4.
	 * @since 2.6.1
	 */
	protected function check_and_maybe_update_terms() {
		if ( ! function_exists( 'get_term_meta' ) ) {
			return;
		}
		if ( $this->terms_have_been_updated() ) {
			return;
		}
		$previous_user = get_option( 'displayfeaturedimagegenesis', false );
		if ( ! $previous_user ) {
			update_option( 'displayfeaturedimagegenesis_updatedterms', true );

			return;
		}

		include_once plugin_dir_path( __FILE__ ) . 'class-displayfeaturedimagegenesis-settings-terms.php';
		$terms = new Display_Featured_Image_Genesis_Settings_Terms();
		$terms->maybe_update_terms();
	}

	/**
	 * Output tabs.
	 *
	 * @param $active_tab
	 *
	 * @return string
	 * @since 2.5.0
	 */
	protected function do_tabs( $active_tab ) {
		$tabs    = include 'tabs.php';
		$output  = '<div class="nav-tab-wrapper">';
		$output .= sprintf( '<h2 id="settings-tabs" class="screen-reader-text">%s</h2>', __( 'Settings Tabs', 'display-featured-image-genesis' ) );
		$output .= '<ul>';
		foreach ( $tabs as $tab ) {
			$class = 'nav-tab';
			if ( $active_tab === $tab['id'] ) {
				$class .= ' nav-tab-active';
			}
			$query   = add_query_arg(
				array(
					'page' => $this->page,
					'tab'  => $tab['id'],
				),
				'themes.php'
			);
			$output .= sprintf(
				'<li><a href="%s" class="%s">%s</a></li>',
				esc_url( $query ),
				$class,
				$tab['tab']
			);
		}
		$output .= '</ul>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Settings for options screen
	 *
	 * @since 1.1.0
	 */
	public function register_settings() {
		register_setting( 'displayfeaturedimagegenesis', 'displayfeaturedimagegenesis', array( $this, 'do_validation_things' ) );
	}

	/**
	 * Section description
	 *
	 * @since 1.1.0
	 */
	public function main_section_description() {
		return __( 'Use these settings to modify the plugin behavior throughout your site. Check the Help tab for more information. ', 'display-featured-image-genesis' );
	}

	/**
	 * Style section description
	 */
	public function style_section_description() {
		return __( 'These settings modify the output style/methods for the banner image.', 'display-featured-image-genesis' );
	}

	/**
	 * Backtretch section description
	 * @return string
	 */
	public function backstretch_section_description() {
		return __( 'These settings apply only to the backstretch (script) banner image.', 'display-featured-image-genesis' );
	}

	/**
	 * Section description
	 *
	 * @since 1.1.0
	 */
	public function cpt_section_description() {
		$description = __( 'Optional: set a custom image for search results and 404 (no results found) pages.', 'display-featured-image-genesis' );
		$post_types  = $this->get_content_types();
		unset( $post_types['post'] );
		if ( $post_types ) {
			$description .= __( ' Additionally, since you have custom post types with archives, you might like to set a featured image for each of them.', 'display-featured-image-genesis' );
		}

		return $description;
	}

	/**
	 * Description for the advanced settings section/tab.
	 *
	 * @return string
	 */
	public function advanced_section_description() {
		return __( 'Optionally, change the hook location and priority of the featured image output. Use with caution. Note: this will change the hook/priority of the featured image sitewide. If you need to make changes based on content type, check the readme for code examples.', 'display-featured-image-genesis' );
	}

	/**
	 * Default image uploader
	 *
	 * @since  1.2.1
	 *
	 * @param $args
	 */
	public function set_default_image( $args ) {

		$id   = $this->setting[ $args['id'] ] ? $this->setting[ $args['id'] ] : '';
		$name = $this->page . '[' . $args['id'] . ']';
		if ( ! empty( $id ) ) {
			echo wp_kses_post( $this->render_image_preview( $id, $args['id'] ) );
		}
		$this->render_buttons( $id, $name );
		$this->do_description( $args );
	}

	/**
	 * Custom Post Type image uploader
	 *
	 * @since  2.0.0
	 *
	 * @param $args
	 */
	public function set_cpt_image( $args ) {

		$this->do_image_buttons( $args );

		if ( empty( $id ) || in_array( $args['id'], array( 'search', 'fourohfour', 'post' ), true ) ) {
			return;
		}

		$this->do_cpt_description( $args );
	}

	/**
	 * Print the featured image preview and buttons.
	 *
	 * @param $args
	 */
	protected function do_image_buttons( $args ) {
		$show_on_front = get_option( 'show_on_front' );
		$posts_page    = get_option( 'page_for_posts' );
		if ( 'page' === $show_on_front && $posts_page && 'post' === $args['id'] ) {
			$link = get_edit_post_link( $posts_page );
			/* translators: the link is to edit the posts page. */
			printf( wp_kses_post( __( 'You may set a fallback image for Posts on your <a href="%s">posts page</a>.', 'display-featured-image-genesis' ) ), esc_url( $link ) );
			return;
		}
		$id   = isset( $this->setting['post_type'][ $args['id'] ] ) && $this->setting['post_type'][ $args['id'] ] ? $this->setting['post_type'][ $args['id'] ] : '';
		$name = $this->page . '[post_type][' . esc_attr( $args['id'] ) . ']';
		if ( $id ) {
			echo wp_kses_post( $this->render_image_preview( $id, $args['id'] ) );
		}
		$this->render_buttons( $id, $name );
	}

	/**
	 * Print the CPT description.
	 *
	 * @param $args
	 */
	protected function do_cpt_description( $args ) {
		$archive_link = get_post_type_archive_link( $args['id'] );
		if ( ! $archive_link ) {
			return;
		}
		/* translators: placeholder is the post type name. */
		$description = sprintf( __( 'View your <a href="%1$s" target="_blank">%2$s</a> archive.', 'display-featured-image-genesis' ),
			esc_url( $archive_link ),
			esc_attr( $args['title'] )
		);
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * @return array
	 */
	public function pick_center() {
		return array(
			1 => __( 'Center', 'display-featured-image-genesis' ),
			0 => __( 'Do Not Center', 'display-featured-image-genesis' ),
		);
	}

	/**
	 * Get the post types as options.
	 * @return array
	 */
	protected function get_post_types() {
		$post_types = $this->get_content_types_built_in();
		$options    = array();
		foreach ( $post_types as $post_type ) {
			$object                = get_post_type_object( $post_type );
			$options[ $post_type ] = $object->label;
		}

		return $options;
	}

	/**
	 * Get the hooks for the large image.
	 *
	 * @return array
	 */
	protected function large_hook_options() {
		$hooks = array(
			'genesis_before_loop'                 => 'genesis_before_loop ' . __( '(default)', 'display-featured-image-genesis' ),
			'genesis_after_header'                => 'genesis_after_header',
			'genesis_before_content_sidebar_wrap' => 'genesis_before_content_sidebar_wrap',
		);
		$html5 = genesis_html5() ? array(
			'genesis_before_entry'  => 'genesis_before_entry ' . __( '(HTML5 themes)', 'display-featured-image-genesis' ),
			'genesis_entry_header'  => 'genesis_entry_header ' . __( '(HTML5 themes)', 'display-featured-image-genesis' ),
			'genesis_entry_content' => 'genesis_entry_content ' . __( '(HTML5 themes)', 'display-featured-image-genesis' ),
		) : array();

		return array_merge( $hooks, $html5 );
	}

	/**
	 * validate all inputs
	 *
	 * @param $new_value array
	 *
	 * @return array
	 *
	 * @since  1.4.0
	 */
	public function do_validation_things( $new_value ) {

		$action = $this->page . '_save-settings';
		$nonce  = $this->page . '_nonce';
		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $action, $nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		check_admin_referer( $action, $nonce );
		$new_value = array_merge( $this->setting, $new_value );

		include_once 'class-displayfeaturedimagegenesis-settings-validate.php';
		$validation = new Display_Featured_Image_Genesis_Settings_Validate( $this->get_fields(), $this->setting );

		return $validation->validate( $new_value );
	}

	/**
	 * Check whether terms need to be updated
	 * @return boolean true if on 4.4 and wp_options for terms exist; false otherwise
	 *
	 * @since 2.4.0
	 */
	protected function terms_have_been_updated() {
		$updated = get_option( $this->page . '_updatedterms', false );

		return (bool) $updated;
	}
}
