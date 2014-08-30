<?php

class RSS_Post_Aggregation_Modal {

	public $feed_links = array();

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
		foreach ( $posts as $post ) {
			$updated[ $post['title'] ] = $this->cpt->insert( $post, $feed_id );
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
			wp_send_json_error( __( 'There was an error with the RSS feed link creation.', 'rss_post_aggregation' ) );
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
			'no_data'         => __( 'No feed data found', 'rss_post_aggregation' ),
			'nothing_checked' => __( "You didn't select any posts. Do you want to close the search?", 'rss_post_aggregation' ),
		) );

		delete_option( 'msnc_saved_feed_urls' );
		// Needed to style the search modal
		wp_register_style( 'rss-search-box', admin_url( "/css/media{$min}.css" ) );
		wp_enqueue_style( 'rss-aggregator', RSS_Post_Aggregation::url( "assets/css/rss_post_aggregation{$min}.css" ), array( 'rss-search-box' ), RSS_Post_Aggregation::VERSION );

		add_action( 'admin_footer', array( $this, 'js_modal_template' ) );

		if ( ! isset( $_GET['test_import'] ) ) {
			return;
		}
		// $this->get_feeds();
		$posts = get_option( 'lblblblblbb' );
		$posts[] = array(
			'link' => 'http://blogs.microsoft.com/firehose/2014/08/28/sci-fi-fans-can-now-channel-doctor-who-in-minecraft-xbox-360-edition/',
			'source' => 'blogs.windows.com',
			'title' => 'Sci-fi fans can now channel ‘Doctor Who’ in ‘Minecraft: Xbox 360 Edition’',
			'date' => 'August 28, 2014',
			'image' => 'http://mscorp.blob.core.windows.net/mscorpmedia/2014/08/FH_Dr-Who-Minecraft-640x360.jpg',
			'summary' => 'Are you a fan of “Doctor Who” – the sci-fi staple that’s been on since 1963 – and also of “Minecraft: Xbox 360 Edition”? Now you can have both, thanks to a recently announced deal from Microsoft Studios, Mojang and the BBC.

“Minecraft” players will be able to use characters from the “Doctor Who” series in new skin packs that will launch in September. Six Doctors are included in each skin pack, starting with the 11th Doctor and five other Doctors, their companions, their biggest adversaries and the Doctors’ archenemy, the Daleks.',
			'author' => 'Athima Chansanchai',
			'rss_link' => 'http://blogs.windows.com/feed/',
			'index' => '3',
			'checked' => 'true',
		);
		if ( $posts ) {
			$updated = $this->save_posts( $posts, 968 );
			wp_die( '<xmp>$updated: '. print_r( $updated, true ) .'</xmp>' );
		}
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

	public function get_feeds() {
		// add_option( 'msnc_saved_feed_urls', array(
		// 	'http://blogs.office.com/feed/',
		// 	'http://blogs.microsoft.com/firehose/feed/',
		// ), '', 'no' );

		$feeds = get_option( 'msnc_saved_feed_urls' );

		$feed_data = array();
		foreach ( $feeds as $feed_url ) {
			$feed_data = array_merge( $feed_data, $this->rss->get_items( $feed_url ) );
			// $feed_data[ $feed_url ] = $this->rss->get_items( $feed_url );
		}

		// Comparison function
		// uasort( $feed_data, array( $this, 'sort_by_title' ) );
		// ksort( $feed_data );

		wp_die( '<xmp>: '. print_r(
			// array(
			// 	array_values( $feed_data ),
				$feed_data,
			// ),
			true ) .'</xmp>' );
	}

}
