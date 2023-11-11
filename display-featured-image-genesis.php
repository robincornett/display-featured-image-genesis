<?php
/**
 * This plugin works within the Genesis Framework, to display featured images in beautiful and dynamic ways.
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2021 Robin Cornett Creative, LLC
 *
 * @wordpress-plugin
 * Plugin Name:       Display Featured Image for Genesis
 * Plugin URI:        https://github.com/robincornett/display-featured-image-genesis/
 * Description:       This plugin works within the Genesis Framework, to display featured images in beautiful and dynamic ways.
 * Version:           3.2.3
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Tested up to:      6.4
 * Author:            Robin Cornett
 * Author URI:        https://robincornett.com
 * Text Domain:       display-featured-image-genesis
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
		'settings/class-displayfeaturedimagegenesis-getsetting',
		'settings/class-displayfeaturedimagegenesis-helper',
		'class-displayfeaturedimagegenesis-admin',
		'class-displayfeaturedimagegenesis-common',
		'meta/class-displayfeaturedimagegenesis-author',
		'meta/class-displayfeaturedimagegenesis-postmeta',
		'meta/class-displayfeaturedimagegenesis-taxonomies',
		'output/class-displayfeaturedimagegenesis-output',
		'output/class-displayfeaturedimagegenesis-rss',
		'settings/class-displayfeaturedimagegenesis-settings',
		'settings/class-displayfeaturedimagegenesis-customizer',
		'sixtenpress-shortcodes/sixtenpress-shortcodes',
		'widgets/class-displayfeaturedimagegenesis-widgets',
	);

	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}
}
display_featured_image_genesis_require();

// Instantiate dependent classes
$displayfeaturedimagegenesis_helper     = new Display_Featured_Image_Genesis_Helper();
$displayfeaturedimagegenesis_admin      = new Display_Featured_Image_Genesis_Admin();
$displayfeaturedimagegenesis_author     = new Display_Featured_Image_Genesis_Author();
$displayfeaturedimagegenesis_customizer = new Display_Featured_Image_Genesis_Customizer();
$displayfeaturedimagegenesis_output     = new Display_Featured_Image_Genesis_Output();
$displayfeaturedimagegenesis_post_meta  = new Display_Featured_Image_Genesis_Post_Meta();
$displayfeaturedimagegenesis_rss        = new Display_Featured_Image_Genesis_RSS();
$displayfeaturedimagegenesis_settings   = new Display_Featured_Image_Genesis_Settings();
$displayfeaturedimagegenesis_taxonomies = new Display_Featured_Image_Genesis_Taxonomies();
$displayfeaturedimagegenesis_widgets    = new DisplayFeaturedImageGenesisWidgets();

$displayfeaturedimage = new Display_Featured_Image_Genesis(
	$displayfeaturedimagegenesis_admin,
	$displayfeaturedimagegenesis_author,
	$displayfeaturedimagegenesis_customizer,
	$displayfeaturedimagegenesis_output,
	$displayfeaturedimagegenesis_post_meta,
	$displayfeaturedimagegenesis_rss,
	$displayfeaturedimagegenesis_settings,
	$displayfeaturedimagegenesis_taxonomies,
	$displayfeaturedimagegenesis_widgets
);

$displayfeaturedimage->run();
