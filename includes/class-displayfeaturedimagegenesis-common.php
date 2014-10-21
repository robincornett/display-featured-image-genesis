<?php

/**
 * Common functions for plugin
 *
 * @package DisplayFeaturedImageGenesis
 * @since 1.2.1
 */
class Display_Featured_Image_Genesis_Common {

	/**
	 * Get the ID of each image dynamically.
	 *
	 * @since 1.2.0
	 *
	 * @author Philip Newcomer
	 * @link   http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
	 */
	static function get_image_id( $attachment_url ) {
		global $wpdb;
		$attachment_id = false;

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '(-\d{3,4}x\d{3,4}.)', '.', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

		}

		return $attachment_id;
	}
}
