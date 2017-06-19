<?php

// Our namespace.
namespace WebDevStudios\RSS_Post_Aggregator;

class RSS_Post_Aggregation_Frontend {

	public function __construct( $cpt ) {
		$this->cpt = $cpt;
	}

	public function hooks() {
		add_filter( 'post_link', array( $this, 'post_link' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'post_link' ), 10, 2 );
		add_filter( 'the_permalink', array( $this, 'get_post_and_post_link' ) );
	}


	public function get_post_and_post_link( $link ) {
		$post = get_post();
		if ( empty( $post ) ) {
			return $link;
		}

		return $this->post_link( $link, $post );
	}

	function post_link( $link, $post ) {

		// Don't mess w/ the permalink for attachments
		if ( isset( $GLOBALS['post'], $GLOBALS['post']->post_type ) && 'attachment' === $GLOBALS['post']->post_type ) {
			return $link;
		}

		if ( ! isset( $post->post_type ) || $post->post_type != $this->cpt->post_type() ) {
			return $link;
		}

		if ( isset( $post->original_url ) ) {
			return $post->original_url;
		}

		$original_url = is_numeric( $post )
			 ? get_post_meta( $post, $this->cpt->prefix .'original_url', true )
			 : get_post_meta( $post->ID, $this->cpt->prefix .'original_url', true );


		// cache to the post object
		$post->original_url = $original_url ? $original_url : $link;

		return $post->original_url;
	}

}
