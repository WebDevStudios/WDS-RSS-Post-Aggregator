<?php

if ( ! class_exists( 'Taxonomy_Core' ) ) {
	RSS_Post_Aggregation::include_file( 'libraries/Taxonomy_Core/Taxonomy_Core' );
}

/**
 * CPT child class
 */
class RSS_Post_Aggregation_Taxonomy extends Taxonomy_Core {

	/**
	 * Register Custom Post Types. See documentation in Taxonomy_Core, and in wp-includes/post.php
	 */
	public function __construct( $tax_slug, $cpt ) {

		// Register this cpt
		parent::__construct(
			array( __( 'RSS Feed Link', 'rss_post_aggregation' ), __( 'RSS Feed Links', 'rss_feed_link' ), $tax_slug ),
			array( 'show_admin_column' => false ),
			array( $cpt->post_type() )
		);
	}

	public function hooks() {
	}

}
