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
	 *
	 * @param string $tax_slug
	 * @param CPT_Core $cpt
	 */
	public function __construct( $tax_slug, $cpt ) {

		// Register this cpt
		parent::__construct(
			array( __( 'RSS Feed Link', 'wds-rss-post-aggregation' ), __( 'RSS Feed Links', 'wds-rss-post-aggregation' ), $tax_slug ),
			array(
				'show_admin_column' => false,
			),
			array( $cpt->post_type() )
		);
	}

	public function hooks() {
	}

}
