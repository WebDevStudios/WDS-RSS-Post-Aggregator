<?php
/**
 * Plugin Name: RSS Post Aggregator
 * Plugin URI:  http://webdevstudios.com
 * Description: Aggregate posts from RSS Feeds
 * Version:     0.1.1
 * Author:      WebDevStudios, Justin Sternberg
 * Author URI:  http://webdevstudios.com
 * Donate link: http://webdevstudios.com
 * License:     GPLv2+
 * Text Domain: wds-rss-post-aggregator
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 WebDevStudios, Justin Sternberg (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Our namespace.
namespace WebDevStudios\RSS_Post_Aggregator;

/**
 * Autoloads files with classes when needed
 * @since  0.1.0
 * @param  string $class_name Name of the class being requested
 */
function rss_post_aggregator_autoload_classes( $class_name ) {
	if ( class_exists( $class_name, false ) || false === stripos( $class_name, 'RSS_Post_Aggregator_' ) ) {
		return;
	}

	// If our class doesn't have our namespace, don't load it.
	if ( 0 !== strpos( $class_name, 'WebDevStudios\\RSS_Post_Aggregator\\' ) ) {
		return;
	}

	$parts = explode( '\\', $class_name );

	$filename = strtolower( str_ireplace( 'RSS_Post_Aggregator_', '', end( $parts ) ) );

	RSS_Post_Aggregator::include_file( $filename );
}

spl_autoload_register( __NAMESPACE__ . '\rss_post_aggregator_autoload_classes' );

/**
 * Main initiation class
 *
 * @property string $tax_slug
 * @property string $cpt_slug
 * @property string $rss_category_slug
 */
class RSS_Post_Aggregator {

	const VERSION = '0.1.1';
	private $cpt_slug          = 'rss-posts';
	private $tax_slug          = 'rss-feed-links';
	private $rss_category_slug = 'rss-category';

	/**
	 * @var RSS_Post_Aggregator_CPT
	 */
	public $rsscpt;

	/**
	 * @var RSS_Post_Aggregator_Taxonomy
	 */
	public $taxonomy;

	/**
	 * @var RSS_Post_Aggregator_Feeds
	 */
	public $rss;

	/**
	 * @var RSS_Post_Aggregator_Modal
	 */
	public $modal;

	/**
	 * @var RSS_Post_Aggregator_Frontend
	 */
	public $frontend;

	/**
	 * @var RSS_Post_Aggregator_Widgets
	 */
	public $widgets;

	/**
	 * @var Taxonomy_Core
	 */
	public $rss_category;


	/**
	 * Sets up our plugin
	 * @since  0.1.0
	 */
	public function __construct() {
		$this->plugin_classes();
	}

	/**
	 * Retains all plugin cleasses for organization
	 *
	 * @since 0.1.1
	 * @author JayWood
	 */
	public function plugin_classes() {
		$this->rsscpt   = new RSS_Post_Aggregator_CPT( $this->cpt_slug, $this->tax_slug );
		$this->taxonomy = new RSS_Post_Aggregator_Taxonomy( $this->tax_slug, $this->rsscpt );
		$this->rss      = new RSS_Post_Aggregator_Feeds();
		$this->modal    = new RSS_Post_Aggregator_Modal( $this->rss, $this->rsscpt, $this->taxonomy );

		// Handles frontend modification for aggregate site
		$this->frontend = new RSS_Post_Aggregator_Frontend( $this->rsscpt );
		$this->widgets = new RSS_Post_Aggregator_Widgets();

		$this->rss_category = register_via_taxonomy_core( array(
			__( 'RSS Category', 'wds-rss-post-aggregator' ),
			__( 'RSS Categories', 'wds-rss-post-aggregator' ),
			$this->rss_category_slug,
		), array(), array( $this->cpt_slug ) );
	}

	public function hooks() {
		register_activation_hook( __FILE__, array( $this, '_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, '_deactivate' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_hooks' ) );

		$this->rsscpt->hooks();
		$this->taxonomy->hooks();
		$this->modal->hooks();
		$this->frontend->hooks();
		$this->widgets->hooks();
	}

	/**
	 * Activate the plugin
	 */
	function _activate() {
		// Make sure any rewrite functionality has been loaded
		flush_rewrite_rules();
		add_option( 'wds_rss_aggregate_saved_feed_urls', array(), '', 'no' );
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 */
	function _deactivate() {

	}

	/**
	 * Init hooks
	 * @since  0.1.0
	 * @return null
	 */
	public function init() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wds-rss-post-aggregator' );
		load_textdomain( 'wds-rss-post-aggregator', WP_LANG_DIR . '/wds-rss-post-aggregator/wds-rss-post-aggregator-' . $locale . '.mo' );
		load_plugin_textdomain( 'wds-rss-post-aggregator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Hooks for the Admin
	 * @since  0.1.0
	 * @return null
	 */
	public function admin_hooks() {
	}

	/**
	 * Include a file from the includes directory
	 * @since  0.1.0
	 *
	 * @param  string $filename Name of the file to be included.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/' . $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}

		return false;
	}

	/**
	 * This plugin's directory
	 * @since  0.1.0
	 * @param  string $path (optional) appended path
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 * @since  0.1.0
	 * @param  string $path (optional) appended path
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field
	 *
	 * @throws Exception Throws an exception if the field is invalid.
	 *
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'cpt_slug':
			case 'tax_slug':
			case 'rss_category_slug':
				return $this->{$field};
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}
}

// init our class

$RSS_Post_Aggregator = new RSS_Post_Aggregator();
$RSS_Post_Aggregator->hooks();
