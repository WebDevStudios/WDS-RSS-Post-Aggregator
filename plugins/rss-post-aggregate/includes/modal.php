<?php

class RSS_Post_Aggregation_Modal {

	public function __construct( $cpt ) {
		$this->cpt = $cpt;
	}

	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_rss_get_data', array( $this, array( $this, 'rss_get_data' ) ) );
		// add_action( 'wp_ajax_nopriv_rss_get_data', array( $this, array( $this, 'rss_get_data' ) ) );

	}

	public function rss_get_data() {
		// foreach ( array( 'articles', 'highlights', 'template' ) as $required ) {
			if ( ! isset( $_REQUEST[ 'feed_url' ] ) ) {
				wp_send_json_error( 'feed_url missing.' );
			}
		// }

		wp_send_json_success( $_REQUEST );

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

		wp_localize_script( 'rss-aggregator', 'RSSPost_l10n', array(
			'debug' => ! $min,
			'feeds' => get_option( 'msnc_saved_feed_urls' ),
		) );

		// Needed to style the search modal
		wp_register_style( 'rss-search-box', admin_url( "/css/media{$min}.css" ) );
		wp_enqueue_style( 'rss-aggregator', RSS_Post_Aggregation::url( "assets/css/rss_post_aggregation{$min}.css" ), array( 'rss-search-box' ), RSS_Post_Aggregation::VERSION );

		add_action( 'admin_footer', array( $this, 'js_modal_template' ) );

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
			$feed_data = array_merge( $feed_data, $this->rss_items( $feed_url ) );
			// $feed_data[ $feed_url ] = $this->rss_items( $feed_url );
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

	function rss_items( $rss_link ) {
		$unique = md5( $rss_link );

		if ( ! isset( $_GET['delete-trans'] ) && $rss_items = get_transient( $unique ) ) {
			return $rss_items;
		}

		$rss = fetch_feed( $rss_link );

		if ( is_wp_error( $rss ) ) {
			// if ( is_admin() || current_user_can( 'manage_options' ) )
			return array(
				'error' => sprintf( __( 'RSS Error: %s' ), $rss->get_error_message() ),
			);
		}

		if ( ! $rss->get_item_quantity() ) {
			$rss->__destruct();
			unset( $rss );
			return array(
				'error' => __( 'An error has occurred, which probably means the feed is down. Try again later.' ),
			);
		}

		$rss_items = array();

		foreach ( $rss->get_items( 0, 20 ) as $item ) {

 			$link = $title = $date = $summary = $author = '';

			$link = $item->get_link();

			while ( stristr( $link, 'http' ) != $link ) {
				$link = substr( $link, 1 );
			}
			$link = esc_url( strip_tags( $link ) );

			$title = esc_html( trim( strip_tags( $item->get_title() ) ) );
			if ( empty( $title ) ) {
				$title = __( 'Untitled' );
			}

			$summary = @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) );
			$summary = esc_attr( wp_trim_words( $summary, 100, ' [&hellip;]' ) );

			// Change existing [...] to [&hellip;].
			if ( '[...]' == substr( $summary, -5 ) ) {
				$summary = substr( $summary, 0, -5 ) . '[&hellip;]';
			}

			$summary = esc_html( $summary );

			$date = ( $get_date = $item->get_date( 'U' ) )
				? date_i18n( get_option( 'date_format' ), $get_date )
				: '';

			$author = ( $author = $item->get_author() && is_object( $author ) )
				? esc_html( strip_tags( $author->get_name() ) )
				: '';

			$rss_items[ md5( $link ) ] = array(
				'link'     => $link,
				'title'    => $title,
				'date'     => $date,
				'summary'  => $summary,
				'author'   => $author,
				'rss_link' => $rss_link,
			);
		}
		$rss->__destruct();
		unset($rss);

		set_transient( $unique, $rss_items, DAY_IN_SECONDS );
		return $rss_items;
	}

}
