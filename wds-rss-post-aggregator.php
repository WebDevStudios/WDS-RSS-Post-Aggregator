<?php
/**
 * Plugin Name: RSS Post Aggregator
 * Plugin URI:  http://webdevstudios.com
 * Description: Aggregate posts from RSS Feeds
 * Version:     1.0.0
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
 * Autolaods namespaced classes.
 *
 * @param $class_name
 *
 * @return void
 *
 * @author JayWood
 * @since  NEXT
 */
function autoload_classes( $class_name ) {

	if ( false === strpos( $class_name, 'WebDevStudios\RSS_Post_Aggregator' ) ) {
		return;
	}

	// Break everything into parts.
	$class_array = explode( '\\', $class_name );

	// Build the filename from the last item in the array.
	$filename = strtolower( str_ireplace( '_', '-', end( $class_array ) ) );

	/*
	 * Cut off the first two, and last item from the array, this gives us the folder(s)
	 * where the namespaced file will live.
	*/
	$new_dir = array_slice( $class_array, 2, count( $class_array ) - 3 );

	// Glue the pieces back together.
	$new_dir = implode( '/', array_map( 'strtolower', $new_dir ) );

	RSS_Post_Aggregator::load_class( $filename, trailingslashit( 'includes/' . $new_dir ) );
}
spl_autoload_register( '\WebDevStudios\RSS_Post_Aggregator\autoload_classes' );

class RSS_Post_Aggregator {

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Sets up our plugin
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$this->plugin_classes();
	}

	/**
	 * Instance of RSS_Aggregator
	 *
	 * @var RSS_Post_Aggregator
	 */
	public static $instance = null;

	public static function init() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add hooks and filters
	 *
	 * @return void
	 */
	public function hooks() {
		// All hooks here.
	}


	/**
	 * Require a file from the includes directory
	 *
	 * @param string $filename Name of the file to be included.
	 * @param string $dir      Directory of the include, defaults to includes/
	 *
	 * @return bool   Result of include call.
	 */
	public static function load_class( $filename, $dir = 'includes' ) {

		if ( ! empty( $dir ) ) {
			$dir = trailingslashit( $dir );
		}

		$file = self::dir( sprintf( '%1$sclass-%2$s.php', $dir, $filename ) );
		if ( file_exists( $file ) ) {
			return require_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}

	/**
	 * Renders a view file
	 *
	 * @param string $file
	 */
	public static function get_view( $file ) {
		$file = '/views/' . $file . '.php';
		$dir = dirname( __FILE__ );

		if ( file_exists( $dir . $file ) ) {
			include_once $dir . $file;
		}
	}

	/**
	 * Loads required classes into the singleton.
	 *
	 * @return void
	 *
	 * @author JayWood
	 * @since  NEXT
	 */
	private function plugin_classes() {
		$this->cpt = new CPT\RSS_Post( $this );
	}
}

function load() {
	return RSS_Post_Aggregator::init();
}
add_action( 'plugins_loaded', array( load(), 'hooks' ) );

// /**
//  * Main initiation class
//  *
//  * @property string $tax_slug
//  * @property string $cpt_slug
//  * @property string $rss_category_slug
//  */
// class RSS_Post_Aggregator {
//
// 	const VERSION = '1.0.0';
// 	private $cpt_slug          = 'rss-posts';
// 	private $tax_slug          = 'rss-feed-links';
// 	private $rss_category_slug = 'rss-category';
//
// 	/**
// 	 * @var RSS_Post_Aggregator_CPT
// 	 */
// 	public $rsscpt;
//
// 	/**
// 	 * @var RSS_Post_Aggregator_Taxonomy
// 	 */
// 	public $taxonomy;
//
// 	/**
// 	 * @var RSS_Post_Aggregator_Feeds
// 	 */
// 	public $rss;
//
// 	/**
// 	 * @var RSS_Post_Aggregator_Modal
// 	 */
// 	public $modal;
//
// 	/**
// 	 * @var RSS_Post_Aggregator_Frontend
// 	 */
// 	public $frontend;
//
// 	/**
// 	 * @var RSS_Post_Aggregator_Widgets
// 	 */
// 	public $widgets;
//
// 	/**
// 	 * @var Taxonomy_Core
// 	 */
// 	public $rss_category;
//
//
// 	/**
// 	 * Sets up our plugin
// 	 * @since  0.1.0
// 	 */
// 	public function __construct() {
// 		$this->plugin_classes();
// 	}
//
// 	/**
// 	 * Retains all plugin cleasses for organization
// 	 *
// 	 * @since 0.1.1
// 	 * @author JayWood
// 	 */
// 	public function plugin_classes() {
// 		$this->rsscpt   = new RSS_Post_Aggregator_CPT( $this->cpt_slug, $this->tax_slug );
// 		$this->taxonomy = new RSS_Post_Aggregator_Taxonomy( $this->tax_slug, $this->rsscpt );
// 		$this->rss      = new RSS_Post_Aggregator_Feeds();
// 		$this->modal    = new RSS_Post_Aggregator_Modal( $this->rss, $this->rsscpt, $this->taxonomy );
//
// 		// Handles frontend modification for aggregate site
// 		$this->frontend = new RSS_Post_Aggregator_Frontend( $this->rsscpt );
// 		$this->widgets = new RSS_Post_Aggregator_Widgets();
//
// 		$this->rss_category = register_via_taxonomy_core( array(
// 			__( 'RSS Category', 'wds-rss-post-aggregator' ),
// 			__( 'RSS Categories', 'wds-rss-post-aggregator' ),
// 			$this->rss_category_slug,
// 		), array(), array( $this->cpt_slug ) );
// 	}
//
// 	public function hooks() {
// 		register_activation_hook( __FILE__, array( $this, '_activate' ) );
// 		register_deactivation_hook( __FILE__, array( $this, '_deactivate' ) );
// 		add_action( 'init', array( $this, 'init' ) );
// 		add_action( 'admin_init', array( $this, 'admin_hooks' ) );
//
// 		$this->rsscpt->hooks();
// 		$this->taxonomy->hooks();
// 		$this->modal->hooks();
// 		$this->frontend->hooks();
// 		$this->widgets->hooks();
// 	}
//
// 	/**
// 	 * Activate the plugin
// 	 */
// 	function _activate() {
// 		// Make sure any rewrite functionality has been loaded
// 		flush_rewrite_rules();
// 		add_option( 'wds_rss_aggregate_saved_feed_urls', array(), '', 'no' );
// 	}
//
// 	/**
// 	 * Deactivate the plugin
// 	 * Uninstall routines should be in uninstall.php
// 	 */
// 	function _deactivate() {
//
// 	}
//
// 	/**
// 	 * Init hooks
// 	 * @since  0.1.0
// 	 * @return null
// 	 */
// 	public function init() {
// 		$locale = apply_filters( 'plugin_locale', get_locale(), 'wds-rss-post-aggregator' );
// 		load_textdomain( 'wds-rss-post-aggregator', WP_LANG_DIR . '/wds-rss-post-aggregator/wds-rss-post-aggregator-' . $locale . '.mo' );
// 		load_plugin_textdomain( 'wds-rss-post-aggregator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
// 	}
//
// 	/**
// 	 * Hooks for the Admin
// 	 * @since  0.1.0
// 	 * @return null
// 	 */
// 	public function admin_hooks() {
// 	}
//
// 	/**
// 	 * Include a file from the includes directory
// 	 * @since  0.1.0
// 	 *
// 	 * @param  string $filename Name of the file to be included.
// 	 */
// 	public static function include_file( $filename ) {
// 		$file = self::dir( 'includes/' . $filename . '.php' );
// 		if ( file_exists( $file ) ) {
// 			return include_once( $file );
// 		}
//
// 		return false;
// 	}
//
// 	/**
// 	 * This plugin's directory
// 	 * @since  0.1.0
// 	 * @param  string $path (optional) appended path
// 	 * @return string       Directory and path
// 	 */
// 	public static function dir( $path = '' ) {
// 		static $dir;
// 		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
// 		return $dir . $path;
// 	}
//
// 	/**
// 	 * This plugin's url
// 	 * @since  0.1.0
// 	 * @param  string $path (optional) appended path
// 	 * @return string       URL and path
// 	 */
// 	public static function url( $path = '' ) {
// 		static $url;
// 		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
// 		return $url . $path;
// 	}
//
// 	/**
// 	 * Magic getter for our object.
// 	 *
// 	 * @param string $field
// 	 *
// 	 * @throws Exception Throws an exception if the field is invalid.
// 	 *
// 	 * @return mixed
// 	 */
// 	public function __get( $field ) {
// 		switch ( $field ) {
// 			case 'cpt_slug':
// 			case 'tax_slug':
// 			case 'rss_category_slug':
// 				return $this->{$field};
// 			default:
// 				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
// 		}
// 	}
// }
//
// // init our class
//
// $RSS_Post_Aggregator = new RSS_Post_Aggregator();
// $RSS_Post_Aggregator->hooks();
