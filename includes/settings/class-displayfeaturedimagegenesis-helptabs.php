<?php

/**
 * Set up the help tabs for the Display Featured Image Genesis Settings page.
 * Class Display_Featured_Image_Genesis_HelpTabs
 * @package DisplayFeaturedImageGenesis
 * @copyright 2016 Robin Cornett
 */
class Display_Featured_Image_Genesis_HelpTabs extends Display_Featured_Image_Genesis_Helper {

	/**
	 * Base id for the help tabs
	 * @var string $id
	 */
	protected $id = 'displayfeaturedimage-help-';

	/**
	 * Help tab for settings screen
	 *
	 * @since 1.1.0
	 */
	public function help() {

		$screen    = get_current_screen();
		$help_tabs = $this->define_tabs();
		if ( ! $help_tabs ) {
			return;
		}
		foreach ( $help_tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	/**
	 * Define the help tabs.
	 * @return array
	 * @since 2.6.0
	 */
	protected function define_tabs() {

		$active_tab = $this->get_active_tab();
		switch ( $active_tab ) {
			case 'style':
				$help_tabs = array(
					array(
						'id'      => $this->id . 'less_header',
						'title'   => __( 'Height', 'display-featured-image-genesis' ),
						'content' => $this->height(),
					),
					array(
						'id'      => $this->id . 'centering',
						'title'   => __( 'Centering', 'display-featured-image-genesis' ),
						'content' => $this->centering(),
					),
					array(
						'id'      => $this->id . 'fade',
						'title'   => __( 'Fade', 'display-featured-image-genesis' ),
						'content' => $this->fade(),
					),
				);
				break;

			case 'cpt':
				$help_tabs = array(
					array(
						'id'      => $this->id . 'special',
						'title'   => __( 'Special Pages', 'display-featured-image-genesis' ),
						'content' => $this->special(),
					),
					array(
						'id'      => $this->id . 'skip',
						'title'   => __( 'Skip Content Types', 'display-featured-image-genesis' ),
						'content' => $this->skip(),
					),
					array(
						'id'      => $this->id . 'fallback',
						'title'   => __( 'Fallback Images', 'display-featured-image-genesis' ),
						'content' => $this->fallback(),
					),
				);
				if ( $this->get_content_types() ) {
					$help_tabs[] = array(
						'id'      => $this->id . 'cpt',
						'title'   => __( 'Custom Content Types', 'display-featured-image-genesis' ),
						'content' => $this->cpt(),
					);
				}
				break;

			default:
				$help_tabs = array(
					array(
						'id'      => $this->id . 'sitewide',
						'title'   => __( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
						'content' => $this->image_size() . $this->skip_front() . $this->keep_titles() . $this->excerpts(),
					),
					array(
						'id'      => $this->id . 'archives',
						'title'   => __( 'Optional Archive Settings', 'display-featured-image-genesis' ),
						'content' => $this->paged() . $this->feed() . $this->archive(),
					),
					array(
						'id'      => $this->id . 'default',
						'title'   => __( 'Optional Default Image', 'display-featured-image-genesis' ),
						'content' => $this->default_image(),
					),
				);
				break;
		}

		return $help_tabs;
	}

	/**
	 * Help information for the height settings.
	 * @return string
	 * @since 2.6.0
	 */
	protected function height() {
		$help  = '<p>' . __( 'Depending on how your header/nav are set up, or if you just do not want your backstretch image to extend to the bottom of the user screen, you may want to change this number. It will raise the bottom line of the backstretch image, making it shorter.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . __( 'The plugin determines the size of your backstretch image based on the size of the user\'s browser window. Changing the "Height" setting tells the plugin to subtract that number of pixels from the measured height of the user\'s window, regardless of the size of that window.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . __( 'If you need to control the size of the backstretch Featured Image output with more attention to the user\'s screen size, add a Maximum Height number, which affects the CSS.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help information for the default image.
	 * @return string
	 * @since 2.6.0
	 */
	protected function default_image() {
		$common = new Display_Featured_Image_Genesis_Common();
		$large  = $common->minimum_backstretch_width();
		$help   = '<p>' . __( 'You may set a large image to be used sitewide if a featured image is not available. This image will show on posts, pages, and archives.', 'display-featured-image-genesis' ) . '</p>';
		$help  .= '<p>' . sprintf(
			__( 'Supported file types are: jpg, jpeg, png, and gif. The image must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
		) . '</p>';
		$help  .= '<p>' . __( 'If you choose "Always Use Default", your default image will be used site wide, no matter what content types/posts/etc. have featured images set.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help information for the skip front page setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function skip_front() {
		$help  = '<h3>' . __( 'Skip Front Page', 'display-featured-image-genesis' ) . '</h3>';
		$help .= '<p>' . __( 'If you set a Default Featured Image, it will show on every post/page of your site. This may not be desirable on child themes with a front page constructed with widgets, so you can select this option to prevent the Featured Image from showing on the front page. Checking this will prevent the Featured Image from showing on the Front Page, even if you have set an image for that page individually.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . sprintf(
			__( 'If you want to prevent entire groups of posts from not using the Featured Image, you will want to <a href="%s" target="_blank">add a filter</a> to your theme functions.php file.', 'display-featured-image-genesis' ),
			esc_url( 'https://github.com/robincornett/display-featured-image-genesis#how-do-i-stop-the-featured-image-action-from-showing-on-my-custom-post-types' )
		) . '</p>';

		return $help;
	}

	/**
	 * Help text for the keep titles setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function keep_titles() {
		$help  = '<h3>' . __( 'Do Not Move Titles', 'display-featured-image-genesis' ) . '</h3>';
		$help .= '<p>' . __( 'This setting applies to the backstretch Featured Image only. It allows you to keep the post/page titles in their original location, instead of overlaying the new image.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help text for the excerpts setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function excerpts() {
		$help  = '<h3>' . __( 'Move Excerpts', 'display-featured-image-genesis' ) . '</h3>';
		$help .= '<p>' . __( 'By default, archive descriptions (set on the Genesis Archive Settings pages) show below the Default Featured Image, while the archive title displays on top of the image. If you check this box, all headlines, descriptions, and optional excerpts will display in a box overlaying the Featured Image.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help text for the paged setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function paged() {
		$help  = '<h3>' . __( 'Subsequent Pages', 'display-featured-image-genesis' ) . '</h3>';
		$help .= '<p>' . __( 'Featured Images do not normally show on the second (and following) page of term/blog/post archives. Check this setting to ensure that they do.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help text for the feed setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function feed() {
		$help  = '<h3>' . __( 'RSS Feed', 'display-featured-image-genesis' ) . '</h3>';
		$help .= '<p>' . __( 'This plugin does not add the Featured Image to your content, so normally you will not see your Featured Image in the feed. If you select this option, however, the Featured Image (if it is set) will be added to each entry in your RSS feed.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . __( 'If your RSS feed is set to Full Text, the Featured Image will be added to the entry content. If it is set to Summary, the Featured Image will be added to the excerpt instead.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help text for archive thumbnails setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function archive() {
		$help  = '<h3>' . __( 'Archive Thumbnails', 'display-featured-image-genesis' ) . '</h3>';
		$help .= '<p>' . __( 'This setting will set a fallback image for all content types in your archives. If there is no featured image, and no images uploaded to the post/page, the plugin will use the featured image for the term, or post type, as the thumbnail.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . __( 'The thumbnail will adhere to the settings from the Genesis settings page.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help text for special pages images.
	 * @return string
	 * @since 2.6.0
	 */
	protected function special() {
		return '<p>' . __( 'You can now set a featured image for search results and 404 (no results found) pages.', 'display-featured-image-genesis' ) . '</p>';
	}

	/**
	 * Help text for CPT images.
	 * @return string
	 * @since 2.6.0
	 */
	protected function cpt() {
		$help  = '<p>' . __( 'Some plugins and/or developers extend the power of WordPress by using Custom Post Types to create special kinds of content.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . __( 'Since you have custom post types with archives, you might like to set a featured image for each of them.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . __( 'Featured Images for archives can be smaller than the Default Featured Image, but still need to be larger than your site\'s "medium" image size.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help text for the skip content types setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function skip() {
		return '<p>' . __( 'Tell WordPress which content types should never have the featured image added. This applies to singular views of the content type, not the archive. If there is an archive or default image set, that will show on the content type archive.', 'display-featured-image-genesis' ) . '</p>';
	}

	/**
	 * Help text for the CPT fallback setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function fallback() {

		$help  = '<p>' . __( 'Instead of using the content type\'s Featured Image on singular posts, use one of the fallback images. This may be assigned to a term within a taxonomy, or be the content type featured image, or be the sitewide default image.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . __( 'If no fallback image exists, no featured image will display, as this will shortcut the check for the post\'s featured image.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help text for the centering settings.
	 * @return string
	 * @since 2.6.0
	 */
	protected function centering() {
		$help  = '<p>' . __( 'By default, backstretch images are centered both vertically and horizontally. If centering is disabled horizontally, the image will start at left edge of the screen; if vertically, the top of the image will align with the top of the screen.', 'display-featured-image-genesis' ) . '</p>';
		$help .= '<p>' . __( 'Depending on the screen size and orientation, this can make a significant difference in the output of the image. Please note that although not all images will center well, not all images will <em>not</em> center well, either.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}

	/**
	 * Help text for the fade setting.
	 * @return string
	 * @since 2.6.0
	 */
	protected function fade() {
		return '<p>' . __( 'By default, the backstretch image takes 750 milliseconds to fade in once the image has loaded into the browser. You can make the image fade in more quickly or slowly, as you prefer.', 'display-featured-image-genesis' ) . '</p>';
	}

	/**
	 * Help text for the preferred image size.
	 * @since 3.1.0
	 * @return string
	 */
	protected function image_size() {
		$help  = '<h3>' . __( 'Preferred Image Size', 'display-featured-image-genesis' ) . '</h3>';
		$help .= '<p>' . __( 'Set the default image size you would like to use sitewide. This can be overridden per content type, or on an individual post.', 'display-featured-image-genesis' ) . '</p>';

		return $help;
	}
}
