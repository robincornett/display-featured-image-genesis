# Display Featured Image for Genesis

This plugin works within the Genesis Framework, to display featured images in new and fun ways. It should work with either HTML5 or XHTML themes, but older themes may have a width set on elements which may not allow the full backstretch experience.

## Description

This plugin takes a different approach to how we use and display featured images for posts and pages. Instead of simply reusing an image which already exists in the post/page content, the plugin anticipates that you will want to use lovely large images for your featured images, but to do so intelligently. Depending on what you upload, the plugin will:

* display the image as a _backstretch_ (screen width) image if the image is wider than your site's Large Media Setting.
* display the image above your post/page content, centered and up to the width of the content, if your image is larger than your Medium Media Setting, and less than or equal to your Large Media Setting.
* display _nothing_ if your featured image width is less than or equal to your Medium Media Setting.
* display _nothing_ if your featured image is already displayed in your content (the original image, not a resized version).
* display a _default featured image_ as a backstretch image if one is uploaded.

More words at [my site](http://robincornett.com/plugins/display-featured-image-genesis/).

_Note: although this plugin requires the [Genesis Framework by StudioPress](http://studiopress.com/) or child themes, it is not an official plugin for this framework and is neither endorsed nor supported by StudioPress._

#### An Image for Every Page

__Display Featured Image for Genesis__ now allows you to select a default, or fallback, Featured Image, which will be used if a post/page does not have a Featured Image set, or if the post/page's Featured Image is too small (smaller than your medium image setting), and on archive pages. You may set the Default Featured Image under Appearance > Display Featured Image Settings.

As of version x.y.z, you can now set a Featured Image for each taxonomy (categories, tags, and any taxonomy for custom post types). This image will be used on taxonomy archives, and as a fallback image for posts within that taxonomy if no featured image exists (or if the featured image is too small). If a post is assigned to multiple terms, the most used term will be the one used.

#### Add Your Featured Image to Your RSS Feed

Now you can add the Featured Image from each post to your RSS feed. This is an optional setting and applied intelligently:

* if your feed is set to output the full text, the Featured Image will be added to the beginning of your post content as a full width image.
* if your feed is set to output only the summary of your content, the Featured image will be added to the beginning of your summary as a thumbnail, aligned to the left.

You can check/change your feed settings on your site's Settings > Reading page.

_If you are already inserting your Featured Image into your feed through another function or plugin, you'll want to remove that before activating this feature; otherwise you will have two copies of the image added to your feed!_

#### Simple Styling

__Display Featured Image for Genesis__ has some styling built in but I have intentionally tried to keep it minimal. All styling is for the backstretch image options, as the large options seem pretty straightforward. Stying for titles are largely inherited from your theme--for example, the title will use the same size and font for your page titles, whether you are using a Featured Image or not. Some styles you can incorporate into your own theme:

* `.has-leader` applies to any page using a leader/backstretch image. Applies to the whole page.
* `.big-leader` the container which holds the leader/backstretch image and the post/page Title and excerpt or description.
* `.featured-image-overlay` style appended to the post/page title if Move Excerpts option _is not_ selected (default).
* `.excerpt` (for single posts/pages) and `.archive-description` (for archives) are styled as a unit. These are the containers for the post/page/archive/taxonomy title and description if the Move Excerpts option _is_ selected.
* `.featured` is appended to the large image output directly above the post/page content.

## Requirements
* WordPress 3.8, tested up to 4.1
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

## Frequently Asked Questions

### Where do I set a Default Featured Image?

Display Featured Image for Genesis has its own settings page, under the main Appearance menu.

### Does this work with any Genesis child theme?

Yes and no. Technically, it does, even older (XHTML) themes. However, depending on other factors such as the individual theme's styling and layout. Not recommended for themes such as Sixteen Nine Pro, or The 411 Pro due to layout, and not for Ambiance Pro or Minimum Pro without changing some theme functionality.

### How do I stop the featured image action from showing on my custom post types?

You'll want to add a filter to your theme (functions.php file). Here's an example:

```php
add_filter( 'display_featured_image_genesis_skipped_posttypes', 'rgc_skip_post_types' );
function rgc_skip_post_types( $post_types ) {
	$post_types[] = 'listing';
	$post_types[] = 'staff';

	return $post_types;
}
```

It seems that you can also include [conditional tags](http://codex.wordpress.org/Conditional_Tags) in the above, eg `$post_types[] = is_front_page();` to stop the featured image from displaying. This is most helpful if you have set a default featured image on the plugin's settings page.

### Can I force my site to use the default image on a post type even if it has its own Featured Image?

Yes! You'll want to add a filter to your theme (functions.php file). Here's an example:

```php
add_filter( 'display_featured_image_genesis_use_default', 'rgc_force_default_image' );
function rgc_force_default_image( $post_types ) {
	$post_types[] = 'attorney';

	return $post_types;
}
```

### The backstretch image is a little too tall.

If you do not want the height of the backstretch image to be quite the height of the user's window, you can reduce it by just a hair. Go to Appearance > Display Featured Image Settings and change the 'Height' number from the default of 0. The higher this number is, the shorter your image will be. Feel free to experiment, as no images are harmed by changing this number.

Additionally/alternatively, you could set a max-height for the backstretch image area via css:

```css
.big-leader {
	max-height: 700px;
}
```

### I checked the __Move Excerpts/Archive Descriptions__ option, but don't want excerpts to show on a certain custom post type, even with the featured image.

There's a filter for that, too. For example, adding this to your functions.php file would make sure that the excerpt does not show on single posts, or posts from the Staff post type, even if they have an excerpt.

```php
add_filter( 'display_featured_image_genesis_omit_excerpt', 'rgc_omit_excerpts' );
function rgc_omit_excerpts( $post_types ) {
	$post_types[] = 'staff';
	$post_types[] = 'post';

	return $post_types;
}
```

_Note:_ unless you check the option to __Move Excerpts/Archive Descriptions__, archive headlines will be styled similarly to the standard single post/page output. If you check this option, all titles and descriptions will move to overlay the leader image.

## Credits

* Built by [Robin Cornett](http://robincornett.com/)

## Changelog

### x.y.z
* added featured images to taxonomies!
* added featured images to admin archive pages!
* added new setting to not move post titles to overlay Featured Image

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
