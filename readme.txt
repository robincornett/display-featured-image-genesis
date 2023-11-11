=== Display Featured Image for Genesis ===

Contributors: littler.chicken
Donate link: https://robincornett.com/donate/
Tags: banner, featured image, featured images, genesis, studiopress, post thumbnails, featured image rss, rss
Requires at least: 5.2
Tested up to: 6.4
Stable tag: 3.2.3
Requires PHP: 7.4
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

This plugin works within the Genesis Framework, to display featured images in beautiful and dynamic ways.

== Description ==

This plugin takes a different approach to how we use and display featured images for posts and pages. Instead of simply reusing an image which already exists in the post/page content, the plugin anticipates that you will want to use lovely large images for your featured images, but to do so intelligently. Depending on what you upload, the plugin will:

* display the image as a _banner_ (screen width) image if the image is wider than your site's Large Media Setting.
* display the image above your post/page content, centered and up to the width of the content, if your image is larger than your Medium Media Setting, and less than or equal to your Large Media Setting.
* display _nothing_ if your featured image width is less than or equal to your Medium Media Setting.
* display a _default featured image_ as a banner image if one is uploaded.

More words at [my site](https://robincornett.com/downloads/display-featured-image-genesis/).

_Note: although this plugin requires the [Genesis Framework by StudioPress](https://studiopress.com/) or child themes, it is not an official plugin for this framework and is neither endorsed nor supported by StudioPress._

= An Image for Every Page =

__Display Featured Image for Genesis__ allows you to select a default, or fallback, Featured Image, which will be used if a post/page does not have a Featured Image set, or if the post/page's Featured Image is too small (smaller than your medium image setting), and on archive and taxonomy pages. You may set the Default Featured Image under Appearance > Display Featured Image Settings.

You may set a Featured Image for each term within a taxonomy (categories, tags, and any taxonomy for custom post types). This image will be used on taxonomy archives, and as a fallback image for posts within that taxonomy if no featured image exists (or if the featured image is too small). If a post is assigned to multiple terms and has no featured image of its own, the most used term which has a featured image assigned will be the one used.

If your site uses Custom Post Types, you can set a Featured Image for each Post Type on the main Display Featured Image for Genesis settings page. If your single post within this type does not have a featured image, the Post Type Featured Image will be used as a fallback.

= Add Your Featured Image to Your RSS Feed =

Now you can add the Featured Image from each post to your RSS feed. This is an optional setting and applied intelligently:

* if your feed is set to output the full text, the Featured Image will be added to the beginning of your post content as a full width image.
* if your feed is set to output only the summary of your content, the Featured image will be added to the beginning of your summary as a thumbnail, aligned to the left.

You can check/change your feed settings on your site's Settings > Reading page.

_If you are already inserting your Featured Image into your feed through another function or plugin, you'll want to remove that before activating this feature; otherwise you will have two copies of the image added to your feed! If you are using Send Images to RSS, don't worry about it. I've made sure these two plugins coexist happily._

= Simple Styling =

__Display Featured Image for Genesis__ has some styling built in but I have intentionally tried to keep it minimal. All styling is for the banner image options, as the large options seem pretty straightforward. Stying for titles are largely inherited from your theme--for example, the title will use the same size and font for your page titles, whether you are using a Featured Image or not. Some styles you can incorporate into your own theme:

* `.has-leader` applies to any page using a leader/banner image. Applies to the whole page.
* `.big-leader` the container which holds the leader/banner image and the post/page Title and excerpt or description.
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

= I'm not a huge fan of adding more JavaScript to my website. =

As of version 3.1.0, you can choose to display even banner images completely with WordPress' native responsive images and CSS. No JavaScript required. Just visit the settings page (the Banner Output tab) and check the "Disable JavaScript" option. If you have previously used the banner (backstretch) featured image, you may notice that the output is slightly different, but it should be very close to the same, and easier to override with pure CSS if you need to.

= I switched to the scriptless banner image option and the output is more than a little different. What happened? =

Generally, the banner images will display more or less the same whether you choose to use the JavaScript version or not. Where you may experience a significant difference is if you have the following setup:

* your theme CSS includes a max-height for the `.big-leader` element which is significantly different than the screen height (at least sometimes)
* your plugin settings leave the max-height for the banner image empty

The scriptless banner image position will ignore the parent container max-height. If you set the Maximum Height in the plugin, the new rule is added to the `.big-leader__image` instead of the `.big-leader` container. So if your theme is set up this way but you want to switch to the scriptless banner image, you can either enter the max-height number on the plugin settings options, or you can add the same max-height rule to your CSS, but on the `.big-leader__image` element in addition to the `.big-leader`. The plugin will add the rule to both elements if you use the setting.

= Can I add a Display Featured Image widget to my post or page content? =

Yes. As of version 3.2.0, blocks have been registered for each widget. Add a featured term, author, or post type block anywhere you can add a block.

Alternatively, but not as nice: shortcodes for each widget include:

* displayfeaturedimagegenesis_author
* displayfeaturedimagegenesis_term
* displayfeaturedimagegenesis_post_type

The parameters/attributes for these mirror the widget options, so you can explore the code (or inspect the widget form) to find the shortcode attributes.

Alternatively, the much easier method entails visiting the settings page (under Appearance) and enabling the shortcode buttons for the post editor. With the shortcode buttons enabled, you can use the familiar widget form to build the shortcode and add it anywhere you like.

= What happened to my default/post type featured image? =

If these images were saved to your database prior to version 2.2.0 of this plugin and you've never updated the plugin settings since then, these images may have effectively disappeared in version 3.0.0. To fix this, visit the plugin settings page, reselect your default/post type image(s), and save.

Prior to version 2.2.0 of the plugin, these images were saved to the database as URL strings, rather than as ID numbers, which was hugely inefficient. This was changed in version 2.2.0, with backwards compatible helper functions to ease the transition, but the helper functions are no longer used as of version 3.0.0.

= How can I change how the plugin works? =

Check the settings page before digging into filters. As of version 3.0.0, most questions/support requests have been implemented as options on the settings pages, including:

* setting a sitewide preferred image size
* setting a preferred image size per content/post type
* setting preferred fallback images for content types, search results, and 404 pages
* changing the default hooks/priorities the plugin uses for image output

Additionally, some of these can be overridden on any individual post, page, or content type, which can be set to use the default image size, not show a featured image at all, or force a large/banner image for that post only.

There are several filters built into Display Featured Image for Genesis, to give developers more control over the output. Several of them are very similar, and are applied in a specific order, so an earlier filter will take precedence over a later one.

Available filters include, but are not limited to:

* `display_featured_image_genesis_skipped_posttypes`: select post type(s) which will not have the featured image effect applied __(Note: this filter still totally works, but there is now a setting to handle this. It's on the Content Types tab.)__
* `display_featured_image_genesis_use_default`: force post type(s) to use your sitewide default image (set on the main plugin settings page) for the featured image effect, regardless of what is set as the individual post's featured image
* `displayfeaturedimagegenesis_use_post_type_image`: force post type(s) to use the image assigned as the custom post type featured image (if one is set), regardless of what is set as the individual post's featured image
* `display_featured_image_genesis_use_taxonomy`: force post type(s) to use a taxonomy term's image (if one is set) for the featured image effect, regardless of what is set as the individual post's featured image
__Note: as of version 2.5, you can set any post type to use a fallback image without using one of the above filters. It will use the images in this order as they exist: term, content type, default.__
* `display_featured_image_genesis_use_large_image`: force post type(s) to output the featured image as a large image above the post content, and to not use the banner effect at all
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

If you're needing to have a little more control than just specifying which post type to skip, and maybe want to use WordPress conditional statements, you'll want a different filter. This example disables the plugin on WooCommerce term archives:

	add_filter( 'displayfeaturedimagegenesis_disable', 'prefix_skip_woo_terms' );
	function prefix_skip_woo_terms( $disable ) {
		if ( 'product' === get_post_type() && is_tax() ) {
			return true;
		}
		return $disable;
	}

= If a post does not have a featured image of its own, can the term, post type, or default featured image show in the archives? =

Please see the plugin settings page. If you were using the old method (`display_featured_image_genesis_add_archive_thumbnails`) to do this, the plugin will attempt to remove that from your output, but you may want to double check your archives.

This will follow the settings you choose in the Genesis Theme Settings.

= The banner image takes up too much room on the screen. =

If you do not want the height of the banner image to be quite the height of the user's browser window, which is the standard, you can reduce it by just a hair. Go to Appearance > Display Featured Image Settings and change the 'Height' number from the default of 0. The higher this number is, the shorter the window will be calculated to be. Feel free to experiment, as no images are harmed by changing this number.

_Note:_ __Display Featured Image for Genesis__ determines the size of your banner image based on the size of the user's browser window. Changing the "Height/Pixels to Remove" setting tells the plugin to subtract that number of pixels from the measured height of the user's window, regardless of the size of that window, which is partly why you cannot set this to more than 400.

If you need to control the size of the banner Featured Image output with more attention to the user's screen size, you will want to consider a CSS approach instead. You can use the plugin's Maximum Height setting, which will affect all screen sizes, or add something like this to your theme's stylesheet, or the additional CSS panel in the Customizer:

	.big-leader,
	.big-leader__image {
		max-height: 700px;
	}

	@media only screen and (max-width: 800px) {

		.big-leader,
		.big-leader__image {
			max-height: 300px;
		}
	}

_Note:_ if your theme has CSS like this in it already, and you change the Maximum Height setting, it will (most likely) override your theme's styling, due to the order in which stylesheets load.

= My (large) Featured Image is above my post/page title, and I want it to show below it instead. =

As of version 3.0.0, you can change the hook/location of the large featured image without code by going to Appearance > Display Featured Image for Genesis, and then the Advanced tab.

There is a filter for this, too. By default, the large (as opposed to banner) image is added before the Genesis loop, which places it above your post or page title. You can add this filter to your theme's functions.php file to move the image below your post/page title:

	add_filter( 'display_featured_image_genesis_move_large_image', 'prefix_move_image' );
	function prefix_move_image( $hook ) {
		return 'genesis_entry_header';
	}

_Note:_ because the entry header applies to all posts on a page, on archive pages, this filter will be overridden with the default `genesis_before_loop`. To move the large image on an archive page, do not use a hook related to a single post.

Similar hooks:

* `display_featured_image_genesis_move_large_image_priority`: change the priority of the large featured image output
* `display_featured_image_move_backstretch_image`: change the hook of the banner featured image output
* `display_featured_image_move_backstretch_image_priority`: change the priority of the banner featured image output

== Screenshots ==
1. Screenshot of a page using the Backstretch Featured Image
2. Set a Default Featured Image on the Appearance > Display Featured Image Settings page.
3. Optionally, set featured images for custom content types, or change plugin behavior for custom content types.
4. Quickly see the featured image assigned to each post or term.

== Upgrade Notice ==

3.2.3: updated for PHP 8 compatibility

== Changelog ==

= 3.2.3 =
* updated: PHP 8 compatibility

= 3.2.2 =
* added: support for webp images
* fixed: user's custom column filter
* fixed: missing label for post meta select input
* updated: tested to version

= 3.2.1 =
* updated: shortcodes library
* fixed: dynamic term selector on widgets page
* fixed: notices in PHP 7.4 if no featured image is set

= 3.2.0 =
* added: blocks for featured term, featured content type, and featured author
* changed: plugin's registered image size is being replaced in 5.3, so new images will be used at that size
* changed: backstretch variables filter now allows for slideshow/slider output
* updated: shortcode/block validation
* updated: new minimum WordPress version is 5.0

= 3.1.2 =
* fixed: posts page checks for title and post meta

= 3.1.1 =
* fix widget output error when multiple instances are called on a page

= 3.1.0 =
* added: option to display the banner image using only CSS and responsive images, instead of JavaScript
* changed: CSS, mostly related to the CSS-only banner image, but also made entry title CSS less specific
* changed: significant code reorganization for improved validation, portablility
* changed: improved the settings/meta image uploader
* changed: improved settings validation
* changed: the plugin now serves minified CSS/JS files

= 3.0.2 =
* fixed: metabox now properly shows in the block editor (WordPress 5.0)
* changed: settings page organization

= 3.0.1 =
* fixed: check for default featured image

= 3.0.0 =
* added: preferred image size (set to backstretch or large for the entire site)
* added: setting to prefer fallback/large images per content type
* added: setting on individual posts/pages to change the image size for each post
* added: advanced settings for changing featured image hooks without code
* added: shortcodes for outputting featured image widgets anywhere
* added: optional media buttons to make shortcode creation easier
* added: optional custom text for content type, term widgets
* added: optional link to term archive on widget
* added: support for Gutenberg editor
* removed: all use of displayfeaturedimagegenesis_check_image_id helper function (images must be saved by ID, not URL, as determined in 2.2.0)
* updated: Backstretch 2.1.16
* improved: widgets have been tidied up and refactored
* improved: settings pages have been tidied up and refactored
* changed: new minimum WP version is 4.4

= 2.6.3 =
* added: filter for term selection for term fallback image
* improved: decision making process to select the appropriate responsive image size
* improved: term fallback image function (due to changes in WP4.8)

= 2.6.2 =
* added: filter to disable responsive images (backstretch)
* added: filter to manage supported taxonomies
* changed: noscript fallback image is now inline, not background
* fixed: entry title output
* fixed: title/description output on subsequent archive pages

= 2.6.1 =
* added: filter to disable plugin output conditionally
* fixed: admin columns display on mobile
* fixed: allow max height field to be empty
* fixed: possible wild database query on plugin settings page
* marked as compatible with 4.6

= 2.6.0 =
* added: backstretch control settings
* added: setting to always use default image
* added: Customizer support for main plugin settings
* added: setting to not move title over image on a per-post basis
* added/fixed: alt attribute and aria value for backstretch featured image
* fixed: aria attribute on widget images
* fixed: media uploader limited to images
* bugfix: nonce output causing some issues in post editor
* bugfix: large image size filter no longer overrides earlier setting

= 2.5.1 =
* enhancement: large image can now be moved on archive pages
* bugfix: array filter has been reset to less strict mode

= 2.5.0 =
* added: filter to modify plugin defaults
* added: setting to disable plugin output on individual posts
* added: setting to disable plugin output on specific content types
* added: setting to use a fallback image on specific content types
* added: custom featured images for search and 404 pages
* added: setting to add fallback images for archive thumbnails
* added: supports new term meta (headlines/intro text) from Genesis
* added: the featured image column is now sortable
* added: filter to check if plugin can do its thing
* added: filter for the title output
* added: filter to change which image size to use
* improved: plugins settings page is now accessible
* bugfix: make sure an appropriately sized image is always used
* bugfix: error on post type archive widget if there is no image
* bugfix: featured image column no longer borks on mobile

= 2.4.1 =
* bugfix: correctly retrieves posts page image as fallback for single posts
* bugfix: medium image size comparison consistent throughout plugin

= 2.4.0 =
* Now supports term metadata, added in WordPress 4.4. All new featured images for terms will be added to the termmeta table instead of wp_options. Old term images can be converted from the settings page.
* improved: alternate sources for backstretch image on smaller screens
* changed: generic functions have all been moved to a helper class for optimization.
* bugfix: home/posts page no longer uses latest post's featured image

= 2.3.4 =
* added: filters to modify image priority as well as hook (due to Workstation Pro theme)
* improved: checks for ability to output and what to output
* bugfix: fix variables passed to javascript (centering, fade)
* bugfix: update image ID database query (backwards compat)

= 2.3.3 =
* bugfix: corrected logic on title/excerpt output for front/posts page
* fix duplicate posts page title due to new function introduced in Genesis 2.2.1, for sites which support genesis-accessibility

= 2.3.2 =
* bugfix: invalid images on settings page are again set to most recent setting, rather than removed
* bugfix: output in IE (props Ryan Townley)
* bugfix: added title back to blog template pages due to accessbility changes in Genesis 2.2
* bugfix: featured author widget output for pre-2.2 Genesis installs

= 2.3.1 =
* bugfix: no longer removes titles on pages using the Genesis blog template.
* sanity check: cleaned up code redundancies and confusions.

= 2.3.0 =
* new: set a featured image for each author!
* new: load smaller images on smaller screens!
* added hooks to title output over backstretch images
* added settings page link to plugin table
* refactored settings page
* bugfix: admin column output

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
