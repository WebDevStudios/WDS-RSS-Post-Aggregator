<?php
/*
 * Simple widget that displays the headline as a title
 */
 
 // Our namespace.
 namespace WebDevStudios\RSS_Post_Aggregator;
 use WP_Widget;
 

class RSS_Post_Aggregator_Category_Headlines_Widget extends WP_Widget {

	/**
	 * Default widget options
	 *
	 * @var array
	 **/
	private $defaults = array(
		'title'	 	     => '',
		'category' 	     => '',
		'count'		     => 5,
		'excerpt' 	     => 0,
		'excerpt_length' => '15',
		'read_more_text' => '',
		'cat_link'       => '',
	);

	// Need to access this other places
	private $instance_data = null;

	public function __construct() {
		parent::__construct(
			'rss_post_aggregator_category_headlines',
			__( 'RSS Category Headlines', 'wds-rss-post-aggregator' ),
			array( 'description' => __( 'Displays the title as the headline for imported RSS feed items.', 'wds-rss-post-aggregator' ) )
		);
	}

	public function form( $instance ) {

		$instance = wp_parse_args( $instance, $this->defaults );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title', 'wds-rss-post-aggregator' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category' )?>"><?php echo __( 'Category', 'wds-rss-post-aggregator' ); ?></label>
			<?php echo $this->category_dropdown( $this->get_field_name( 'category' ), $instance['category'] ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' )?>"><?php echo __( 'Number to show', 'wds-rss-post-aggregator' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $instance['count'] ); ?>" />
		</p>
		<p>
        	<input id="<?php echo $this->get_field_id( 'excerpt' ); ?>" name="<?php echo $this->get_field_name( 'excerpt' ); ?>" type="checkbox" value="1" <?php checked( '1', esc_attr( $instance['excerpt'] ) ); ?> />
        	<label for="<?php echo $this->get_field_id( 'excerpt' ); ?>"><?php echo __( 'Display Post Excerpt', 'wds-rss-post-aggregator' ); ?></label>
        </p>
		<p>
        	<input id="<?php echo $this->get_field_id( 'cat_link' ); ?>" name="<?php echo $this->get_field_name( 'cat_link' ); ?>" type="checkbox" value="1" <?php checked( '1', esc_attr( $instance['cat_link'] ) ); ?> />
        	<label for="<?php echo $this->get_field_id( 'cat_link' ); ?>"><?php echo __( 'Display Category Link', 'wds-rss-post-aggregator' ); ?></label>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id( 'excerpt_length' )?>"><?php echo __( 'Excerpt Length', 'wds-rss-post-aggregator' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="text" value="<?php echo esc_attr( $instance['excerpt_length'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'read_more_text' )?>"><?php echo __( '"Read More" text', 'wds-rss-post-aggregator' ); ?></label>
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
		$instance['excerpt']        = strip_tags( $new_instance['excerpt'] );
		$instance['excerpt_length'] = strip_tags( $new_instance['excerpt_length'] );

		// Sanitize options
		foreach ( $this->defaults as $key => $default_value ) {
			$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
		}

		return $instance;
	}

	public function widget( $args, $instance ) {
		// Set the class variable.
		$this->instance_data = $instance;

		echo isset( $args['before_widget'] ) ? $args['before_widget'] : '';

		echo isset( $args['before_title'] ) ? $args['before_title'] : '';
		echo apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		echo isset( $args['after_title'] ) ? $args['after_title'] : '';

		$query_args = array(
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

		$excerpt	= strip_tags( $instance['excerpt'] );
		$posts      = get_posts( $query_args );
		if ( ! empty( $posts ) ) {
			global $post;

			echo '<ul>';
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				echo '<li>';

				// display the linked post title
				echo '<a class="post-title" href="' . get_permalink() . '" target="blank">';
				the_title();
				echo '</a>';

				// display the date
				echo '<p class="date">';
				echo get_the_date( get_option( 'date_format' ) );
				echo '</p>';

				if ( $excerpt ){
					the_excerpt();
				}
				echo '</li>';
			} // END foreach
			echo '</ul>';
			wp_reset_postdata();

			if ( ! empty( $instance['cat_link'] ) ){
				$cat_data = get_term_by( 'slug', $instance['category'], 'rss-category' );
				if ( ! empty( $cat_data ) ) {
					echo '<p><a href="' . get_term_link( $cat_data->term_id, 'rss-category' ) . '" title="' . sprintf( __( 'More from %s', 'wds-rss-post-aggregator' ), $cat_data->name ) . '" class="rss_cat_link">' . sprintf( __( 'More from %s', 'wds-rss-post-aggregator' ), $cat_data->name ) . ' &raquo;</a></p>';
				}
			}
		} else {
			echo __( 'Nothing yet! Check again later', 'wds-rss-post-aggregator' );
		}

		echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';
	}

	/**
	 * Filter Excerpt More
	 * Will filter the more >> tag for this widget only.
	 * @param string $more Default more tag
	 * @return string
	 */
	function excerpt_more( $more ){
		$output = ''; // Blank it out as default.
		if ( isset( $this->instance_data['read_more_text'] ) && trim( $this->instance_data['read_more_text'] ) ) {
			$output = ' <a class="read-more" href="'. get_permalink() .'" target="blank">'. esc_html( $this->instance_data['read_more_text'] ) .'</a>';
		}
		return $output;
	}

	/**
	 * Excerpt Length Filter
	 * @param  int $default_length Excerpt Length
	 * @return int
	 */
	function excerpt_length( $default_length ){
		// Old code caps this at 10, so I'm leaving this here.
		$new_length = 10;
		if ( isset( $this->instance_data['excerpt_length'] ) && $this->instance_data['excerpt_length'] > 0 ){
			$new_length = absint( $this->instance_data['excerpt_length'] );
		}
		return $new_length;
	}

	/**
	 * Creates a dropdown form element from the RSS Post Aggregator categories
	 *
	 * $param string $name Form element name
	 * @param string $term_slug Selected category
	 * @return string
	 **/
	function category_dropdown( $name, $term_slug = '' ) {
		$s = '<select name="' . esc_attr( $name ) . '" class="widefat">';

		$terms = get_terms( 'rss-category', array( 'hide_empty' => false ) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
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

} // end class
