# Display Featured Image for Genesis

This plugin works within the Genesis Framework, to display featured images in new and fun ways. It should work with either HTML5 or XHTML themes, but older themes may have a width set on elements which may not allow the full backstretch experience.

## Description

This plugin takes a different approach to how we use and display featured images for posts and pages. Instead of simply reusing an image which already exists in the post/page content, the plugin anticipates that you will want to use lovely large images for your featured images, but to do so intelligently. Depending on what you upload, the plugin will:

* display the image as a _backstretch_ (screen width) image if the image is wider than your site's Large Media Setting.
* display the image above your post/page content, centered and up to the width of the content, if your image is larger than your Medium Media Setting, and less than or equal to your Large Media Setting.
* display _nothing_ if your featured image width is less than or equal to your Medium Media Setting.
* display _nothing_ if your featured image is already displayed in your content (the original image, not a resized version).
* display a _default featured image_ as a backstretch image if one is uploaded.

More words at [my site](http://robincornett.com/downloads/display-featured-image-genesis/).

_Note: although this plugin requires the [Genesis Framework by StudioPress](http://studiopress.com/) or child themes, it is not an official plugin for this framework and is neither endorsed nor supported by StudioPress._

#### An Image for Every Page

__Display Featured Image for Genesis__ allows you to select a default, or fallback, Featured Image, which will be used if a post/page does not have a Featured Image set, or if the post/page's Featured Image is too small (smaller than your medium image setting), and on archive pages. You may set the Default Featured Image under Appearance > Display Featured Image Settings.

You may set a Featured Image for each term within a taxonomy (categories, tags, and any taxonomy for custom post types). This image will be used on taxonomy archives, and as a fallback image for posts within that taxonomy if no featured image exists (or if the featured image is too small). If a post is assigned to multiple terms and has no featured image of its own, the most used term which has a featured image assigned will be the one used.

If your site uses Custom Post Types, you can set a Featured Image for each Post Type on the main Display Featured Image for Genesis settings page. If your single post within this type does not have a featured image, the Post Type Featured Image will be used as a fallback.

#### Add Your Featured Image to Your RSS Feed

Now you can add the Featured Image from each post to your RSS feed. This is an optional setting and applied intelligently:

* if your feed is set to output the full text, the Featured Image will be added to the beginning of your post content as a full width image.
* if your feed is set to output only the summary of your content, the Featured image will be added to the beginning of your summary as a thumbnail, aligned to the left.

You can check/change your feed settings on your site's Settings > Reading page.

_If you are already inserting your Featured Image into your feed through another function or plugin, you'll want to remove that before activating this feature; otherwise you will have two copies of the image added to your feed! If you are using Send Images to RSS, don't worry about it. I've made sure these two plugins coexist happily._

#### Simple Styling

__Display Featured Image for Genesis__ has some styling built in but I have intentionally tried to keep it minimal. All styling is for the backstretch image options, as the large options seem pretty straightforward. Stying for titles are largely inherited from your theme--for example, the title will use the same size and font for your page titles, whether you are using a Featured Image or not. Some styles you can incorporate into your own theme:

* `.has-leader` applies to any page using a leader/backstretch image. Applies to the whole page.
* `.big-leader` the container which holds the leader/backstretch image and the post/page Title and excerpt or description.
* `.featured-image-overlay` style appended to the post/page title if Move Excerpts option _is not_ selected (default).
* `.excerpt` (for single posts/pages) and `.archive-description` (for archives) are styled as a unit. These are the containers for the post/page/archive/taxonomy title and description if the Move Excerpts option _is_ selected.
* `.featured` is appended to the large image output directly above the post/page content.

## Requirements
* WordPress 4.1, tested up to 4.5
* the Genesis Framework

## Installation

### Upload

1. Download the latest tagged archive (choose the "zip" option).
2. Go to the __Plugins -> Add New__ screen and click the __Upload__ tab.
3. Upload the zipped archive directly.
4. Go to the Plugins screen and click __Activate__.

### Manual

1. Download the latest tagged archive (choose the "zip" option).
2. Unzip the archive.
3. Copy the folder to your `/wp-content/plugins/` directory.
4. Go to the Plugins screen and click __Activate__.

Check out the Codex for more information about [installing plugins manually](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

### Git

Using git, browse to your `/wp-content/plugins/` directory and clone this repository:

`git clone git@github.com:robincornett/display-featured-image-genesis.git`

Then go to your Plugins screen and click __Activate__.

## Screenshots
![Screenshot of a page using the Backstretch Featured Image](https://github.com/robincornett/display-featured-image-genesis/blob/develop/assets/screenshot-1.jpg)  
_Screenshot of a page using the Backstretch Featured Image._

![Set a Default Featured Image on the Appearance > Display Featured Image for Genesis settings page.](https://github.com/robincornett/display-featured-image-genesis/blob/develop/assets/screenshot-2.jpg)  
_Set a Default Featured Image on the Appearance > Display Featured Image for Genesis settings page._

![Optionally, set featured images for custom content types, or change plugin behavior for custom content types.](https://github.com/robincornett/display-featured-image-genesis/blob/develop/assets/screenshot-3.jpg)
_Optionally, set featured images for custom content types, or change plugin behavior for custom content types._

![Quickly see which posts and terms have been assigned a Featured Image.](https://github.com/robincornett/display-featured-image-genesis/blob/develop/assets/screenshot-4.png)  
_Quickly see which posts and terms have been assigned a Featured Image._

## Frequently Asked Questions

### What is term metadata and why does it matter to me? ###

*Update for version 2.5:* Genesis 2.3, when it is released, will change how term archive headlines/descriptions will be pulled from the database. __Display Featured Image for Genesis__ has been using the old, inefficient method for getting the Genesis term information, which will no longer be supported in Genesis 2.3. Version 2.5 will use the new, better method to retrieve the Genesis term metadata (for archive headlines and intro text). Please make sure that your plugin is up to date so that you do not get unexpected behavior. (see [StudioPress](http://www.studiopress.com/important-announcement-for-genesis-plugin-developers/) for more information)

Term metadata is a new feature introduced in WordPress 4.4, which allows us to add custom data to each term (categories, tags, etc.) on a site. Version 2.4 of __Display Featured Image for Genesis__ will use the new term metadata.

If you have been using __Display Featured Image for Genesis__ and have already added featured images to your terms, when you visit the main plugin settings page, you'll be prompted to allow the plugin to update all terms with featured images, or given the information to allow you to do it yourself. This _should_ be a simple, pain-free process, but make sure your database is backed up, and please check your terms after the update.

If you have not yet updated your site to WordPress 4.4, fear not: for the time being, __Display Featured Image for Genesis__ will still work just as it has in the past. A future release of the plugin will require a minimum version of WordPress to properly support term images.

### Where do I set a Default Featured Image?

Display Featured Image for Genesis has its own settings page, under the main Appearance menu.

### Does this work with any Genesis child theme?

Yes and no. Technically, it does, even older (XHTML) themes. However, depending on other factors such as the individual theme's styling and layout, the output may be unexpected, and require some tweaking. Not recommended for themes such as Sixteen Nine Pro, or The 411 Pro due to layout, and not for Ambiance Pro or Minimum Pro without changing some theme functionality.

### How can I change how the plugin works?
*Update for version 2.5:* quite a few new settings have been added to the plugin, some of which make options available which were previously limited to these filters.

There are several filters built into Display Featured Image for Genesis, to give developers more control over the output. Several of them are very similar, and are applied in a specific order, so an earlier filter will take precedence over a later one.

Available filters include, but are not limited to:

* `display_featured_image_genesis_skipped_posttypes`: select post type(s) which will not have the featured image effect applied __(Note: this filter still totally works, but there is now a setting to handle this. It's on the Content Types tab.)__
* `display_featured_image_genesis_use_default`: force post type(s) to use your sitewide default image (set on the main plugin settings page) for the featured image effect, regardless of what is set as the individual post's featured image
* `displayfeaturedimagegenesis_use_post_type_image`: force post type(s) to use the image assigned (if one is set) as the custom post type featured image, regardless of what is set as the individual post's featured image
* `display_featured_image_genesis_use_taxonomy`: force post type(s) to use a taxonomy term's image (if one is set) for the featured image effect, regardless of what is set as the individual post's featured image
__Note: as of version 2.5, you can set any post type to use a fallback image without using one of the above filters. It will use the images in this order as they exist: term, content type, default.__
* `display_featured_image_genesis_use_large_image`: force post type(s) to output the featured image as a large image above the post content, and to not use the backstretch effect at all
* `display_featured_image_genesis_omit_excerpt`: force post type(s) to not move the excerpt to overlay the featured image, even if the "Move Excerpts/Archive Descriptions" setting is selected

These filters all work the same way, so using any one in your theme will all follow the same pattern. For example, to prevent the featured image effect on the `listing` or `staff` post types, you would add the following to your theme's functions.php file:

```php
add_filter( 'display_featured_image_genesis_skipped_posttypes', 'rgc_skip_post_types' );
function rgc_skip_post_types( $post_types ) {
	$post_types[] = 'listing';
	$post_types[] = 'staff';

	return $post_types;
}
```

To force a post type to use the sitewide Featured Image, use this filter instead:

```php
add_filter( 'display_featured_image_genesis_use_default', 'rgc_force_default_image' );
function rgc_force_default_image( $post_types ) {
	$post_types[] = 'post';

	return $post_types;
}
```

Alternatively, you can also set a specific post type to use the taxonomy term featured image, if one exists, even if the post type has its own Featured Image:

```php
add_filter( 'display_featured_image_genesis_use_taxonomy', 'rgc_use_tax_image' );
function rgc_use_tax_image( $post_types ) {
	$post_types[] = 'post';

	return $post_types;
}
```

If a post has no featured image of its own, and is assigned to multiple taxonomy terms which do have images assigned, the plugin will opt to use the featured image from the most popular term (the one with the most posts already).

It seems that you can also include [conditional tags](http://codex.wordpress.org/Conditional_Tags) in the above, eg `$post_types[] = is_post_type_archive();`.

### The backstretch image takes up too much room on the screen.

If you do not want the height of the backstretch image to be quite the height of the user's browser window, which is the standard, you can reduce it by just a hair. Go to Appearance > Display Featured Image Settings and change the 'Height' number from the default of 0. The higher this number is, the shorter the window will be calculated to be. Feel free to experiment, as no images are harmed by changing this number.

_Note:_ __Display Featured Image for Genesis__ determines the size of your backstretch image based on the size of the user's browser window. Changing the "Height/Pixels to Remove" setting tells the plugin to subtract that number of pixels from the measured height of the user's window, regardless of the size of that window.

If you need to control the size of the backstretch Featured Image output with more attention to the user's screen size, you will want to consider a CSS approach instead.

```css
.big-leader {
	max-height: 700px;
}

@media only screen and (max-width: 800px) {

	.big-leader {
		max-height: 300px;
	}

}
```

### My (large) Featured Image is above my post/page title, and I want it to show below it instead.

There is a filter for this, too. By default, the large (as opposed to backstretch) image is added before the Genesis loop, which places it above your post or page title. You can add this filter to your theme's functions.php file to move the image below your post/page title:

```php
add_filter( 'display_featured_image_genesis_move_large_image', 'prefix_move_image' );
function prefix_move_image( $hook ) {
	return 'genesis_entry_header';
}
```

_Note:_ because the entry header applies to all posts on a page, on archive pages, this filter will be overridden with the default `genesis_before_loop`. To move the large image on an archive page, do not use a hook related to a single post.

Similar hooks:

* `display_featured_image_genesis_move_large_image_priority`: change the priority of the large featured image output
* `display_featured_image_move_backstretch_image`: change the hook of the backstretch featured image output
* `display_featured_image_move_backstretch_image_priority`: change the priority of the backstretch featured image output

### If a post does not have a featured image of its own, can the term, post type, or default featured image show in the archives?

Yes! This is a new setting, added in version 2.5. Please see the plugin settings page. If you were using the old method (`display_featured_image_genesis_add_archive_thumbnails`) to do this, the plugin will attempt to remove that from your output, but you may want to double check your archives.

This will follow the settings you choose in the Genesis Theme Settings.

### I'd like for the front page title to still show on top of the featured image.

Sure thing, if your front page is set to be a static front page. Just add:

```php
add_filter( 'display_featured_image_genesis_excerpt_show_front_page_title', '__return_true' );
```

to a convenient location, such as your functions.php file. Otherwise, the page title will not display on the front page of your site.

## Credits

* Built by [Robin Cornett](http://robincornett.com/)

## Changelog

### 2.5.1
* enhancement: large image can now be moved on archive pages
* bugfix: array filter has been reset to less strict mode

### 2.5.0
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

### 2.4.1 - 2015-12-16
* bugfix: correctly retrieves posts page image as fallback for single posts
* bugfix: medium image size comparison consistent throughout plugin

### 2.4.0 - 2015-12-11
* Now supports term metadata, added in WordPress 4.4. All new featured images for terms will be added to the termmeta table instead of wp_options. Old term images can be converted from the settings page.
* improved: alternate sources for backstretch image on smaller screens
* changed: generic functions have all been moved to a helper class for optimization.
* bugfix: home/posts page no longer uses latest post's featured image

### 2.3.4 - 2015-11-13
* added: filters to modify image priority as well as hook (due to Workstation Pro theme)
* improved: checks for ability to output and what to output
* bugfix: fix variables passed to javascript (centering, fade)
* bugfix: update image ID database query (backwards compat)

### 2.3.3 - 2015-09-08
* bugfix: corrected logic on title/excerpt output for front/posts page
* fix duplicate posts page title due to new function introduced in Genesis 2.2.1, for sites which support genesis-accessibility

### 2.3.2 - 2015-09-02
* bugfix: invalid images on settings page are again set to most recent setting, rather than removed
* bugfix: output in IE (props Ryan Townley)
* bugfix: added title back to blog template pages due to accessbility changes in Genesis 2.2
* bugfix: featured author widget output for pre-2.2 Genesis installs

### 2.3.1 - 2015-08-31
* bugfix: no longer removes titles on pages using the Genesis blog template.
* sanity check: cleaned up code redundancies and confusions.

### 2.3.0 - 2015-08-17
* new: set a featured image for each author!
* new: load smaller images on smaller screens!
* added hooks to title output over backstretch images
* added settings page link to plugin table
* refactored settings page
* bugfix: admin column output

### 2.2.2 - 2015-05-09
* fixed default image id function error

### 2.2.1 - 2015-05-08
* fixed fallback filters
* escaped even more things

### 2.2.0 - 2015-04-20
* default, term, and custom post type featured images are now stored in the database by ID, rather than URL.
* added filters for backstretch image output, RSS excerpt image output
* added setting for page 2+ of archives (fixed output)

### 2.1.0 - 2015-03-05
* added helper functions for term/custom post type images
* added HTML5 headline support
* added lots of filters: for large image output, descriptions, titles, body classes, backstretch image settings
* fixed image assignment process to correctly handle term, post type featured images as intermediate fallback images
* bugfix: corrected output if term image has been deleted

### 2.0.0 - 2015-02-03
* added featured images to taxonomy terms!
* added featured images to custom post type archive pages!
* added featured image previews to the admin!
* added new widgets for featured taxonomy terms and custom post type archives
* added new setting to not move post titles to overlay Featured Image
* added filters to force plugin to use taxonomy term images, or output only large images

### 1.5.0 - 2014-12-13
* added new setting to include Featured Image in RSS feeds
* added fallback image output if js is disabled
* fixed output if user is using Photon (the Jetpack module)
* fixed output for large image (not backstretch)

### 1.4.3 - 2014-11-18
* better decision making process for firing up scripts/styles
* moved scripts to footer
* set plugin version to be used for scripts/style versions
* bugfix: now we play nice with silly Jetpack Photon

### 1.4.2
* bugfix: titles fixed for Genesis Blog Template

### 1.4.1
* bugfix: correctly added post type support for excerpts to pages
* simplified deactivation/translation

### 1.4.0
* all settings updated for bloat and moved to a new submenu page under Appearance
* efficiency in descriptions, output, and variables

### 1.3.0
* optional taxonomy/author/CPT headline now shows over leader image
* optional taxonomy/author/CPT description and single post excerpt display optionally over leader image as well

### 1.2.2
* default image validation

### 1.2.1
* moved default image from Customizer to Media Settings page
* new filter for forcing default image for any post type
* common class

### 1.2.0
* new feature: default featured image to display if no image is set
* better method naming/organization

### 1.1.3
* output is now properly managed to show only on single posts/pages and home page, not archives

### 1.1.2
* plugin properly deactivates if Genesis isn't running

### 1.1.1
* corrected XHTML hooks

### 1.1.0
* added a setting in the admin to optionally reduce the height of the backstretch image
* refactoring

### 1.0.1
* added the filter for certain post types, and optional filter for other custom post types

### 1.0.0
* Initial release on Github
