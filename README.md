# Display Featured Image for Genesis

This plugin works within the Genesis Framework, to display your post/page featured images in new and fun ways. For now, an HTML5 theme is required.

## Description

This plugin takes a different approach to how we use and display featured images for posts and pages. Instead of simply reusing an image which already exists in the post/page content, the plugin anticipates that you will want to use lovely large images for your featured images, but to do so intelligently. Depending on what you upload, the plugin will:
* display the image as a backstretch (screen width) image if the image is wider than your site's Large Media Setting.
* display the image above your post/page content, centered and up to the width of the content, if your image is larger than your Medium Media Setting, and less than or equal to your Large Media Setting.
* display nothing if your featured image width is less than or equal to your Medium Media Setting.
* display nothing if your featured image is already displayed in your content (the original image, not a resized version).

## Requirements
* WordPress 3.8, tested up to 4.0
* the Genesis Framework with an HTML5 child theme.

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

## Frequently Asked Questions


### What is Simplify Feed?

If you use native WordPress galleries in your posts, they're sent to your feed as thumbnails. Even if you do not use an RSS/email service, you can still use this plugin to sort out your galleries for subscribers who use an RSS reader. If you select Simplify Feed, your galleries will be converted, but there will not be an email sized image created, and no alternate feed will be created.

## Credits

* Built by [Robin Cornett](http://robincornett.com/)

## Changelog

###1.0.0
* Initial release on Github