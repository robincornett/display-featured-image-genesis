<?php

/**
 * Class to add customizer settings.
 * Class Display_Featured_Image_Genesis_Customizer
 * @package   Display_Featured_Image_Genesis
 * @copyright 2016 Robin Cornett
 */
class Display_Featured_Image_Genesis_Customizer extends Display_Featured_Image_Genesis_Settings {

	/**
	 * Section for the Customizer.
	 * @var string $section
	 */
	protected $section = 'displayfeaturedimagegenesis';

	/**
	 * Display Featured Image for Genesis Settings class.
	 * @var $settings SuperSide_Me_Settings
	 */
	protected $settings;

	/**
	 * Default plugin settings.
	 * @var array $defaults
	 */
	protected $defaults;

	/**
	 * Plugin setting from database.
	 * @var array $setting
	 */
	protected $setting;

	/**
	 * @var \Display_Featured_Image_Genesis_Settings_Validate
	 */
	protected $validation;

	/**
	 * Adds the individual sections, settings, and controls to the theme customizer
	 *
	 * @param $wp_customize WP_Customize_Manager
	 *
	 * @uses  add_section() adds a section to the customizer
	 * @since 2.6.0
	 */
	public function customizer( $wp_customize ) {

		$this->defaults = $this->defaults();
		$this->setting  = get_option( 'displayfeaturedimagegenesis', false );
		if ( ! $this->setting ) {
			add_option( 'displayfeaturedimagegenesis', $this->defaults );
		}
		$wp_customize->add_panel( $this->section, array(
			'priority'       => 90,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => __( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			'description'    => __( 'Only general settings are available in the Customizer; more can be found on the Display Featured Image for Genesis settings page.', 'display-featured-image-genesis' ),
		) );

		$sections = array(
			array(
				'id'     => 'main',
				'title'  => __( 'Main', 'display-featured-image-genesis' ),
				'fields' => include 'fields-main.php',
			),
			array(
				'id'     => 'backstretch',
				'title'  => __( 'Backstretch Output', 'display-featured-image-genesis' ),
				'fields' => include 'fields-style.php',
			),
			array(
				'id'          => 'cpt',
				'title'       => __( 'Content Types', 'display-featured-image-genesis' ),
				'fields'      => include 'fields-cpt.php',
				'description' => __( 'Optional: set a custom image for search results and 404 (no results found) pages, as well as content types.', 'display-featured-image-genesis' ),
			),
			array(
				'id'     => 'advanced',
				'title'  => __( 'Advanced', 'display-featured-image-genesis' ),
				'fields' => include 'fields-advanced.php',
			),
		);
		foreach ( $sections as $section ) {
			$wp_customize->add_section( $this->section . '_' . $section['id'], array(
				'title'       => $section['title'],
				'panel'       => $this->section,
				'description' => isset( $section['description'] ) ? $section['description'] : '',
			) );
			$this->add_section_controls( $wp_customize, $section['fields'], $this->section . '_' . $section['id'] );
		}

	}

	/**
	 * @param        $wp_customize WP_Customize_Manager
	 * @param        $setting
	 *
	 * @param string $section
	 *
	 * @since 2.6.0
	 */
	protected function do_image_setting( $wp_customize, $setting, $section = 'main' ) {
		$image = 'default' === $setting['id'] ? $this->section . '[' . $setting['id'] . ']' : $this->section . '[post_type][' . $setting['id'] . ']';
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$setting['id'],
				array(
					'label'           => $setting['title'],
					'section'         => $section,
					'settings'        => $image,
					'description'     => isset( $setting['label'] ) ? $setting['label'] : '',
					'active_callback' => array( $this, 'check_post_type' ),
				)
			)
		);
	}

	/**
	 * Check whether an image control should show (content type images will
	 * only show on their related content type).
	 *
	 * @param $control \WP_Customize_Image_Control
	 *
	 * @return bool
	 */
	public function check_post_type( $control ) {
		$always_show = array( 'default', 'fourohfour', 'search' );
		if ( in_array( $control->id, $always_show, true ) ) {
			return true;
		}
		$post_type = get_post_type();
		if ( $post_type === $control->id ) {
			if ( 'post' === $control->id ) {
				$show_on_front = get_option( 'show_on_front' );
				$posts_page    = get_option( 'page_for_posts' );

				return 'page' === $show_on_front && $posts_page ? false : true;
			}

			return true;
		}

		return false;
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $fields
	 *
	 * @param $section
	 *
	 * @since 3.0.0
	 */
	protected function add_section_controls( $wp_customize, $fields, $section ) {
		foreach ( $fields as $setting ) {
			if ( isset( $setting['skip'] ) && $setting['skip'] ) {
				continue;
			}
			$this->add_setting( $wp_customize, $setting );
			if ( 'image' === $setting['type'] ) {
				$this->do_image_setting( $wp_customize, $setting, $section );
				continue;
			}
			$wp_customize->add_control(
				$this->section . '[' . $setting['id'] . ']',
				array(
					'label'       => $setting['title'],
					'section'     => $section,
					'type'        => isset( $setting['type'] ) ? $setting['type'] : '',
					'description' => isset( $setting['label'] ) ? $setting['label'] : '',
					'choices'     => isset( $setting['choices'] ) ? $setting['choices'] : array(),
					'input_attrs' => isset( $setting['input_attrs'] ) ? $setting['input_attrs'] : array(),
				)
			);
		}
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 *
	 * @since 2.6.0
	 */
	protected function add_setting( $wp_customize, $setting ) {
		$id      = $this->section . '[' . $setting['id'] . ']';
		$default = isset( $this->defaults[ $setting['id'] ] ) ? $this->defaults[ $setting['id'] ] : '';
		if ( 'image' === $setting['type'] && 'default' !== $setting['id'] ) {
			$id = $this->section . '[post_type][' . $setting['id'] . ']';
		}
		$wp_customize->add_setting(
			$id,
			array(
				'capability'        => 'manage_options',
				'default'           => $default,
				'sanitize_callback' => $this->get_sanitize_callback( $setting['type'], $setting ),
				'type'              => 'option',
				'transport'         => isset( $setting['transport'] ) ? $setting['transport'] : 'refresh',
			)
		);
	}

	/**
	 * Get the appropriate sanitization callback for each setting.
	 * @param $type
	 * @param $setting
	 *
	 * @return array|string
	 */
	protected function get_sanitize_callback( $type, $setting ) {
		$validation = $this->validation_class();
		switch ( $type ) {
			case 'checkbox':
				$function = array( $validation, 'one_zero' );
				break;

			case 'number':
				$function = 'absint';
				break;

			case 'text':
				$function = 'sanitize_text_field';
				break;

			case 'color':
				$function = 'sanitize_hex_color';
				break;

			case 'radio':
				$function = 'esc_attr';
				break;

			case 'image':
				$function = array( $this, 'send_image_to_validator' );
				break;

			default:
				$function = 'esc_attr';
				break;
		}

		if ( isset( $setting['sanitize_callback'] ) && $setting['sanitize_callback'] ) {
			$function = array( $this, $setting['sanitize_callback'] );
		}

		return $function;
	}

	/**
	 * Custom validation function for the default image--ensure image is appropriately sized.
	 *
	 * @param $new_value
	 *
	 * @param $setting
	 *
	 * @return string
	 * @since 2.6.0
	 */
	public function send_image_to_validator( $new_value, $setting ) {
		$size       = displayfeaturedimagegenesis_get()->minimum_backstretch_width();
		$validation = $this->validation_class();

		return $validation->validate_image( $new_value, $setting->id, __( 'Default', 'display-featured-image-genesis' ), $size );
	}

	/**
	 * Get the settings validation class.
	 * @return \Display_Featured_Image_Genesis_Settings_Validate
	 */
	protected function validation_class() {
		if ( isset( $this->validation ) ) {
			return $this->validation;
		}
		include_once plugin_dir_path( __FILE__ ) . 'class-displayfeaturedimagegenesis-settings-validate.php';
		$this->validation = new Display_Featured_Image_Genesis_Settings_Validate( array(), $this->setting );

		return $this->validation;
	}
}
