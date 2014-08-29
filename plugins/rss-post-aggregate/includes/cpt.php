<?php

if ( ! class_exists( 'CPT_Core' ) ) {
	RSS_Post_Aggregation::include_file( 'libraries/CPT_Core/CPT_Core' );
}

/**
 * CPT child class
 */
class RSS_Post_Aggregation_CPT extends CPT_Core {

	public $key = '_rsspost_';

	/**
	 * Register Custom Post Types. See documentation in CPT_Core, and in wp-includes/post.php
	 */
	public function __construct() {

		// Register this cpt
		parent::__construct(
			array( __( 'RSS Post', 'rss_post_aggregation' ), __( 'RSS Posts', 'rss_post_aggregation' ), 'rss-posts' ),
			array(
				'supports'  => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
				'menu_icon' => 'dashicons-rss',
			)
		);
	}

	public function hooks() {
	}

	public function is_listing() {
		if ( isset( $this->is_listing) ) {
			return $this->is_listing;
		}

		$screen = get_current_screen();
		$this->is_listing = isset( $screen->base, $screen->post_type ) && 'edit' == $screen->base && $this->post_type() == $screen->post_type;

		return $this->is_listing;
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 * @since  0.1.0
	 * @param  array  $columns Array of registered column names/labels
	 * @return array           Modified array
	 */
	public function columns( $columns ) {
		$columns = array(
			'thumbnail' => __( 'Thumbnail', 'rss_post_aggregation' ),
			'cb'        => $columns['cb'],
			'title'     => $columns['title'],
			'source'    => __( 'Source', 'rss_post_aggregation' ),
			'date'      => $columns['date'],
		);
		return $columns;
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 * @since  0.1.0
	 * @param  array  $column Array of registered column names
	 */
	public function columns_display( $column ) {
		switch ( $column ) {

			case 'thumbnail':
				$size = isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ? 'thumb' : array( 50, 50 );
				the_post_thumbnail( $size );
				break;

			case 'source':
				if ( $url = get_post_meta( get_the_ID(), $this->key . 'source_url', 1 ) ) {
					$parse = parse_url( $url );
					$label = isset( $parse['host'] ) ? $parse['host'] : $url;
					echo '<a target="_blank" href="'. esc_url( $url ) .'">'. $label .'</a>';
				}
				break;
		}
	}

}
