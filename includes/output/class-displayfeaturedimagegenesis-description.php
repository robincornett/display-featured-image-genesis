<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2020 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Description {

	/**
	 * Remove Genesis titles/descriptions
	 * @since 2.3.1
	 */
	public function remove_title_descriptions() {
		if ( is_singular() && ! is_page_template( 'page_blog.php' ) ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' ); // HTML5
			remove_action( 'genesis_post_title', 'genesis_do_post_title' ); // XHTML
			if ( ! post_type_supports( get_post_type( get_the_ID() ), 'genesis-entry-meta-before-content' ) && apply_filters( 'displayfeaturedimagegenesis_remove_entry_header', true ) ) {
				remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
				remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
			}
		}
		remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
		remove_action( 'genesis_before_loop', 'genesis_do_blog_template_heading' );
		remove_action( 'genesis_before_loop', 'genesis_do_posts_page_heading' );
	}

	/**
	 * Do title and description together (for excerpt output)
	 *
	 * @since 2.3.1
	 */
	public function do_title_descriptions() {
		$this->do_front_blog_excerpt();
		$this->do_excerpt();
		genesis_do_taxonomy_title_description();
		genesis_do_author_title_description();
		genesis_do_cpt_archive_title_description();
	}

	/**
	 * Separate archive titles from descriptions. Titles show in leader image
	 * area; descriptions show before loop.
	 *
	 * @since  1.3.0
	 *
	 */
	public function add_descriptions() {
		$this->do_tax_description();
		$this->do_author_description();
		$this->do_cpt_archive_description();
	}

	/**
	 * Show optional excerpt on single posts.
	 * If it's not a single post, nothing happens.
	 * If there's an excerpt and the move excerpts option is selected,
	 * it runs through `wpautop()` before being added to a div.
	 *
	 * @since 1.3.0
	 */
	public function do_excerpt() {

		if ( ! is_singular() || is_front_page() ) {
			return;
		}

		$headline   = '';
		$intro_text = '';
		$itemprop   = '';
		if ( genesis_html5() ) {
			$itemprop = ' itemprop="headline"';
		}

		$setting = displayfeaturedimagegenesis_get_setting( 'keep_titles' );
		if ( ! $setting ) {
			$headline = sprintf( '<h1 class="entry-title"%s>%s</h1>', $itemprop, get_the_title() );
		}

		if ( has_excerpt() ) {
			$intro_text = apply_filters( 'display_featured_image_genesis_singular_description', get_the_excerpt() );
		}
		if ( $headline || $intro_text ) {
			$print = $headline . $intro_text;
			$class = 'excerpt';
			$this->print_description( $print, $class, $class );
		}
	}

	/**
	 * Show optional excerpt on blog or front page.
	 * If it's not the front page and isn't home, nothing happens.
	 * If there's an excerpt and the move excerpts option is selected, it runs through `wpautop()` before being added to a div.
	 *
	 * @since 1.3.0
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
			$print = $headline . $intro_text;
			$class = 'excerpt';
			$this->print_description( $print, $class, $class );
		}
	}

	/**
	 * Add custom description to category / tag / taxonomy archive pages.
	 * If the page is not a category, tag or taxonomy term archive, or we're not on the first page, or there's no term, or
	 * no term meta set, then nothing extra is displayed.
	 * If there's a description to display, it runs through `wpautop()` before being added to a div.
	 *
	 * @since 1.3.0
	 *
	 * @global WP_Query $wp_query Query object.
	 */

	public function do_tax_description() {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return;
		}

		global $wp_query;
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
	 * If we're not on an author archive page, or not on page 1, then nothing extra is displayed.
	 * If there's a custom headline to display, it is marked up as a level 1 heading.
	 * If there's a description (intro text) to display, it is run through `wpautop()` before being added to a div.
	 *
	 * @since 1.4.0
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
	 * If we're not on a post type archive page, or not on page 1, then nothing extra is displayed.
	 * If there's a custom headline to display, it is marked up as a level 1 heading.
	 * If there's a description (intro text) to display, it is run through wpautop() before being added to a div.
	 *
	 * @since 2.0.0
	 *
	 * @uses genesis_has_post_type_archive_support() Check if a post type should potentially support an archive setting page.
	 * @uses genesis_get_cpt_option()                Get list of custom post types which need an archive settings page.
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
		$intro_text = $this->get_content( $intro_text );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $intro_text;
		do_action( "displayfeaturedimagegenesis_after_{$context}" );
		echo '</div>';
	}

	/**
	 * Run the intro text (content) through standard filters.
	 *
	 * @param $content
	 *
	 * @return string
	 * @since 3.1.0
	 */
	private function get_content( $content ) {
		global $wp_embed;
		$original = $content;
		$content  = trim( $content );
		$content  = wp_kses_post( $content );
		$content  = wptexturize( $content );
		$content  = wpautop( $content );
		$content  = shortcode_unautop( $content );
		$content  = prepend_attachment( $content );
		$content  = convert_smilies( $content );
		$content  = $wp_embed->autoembed( $content );
		$content  = do_shortcode( $content );

		return apply_filters( 'displayfeaturedimagegenesis_intro_text', $content, $original );
	}
}
