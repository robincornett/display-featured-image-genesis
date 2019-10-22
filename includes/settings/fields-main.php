<?php

$large = displayfeaturedimagegenesis_get()->minimum_backstretch_width();

return array(
	array(
		'id'      => 'default',
		'title'   => __( 'Default Featured Image', 'display-featured-image-genesis' ),
		'section' => 'default',
		'type'    => 'image',
	),
	array(
		'id'          => 'always_default',
		'title'       => __( 'Always Use Default', 'display-featured-image-genesis' ),
		'section'     => 'default',
		'label'       => __( 'Always use the default image, even if a featured image is set.', 'display-featured-image-genesis' ),
		'description' => sprintf(
			/* translators: placeholder is a number equivalent to the width of the site's Large image (Settings > Media) */
			esc_html__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
		),
		'type'        => 'checkbox',
	),
	array(
		'id'      => 'image_size',
		'title'   => __( 'Preferred Image Size', 'display-featured-image-genesis' ),
		'section' => 'main',
		'choices' => apply_filters(
			'displayfeaturedimagegenesis_image_size_choices',
			array(
				'banner' => __( 'Banner (default)', 'display-featured-image-genesis' ),
				'large'  => __( 'Large', 'display-featured-image-genesis' ),
			)
		),
		'type'    => 'select',
	),
	array(
		'id'      => 'exclude_front',
		'title'   => __( 'Skip Front Page', 'display-featured-image-genesis' ),
		'section' => 'main',
		'label'   => __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ),
		'type'    => 'checkbox',
	),
	array(
		'id'      => 'keep_titles',
		'title'   => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
		'section' => 'main',
		'label'   => __( 'Do not move the titles to overlay the banner featured image.', 'display-featured-image-genesis' ),
		'type'    => 'checkbox',
	),
	array(
		'id'      => 'move_excerpts',
		'title'   => __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ),
		'section' => 'main',
		'label'   => __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ),
		'type'    => 'checkbox',
	),
	array(
		'id'      => 'is_paged',
		'title'   => __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ),
		'section' => 'archives',
		'label'   => __( 'Show featured image on pages 2+ of blogs and archives.', 'display-featured-image-genesis' ),
		'type'    => 'checkbox',
	),
	array(
		'id'      => 'feed_image',
		'title'   => __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ),
		'section' => 'archives',
		'label'   => __( 'Optionally, add the featured image to your RSS feed.', 'display-featured-image-genesis' ),
		'type'    => 'checkbox',
	),
	array(
		'id'      => 'thumbnails',
		'title'   => __( 'Archive Thumbnails', 'display-featured-image-genesis' ),
		'section' => 'archives',
		'label'   => __( 'Use term/post type fallback images for content archives?', 'display-featured-image-genesis' ),
		'type'    => 'checkbox',
	),
	array(
		'id'      => 'shortcodes',
		'title'   => __( 'Add Shortcode Buttons', 'display-featured-image-genesis' ),
		'type'    => 'checkbox',
		'section' => 'main',
		'label'   => __( 'Add optional shortcode buttons to the post editor', 'display-featured-image-genesis' ),
		'skip'    => true,
	),
);
