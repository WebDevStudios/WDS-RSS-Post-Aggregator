<?php

if ( ! class_exists( 'CPT_Core' ) ) {
	RSS_Post_Aggregation::include_file( 'libraries/CPT_Core/CPT_Core' );
}

/**
 * CPT child class
 */
class RSS_Post_Aggregation_CPT extends CPT_Core {

	public $prefix = '_rsspost_';
	public $slug_to_redirect = 'rss_search_modal';

	/**
	 * @var string $tax_slug
	 */
	public $tax_slug;

	/**
	 * Register Custom Post Types. See documentation in CPT_Core, and in wp-includes/post.php
	 *
	 * @param string $cpt_slug
	 * @param string $tax_slug
	 */
	public function __construct( $cpt_slug, $tax_slug ) {
		$this->tax_slug = $tax_slug;

		// Register this cpt
		parent::__construct(
			array( __( 'RSS Post', 'wds-rss-post-aggregation' ), __( 'RSS Posts', 'wds-rss-post-aggregation' ), $cpt_slug ),
			array(
				'supports'  => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
				'menu_icon' => 'dashicons-rss',
			)
		);
	}

	public function hooks() {
		add_filter( 'cmb2_meta_boxes', array( $this, 'meta_box' ) );
		add_action( 'admin_menu', array( $this, 'pseudo_menu_item' ) );
	}

	public function pseudo_menu_item() {
		add_submenu_page( 'edit.php?post_type='. $this->post_type(), '', __( 'Find RSS Post', 'wds-rss-post-aggregation' ), 'edit_posts', $this->slug_to_redirect, '__return_empty_string' );

		if ( ! isset( $_GET['page'] ) || $this->slug_to_redirect != $_GET['page'] ) {
			return;
		}

		wp_redirect( add_query_arg( array(
			'post_type'             => $this->post_type(),
			$this->slug_to_redirect => true,
		), admin_url( '/edit.php' ) ) );
		exit();
	}


	public function is_listing() {
		if ( isset( $this->is_listing ) ) {
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
			'thumbnail'             => __( 'Thumbnail', 'wds-rss-post-aggregation' ),
			'cb'                    => $columns['cb'],
			'title'                 => $columns['title'],
			'source'                => __( 'Source', 'wds-rss-post-aggregation' ),
			'taxonomy-rss-category' => $columns['taxonomy-rss-category'],
			'date'                  => $columns['date'],
		);
		return $columns;
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 * @since  0.1.0
	 *
	 * @param  array $column Array of registered column names
	 * @param int    $post_id
	 */
	public function columns_display( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'thumbnail':
				$size = isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ? 'thumb' : array( 50, 50 );
				the_post_thumbnail( $size );
				break;

			case 'source':
				$link = rss_post_get_feed_url( $post->ID );
				if ( $link ) {
					echo '<a target="_blank" href="'. esc_url( $link ) .'">'. $link .'</a>';
				}
				break;
		}
	}

	/**
	 * CMB2 Metaboxes
	 * @param $meta_boxes
	 *
	 * @TODO: Remove this in favor a a single custom meta box. No need to use CMB for ONE text field.
	 *
	 * @return mixed
	 */
	public function meta_box( $meta_boxes ) {

		$meta_boxes['rsslink_mb'] = array(
			'id'           => 'rsslink_mb',
			'title'        => __( 'RSS Item Info', 'wds-rss-post-aggregation' ),
			'object_types' => array( $this->post_type() ),
			// 'context'      => 'side',
			'show_names'   => false,
			'fields'       => array(
				array(
					'name'       => __( 'Original URL', 'wds-rss-post-aggregation' ),
					'id'         => $this->prefix . 'original_url',
					'type'       => 'text_url',
					'attributes' => array(
						'class' => 'large-text',
					),
				),
				// array(
				// 	'name' => __( 'Image', 'wds-rss-post-aggregation' ),
				// 	'id'   => $this->prefix . 'img_src',
				// 	'type' => 'file',
				// ),
			),
		);

		return $meta_boxes;
	}

	/**
	 * Inserts the feed post items.
	 *
	 * @param array $post An array of post data, similar to WP_Post
	 * @param int $feed_id
	 *
	 * @author JayWood, Justin Sternberg
	 * @return array|string
	 */
	public function insert( $post, $feed_id ) {
		$args = array(
			'post_content'   => wp_kses_post( stripslashes( $post[ 'summary' ] ) ),
			'post_title'     => esc_html( stripslashes( $post['title'] ) ),
			'post_status'    => 'draft',
			'post_type'      => $this->post_type(),
			'post_date'      => date( 'Y-m-d H:i:s', strtotime( $post['date'] ) ),
			'post_date_gmt'  => gmdate( 'Y-m-d H:i:s', strtotime( $post['date'] ) ),
		);

		if ( $existing_post = $this->post_exists( $post['link'], $feed_id ) ) {
			$args['ID'] = $existing_post->ID;
			$args['post_status'] = $existing_post->post_status;
		}

		if ( $post_id = wp_insert_post( $args ) ) {

			$report = array(
				'post_id'           => $post_id,
				'original_url'      => update_post_meta( $post_id, $this->prefix . 'original_url', esc_url_raw( $post['link'] ) ),
				'img_src'           => $this->sideload_featured_image( esc_url_raw( $post['image'] ), $post_id ),
				'wp_set_post_terms' => wp_set_post_terms( $post_id, array( $feed_id ), $this->tax_slug, true ),
			);
		} else {
			$report = 'failed';
		}

		return $report;
	}

	/**
	 * @param string $url
	 * @param int $feed_id
	 *
	 * @since 0.1.0
	 *
	 * @author JayWood, Justin Sternberg
	 * @return bool|mixed
	 */
	public function post_exists( $url, $feed_id ) {
		$args = array(
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'pending', 'draft', 'future'),
			'post_type'      => $this->post_type(),
			'meta_key'       => $this->prefix . 'original_url',
			'meta_value'     => esc_url_raw( $url ),
		);
		$posts = get_posts( $args );

		return $posts && is_array( $posts ) ? $posts[0] : false;
	}

	public function sideload_featured_image( $file_url, $post_id ) {
		if ( empty( $file_url ) || empty( $post_id ) ) {
			return false;
		}

		// Set variables for storage, fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file_url, $matches );
		$file_array = array();
		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $file_url );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return $file_array['tmp_name'];
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, $post_id );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		}

		if ( $src = wp_get_attachment_url( $id ) ) {
			set_post_thumbnail( $post_id, $id );
		}

		return $src;
	}


}

function rss_post_get_feed_object( $post = false ) {
	global $RSS_Post_Aggregation;

	if ( ! $post ) {
		$post = get_post( get_the_ID() );
	} else {
		$post = $post && is_int( $post ) ? get_post( $post ) : $post;
	}

	if ( isset( $post->source_link ) ) {
		return $post->source_link;
	}

	$links = get_the_terms( $post->ID, $RSS_Post_Aggregation->tax_slug );
	$post->source_link = ( $links && is_array( $links ) )
		? array_shift( $links )
		: '';

	return $post->source_link;
}

function rss_post_get_feed_url( $post = false ) {
	$feed = rss_post_get_feed_object( $post );

	if ( $feed && isset( $feed->name ) ) {
		return $feed->name;
	}
}

function rss_post_get_feed_source( $post = false ) {
	if ( $feed = rss_post_get_feed_object( $post ) ) {
		if ( isset( $feed->description ) && $feed->description ) {
			return esc_html( $feed->description );
		}
	}

	if ( $url = rss_post_get_feed_url( $post ) ) {
		$parts = parse_url( $url );
		return isset( $parts['host'] ) ? $parts['host'] : '';
	}
}
