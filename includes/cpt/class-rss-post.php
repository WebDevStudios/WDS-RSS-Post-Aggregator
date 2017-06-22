<?php

// Our namespace.
namespace WebDevStudios\RSS_Post_Aggregator\CPT;
use CPT_Core;
use WebDevStudios\RSS_Post_Aggregator\RSS_Post_Aggregator;

if ( ! class_exists( 'CPT_Core' ) ) {
	require_once RSS_Post_Aggregator::dir( 'includes/libraries/CPT_Core/CPT_Core.php' );
}

/**
 * CPT child class
 */
class RSS_Post extends CPT_Core {

	/**
	 * Prefix.
	 *
	 * @since 0.1.1
	 *
	 * @var string
	 */
	public $prefix = '_rsspost_';

	/**
	 * Redirect slug.
	 *
	 * @since 0.1.1
	 *
	 * @var string
	 */
	public $slug_to_redirect = 'rss_search_modal';

	protected $post_type = 'rss-posts';

	/**
	 * Instance of the parent plugin class.
	 *
	 * @since NEXT
	 *
	 * @var RSS_Post_Aggregator
	 */
	private $plugin;

	/**
	 * Register Custom Post Types. See documentation in CPT_Core, and in wp-includes/post.php
	 *
	 * @since 0.1.1
	 *
	 * @param RSS_Post_Aggregator $plugin
	 */
	public function __construct( $plugin ) {

		// Bring over the instance of the parent.
		$this->plugin = $plugin;

		// Register this cpt
		parent::__construct(
			array( __( 'RSS Post', 'wds-rss-post-aggregator' ), __( 'RSS Posts', 'wds-rss-post-aggregator' ), $this->post_type ),
			array(
				'supports'  => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
				'menu_icon' => 'dashicons-rss',
			)
		);

		$this->hooks();
	}

	/**
	 * Initiate hooks.
	 *
	 * @since 0.1.1
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'pseudo_menu_item' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
	}

	/**
	 * Redirect menu item.
	 *
	 * @since 0.1.1
	 *
	 * @return void Return early if requirements are not met.
	 */
	public function pseudo_menu_item() {
		add_submenu_page( 'edit.php?post_type=' . $this->post_type(), '', esc_html__( 'Find RSS Post', 'wds-rss-post-aggregator' ), 'edit_posts', $this->slug_to_redirect, '__return_empty_string' );

		if ( ! isset( $_GET['page'] ) || $this->slug_to_redirect != $_GET['page'] ) {
			return;
		}

		$redirect = add_query_arg( array(
			'post_type'             => $this->post_type,
			$this->slug_to_redirect => true,
		), admin_url( '/edit.php' ) );

		wp_redirect( esc_url( $redirect ) );
		exit();
	}

	/**
	 * Check if listing screen.
	 *
	 * @since 0.1.1
	 *
	 * @return boolean Returns boolean.
	 */
	public function is_listing() {
		if ( isset( $this->is_listing ) ) {
			return $this->is_listing;
		}

		$screen = get_current_screen();
		$this->is_listing = isset( $screen->base, $screen->post_type ) && 'edit' == $screen->base && $this->post_type() == $screen->post_type;

		return $this->is_listing;
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 *
	 * @todo Source column currently pulls nothing, and taxonomy-rss-category is always empty.
	 * @param  array $columns Array of registered column names/labels
	 *
	 * @return array           Modified array
	 */
	public function columns( $columns ) {
		$columns = array(
			'thumbnail'             => esc_html__( 'Thumbnail', 'wds-rss-post-aggregator' ),
			'cb'                    => $columns['cb'],
			'title'                 => $columns['title'],
			'source'                => esc_html__( 'Source', 'wds-rss-post-aggregator' ),
			'taxonomy-rss-category' => $columns['taxonomy-rss-category'],
			'date'                  => $columns['date'],
		);
		return $columns;
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 * @since  0.1.0
	 *
	 * @param  array $column Array of registered column names
	 * @param int    $post_id
	 */
	public function columns_display( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'thumbnail':
				$size = isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ? 'thumb' : array( 50, 50 );
				the_post_thumbnail( $size );
				break;

			case 'source':
				$link = rss_post_get_feed_url( $post->ID );
				if ( $link ) {
					echo '<a target="_blank" href="' . esc_url( $link ) . '">' . $link . '</a>';
				}
				break;
		}
	}

	/**
	 * Loads up metaboxes.
	 *
	 * @since 0.1.1
	 * @author JayWood
	 */
	public function add_meta_box() {
		add_meta_box( 'rsslink_mb', esc_html__( 'RSS Item Info', 'wds-rss-post-aggregator' ), array( $this, 'render_metabox' ), $this->post_type() );
	}

	/**
	 * Renders custom metabox output.
	 *
	 * @param \WP_Post $wp_post The post the metabox is being rendered on.
	 *
	 * @since 0.1.1
	 * @author JayWood
	 */
	public function render_metabox( $wp_post ) {
		wp_nonce_field( 'rsslink_mb_metabox', 'rsslink_mb_nonce' );

		$meta       = get_post_meta( $wp_post->ID, $this->prefix . 'original_url', 1 );
		$meta_value = empty( $meta ) ? '' : esc_url( $meta );

		?>
		<fieldset>
			<label for="<?php echo $this->prefix; ?>original_url"><?php esc_html_e( 'Original URL', 'wds-rss-post-aggregator' ); ?></label><br />
			<input name="<?php echo $this->prefix; ?>original_url" id="<?php echo $this->prefix; ?>original_url" value="<?php echo $meta_value; ?>" class="regular-text" />
		</fieldset>
		<?php
	}

	/**
	 * Save the post meta.
	 *
	 * @since 0.1.1
	 *
	 * @param integer $post_id The post ID being saved.
	 *
	 * @author JayWood
	 * @return void Bail early if requirements aren't met.
	 */
	public function save_meta( $post_id ) {
		if ( ( ! isset( $_POST['rsslink_mb_nonce'] ) || ! wp_verify_nonce( $_POST['rsslink_mb_nonce'], 'rsslink_mb_metabox' ) )
			|| ! current_user_can( 'edit_post', $post_id )
			|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ! isset( $_POST[ $this->prefix . 'original_url' ] )
		) {
			return;
		}

		$url = esc_url( $_POST[ $this->prefix . 'original_url' ] );

		update_post_meta( $post_id, $this->prefix . 'original_url', $url );
	}

	/**
	 * Inserts the feed post items.
	 *
	 * @param array $post_data An array of post data, similar to WP_Post
	 * @param int   $feed_id   A term ID from the links taxonomy.
	 *
	 * @since 0.1.0
	 *
	 * @author JayWood, Justin Sternberg
	 * @return array|string
	 */
	public function insert( $post_data, $feed_id ) {
		$args = array(
			'post_content'  => wp_kses_post( stripslashes( $post_data['summary'] ) ),
			'post_title'    => esc_html( stripslashes( $post_data['title'] ) ),
			'post_status'   => 'draft',
			'post_type'     => $this->post_type(),
			'post_date'     => date( 'Y-m-d H:i:s', strtotime( $post_data['date'] ) ),
			'post_date_gmt' => gmdate( 'Y-m-d H:i:s', strtotime( $post_data['date'] ) ),
		);

		$existing_post = $this->post_exists( $post_data['link'] );
		if ( $existing_post ) {
			$args['ID'] = $existing_post->ID;
			$args['post_status'] = $existing_post->post_status;
		}

		$post_id = wp_insert_post( $args );
		if ( $post_id ) {
			$report = array(
				'post_id'           => $post_id,
				'original_url'      => update_post_meta( $post_id, $this->prefix . 'original_url', esc_url_raw( $post_data['link'] ) ),
				'img_src'           => $this->sideload_featured_image( esc_url_raw( $post_data['image'] ), $post_id ),
				// TODO Update the $this->tax_slug reference once taxonomy is loaded propertly.
				'wp_set_post_terms' => wp_set_post_terms( $post_id, array( $feed_id ), $this->tax_slug, true ),
			);
		} else {
			$report = 'failed';
		}

		return $report;
	}

	/**
	 * Check if post exists via Url.
	 *
	 * @param string $url The full URL to check against.
	 *
	 * @since 0.1.0
	 *
	 * @author JayWood, Justin Sternberg
	 * @return \WP_Post|false An array of post data if found, false otherwise.
	 */
	public function post_exists( $url ) {
		$args = array(
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'pending', 'draft', 'future' ),
			'post_type'      => $this->post_type(),
			'meta_key'       => $this->prefix . 'original_url',
			'meta_value'     => esc_url_raw( $url ),
		);
		$posts = get_posts( $args );

		return $posts && is_array( $posts ) ? $posts[0] : false;
	}

	/**
	 * Import image via url.
	 *
	 * @since 0.1.1
	 *
	 * @param string $file_url A full HTTP url address to an image.
	 * @param int    $post_id  The post ID to assign the image.
	 *
	 * @author JayWood, Justin Sternberg
	 * @return \WP_Error|string A full attachment URL once imported, WP_Error otherwise.
	 */
	public function sideload_featured_image( $file_url, $post_id ) {
		if ( empty( $file_url ) || empty( $post_id ) ) {
			return false;
		}

		// Set variables for storage, fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file_url, $matches );
		$file_array = array();
		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $file_url );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return $file_array['tmp_name'];
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, $post_id );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );  // @codingStandardsIgnoreLine
			return $id;
		}

		$src = wp_get_attachment_url( $id );

		if ( $src ) {
			set_post_thumbnail( $post_id, $id );
		}

		return $src;
	}
}

/**
 * Get RSS feed object.
 *
 * @param bool|WP_Post|int $post
 *
 * @since 0.1.0
 *
 * @author JayWood, Justin Sternberg
 * @return string
 */
function rss_post_get_feed_object( $post = false ) {
	global $RSS_Post_Aggregator;

	if ( ! $post ) {
		$post = get_post( get_the_ID() );
	} else {
		$post = $post && is_int( $post ) ? get_post( $post ) : $post;
	}

	if ( isset( $post->source_link ) ) {
		return $post->source_link;
	}

	$links = get_the_terms( $post->ID, $RSS_Post_Aggregator->tax_slug );
	$post->source_link = ( $links && is_array( $links ) )
		? array_shift( $links )
		: '';

	return $post->source_link;
}

/**
 * Get RSS feed name.
 *
 * @param bool|WP_Post|int $post
 *
 * @since 0.1.0
 *
 * @author JayWood, Justin Sternberg
 * @return bool|string
 */
function rss_post_get_feed_url( $post = false ) {
	$feed = rss_post_get_feed_object( $post );

	if ( $feed && isset( $feed->name ) ) {
		return $feed->name;
	}

	return false;
}

/**
 * Get RSS feed source.
 *
 * @param bool|WP_Post|int $post
 *
 * @since 0.1.0
 *
 * @author JayWood, Justin Sternberg
 * @return bool|string
 */
function rss_post_get_feed_source( $post = false ) {

	$feed = rss_post_get_feed_object( $post );
	if ( $feed ) {
		if ( isset( $feed->description ) && $feed->description ) {
			return esc_html( $feed->description );
		}
	}

	$url = rss_post_get_feed_url( $post );
	if ( $url ) {
		$parts = parse_url( $url );
		return isset( $parts['host'] ) ? $parts['host'] : '';
	}

	return false;
}
