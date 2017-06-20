<?php

// Our namespace.
namespace WebDevStudios\RSS_Post_Aggregator;
use WP_Widget;

class RSS_Post_Aggregator_Widgets {
	public function hooks() {
		add_action( 'widgets_init', function() {
			register_widget( 'RSS_Post_Aggregator_Category_Headlines_Widget' );
			register_widget( 'RSS_Post_Aggregator_Category_Featured_Images_Widget' );
		} );
	}
}
