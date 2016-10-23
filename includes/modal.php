<?php

class RSS_Post_Aggregation_Modal {

	public $feed_links = array();

	/**
	 * @var RSS_Post_Aggregation_Feeds
	 */
	public $rss;

	/**
	 * @var RSS_Post_Aggregation_CPT
	 */
	public $cpt;

	/**
	 * @var RSS_Post_Aggregation_Taxonomy
	 */
	public $tax;

	/**
	 * RSS_Post_Aggregation_Modal constructor.
	 *
	 * @param RSS_Post_Aggregation_Feeds $rss
	 * @param RSS_Post_Aggregation_CPT $cpt
	 * @param RSS_Post_Aggregation_Taxonomy $tax
	 */
	public function __construct( $rss, $cpt, $tax ) {
		$this->rss = $rss;
		$this->cpt = $cpt;
		$this->tax = $tax;
	}

	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_rss_get_data', array( $this, 'rss_get_data' ) );
		add_action( 'wp_ajax_rss_save_posts', array( $this, 'rss_save_posts' ) );
	}

	public function rss_save_posts() {
		foreach ( array( 'to_add', 'feed_url', 'feed_id' ) as $required ) {
			if ( ! isset( $_REQUEST[ $required ] ) ) {
				wp_send_json_error( $required .' missing.' );
			}
		}

		$updated = $this->save_posts( $_REQUEST['to_add'], $_REQUEST['feed_id'] );
		wp_send_json_success( array( $_REQUEST, $updated ) );

	}

	public function save_posts( $posts, $feed_id ) {

		$updated = array();
		foreach ( $posts as $post_data ) {
			$updated[ $post_data['title'] ] = $this->cpt->insert( $post_data, $feed_id );
		}

		return $updated;
	}

	public function rss_get_data() {
		foreach ( array( 'feed_url', 'feed_id' ) as $required ) {
			if ( ! isset( $_REQUEST[ $required ] ) ) {
				wp_send_json_error( $required .' missing.' );
			}
		}

		$feed_url = esc_url( $_REQUEST['feed_url'] );
		$feed_id  = absint( $_REQUEST['feed_id'] );


		if ( ! $feed_id ) {

			$link = get_term_by( 'name', $feed_url, $this->tax->taxonomy() );

			if ( $link ) {

				$feed_id = $link->term_id;
			} elseif ( $link = wp_insert_term( $feed_url, $this->tax->taxonomy() ) ) {

				$feed_id = $link['term_id'];
			} else {

				$feed_id = false;
			}
		}

		if ( ! $feed_id ) {
			wp_send_json_error( __( 'There was an error with the RSS feed link creation.', 'wds-rss-post-aggregation' ) );
		}

		$feed_items = $this->rss->get_items( esc_url( $_REQUEST['feed_url'] ), array(
			'show_author'  => true,
			'show_date'    => true,
			'show_summary' => true,
			'show_image'   => true,
			'items'        => 20,
		) );

		if ( isset( $feed_items['error'] ) ) {
			wp_send_json_error( $feed_items['error'] );
		}

		wp_send_json_success( compact( 'feed_url', 'feed_id', 'feed_items' ) );
	}

	public function enqueue() {
		if ( ! $this->cpt->is_listing() ) {
			return;
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$dependencies = array(
			'jquery', // obvious reasons
			'wp-backbone', // Needed for backbone and `wp.template`
		);

		wp_enqueue_script( 'rss-aggregator', RSS_Post_Aggregation::url( "assets/js/rss_post_aggregation{$min}.js" ), $dependencies, RSS_Post_Aggregation::VERSION );

		// wp_die( '<xmp>: '. print_r( $this->cpt->slug_to_redirect, true ) .'</xmp>' );
		wp_localize_script( 'rss-aggregator', 'RSSPost_l10n', array(
			'debug'           => ! $min,
			'cpt_url'         => add_query_arg( 'post_type', $this->cpt->post_type(), admin_url( '/edit.php' ) ),
			'feeds'           => $this->get_feed_links(),
			'cpt'             => $this->cpt->post_type(),
			'show_modal'      => isset( $_GET[ $this->cpt->slug_to_redirect ] ),
			'no_data'         => __( 'No feed data found', 'wds-rss-post-aggregation' ),
			'nothing_checked' => __( "You didn't select any posts. Do you want to close the search?", 'wds-rss-post-aggregation' ),
		) );

		delete_option( 'wds_rss_aggregate_saved_feed_urls' );
		// Needed to style the search modal
		wp_register_style( 'rss-search-box', admin_url( "/css/media{$min}.css" ) );
		wp_enqueue_style( 'rss-aggregator', RSS_Post_Aggregation::url( "assets/css/rss_post_aggregation{$min}.css" ), array( 'rss-search-box' ), RSS_Post_Aggregation::VERSION );

		add_action( 'admin_footer', array( $this, 'js_modal_template' ) );

	}

	public function get_feed_links() {
		if ( ! empty( $this->feed_links ) ) {
			return $this->feed_links;
		}

		$feed_links = get_terms( $this->tax->taxonomy(), array( 'hide_empty' => false ) );

		if ( $feed_links && is_array( $feed_links ) ) {
			foreach ( $feed_links as $link ) {
				$this->feed_links[ $link->term_id ] = esc_url( $link->name );
			}
		}

		return $this->feed_links;
	}

	public function js_modal_template() {
		include_once 'modal-markup.php';
	}

}
