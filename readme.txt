=== Display Featured Image for Genesis ===

Contributors: littler.chicken
Donate link: https://robincornett.com/donate/
Tags: backstretch, featured image, genesis, studiopress
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 1.1.1
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

This plugin works within the Genesis Framework, to display your featured images in beautiful and dynamic ways.

== Description ==

This plugin takes a different approach to how we use and display featured images for posts and pages. Instead of simply reusing an image which already exists in the post/page content, the plugin anticipates that you will want to use lovely large images for your featured images, but to do so intelligently. Depending on what you upload, the plugin will:

* display the image as a _backstretch_ (screen width) image if the image is wider than your site's Large Media Setting.
* display the image above your post/page content, centered and up to the width of the content, if your image is larger than your Medium Media Setting, and less than or equal to your Large Media Setting.
* display _nothing_ if your featured image width is less than or equal to your Medium Media Setting.
* display _nothing_ if your featured image is already displayed in your content (the original image, not a resized version).

_Note: This plugin works with the Genesis Framework and child themes only._

== Installation ==

1. Upload the entire `display-featured-image-genesis` folder to your `/wp-content/plugins` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally, visit the Settings > Media page to change the default behavior of the plugin.

== Frequently Asked Questions ==

= How do I stop the featured image action from showing on my custom post types? =

You'll want to add a filter to your theme (functions.php file). Here's an example:

	add_filter( 'display_featured_image_genesis_skipped_posttypes', 'rgc_skip_post_types' );
	function rgc_skip_post_types( $post_types ) {
		$post_types[] = 'listing';
		$post_types[] = 'staff';

		return $post_types;
	}

= The backstretch image is a little too tall. =

If you do not want the height of the backstretch image to be quite the height of the user's window, you can reduce it by just a hair. Go to Settings > Media and change the 'Reduction amount' number from the default of 0. The higher this number is, the shorter your image will be. Feel free to experiment, as no images are harmed by changing this number.

Additionally/alternatively, you could set a max-height for the backstretch image via css:


	.backstretch {
		max-height: 700px;
	}


== Screenshots ==
1. Screenshot of a page using the Backstretch Featured Image

== Upgrade Notice ==
= 1.1.1 =
corrected XHTML hooks
== Changelog ==

= 1.1.1 =
* corrected XHTML hooks

= 1.1.0 =
* added a setting in the admin to optionally reduce the height of the backstretch image (eg. due to header height issues)
* refactoring
* wp.org release

= 1.0.1 =
* added the filter for certain post types, and optional filter for other custom post types

= 1.0.0 =
* Initial release on Github