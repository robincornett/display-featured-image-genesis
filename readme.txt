=== Display Featured Image for Genesis ===

Contributors: littler.chicken
Donate link: https://robincornett.com/donate/
Tags: backstretch, featured image, featured images, genesis, studiopress, post thumbnails, featured image rss, rss
Requires at least: 3.8
Tested up to: 4.3
Stable tag: 2.3.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

This plugin works within the Genesis Framework, to display featured images in beautiful and dynamic ways.

== Description ==

This plugin takes a different approach to how we use and display featured images for posts and pages. Instead of simply reusing an image which already exists in the post/page content, the plugin anticipates that you will want to use lovely large images for your featured images, but to do so intelligently. Depending on what you upload, the plugin will:

* display the image as a _backstretch_ (screen width) image if the image is wider than your site's Large Media Setting.
* display the image above your post/page content, centered and up to the width of the content, if your image is larger than your Medium Media Setting, and less than or equal to your Large Media Setting.
* display _nothing_ if your featured image width is less than or equal to your Medium Media Setting.
* display a _default featured image_ as a backstretch image if one is uploaded.

More words at [my site](http://robincornett.com/plugins/display-featured-image-genesis/).

_Note: although this plugin requires the [Genesis Framework by StudioPress](http://studiopress.com/) or child themes, it is not an official plugin for this framework and is neither endorsed nor supported by StudioPress._

= An Image for Every Page =

__Display Featured Image for Genesis__ now allows you to select a default, or fallback, Featured Image, which will be used if a post/page does not have a Featured Image set, or if the post/page's Featured Image is too small (smaller than your medium image setting), and on archive and taxonomy pages. You may set the Default Featured Image under Appearance > Display Featured Image Settings.

As of version 2.0.0, you can now set a Featured Image for each term within a taxonomy (categories, tags, and any taxonomy for custom post types). This image will be used on taxonomy archives, and as a fallback image for posts within that taxonomy if no featured image exists (or if the featured image is too small). If a post is assigned to multiple terms and has no featured image of its own, the most used term which has a featured image assigned will be the one used.

If your site uses Custom Post Types, you can set a Featured Image for each Post Type on the main Display Featured Image for Genesis settings page. If your single post within this type does not have a featured image, the Post Type Featured Image will be used as a fallback.

= Add Your Featured Image to Your RSS Feed =

Now you can add the Featured Image from each post to your RSS feed. This is an optional setting and applied intelligently:

* if your feed is set to output the full text, the Featured Image will be added to the beginning of your post content as a full width image.
* if your feed is set to output only the summary of your content, the Featured image will be added to the beginning of your summary as a thumbnail, aligned to the left.

You can check/change your feed settings on your site's Settings > Reading page.

_If you are already inserting your Featured Image into your feed through another function or plugin, you'll want to remove that before activating this feature; otherwise you will have two copies of the image added to your feed! If you are using Send Images to RSS, don't worry about it. I've made sure these two plugins coexist happily._

= Simple Styling =

__Display Featured Image for Genesis__ has some styling built in but I have intentionally tried to keep it minimal. All styling is for the backstretch image options, as the large options seem pretty straightforward. Stying for titles are largely inherited from your theme--for example, the title will use the same size and font for your page titles, whether you are using a Featured Image or not. Some styles you can incorporate into your own theme:

* `.has-leader` applies to any page using a leader/backstretch image. Applies to the whole page.
* `.big-leader` the container which holds the leader/backstretch image and the post/page Title and excerpt or description.
* `.featured-image-overlay` style appended to the post/page title if Move Excerpts option _is not_ selected (default).
* `.excerpt` (for single posts/pages) and `.archive-description` (for archives) are styled as a unit. These are the containers for the post/page/archive/taxonomy title and description if the Move Excerpts option _is_ selected.
* `.featured` is appended to the large image output directly above the post/page content.

== Installation ==

1. Upload the entire `display-featured-image-genesis` folder to your `/wp-content/plugins` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally, visit the Settings > Media page to change the default behavior of the plugin.

== Frequently Asked Questions ==

= Does this work with any Genesis child theme? =

Yes and no. Technically, it does, even older (XHTML) themes. However, depending on other factors such as the individual theme's styling and layout, the output may be unexpected, and require some tweaking. Not recommended for themes such as Sixteen Nine Pro, or The 411 Pro due to layout, and not for Ambiance Pro or Minimum Pro without changing some theme functionality.

= How can I change how the plugin works? =

There are several filters built into Display Featured Image for Genesis, to give developers more control over the output. Several of them are very similar, and are applied in a specific order, so an earlier filter will take precedence over a later one.

Available filters include, but are not limited to:

* `display_featured_image_genesis_skipped_posttypes`: select post type(s) which will not have the featured image effect applied
* `display_featured_image_genesis_use_default`: force post type(s) to use your sitewide default image (set on the main plugin settings page) for the featured image effect, regardless of what is set as the individual post's featured image
* `displayfeaturedimagegenesis_use_post_type_image`: force post type(s) to use the image assigned as the custom post type featured image (if one is set), regardless of what is set as the individual post's featured image
* `display_featured_image_genesis_use_taxonomy`: force post type(s) to use a taxonomy term's image (if one is set) for the featured image effect, regardless of what is set as the individual post's featured image
* `display_featured_image_genesis_use_large_image`: force post type(s) to output the featured image as a large image above the post content, and to not use the backstretch effect at all
* `display_featured_image_genesis_omit_excerpt`: force post type(s) to not move the excerpt to overlay the featured image, even if the "Move Excerpts/Archive Descriptions" setting is selected

These filters all work the same way, so using any one in your theme will all follow the same pattern. For example, to prevent the featured image effect on the `listing` or `staff` post types, you would add the following to your theme's functions.php file:

	add_filter( 'display_featured_image_genesis_skipped_posttypes', 'rgc_skip_post_types' );
	function rgc_skip_post_types( $post_types ) {
		$post_types[] = 'listing';
		$post_types[] = 'staff';

		return $post_types;
	}

To force a post type to use the sitewide Featured Image, use this filter instead:

	add_filter( 'display_featured_image_genesis_use_default', 'rgc_force_default_image' );
	function rgc_force_default_image( $post_types ) {
		$post_types[] = 'post';

		return $post_types;
	}

Alternatively, you can also set a specific post type to use the taxonomy featured image, if one exists, even if the post type has its own Featured Image:

	add_filter( 'display_featured_image_genesis_use_taxonomy', 'rgc_use_tax_image' );
	function rgc_use_tax_image( $post_types ) {
		$post_types[] = 'post';

		return $post_types;
	}

If a post has no featured image of its own, and is assigned to multiple taxonomy terms which do have images assigned, the plugin will opt to use the featured image from the most popular term (the one with the most posts already).

It seems that you can also include [conditional tags](http://codex.wordpress.org/Conditional_Tags) in the above, eg `$post_types[] = is_post_type_archive();`.

= The backstretch image takes up too much room on the screen. =

If you do not want the height of the backstretch image to be quite the height of the user's browser window, which is the standard, you can reduce it by just a hair. Go to Appearance > Display Featured Image Settings and change the 'Height' number from the default of 0. The higher this number is, the shorter the window will be calculated to be. Feel free to experiment, as no images are harmed by changing this number.

_Note:_ **Display Featured Image for Genesis** determines the size of your backstretch image based on the size of the user's browser window. Changing the "Height/Pixels to Remove" setting tells the plugin to subtract that number of pixels from the measured height of the user's window, regardless of the size of that window, which is partly why you cannot set this to more than 400.

If you need to control the size of the backstretch Featured Image output with more attention to the user's screen size, you will want to consider a CSS approach instead.

	.big-leader {
		max-height: 700px;
	}

	@media only screen and (max-width: 800px) {

		.big-leader {
			max-height: 300px;
		}

	}

= My (large) Featured Image is above my post/page title, and I want it to show below it instead. =

There is a filter for this, too. By default, the large (as opposed to backstretch) image is added before the Genesis loop, which places it above your post or page title. You can add this filter to your theme's functions.php file to move the image below your post/page title:

	add_filter( 'display_featured_image_genesis_move_large_image', 'rgc_move_image' );
	function rgc_move_image( $hook ) {
		$hook = 'genesis_entry_header';
		return $hook;
	}

_Note:_ because the entry header applies to all posts on a page, such as a blog or archive page, this filter modifies the output only on singular posts.

= If a post does not have a featured image of its own, can the term, post type, or default featured image show in the archives? =

Yes! A helper function exists for this, but only runs if you add it. You can easily do this by adding the following to your theme's functions.php file:

	add_action( 'genesis_before_entry', 'rgc_add_archive_thumbnails' );
	function rgc_add_archive_thumbnails() {
		if ( class_exists( 'Display_Featured_Image_Genesis' ) ) {
			add_action( 'genesis_entry_content', 'display_featured_image_genesis_add_archive_thumbnails', 5 ); // HTML5 themes
			add_action( 'genesis_post_content', 'display_featured_image_genesis_add_archive_thumbnails', 5 ); // XHTML themes
		}
	}

This will follow the settings you choose in the Genesis Theme Settings.

== Screenshots ==
1. Screenshot of a page using the Backstretch Featured Image
2. Set a Default Featured Image on the Appearance > Display Featured Image Settings page.
3. Quickly see the featured image assigned to each post or term.

== Upgrade Notice ==
= 2.3.0 =
featured images for authors, yay!

== Changelog ==

= 2.3.0 =
* new: set a featured image for each author!
* new: load smaller images on smaller screens!
* added settings page link to plugin table
* refactored settings page

= 2.2.2 =
* fixed default image id function error

= 2.2.1 =
* fixed fallback filters
* escaped even more things

= 2.2.0 =
* default, term, and custom post type featured images are now stored in the database by ID, rather than URL.
* added filters for backstretch image output, RSS excerpt image output
* added setting for page 2+ of archives (fixed output)

= 2.1.0 =
* added helper functions for term/custom post type images
* added HTML5 headline support
* added lots of filters: for large image output, descriptions, titles, body classes, backstretch image settings
* fixed image assignment process to correctly handle term, post type featured images as intermediate fallback images
* bugfix: corrected output if term image has been deleted

= 2.0.0 =
* added featured images to taxonomy terms!
* added featured images to custom post type archive pages!
* added featured image previews to the admin!
* added new widgets for featured taxonomy terms and custom post type archives
* added new setting to not move post titles to overlay Featured Image
* added filters to force plugin to use taxonomy term images, or output only large images

= 1.5.0 =
* added new setting to include Featured Image in RSS feeds
* added fallback image output if js is disabled
* fixed output if user is using Photon (the Jetpack module)
* fixed output for large image (not backstretch)

= 1.4.3 =
* better decision making process for firing up scripts/styles
* moved scripts to footer
* set plugin version to be used for scripts/style versions
* bugfix: now we play nice with silly Jetpack Photon

= 1.4.2 =
* bugfix: titles fixed for Genesis Blog Template

= 1.4.1 =
* bugfix: correctly added post type support for excerpts to pages

= 1.4.0 =
* all settings updated for bloat and moved to a new submenu page under Appearance
* efficiency in descriptions, output, and variables

= 1.3.0 =
* optional taxonomy/author/CPT headline now shows over leader image
* optional taxonomy/author/CPT description and single post excerpt display optionally over leader image as well

= 1.2.2 =
* default image validation

= 1.2.1 =
* moved default image from Customizer to Media Settings page
* new filter for forcing default image for any post type
* common class

= 1.2.0 =
* new feature: default featured image to display if no image is set
* better method naming/organization

= 1.1.3 =
* output is now properly managed to show only on single posts/pages and home page, not archives

= 1.1.2 =
* proper deactivation if Genesis isn't the active theme.

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
