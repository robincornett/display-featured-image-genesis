# Display Featured Image for Genesis

This plugin works within the Genesis Framework, to display your post/page featured images in new and fun ways. It should work with either HTML5 or XHTML themes, but older themes may have a width set on elements which may not allow the full backstretch experience.

## Description

This plugin takes a different approach to how we use and display featured images for posts and pages. Instead of simply reusing an image which already exists in the post/page content, the plugin anticipates that you will want to use lovely large images for your featured images, but to do so intelligently. Depending on what you upload, the plugin will:
* display the image as a backstretch (screen width) image if the image is wider than your site's Large Media Setting.
* display the image above your post/page content, centered and up to the width of the content, if your image is larger than your Medium Media Setting, and less than or equal to your Large Media Setting.
* display nothing if your featured image width is less than or equal to your Medium Media Setting.
* display nothing if your featured image is already displayed in your content (the original image, not a resized version).

## Requirements
* WordPress 3.8, tested up to 4.0
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
__Screenshot of a page using the Backstretch Featured Image.__

## Frequently Asked Questions

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
### The backstretch image is a little too tall.

If you do not want the height of the backstretch image to be quite the height of the user's window, you can reduce it by just a hair. Go to Settings > Media and change the 'Reduction amount' number from the default of 0. The higher this number is, the shorter your image will be. Feel free to experiment, as no images are harmed by changing this number.

Additionally/alternatively, you could set a max-height for the backstretch image via css:

```css
.backstretch {
	max-height: 700px;
}
```
## Credits

* Built by [Robin Cornett](http://robincornett.com/)

## Changelog

###1.1.1
* corrected XHTML hooks

###1.1.0
* added a setting in the admin to optionally reduce the height of the backstretch image
* refactoring

###1.0.1
* added the filter for certain post types, and optional filter for other custom post types

###1.0.0
* Initial release on Github