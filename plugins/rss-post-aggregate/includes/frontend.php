<?php

class RSS_Post_Aggregation_Frontend {

	public function __construct( $cpt ) {
		$this->cpt = $cpt;
	}

	public function hooks() {
		add_filter( 'post_link', array( $this, 'post_link' ) );
		add_filter( 'post_type_link', array( $this, 'post_link' ) );
		add_filter( 'the_permalink', array( $this, 'post_link' ) );
	}


	function post_link( $link ) {
		global $post;

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
