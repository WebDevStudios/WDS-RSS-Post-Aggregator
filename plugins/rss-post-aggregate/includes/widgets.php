<?php
/**
 * Creates a dropdown form element from the RSS Post Aggregation categories
 *
 * $param string $name Form element name
 * @param string $term_slug Selected category
 * @return string
 **/
function rss_post_aggregation_category_dropdown( $name, $term_slug = '') {
	$s = '<select name="' . esc_attr( $name ) . '" class="widefat">';

	$terms = get_terms( 'rss-category', array( 'hide_empty' => false ) );
	if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
		foreach( $terms as $term ) {
			$s .= '<option name="' . esc_attr( $term->slug ) . '"';
			$s .= selected( $term_slug, $term->slug, false );
			$s .= '>';
			$s .= $term->name;
			$s .= '</option>';
		}
	}

	$s .= '</select>';

	return $s;
}


/*
 * Simple widget that displays the headline as a title
 */
class RSS_Post_Aggregation_Category_Headlines extends WP_Widget {

	/**
	 * Default widget options
	 *
	 * @var array
	 **/
	private $defaults = array( 
			'title'	 	=> '',
			'category' 	=> '',
			'count'		=> 5
		);

	public function __construct() {
		parent::__construct(
			'rss_post_aggregation_category_headlines',
			__( 'RSS Category Headlines' ),
			array( 'description' => __( 'Displays the title as the headline' ) )
		);
	}

	public function form( $instance ) {
		
		$instance = wp_parse_args( $instance, $this->defaults );
		

		?>
		<p>
			<?php echo __( 'Title' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			<?php echo __( 'Category' ); ?>
			<?php echo rss_post_aggregation_category_dropdown( $this->get_field_name( 'category' ), $instance['category'] ); ?>

			<?php echo __( 'Number to show' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $instance['count'] ); ?>" />
		</p>
		<?php
			
	}

	public function widget( $args, $instance ) {
	
		echo $args['before_widget'];

		echo $args['before_title'];
		echo $instance['title'];
		echo $args['after_title'];
		
		$posts = get_posts(array(
								'post_type' => 'rss-posts',
								'tax_query' => array(
										array(
		                        			'taxonomy' => 'rss-category',
		                                	'field' => 'slug',
		                                    'terms' => $instance['category']
		                                )
		                    		),
								'showposts'	=> $instance['count']
					  )
		);

		if( !empty( $posts ) ) {
			echo '<ul>';
			foreach( $posts AS $p ) {
				//var_dump( get_post_meta( $p->ID ));
				echo '<li>';
				
				echo '<div class="post-title">';
				echo '<a href="' . get_permalink( $p->ID ) . '"/>';
				echo $p->post_title;
				echo '</a>';
				echo '</div>';

				echo date( 'M j, Y', strtotime( $p->post_date ) );


				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo __( 'Nothing yet! Check again later' );
		}

		echo $args['after_widget'];
	}
} // end class


/**
 * Widget that shows the RSS feed with a featured image
 **/
class RSS_Post_Aggregation_Category_Featured_Images extends WP_Widget {

	/**
	 * Default widget options
	 *
	 * @var array
	 **/
	private $defaults = array( 
			'title'	 	=> '',
			'category' 	=> '',
			'count'		=> 5
		);

	public function __construct() {
		parent::__construct(
			'rss_post_aggregation_category_headlines',
			__( 'RSS Category Featured Image' ),
			array( 'description' => __( 'Displays the featured as the headline' ) )
		);
	}

	public function form( $instance ) {
		
		$instance = wp_parse_args( $instance, $this->defaults );
		

		?>
		<p>
			<?php echo __( 'Title' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			<?php echo __( 'Category' ); ?>
			<?php echo rss_post_aggregation_category_dropdown( $this->get_field_name( 'category' ), $instance['category'] ); ?>

			<?php echo __( 'Number to show' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $instance['count'] ); ?>" />
		</p>
		<?php
			
	}

	public function widget( $args, $instance ) {
	
		echo $args['before_widget'];

		echo $args['before_title'];
		echo $instance['title'];
		echo $args['after_title'];
		
		$posts = get_posts(array(
								'post_type' => 'rss-posts',
								'tax_query' => array(
										array(
		                        			'taxonomy' => 'rss-category',
		                                	'field' => 'slug',
		                                    'terms' => $instance['category']
		                                )
		                    		),
								'showposts'	=> $instance['count']
					  )
		);

		if( !empty( $posts ) ) {
			echo '<ul>';
			foreach( $posts AS $p ) {
				//var_dump( get_post_meta( $p->ID ));
				echo '<li>';
				
				echo '<div class="post-title" style="clear:both;">';
				echo '<a href="' . get_permalink( $p->ID ) . '"/>';
				echo $p->post_title;
				echo '</a>';
				echo '</div>';
	
				echo get_the_post_thumbnail( $p->ID, 'thumbnail', array( 'class' => 'alignleft' ) );

				$content = str_replace( 'Read more »', '', $p->post_content );
				echo $content;
				

				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo __( 'Nothing yet! Check again later' );
		}

		echo $args['after_widget'];
	}
} // end class


add_action( 'widgets_init', function() { 
	register_widget( 'RSS_Post_Aggregation_Category_Headlines' ); 
	register_widget( 'RSS_Post_Aggregation_Category_Featured_Images' );
} );
