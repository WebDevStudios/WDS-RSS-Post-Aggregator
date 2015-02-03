<?php

class RSS_Post_Aggregation_Widgets {
	public function hooks() {
		add_action( 'widgets_init', function() {
			register_widget( 'RSS_Post_Aggregation_Category_Headlines_Widget' );
			register_widget( 'RSS_Post_Aggregation_Category_Featured_Images_Widget' );
		} );
	}
}
