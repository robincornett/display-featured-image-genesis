<?php

/**
 * Class to add customizer settings.
 * Class Display_Featured_Image_Genesis_Customizer
 * @package Display_Featured_Image_Genesis
 * @copyright 2016 Robin Cornett
 */
class Display_Featured_Image_Genesis_Customizer extends Display_Featured_Image_Genesis_Helper {

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
	 * Adds the individual sections, settings, and controls to the theme customizer
	 * @param $wp_customize WP_Customize_Manager
	 * @uses add_section() adds a section to the customizer
	 */
	public function customizer( $wp_customize ) {

		$this->settings = new Display_Featured_Image_Genesis_Settings();
		$this->defaults = $this->settings->defaults();
		$setting = get_option( 'displayfeaturedimagegenesis', false );
		if ( ! $setting ) {
			add_option( 'displayfeaturedimagegenesis', $this->defaults );
		}
		$wp_customize->add_section(
			$this->section,
			array(
				'title'       => __( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
				'description' => __( 'Only general settings are available in the Customizer; more can be found on the Display Featured Image for Genesis settings page.', 'display-featured-image-genesis' ),
				'priority'    => 90,
			)
		);

		$this->build_fields( $wp_customize );
	}

	/**
	 * Build the Display Featured Image for Genesis Customizer settings panel.
	 * @param $wp_customize
	 */
	protected function build_fields( $wp_customize ) {
		$numbers = $this->number_fields();
		foreach ( $numbers as $field ) {
			$number['sanitize_callback'] = 'absint';
			$number['type']              = 'number';
			$this->add_control( $wp_customize, $field );
		}

		$this->do_image_setting( $wp_customize, $this->default_image() );

		$checkboxes = $this->checkbox_fields();
		foreach ( $checkboxes as $checkbox ) {
			$checkbox['sanitize_callback'] = array( $this, 'one_zero' );
			$checkbox['type']              = 'checkbox';
			$this->add_control( $wp_customize, $checkbox );
		}

	}

	/**
	 * @return array
	 */
	function number_fields() {
		return array(
			array(
				'setting'     => 'less_header',
				'label'       => __( 'Height', 'display-featured-image-genesis' ),
				'description' => __( 'Changing this number will reduce the backstretch image height by this number of pixels. Default is zero.', 'display-featured-image-genesis' ),
			),
			array(
				'setting'     => 'max_height',
				'label'       => __( 'Maximum Height', 'display-featured-image-genesis' ),
				'description' => __( 'Optionally, set a max-height value for the header image; it will be added to your CSS.', 'display-featured-image-genesis' ),
			),
		);
	}

	protected function checkbox_fields() {
		return array(
			array(
				'setting'     => 'exclude_front',
				'label'       => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'description' => __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ),
			),
			array(
				'setting'     => 'keep_titles',
				'label'       => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'description' => __( 'Do not move the titles to overlay the backstretch Featured Image.', 'display-featured-image-genesis' ),
			),
			array(
				'setting'     => 'move_excerpts',
				'label'       => __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ),
				'description' => __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ),
			),
			array(
				'setting'     => 'thumbnails',
				'label'       => __( 'Archive Thumbnails', 'display-featured-image-genesis' ),
				'description' => __( 'Use term/post type fallback images for content archive thumbnails?', 'display-featured-image-genesis' ),
			),
		);
	}

	protected function default_image() {
		$common = new Display_Featured_Image_Genesis_Common();
		$size   = $common->minimum_backstretch_width();
		return array(
			'setting'     => 'default',
			'description' => sprintf( __( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.' , 'display-featured-image-genesis' ), absint( $size + 1 ) ),
			'label'       => __( 'Default Image', 'display-featured-image-genesis' ),
			'transport'   => 'postMessage',
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function do_color_setting( $wp_customize, $setting ) {

		$this->add_setting( $wp_customize, $setting );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$this->section . '[' . $setting['setting'] . ']',
				array(
					'description' => $setting['description'],
					'label'       => $setting['label'],
					'section'     => $this->section,
					'settings'    => $this->section . '[' . $setting['setting'] . ']',
				)
			)
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function do_image_setting( $wp_customize, $setting ) {
		$this->add_setting( $wp_customize, $setting );
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$this->section . '[' . $setting['setting'] . ']',
				array(
					'label'       => $setting['label'],
					'section'     => $this->section,
					'settings'    => $this->section . '[' . $setting['setting'] . ']',
					'description' => isset( $setting['description'] ) ? $setting['description'] : '',
				)
			)
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function add_control( $wp_customize, $setting ) {
		$this->add_setting( $wp_customize, $setting );
		$wp_customize->add_control(
			$this->section . '[' . $setting['setting'] . ']',
			array(
				'label'       => $setting['label'],
				'section'     => $this->section,
				'type'        => isset( $setting['type'] ) ? $setting['type'] : '',
				'description' => isset( $setting['description'] ) ? $setting['description'] : '',
				'choices'     => isset( $setting['choices'] ) ? $setting['choices'] : array(),
			)
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function add_setting( $wp_customize, $setting ) {
		$wp_customize->add_setting(
			$this->section . '[' . $setting['setting'] . ']',
			array(
				'capability'        => 'manage_options',
				'default'           => $this->defaults[ $setting['setting'] ],
				'sanitize_callback' => isset( $setting['sanitize_callback'] ) ? $setting['sanitize_callback'] : '',
				'type'              => 'option',
				'transport'         => isset( $setting['transport'] ) ? $setting['transport'] : 'refresh',
			)
		);
	}
}
