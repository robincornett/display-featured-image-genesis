<?php

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

	public static function do_excerpt() {

		if ( ! is_singular() || is_front_page() ) {
			return;
		}
		$item = Display_Featured_Image_Genesis_Common::get_image_variables();

		$headline = $intro_text = '';

		if ( $item->title ) {
			$headline = sprintf( '<h1 class="entry-title">%s</h1>', esc_attr( $item->title ) );
		}
		if ( has_excerpt() ) {
			$intro_text = wpautop( $item->description );
		}
		if ( $headline || $intro_text ) {
			printf( '<div class="excerpt">%s</div>', $headline . $intro_text );
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
	public static function do_front_blog_excerpt() {

		if ( ! is_front_page() && ! is_home() ) {
			return;
		}

		$item       = Display_Featured_Image_Genesis_Common::get_image_variables();
		$frontpage  = get_option( 'show_on_front' );
		$headline   = '';
		$intro_text = wpautop( get_bloginfo( 'description' ) );

		if ( is_home() && 'page' === $frontpage ) {
			$headline = sprintf( '<h1 class="entry-title">%s</h1>', esc_attr( $item->title ) );
			$intro_text = wpautop( $item->description );
		}

		if ( $headline || $intro_text ) {
			printf( '<div class="excerpt">%s</div>', $headline . $intro_text );
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

	public static function do_tax_description() {

		global $wp_query;

		if ( ! is_category() && ! is_tag() && ! is_tax() )
			return;

		if ( get_query_var( 'paged' ) >= 2 )
			return;

		$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : $wp_query->get_queried_object();

		if ( ! $term || ! isset( $term->meta ) )
			return;

		$intro_text = '';


		if ( $term->meta['intro_text'] )
			$intro_text = apply_filters( 'genesis_term_intro_text_output', $term->meta['intro_text'] );

		if ( $intro_text )
			printf( '<div class="archive-description taxonomy-description">%s</div>', $intro_text );

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

	public static function do_author_description() {

		if ( ! is_author() )
			return;

		if ( get_query_var( 'paged' ) >= 2 )
			return;

		$intro_text = get_the_author_meta( 'intro_text', (int) get_query_var( 'author' ) );

		$intro_text = $intro_text ? apply_filters( 'genesis_author_intro_text_output', $intro_text ) : '';

		if ( $intro_text )
			printf( '<div class="archive-description author-description">%s</div>', $intro_text );


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

	public static function do_cpt_archive_description() {

		if ( ! is_post_type_archive() || ! genesis_has_post_type_archive_support() )
			return;

		if ( get_query_var( 'paged' ) >= 2 )
			return;

		$intro_text = genesis_get_cpt_option( 'intro_text' );

		$intro_text = $intro_text ? apply_filters( 'genesis_cpt_archive_intro_text_output', $intro_text ) : '';

		if ( $intro_text )
			printf( '<div class="archive-description cpt-archive-description">%s</div>', $intro_text );

	}

}
