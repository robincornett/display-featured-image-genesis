<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Description {

	/**
	 * Show optional excerpt on single posts.
	 *
	 * If it's not a single post, nothing happens.
	 *
	 * If there's an excerpt and the move excerpts option is selected, it runs through `wpautop()` before being added to a div.
	 *
	 * @since 1.3.0
	 *
	 * @return null Return early if not a single post with an excerpt.
	 */

	public function do_excerpt() {

		if ( ! is_singular() || is_front_page() ) {
			return;
		}

		$headline = $intro_text = $itemprop = '';

		if ( genesis_html5() ) {
			$itemprop = ' itemprop="headline"';
		}

		$setting = displayfeaturedimagegenesis_get_setting();
		if ( ! $setting['keep_titles'] ) {
			$headline = sprintf( '<h1 class="entry-title"%s>%s</h1>', $itemprop, get_the_title() );
		}

		if ( has_excerpt() ) {
			$intro_text = apply_filters( 'display_featured_image_genesis_singular_description', get_the_excerpt() );
		}
		if ( $headline || $intro_text ) {
			$print = $headline . wpautop( $intro_text );
			$class = 'excerpt';
			$this->print_description( $print, $class, $class );
		}
	}

	/**
	 * Show optional excerpt on blog or front page.
	 *
	 * If it's not the front page and isn't home, nothing happens.
	 *
	 * If there's an excerpt and the move excerpts option is selected, it runs through `wpautop()` before being added to a div.
	 *
	 * @since 1.3.0
	 *
	 * @return null Return early if not blog/front page.
	 */
	public function do_front_blog_excerpt() {

		if ( ! is_front_page() && ! is_home() ) {
			return;
		}

		// set front page and posts page variables
		$title      = $this->get_front_blog_title();
		$itemprop   = genesis_html5() ? ' itemprop="headline"' : '';
		$headline   = empty( $title ) ? '' : sprintf( '<h1 class="entry-title"%s>%s</h1>', $itemprop, $title );
		$intro_text = $this->get_front_blog_intro_text();

		if ( $headline || $intro_text ) {
			$print = $headline . wpautop( $intro_text );
			$class = 'excerpt';
			$this->print_description( $print, $class, $class );
		}
	}

	/**
	 * Add custom description to category / tag / taxonomy archive pages.
	 *
	 * If the page is not a category, tag or taxonomy term archive, or we're not on the first page, or there's no term, or
	 * no term meta set, then nothing extra is displayed.
	 *
	 * If there's a description to display, it runs through `wpautop()` before being added to a div.
	 *
	 * @since 1.3.0
	 *
	 * @global WP_Query $wp_query Query object.
	 *
	 * @return null Return early if not the correct archive page, not page one, or no term meta is set.
	 */

	public function do_tax_description() {

		global $wp_query;

		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return;
		}

		$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : $wp_query->get_queried_object();

		if ( ! $term ) {
			return;
		}

		$intro_text = displayfeaturedimagegenesis_get_term_meta( $term, 'intro_text' );
		$intro_text = apply_filters( 'display_featured_image_genesis_term_description', $intro_text );

		if ( $intro_text ) {
			$class = 'archive-description taxonomy-description';
			$this->print_description( $intro_text, $class );
		}

	}

	/**
	 * Add custom headline and description to author archive pages.
	 *
	 * If we're not on an author archive page, or not on page 1, then nothing extra is displayed.
	 *
	 * If there's a custom headline to display, it is marked up as a level 1 heading.
	 *
	 * If there's a description (intro text) to display, it is run through `wpautop()` before being added to a div.
	 *
	 * @since 1.4.0
	 *
	 * @return null Return early if not author archive or not page one.
	 */

	public function do_author_description() {

		if ( ! is_author() || get_query_var( 'paged' ) >= 2 ) {
			return;
		}

		$intro_text = apply_filters( 'display_featured_image_genesis_author_description', get_the_author_meta( 'intro_text', (int) get_query_var( 'author' ) ) );

		if ( $intro_text ) {
			$class = 'archive-description author-description';
			$this->print_description( $intro_text, $class );
		}

	}


	/**
	 * Add custom headline and description to relevant custom post type archive pages.
	 *
	 * If we're not on a post type archive page, or not on page 1, then nothing extra is displayed.
	 *
	 * If there's a custom headline to display, it is marked up as a level 1 heading.
	 *
	 * If there's a description (intro text) to display, it is run through wpautop() before being added to a div.
	 *
	 * @since 2.0.0
	 *
	 * @uses genesis_has_post_type_archive_support() Check if a post type should potentially support an archive setting page.
	 * @uses genesis_get_cpt_option()                Get list of custom post types which need an archive settings page.
	 *
	 * @return null Return early if not on relevant post type archive.
	 */

	public function do_cpt_archive_description() {

		if ( ! is_post_type_archive() || ! genesis_has_post_type_archive_support() ) {
			return;
		}

		$intro_text = apply_filters( 'display_featured_image_genesis_cpt_description', genesis_get_cpt_option( 'intro_text' ) );

		if ( $intro_text ) {
			$class = 'archive-description cpt-archive-description';
			$this->print_description( $intro_text, $class );
		}

	}

	/**
	 * Filter to show title on front page.
	 * @return boolean true/false
	 *
	 * @since 2.3.0
	 */
	public function show_front_page_title() {

		$show_front_title = apply_filters( 'display_featured_image_genesis_excerpt_show_front_page_title', false );
		return true === $show_front_title ? true : false;

	}

	/**
	 * Get the front or posts page title.
	 * @param  string $title front page or posts page title
	 * @return string        Site title, or page title
	 *
	 * @since 2.3.3
	 */
	protected function get_front_blog_title( $title = '' ) {
		$frontpage = get_option( 'show_on_front' );
		if ( $this->show_front_page_title() && is_front_page() ) {
			$title = get_the_title();
			if ( is_home() ) {
				$title = get_bloginfo( 'title' );
			}
		} elseif ( is_home() && 'page' === $frontpage ) {
			$postspage = get_post( get_option( 'page_for_posts' ) );
			$title     = $postspage->post_title;
		}
		return apply_filters( 'display_featured_image_genesis_front_blog_title', $title );
	}

	/**
	 * Get the front page excerpt, or site description, or posts page excerpt
	 * @param  string $intro_text excerpt or archive-description
	 * @return string             site description or excerpt
	 *
	 * @since 2.3.3
	 */
	protected function get_front_blog_intro_text( $intro_text = '' ) {
		$frontpage  = get_option( 'show_on_front' );
		$intro_text = get_bloginfo( 'description' );
		if ( is_front_page() && is_singular() && has_excerpt() ) {
			$intro_text = get_the_excerpt();
		} elseif ( is_home() && 'page' === $frontpage ) {
			$postspage  = get_post( get_option( 'page_for_posts' ) );
			$intro_text = empty( $postspage->post_excerpt ) ? '' : $postspage->post_excerpt;
		}
		return apply_filters( 'display_featured_image_genesis_front_blog_description', $intro_text );
	}

	/**
	 * Actually print the description for the archive page. Adds hooks for moving output.
	 *
	 * @param $intro_text   string the archive intro text.
	 * @param string $class optional class for the div.
	 *
	 * @param string $context
	 *
	 * @since 2.5.0
	 */
	protected function print_description( $intro_text, $class = '', $context = 'archive_intro' ) {
		printf( '<div class="%s">', esc_html( $class ) );
		do_action( "displayfeaturedimagegenesis_before_{$context}" );
		echo wp_kses_post( wpautop( $intro_text ) );
		do_action( "displayfeaturedimagegenesis_after_{$context}" );
		echo '</div>';
	}
}
