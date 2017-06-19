<?php

// Our namespace.
namespace WebDevStudios\RSS_Post_Aggregator;
use WP_Widget;

class RSS_Post_Aggregation_Widgets {
	public function hooks() {
		add_action( 'widgets_init', function() {
			register_widget( __NAMESPACE__ . '\RSS_Post_Aggregation_Category_Headlines_Widget' );
			register_widget( __NAMESPACE__ . '\RSS_Post_Aggregation_Category_Featured_Images_Widget' );
		} );
	}
}
