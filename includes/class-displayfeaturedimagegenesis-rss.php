<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_RSS {

	/**
	 * Decide whether or not to add the featured image to the feed or the feed excerpt
	 *
	 * @return filter the_excerpt_rss (if summaries) or the_content_feed (full text)
	 * @since  x.y.z
	 */
	public function maybe_do_feed() {

		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		$feed_image     = $displaysetting['feed_image'];
		$rss_option     = get_option( 'rss_use_excerpt' );

		if ( ! $feed_image ) {
			return;
		}

		if ( '1' === $rss_option ) {
			add_filter( 'the_excerpt_rss', array( $this, 'add_image_to_feed' ), 1000, 1 );
		}
		else {
			add_filter( 'the_content_feed', array( $this, 'add_image_to_feed' ), 15 );
		}

	}

	/**
	 * add the featured image to the feed, unless it already exists
	 * includes allowances for Send Images to RSS plugin, which processes before this
	 *
	 * @param return $content
	 * @since  x.y.z
	 */
	public function add_image_to_feed( $content ) {

		if ( ! has_post_thumbnail() ) {
			return $content;
		}

		$post_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'original' );
		if ( class_exists( 'SendImagesRSS' ) ) {
			$post_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'mailchimp' );
		}
		$image_content  = strpos( $content, 'src="' . $post_thumbnail[0] );

		if ( false !== $image_content ) {
			return $content;
		}

		$rss_option = get_option( 'rss_use_excerpt' );
		$size       = 'large';
		$align      = '';
		$style      = 'display:block;margin:10px auto;';
		$class      = 'rss-featured-image';

		if ( class_exists( 'SendImagesRSS' ) ) {
			$size  = 'mailchimp';
			$class = 'rss-mailchimp';
		}

		if ( '1' === $rss_option ) {
			$size  = 'thumbnail';
			$align = 'left';
			$style = 'margin:0px 0px 20px 20px;';
			$class = 'rss-small';
		}

		$image = get_the_post_thumbnail( get_the_ID(), $size, array( 'align' => $align, 'style' => $style, 'class' => $class ) );

		$content = $image . $content;

		return $content;
	}

}