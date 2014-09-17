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

function rss_post_aggregation_feed_links_dropdown( $name, $term_name ) {
	$s = '<select name="' . esc_attr( $name ) . '" class="widefat">';

	$terms = get_terms( 'rss-feed-links', array( 'hide_empty' => false ) );
	if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
		foreach( $terms as $term ) {
			$s .= '<option name="' . esc_attr( $term->slug ) . '"';
			$s .= selected( $term_name, $term->slug, false );
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
			__( 'RSS Category Headlines', 'rss_post_aggregation' ),
			array( 'description' => __( 'Displays the title as the headline for imported RSS feed items.', 'rss_post_aggregation' ) )
		);
	}

	public function form( $instance ) {

		$instance = wp_parse_args( $instance, $this->defaults );


		?>
		<p>
			<?php echo __( 'Title', 'rss_post_aggregation' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			<?php echo __( 'Category', 'rss_post_aggregation' ); ?>
			<?php echo rss_post_aggregation_category_dropdown( $this->get_field_name( 'category' ), $instance['category'] ); ?>

			<?php echo __( 'Number to show', 'rss_post_aggregation' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $instance['count'] ); ?>" />
		</p>
		<?php

	}

	/**
	 * Update form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		// Sanitize options
		foreach ( $this->defaults as $key => $default_value ) {
			$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
		}

		return $instance;
	}

	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		echo $args['before_title'];
		echo $instance['title'];
		echo $args['after_title'];

		$posts = get_posts( array(
			'post_type' => 'rss-posts',
			'showposts' => $instance['count'],
			'tax_query' => array(
				array(
					'taxonomy' => 'rss-category',
					'field'    => 'slug',
					'terms'    => $instance['category'],
				),
			),
		) );

		if( !empty( $posts ) ) {
			echo '<ul>';
			foreach( $posts as $p ) {
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
			echo __( 'Nothing yet! Check again later', 'rss_post_aggregation' );
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
		'feed_url'	=> '',
		'items'		=> 5,
	);

	public function __construct() {
		parent::__construct(
			'rss_post_aggregation_category_featured_images',
			__( 'RSS Feed with Images', 'rss_post_aggregation' ),
			array( 'description' => __( 'Reads and displays a supplied RSS feed with featured images.', 'rss_post_aggregation' ) )
		);
	}

	public function form( $instance ) {

		$instance = wp_parse_args( $instance, $this->defaults );


		?>
		<p>
			<?php echo __( 'Title', 'rss_post_aggregation' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			<?php echo __( 'Feed', 'rss_post_aggregation' ); ?>
			<?php echo rss_post_aggregation_feed_links_dropdown( $this->get_field_name( 'feed_url' ), $instance['feed_url'] ); ?>

			<?php echo __( 'Number to show', 'rss_post_aggregation' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>" type="text" value="<?php echo esc_attr( $instance['items'] ); ?>" />
		</p>
		<?php

	}

	/**
	 * Update form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		// Sanitize options
		foreach ( $this->defaults as $key => $default_value ) {
			$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
		}

		return $instance;
	}

	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		echo $args['before_title'];
		echo $instance['title'];
		echo $args['after_title'];

		$rss = new RSS_Post_Aggregation_Feeds();
		$rss_args = array (
			'show_author'	=> 0,
			'show_date'		=> 0,
			'show_summary'	=> 1,
			'show_image' 	=> 1,
			'items'			=> isset( $instance['items'] ) ? absint( $instance['items'] ) : 5,
		);
		$posts = $rss->get_items( $instance['feed_url'], $rss_args );

		if( ! empty( $posts ) ) {
			echo '<ul>';
			foreach( $posts as $p ) {
				echo '<li>';

				echo '<div class="post-title" style="clear:both;">';
				echo '<a href="' . esc_attr( $p['link'] ) . '"/>';
				echo $p['title'];
				echo '</a>';
				echo '</div>';

				if( !empty( $p['image'] ) ) {
					echo '<img src="' . esc_attr( $p['image'] ) . '" />';
				}

				$content = str_replace( '»', '', $p['summary'] );
				$content = str_replace( 'Read more', '', $content );
				echo $content;


				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo __( 'Nothing yet! Check again later', 'rss_post_aggregation' );
		}

		echo $args['after_widget'];
	}
} // end class


add_action( 'widgets_init', function() {
	register_widget( 'RSS_Post_Aggregation_Category_Headlines' );
	register_widget( 'RSS_Post_Aggregation_Category_Featured_Images' );
} );
