<?php
/**
 * Widget that shows the RSS feed with a featured image
 */

 // Our namespace.
 namespace WebDevStudios\RSS_Post_Aggregator;
 use WP_Widget;

class RSS_Post_Aggregator_Category_Featured_Images_Widget extends WP_Widget {

	/**
	 * Default widget options
	 *
	 * @since 0.1.1
	 *
	 * @var array
	 **/
	private $defaults = array(
		'title'	 	=> '',
		'feed_url'	=> '',
		'items'		=> 5,
		'excerpt_length' => 100,
		'read_more_text' => '',
	);

	/**
	 * RSS Args.
	 *
	 * @since 0.1.1
	 *
	 * @var array
	 */
	private $rss_args = array(
		'show_author'	=> 0,
		'show_date'		=> 0,
		'show_summary'	=> 1,
		'show_image' 	=> 1,
		'items'			=> 5,
		'cache_time'   => DAY_IN_SECONDS,
	);

	/**
	 * Constructor.
	 *
	 * @since 0.1.1
	 */
	public function __construct() {
		parent::__construct(
			'rss_post_aggregator_category_featured_images',
			__( 'RSS Feed with Images', 'wds-rss-post-Aggregator' ),
			array(
				'description' => __( 'Reads and displays a supplied RSS feed with featured images.', 'wds-rss-post-Aggregator' ),
			)
		);
	}

	/**
	 * Widget Form.
	 *
	 * @param  array $instance Widget Instance.
	 * @since 0.1.1
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );
		?>
		<p>
			<?php echo __( 'Title', 'wds-rss-post-aggregator' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			<?php echo __( 'Feed', 'wds-rss-post-Aggregator' ); ?>
			<?php echo $this->feed_links_dropdown( $this->get_field_name( 'feed_url' ), $instance['feed_url'] ); ?>
		</p>
		<p>
			<?php echo __( 'Number to show', 'wds-rss-post-aggregator' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>" type="text" value="<?php echo esc_attr( $instance['items'] ); ?>" />
		</p>
		<p>
			<?php echo __( 'Excerpt Length', 'wds-rss-post-aggregator' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="text" value="<?php echo esc_attr( $instance['excerpt_length'] ); ?>" />
		</p>
		<p>
			<?php echo __( '"Read More" text', 'wds-rss-post-aggregator' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'read_more_text' ); ?>" name="<?php echo $this->get_field_name( 'read_more_text' ); ?>" type="text" value="<?php echo esc_attr( $instance['read_more_text'] ); ?>" />
		</p>
		<?php
	}

	/**
	 * Update form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @since 0.1.1
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

		$rss_args = $this->rss_args;
		$rss_args['items'] = isset( $instance['items'] ) ? absint( $instance['items'] ) : $rss_args['items'];

		$rss = new RSS_Post_Aggregator_Feeds();
		$rss->rss_link = $instance['feed_url'];
		$rss->process_args( $rss_args );

		delete_transient( $rss->transient_id );

		return $instance;
	}

	/**
	 * Widget output.
	 *
	 * @since 0.1.1
	 *
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Widget instance.
	 * @return false           Return false if rss_post_aggregation_images_widget_visibility is set to on.
	 */
	public function widget( $args, $instance ) {

		$should_show = apply_filters( 'rss_post_aggregator_images_widget_visibility', 'on' );
		$length      = '';

		// Check if excerpt length is empty.
		if ( ! empty( $instance['excerpt_length'] ) ) {
			$length = absint( $instance['excerpt_length'] );
		}
		if ( 'on' !== $should_show ) {
			echo '';
			return;
		}

		echo $args['before_widget'];

		echo $args['before_title'];
		echo apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		echo $args['after_title'];

		if ( isset( $instance['excerpt_length'] ) && $length ) {
			$this->excerpt_length = $length;
			add_filter( 'rss_post_aggregator_feed_summary_length', array( $this, 'filter_excerpt_length' ), 10 );
		}

		$rss = new RSS_Post_Aggregator_Feeds();
		$rss_args = $this->rss_args;
		$rss_args['items'] = isset( $instance['items'] ) ? absint( $instance['items'] ) : $rss_args['items'];

		$feed_url = apply_filters( 'rss_post_aggregator_feed_location', $instance['feed_url'], $rss_args, $this->id_base );

		$posts = $rss->get_items( $feed_url, $rss_args );

		if ( isset( $this->excerpt_length ) ) {
			remove_filter( 'rss_post_aggregator_feed_summary_length', array( $this, 'filter_excerpt_length' ) );
		}

		if ( ! empty( $posts ) ) {
			echo '<ul class="rss-feed-posts">';
			foreach ( $posts as $p ) {
				echo '<li>';

				echo '<a class="post-title" href="' . esc_url( $p['link'] ) . '"/>';
				echo $p['title'];
				echo '</a>';

				if ( ! empty( $p['image'] ) ) {
					echo '<div class="rss-feed-post-image"><img class="featured-post-thumb alignleft" src="' . esc_attr( $p['image'] ) . '" /></div>';
				}

				$content = str_replace( '»', '', $p['summary'] );
				$content = str_replace( 'Read more', '', $content );
				$content = str_replace( '[]', '', $content );
				$content = htmlentities( $content, ENT_QUOTES, 'UTF-8' );
				$content = str_replace( array( '[&amp;hellip;]', '&amp;hellip;' ), '&hellip;', $content );

				echo wpautop( $content );

				if ( isset( $instance['read_more_text'] ) && trim( $instance['read_more_text'] ) ) {
					echo ' <a class="read-more" href="' . esc_url( $p['link'] ) . '">' . esc_html( $instance['read_more_text'] ) . '</a>';
				}

				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo __( 'Nothing yet! Check again later', 'wds-rss-post-aggregator' );
		}

		echo $args['after_widget'];
	}

	/**
	 * Feed Links dropdown select.
	 *
	 * @since 0.1.1
	 *
	 * @param  string $name      Dropdown name attribute.
	 * @param  string $term_name Name of Term.
	 * @return string            Returns the dropdwn html.
	 */
	function feed_links_dropdown( $name = '', $term_name = '' ) {
		$s = '<select name="' . esc_attr( $name ) . '" class="widefat">';

		$terms = get_terms( 'rss-feed-links', array(
			'hide_empty' => false,
		) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
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

	/**
	 * Get the excerpt_length.
	 *
	 * @since 0.1.1
	 *
	 * @param  integer $length Length of excerpt.
	 * @return integer         Return excerpt length.
	 */
	public function filter_excerpt_length( $length = 0 ) {
		if ( isset( $this->excerpt_length ) ) {
			$length = (int) $this->excerpt_length;
		}

		return $length;
	}

} // end class
