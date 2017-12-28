<?php

/**
 * Class to add customizer settings.
 * Class Display_Featured_Image_Genesis_Customizer
 * @package   Display_Featured_Image_Genesis
 * @copyright 2016 Robin Cornett
 */
class Display_Featured_Image_Genesis_Customizer extends Display_Featured_Image_Genesis_Settings_Define {

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
				'title'  => __( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
				'fields' => $this->define_main_fields(),
			),
			array(
				'id'     => 'backstretch',
				'title'  => __( 'Display Settings', 'display-featured-image-genesis' ),
				'fields' => $this->define_style_fields(),
			),
			array(
				'id'     => 'cpt',
				'title'  => __( 'Sitewide Settings', 'display-featured-image-genesis' ),
				'fields' => $this->define_cpt_fields(),
			),
			array(
				'id'     => 'advanced',
				'title'  => __( 'Advanced Plugin Settings', 'display-featured-image-genesis' ),
				'fields' => $this->define_advanced_fields(),
			),
		);
		foreach ( $sections as $section ) {
			$wp_customize->add_section( $this->section . '_' . $section['id'], array(
				'title' => $section['title'],
				'panel' => $this->section,
			) );
			$this->add_section_controls( $wp_customize, $section['fields'], $this->section . '_' . $section['id'] );
		}

	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 *
	 * @param string $section
	 *
	 * @since 2.6.0
	 */
	protected function do_image_setting( $wp_customize, $setting, $section = 'main' ) {
		$this->add_setting( $wp_customize, $setting );
		$image = 'default' === $setting['id'] ? $this->section . '[' . $setting['id'] . ']' : $this->section . '[post_type][' . $setting['id'] . ']';
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$this->section . '[' . $setting['id'] . ']',
				array(
					'label'       => $setting['title'],
					'section'     => $section,
					'settings'    => $image,
					'description' => isset( $setting['label'] ) ? $setting['label'] : '',
				)
			)
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $fields
	 *
	 * @param $section
	 *
	 * @since 2.7.0
	 */
	protected function add_section_controls( $wp_customize, $fields, $section ) {
		foreach ( $fields as $setting ) {
			if ( isset( $setting['skip'] ) && $setting['skip'] ) {
				continue;
			}
			if ( 'image' === $setting['type'] ) {
				$this->do_image_setting( $wp_customize, $setting, $section );
				continue;
			}
			$this->add_setting( $wp_customize, $setting );
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
		$id                = $this->section . '[' . $setting['id'] . ']';
		$validation        = $this->validation_class();
		$default           = isset( $this->defaults[ $setting['id'] ] ) ? $this->defaults[ $setting['id'] ] : '';
		$sanitize_callback = '';
		if ( 'checkbox' === $setting['type'] ) {
			$sanitize_callback = array( $validation, 'one_zero' );
		} elseif ( 'number' === $setting['type'] ) {
			$sanitize_callback = 'absint';
		} elseif ( 'image' === $setting['type'] ) {
			$sanitize_callback = array( $this, 'send_image_to_validator' );
			if ( 'default' !== $setting['id'] ) {
				$id = $this->section . '[post_type][' . $setting['id'] . ']';
			}
		} elseif ( isset( $setting['sanitize_callback'] ) && $setting['sanitize_callback'] ) {
			$sanitize_callback = array( $this, $setting['sanitize_callback'] );
		}
		$wp_customize->add_setting(
			$id,
			array(
				'capability'        => 'manage_options',
				'default'           => $default,
				'sanitize_callback' => $sanitize_callback,
				'type'              => 'option',
				'transport'         => isset( $setting['transport'] ) ? $setting['transport'] : 'refresh',
			)
		);
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
		$common     = new Display_Featured_Image_Genesis_Common();
		$size       = $common->minimum_backstretch_width();
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
		include_once plugin_dir_path( __FILE__ ) . 'settings/class-displayfeaturedimagegenesis-settings-validate.php';
		$this->validation = new Display_Featured_Image_Genesis_Settings_Validate( array(), $this->setting );

		return $this->validation;
	}
}
