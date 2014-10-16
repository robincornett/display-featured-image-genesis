<?php

/**
 * Customize Background Image Control Class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 1.2.0
 */
class Display_Featured_Image_Genesis_Customizer extends WP_Customize_Image_Control {

	/**
	 * Constructor.
	 *
	 * If $args['settings'] is not defined, use the $id as the setting ID.
	 *
	 * @since 1.2.0
	 * @uses WP_Customize_Upload_Control::__construct()
	 *
	 * @param WP_Customize_Manager $manager
	 * @param string $id
	 * @param array $args
	 */
	public function __construct( $manager, $id, $args ) {
		$this->statuses = array( '' => __( 'No Image', 'display-featured-image-genesis' ) );

		parent::__construct( $manager, $id, $args );

		$this->add_tab( 'upload-new', __( 'Upload New', 'display-featured-image-genesis' ), array( $this, 'tab_upload_new' ) );
		$this->add_tab( 'uploaded',   __( 'Uploaded', 'display-featured-image-genesis' ),   array( $this, 'tab_uploaded' ) );

		if ( $this->setting->default )
			$this->add_tab( 'default',  __( 'Default', 'display-featured-image-genesis' ),  array( $this, 'tab_default_background' ) );

		// Early priority to occur before $this->manager->prepare_controls();
		add_action( 'customize_controls_init', array( $this, 'prepare_control' ), 5 );
	}

}

	global $wp_customize;

	$wp_customize->add_section( 'displayfeaturedimage-settings', array(
		'title'    => __( 'Default Featured Image', 'display-featured-image-genesis' ),
		'priority' => 105,
	) );

	$wp_customize->add_setting( 'displayfeaturedimage_default', array(
		'type' => 'option',
	) );

	$wp_customize->add_control( new Display_Featured_Image_Genesis_Customizer( $wp_customize, 'displayfeaturedimage_default', array(
		'label'       => __( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
		'description' => __( 'You may set a default image to be used as the backstretch or large featured image if no image is selected for the post/page.', 'display-featured-image-genesis' ),
		'section'     => 'displayfeaturedimage-settings',
		'settings'    => 'displayfeaturedimage_default',
	) ) );
