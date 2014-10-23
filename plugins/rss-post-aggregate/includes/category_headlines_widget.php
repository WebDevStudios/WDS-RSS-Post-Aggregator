<?php
/*
 * Simple widget that displays the headline as a title
 */
class RSS_Post_Aggregation_Category_Headlines_Widget extends WP_Widget {

	/**
	 * Default widget options
	 *
	 * @var array
	 **/
	private $defaults = array(
		'title'	 	=> '',
		'category' 	=> '',
		'count'		=> 5,
		'excerpt_length' => 15,
		'read_more_text' => '',
	);

	public function __construct() {
		parent::__construct(
			'rss_post_aggregation_category_headlines',
			__( 'RSS Category Headlines', 'wds-rss-post-aggregation' ),
			array( 'description' => __( 'Displays the title as the headline for imported RSS feed items.', 'wds-rss-post-aggregation' ) )
		);
	}

	public function form( $instance ) {

		$instance = wp_parse_args( $instance, $this->defaults );

		?>
		<p>
			<?php echo __( 'Title', 'wds-rss-post-aggregation' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			<?php echo __( 'Category', 'wds-rss-post-aggregation' ); ?>
			<?php echo $this->category_dropdown( $this->get_field_name( 'category' ), $instance['category'] ); ?>

			<?php echo __( 'Number to show', 'wds-rss-post-aggregation' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $instance['count'] ); ?>" />
		</p>
		<p>
			<?php echo __( 'Excerpt Length', 'rss_post_aggregation' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="text" value="<?php echo esc_attr( $instance['excerpt_length'] ); ?>" />
		</p>
		<p>
			<?php echo __( '"Read More" text', 'rss_post_aggregation' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'read_more_text' ); ?>" name="<?php echo $this->get_field_name( 'read_more_text' ); ?>" type="text" value="<?php echo esc_attr( $instance['read_more_text'] ); ?>" />
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

		echo isset( $args['before_widget'] ) ? $args['before_widget'] : '';

		echo isset( $args['before_title'] ) ? $args['before_title'] : '';
		echo apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		echo isset( $args['after_title'] ) ? $args['after_title'] : '';

		if ( isset( $instance['excerpt_length'] ) && ( $length = absint( $instance['excerpt_length'] ) ) ) {
			$this->excerpt_length = $length;
			add_filter( 'excerpt_length', array( $this, 'filter_excerpt_length' ), 10 );
		}

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
		);

		if ( isset( $this->excerpt_length ) ) {
			remove_filter( 'rss_post_aggregation_feed_summary_length', array( $this, 'filter_excerpt_length' ) );
		}


		if( !empty( $posts ) ) {
			echo '<ul>';
			foreach( $posts as $p ) {
				//var_dump( get_post_meta( $p->ID ));
				echo '<li>';

				//display the linked post title
				echo '<a class="post-title" href="' . get_permalink( $p->ID ) . '"/>';
				echo $p->post_title;
				echo '</a>';

				// display the date
				echo '<p class="date">';
				echo date( 'M j, Y', strtotime( $p->post_date ) );
				echo '</p>';

				// display the excerpt if available
				if( !empty( $p->post_excerpt ) ) {
		            echo apply_filters('the_excerpt', $p->post_excerpt);
		        }

				// display the custom read more text if it exists and link to post
				if ( isset( $instance['read_more_text'] ) && trim( $instance['read_more_text'] ) ) {
				 	echo ' <a class="read-more" href="'. get_permalink( $p->ID ) .'">'. esc_html( $instance['read_more_text'] ) .'</a>';
				}

				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo __( 'Nothing yet! Check again later', 'wds-rss-post-aggregation' );
		}

		echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';
	}

	/**
	 * Creates a dropdown form element from the RSS Post Aggregation categories
	 *
	 * $param string $name Form element name
	 * @param string $term_slug Selected category
	 * @return string
	 **/
	function category_dropdown( $name, $term_slug = '') {
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

	public function filter_excerpt_length( $length ) {
		if ( isset( $this->excerpt_length ) ) {
			$length = (int) $this->excerpt_length;
		}

		return $length;
	}

} // end class
