<?php

// Our namespace.
namespace WebDevStudios\RSS_Post_Aggregator\Taxonomy;
use Taxonomy_Core;
use WebDevStudios\RSS_Post_Aggregator\RSS_Post_Aggregator;

if ( ! class_exists( 'Taxonomy_Core' ) ) {
	RSS_Post_Aggregator::include_file( 'libraries/Taxonomy_Core/Taxonomy_Core' );
}

/**
 * CPT child class
 */
class Feed_Links extends Taxonomy_Core {

	/**
	 * Register Custom Post Types. See documentation in Taxonomy_Core, and in wp-includes/post.php
	 *
	 * @since 0.1.1
	 *
	 * @param string $tax_slug
	 * @param CPT_Core $cpt
	 */
	public function __construct( $tax_slug, $cpt ) {

		// Register this cpt
		parent::__construct(
			array( __( 'RSS Feed Link', 'wds-rss-post-aggregator' ), __( 'RSS Feed Links', 'wds-rss-post-aggregator' ), $tax_slug ),
			array(
				'show_admin_column' => false,
			),
			array( $cpt->post_type() )
		);
	}

	public function hooks() {
	}

}
