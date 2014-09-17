<?php
/**
 * Simple plugin to vary how the post/page featured image displays
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014 Robin Cornett Creative, LLC
 *
 * @wordpress-plugin
 * Plugin Name:       Display Featured Image for Genesis
 * Plugin URI:        http://github.com/robincornett/display-featured-image-genesis/
 * Description:       This plugin requires the Genesis Framework. It varies the display of the post or page featured image, depending on size.
 * Version:           1.0.1
 * Author:            Robin Cornett
 * Author URI:        http://robincornett.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

require plugin_dir_path( __FILE__ ) . 'includes/class-displayfeaturedimagegenesis.php';

add_action( 'init', 'displayfeaturedimagegenesis_instantiate' );
function displayfeaturedimagegenesis_instantiate() {
	if ( basename( get_template_directory() ) == 'genesis' ) {
		new Display_Featured_Image_Genesis();
	}
}
