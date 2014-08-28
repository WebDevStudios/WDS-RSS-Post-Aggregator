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
				'supports'  => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
				'menu_icon' => 'dashicons-rss',
			)
		);
	}

	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( $this->is_listing() ) {
			wp_enqueue_script( 'rss-aggregator', RSS_Post_Aggregation::url( "assets/js/rss_post_aggregation{$min}.js" ), array( 'jquery' ), RSS_Post_Aggregation::VERSION );

			wp_localize_script( 'rss-aggregator', 'RSSPost_l10n', array(
				'debug' => ! $min,
			) );

			wp_enqueue_style( 'rss-aggregator', RSS_Post_Aggregation::url( "assets/css/rss_post_aggregation{$min}.css" ), null, RSS_Post_Aggregation::VERSION );
		}
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
		error_log( print_r( $columns, true ) );

		$columns = array(
			'thumbnail' => __( 'Thumbnail', 'rss_post_aggregation' ),
			'cb'        => $columns['cb'],
			'title'     => $columns['title'],
			'source'    => __( 'Source', 'rss_post_aggregation' ),
			'date'      => $columns['date'],
		);
		// $date = $columns;
		// $date = array_splice( $date, 0, -1 );
		// $columns = array_splice( $columns, 0, 2 );
		// $new_column = array(
		// );
		// $columns = array_merge( $new_column, $columns );

		// // array_splice( $columns, 0, 3 )
		// $new_column2 = array(
		// );
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
				the_post_thumbnail( 'thumb' );
				break;
			case 'source':
				// if ( $url = get_post_meta( get_the_ID(), $this->key . 'source_url', 1 ) ) {
				if ( $url = 'http://news.xbox.com/2014/08/games-doctor-who-on-minecraft' ) {
					$parse = parse_url( $url );
					$label = isset( $parse['host'] ) ? $parse['host'] : $url;
					echo '<a target="_blank" href="'. esc_url( $url ) .'">'. $label .'</a>';
				}

				break;
		}
	}

}
