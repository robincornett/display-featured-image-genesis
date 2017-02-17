<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_RSS {

	/**
	 * Decide whether or not to add the featured image to the feed or the feed excerpt
	 *
	 * @return filter the_excerpt_rss (if summaries) or the_content_feed (full text)
	 * @since  1.5.0
	 */
	public function maybe_do_feed() {

		$displaysetting = displayfeaturedimagegenesis_get_setting();
		$feed_image     = $displaysetting['feed_image'];

		// if the user isn't sending images to the feed, we're done
		if ( ! $feed_image || Display_Featured_Image_Genesis_Common::is_in_array( 'skipped_posttypes' ) ) {
			return;
		}

		// if the feed is summary, filter the excerpt
		$which_filter = 'the_excerpt_rss';
		$priority     = 1000;
		$rss_option   = get_option( 'rss_use_excerpt' );
		// if the feed is full text, filter the content
		if ( '0' === $rss_option ) {
			$which_filter = 'the_content_feed';
			$priority     = 15;
		}
		add_filter( $which_filter, array( $this, 'add_image_to_feed' ), $priority );

	}

	/**
	 * add the featured image to the feed, unless it already exists
	 * includes allowances for Send Images to RSS plugin, which processes before this
	 *
	 * @param return $content
	 * @since  1.5.0
	 */
	public function add_image_to_feed( $content ) {

		// if the post doesn't have a thumbnail, we're done here
		if ( ! has_post_thumbnail() && ! is_feed() ) {
			return $content;
		}

		$rss_option = get_option( 'rss_use_excerpt' );
		// first check: see if the featured image already exists in full in the content
		$size = 'original';
		if ( class_exists( 'SendImagesRSS' ) ) {

			if ( '1' === $rss_option && class_exists( 'SendImagesRSS_Excerpt_Fixer' ) ) {
				// if the newer version of Send Images to RSS is installed, bail here because it's better.
				return $content;
			}
			$rss_setting = get_option( 'sendimagesrss' );
			if ( ! $rss_setting ) {
				$defaults = array(
					'simplify_feed'  => get_option( 'sendimagesrss_simplify_feed', 0 ),
					'alternate_feed' => get_option( 'sendimagesrss_alternate_feed', 0 ),
				);

				$rss_setting = get_option( 'sendimagesrss', $defaults );
			}
			if ( ! $rss_setting['simplify_feed'] && ( ( $rss_setting['alternate_feed'] && is_feed( 'email' ) ) || ! $rss_setting['alternate_feed'] ) ) {
				$size = 'mailchimp';
			}
		}

		$post_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), $size );
		$image_content  = strpos( $content, 'src="' . $post_thumbnail[0] );

		// if the featured image already exists in all its glory in the content, we're done here
		if ( false !== $image_content && '0' === $rss_option ) {
			return $content;
		}

		// reset size to large so we don't send huge files to the feed
		$size  = 'large';
		$align = '';
		$style = 'display:block;margin:10px auto;';
		$class = 'rss-featured-image';
		if ( class_exists( 'SendImagesRSS' ) ) {
			// if the user is using Send Images to RSS, send the right images to the right feeds
			if ( ! $rss_setting['simplify_feed'] && ( ( $rss_setting['alternate_feed'] && is_feed( 'email' ) ) || ! $rss_setting['alternate_feed'] ) ) {
				$size  = 'mailchimp';
				$class = 'rss-mailchimp';
			}
		}

		// if the feed output is descriptions only, change image size to thumbnail with small alignment
		if ( '1' === $rss_option ) {
			$size  = 'thumbnail';
			$align = 'left';
			$style = 'margin:0px 20px 20px 0px;';
			$class = 'rss-small';
		}

		// whew. build the image!
		$image = get_the_post_thumbnail(
			get_the_ID(),
			$size,
			array(
				'align' => $align,
				'style' => $style,
				'class' => $class,
			)
		);
		$image = apply_filters( 'display_featured_image_genesis_modify_rss_image', $image, $align, $style, $class );

		return $image . $content;
	}
}
