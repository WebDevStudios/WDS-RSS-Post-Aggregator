<?php

class RSS_Post_Aggregation_Feeds {

	/**
	 * Replaces wp_widget_rss_output
	 */
	function get_items( $rss_link, $args ) {
		$this->rss_link = apply_filters( 'rss_post_aggregation_feed_link', $rss_link, $args, $this );

		$args = $this->process_args( $args );

		if ( ! isset( $_GET['delete-trans'] ) && $this->cache_time && $rss_items = get_transient( $this->transient_id ) ) {
			return $rss_items;
		}

		$items = (int) $args['items'];
		if ( $items < 1 || 20 < $items )
			$items = 10;
		$show_image    = (int) $args['show_image'];
		$show_summary  = (int) $args['show_summary'];
		$show_author   = (int) $args['show_author'];
		$show_date     = (int) $args['show_date'];


		$rss = fetch_feed( $this->rss_link );

		if ( is_wp_error( $rss ) ) {
			// if ( is_admin() || current_user_can( 'manage_options' ) )
			return array(
				'error' => sprintf( __( 'RSS Error: %s', 'rss_post_aggregation' ), $rss->get_error_message() ),
			);
		}


		if ( ! $rss->get_item_quantity() ) {
			$rss->__destruct();
			unset( $rss );
			return array(
				'error' => __( 'An error has occurred, which probably means the feed is down. Try again later.', 'rss_post_aggregation' ),
			);
		}

		$parse  = parse_url( $this->rss_link );
		$source = isset( $parse['host'] ) ? $parse['host'] : $this->rss_link;

		$rss_items = array();

		foreach ( $rss->get_items( 0, $items ) as $index => $item ) {
			$this->item = $item;

			$rss_item = array();

			$rss_item['link'] = $this->get_link();
			$rss_item['title'] = $this->get_title();

			if ( $show_image ) {
				$rss_item['image'] = $this->get_image();
			}

			if ( $show_summary ) {
				$rss_item['summary'] = $this->get_summary();
			}

			if ( $show_date ) {
				$rss_item['date'] = $this->get_date();
			}

			if ( $show_author ) {
				$rss_item['author'] = $this->get_author();
			}

			$rss_item['source']   = $source;
			$rss_item['rss_link'] = $this->rss_link;
			$rss_item['index']    = $index;

			$rss_items[ $index ]  = $rss_item;
		}

		$rss->__destruct();
		unset($rss);

		if ( $this->cache_time ) {
			set_transient( $this->transient_id, $rss_items, $this->cache_time );
		}

		return apply_filters( 'rss_post_aggregation_feed_items', $rss_items, $this->rss_link, $this );
	}

	public function process_args( $args ) {
		$args = apply_filters( 'rss_post_aggregation_feed_args', $args, $this->rss_link, $this );

		$args = wp_parse_args( $args, array(
			'show_author'  => 0,
			'show_date'    => 0,
			'show_summary' => 0,
			'show_image'   => 0,
			'items'        => 0,
			'cache_time'   => DAY_IN_SECONDS
		) );
		$this->cache_time = (int) $args['cache_time'];

		$this->transient_id = md5( serialize( array_merge( array( 'rss_link'  => $this->rss_link ), $args ) ) );
		return $args;
	}

	public function get_title() {
		$title = esc_html( trim( strip_tags( $this->item->get_title() ) ) );
		if ( empty( $title ) ) {
			$title = __( 'Untitled', 'rss_post_aggregation' );
		}

		return apply_filters( 'rss_post_aggregation_feed_title', $title, $this->rss_link, $this );
	}

	public function get_link() {
		$link = $this->item->get_link();

		while ( stristr( $link, 'http' ) != $link ) {
			$link = substr( $link, 1 );
		}

		$link = esc_url( strip_tags( trim( $link ) ) );

		return apply_filters( 'rss_post_aggregation_feed_link', $link, $this->rss_link, $this );
	}

	public function get_date() {
		$date = ( $get_date = $this->item->get_date( 'U' ) )
			? date_i18n( get_option( 'date_format' ), $get_date )
			: '';

		return apply_filters( 'rss_post_aggregation_feed_date', $date, $this->rss_link, $this );
	}

	public function get_author() {
		$author = ( ( $author = $this->item->get_author() ) && is_object( $author ) )
			? esc_html( strip_tags( $author->get_name() ) )
			: '';

		return apply_filters( 'rss_post_aggregation_feed_author', $author, $this->rss_link, $this );
	}

	public function get_summary() {
		$summary = @html_entity_decode( $this->item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) );

		$length = (int) apply_filters( 'rss_post_aggregation_feed_summary_length', 100, $this->rss_link, $this );

		$summary = esc_attr( wp_trim_words( $summary, $length, ' [&hellip;]' ) );

		// Change existing [...] to [&hellip;].
		if ( '[...]' == substr( $summary, -5 ) ) {
			$summary = substr( $summary, 0, -5 ) . '[&hellip;]';
		}

		return apply_filters( 'rss_post_aggregation_feed_summary', $summary, $this->rss_link, $this );
	}

	public function get_image() {
		$content = @html_entity_decode( $this->item->get_content(), ENT_QUOTES, get_option( 'blog_charset' ) );

		@$this->dom()->loadHTML( $content );

		$src = '';
		foreach ( $this->dom()->getElementsByTagName( 'img' ) as $img ) {
			if ( $src = $img->getAttribute('src') ) {
				break;
			}
		}

		return apply_filters( 'rss_post_aggregation_feed_image_src', $src, $this->rss_link, $this );
	}

	public function dom() {
		if ( isset( $this->dom ) ) {
			return $this->dom;
		}
		$this->dom = new DOMDocument();

		return $this->dom;
	}

}
