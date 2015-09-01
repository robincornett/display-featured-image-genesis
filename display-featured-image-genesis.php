<?php
/**
 * Simple plugin to vary how the post/page featured image displays
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014-2015 Robin Cornett Creative, LLC
 *
 * @wordpress-plugin
 * Plugin Name:       Display Featured Image for Genesis
 * Plugin URI:        http://github.com/robincornett/display-featured-image-genesis/
 * Description:       This plugin works within the Genesis Framework, to display featured images in beautiful and dynamic ways.
 * Version:           2.3.2
 * Author:            Robin Cornett
 * Author URI:        http://robincornett.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'DISPLAYFEATUREDIMAGEGENESIS_BASENAME' ) ) {
	define( 'DISPLAYFEATUREDIMAGEGENESIS_BASENAME', plugin_basename( __FILE__ ) );
}

function display_featured_image_genesis_require() {
	$files = array(
		'class-displayfeaturedimagegenesis',
		'class-displayfeaturedimagegenesis-admin',
		'class-displayfeaturedimagegenesis-author',
		'class-displayfeaturedimagegenesis-common',
		'class-displayfeaturedimagegenesis-description',
		'class-displayfeaturedimagegenesis-output',
		'class-displayfeaturedimagegenesis-rss',
		'class-displayfeaturedimagegenesis-settings',
		'class-displayfeaturedimagegenesis-taxonomies',
	);

	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}
}
display_featured_image_genesis_require();

// Instantiate dependent classes
$displayfeaturedimagegenesis_admin       = new Display_Featured_Image_Genesis_Admin();
$displayfeaturedimagegenesis_author      = new Display_Featured_Image_Genesis_Author();
$displayfeaturedimagegenesis_common      = new Display_Featured_Image_Genesis_Common();
$displayfeaturedimagegenesis_description = new Display_Featured_Image_Genesis_Description();
$displayfeaturedimagegenesis_output      = new Display_Featured_Image_Genesis_Output();
$displayfeaturedimagegenesis_rss         = new Display_Featured_Image_Genesis_RSS();
$displayfeaturedimagegenesis_settings    = new Display_Featured_Image_Genesis_Settings();
$displayfeaturedimagegenesis_taxonomies  = new Display_Featured_Image_Genesis_Taxonomies();

$displayfeaturedimage = new Display_Featured_Image_Genesis(
	$displayfeaturedimagegenesis_admin,
	$displayfeaturedimagegenesis_author,
	$displayfeaturedimagegenesis_common,
	$displayfeaturedimagegenesis_description,
	$displayfeaturedimagegenesis_output,
	$displayfeaturedimagegenesis_rss,
	$displayfeaturedimagegenesis_settings,
	$displayfeaturedimagegenesis_taxonomies
);

$displayfeaturedimage->run();
