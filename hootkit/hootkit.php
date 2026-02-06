<?php
/**
 * Plugin Name:       HootKit
 * Description:       HootKit is a great companion plugin for WordPress themes by wpHoot.
 * Version:           3.0.4
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            wphoot
 * Author URI:        https://wphoot.com/
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain:       hootkit
 * Domain Path:       /languages
 * @package           Hootkit
 */

use \HootKit\Inc\HKConfig;
use \HootKit\Inc\Strings;
use \HootKit\Inc\Manifest;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Run in Debug mode to load unminified CSS and JS, and add other developer data to code.
 */
if ( !defined( 'HKIT_DEBUG' ) && defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
	define( 'HKIT_DEBUG', true );

/**
 * The core plugin class.
 *
 * @since   1.0.0
 * @package Hootkit
 */
if ( ! class_exists( 'HootKit' ) ) :

	class HootKit {

		/**
		 * Plugin Info
		 *
		 * @since  1.0.0
		 * @access public
		 */
		public $version;
		public $name;
		public $slug;
		public $file;
		public $dir;
		public $uri;
		public $plugin_basename;

		/**
		 * Constructor method.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Plugin Info
			$this->version         = '3.0.4';
			$this->name            = 'HootKit';
			$this->slug            = 'hootkit';
			$this->file            = __FILE__;
			$this->dir             = trailingslashit( plugin_dir_path( __FILE__ ) );
			$this->uri             = trailingslashit( plugin_dir_url( __FILE__ ) );
			$this->plugin_basename = plugin_basename(__FILE__);

			// Load Text Domain
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// (De)Activation Hook
			register_activation_hook( $this->file, array( $this, 'activate' ) );
			register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );

			// Load Plugin Files and Helpers
			$this->loader();
			add_action( 'plugins_loaded', array( $this, 'dashboardplugs' ), 5 );

			// Initiate Plugin
			add_action( 'after_setup_theme', array( $this, 'load_deprecated' ), 89 );
			add_action( 'after_setup_theme', array( $this, 'loadhootkit' ), 96 );

		}

		/**
		 * Load Plugin Text Domain
		 * @since  1.0.0
		 */
		public function load_plugin_textdomain() {
			$rootdir = dirname( $this->plugin_basename );
			$lang_dir = apply_filters( 'hootkit_languages_directory',  $rootdir . '/languages/' );
			load_plugin_textdomain(
				$this->slug,
				false,
				$lang_dir
			);
		}

		/**
		 * Run when plugin is activated
		 * @since  1.1.0
		 */
		public function activate() {
			$activation = get_option( 'hootkit-activate' );
			if ( !$activation ) {
				$activation = array(
					'time' => time(),
					'version' => $this->version
				);
				add_option( 'hootkit-activate', $activation );
			}
			do_action( 'hootkit/activate', $activation );
		}

		/**
		 * Run when plugin is deactivated
		 * @since  2.0.0
		 */
		public function deactivate() {
			$activation = get_option( 'hootkit-activate' );
			do_action( 'hootkit/deactivate', $activation );
		}

		/**
		 * Load Plugin Files and Helpers
		 *
		 * @since  2.0.0
		 * @access public
		 * @return void
		 */
		public function loader() {
			require_once( $this->dir . 'include/class-strings.php' );
			require_once( $this->dir . 'include/class-config.php' );
			require_once( $this->dir . 'include/class-manifest.php' );
			require_once( $this->dir . 'include/class-assets.php' );
		}

		/**
		 * Load Dashboard Plug Files and Helpers
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function dashboardplugs() {
			require_once( $this->dir . 'misc/import/class-import.php' );
			require_once( $this->dir . 'misc/code/class-customcode.php' );
			require_once( $this->dir . 'misc/tools/class-tools.php' );
			if ( is_admin() ) {
				require_once( $this->dir . 'admin/class-settings.php' );
				require_once( $this->dir . 'admin/class-dashmenu.php' );
			}
		}

		/**
		 * Load Deprecated functions
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function load_deprecated() {
			require_once( $this->dir . 'include/functions-deprecated.php' );
		}

		/**
		 * Plugin Loader
		 * Load plugin and modules
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function loadhootkit() {
			// Admin Functions and Settings
			if ( is_admin() ) {
				require_once( $this->dir . 'admin/functions.php' );
			}

			// Load general helper functions
			require_once( $this->dir . 'include/functions.php' );

			// Template Functions - may be required in admin for creating live preview eg. so page builder
			require_once( $this->dir . 'include/template-functions.php' );

			// Load Parts
			$modtypes = Manifest::$modtypes;
			$activemods = $this->get_config( 'activemods' );
			foreach ( $modtypes as $modtype ) {
				if ( !empty( $activemods[ $modtype ] ) ) {
					if ( file_exists( $this->dir . "{$modtype}s/class-{$modtype}s.php" ) )
						require_once( $this->dir . "{$modtype}s/class-{$modtype}s.php" );
					elseif ( file_exists( $this->dir . "{$modtype}/class-{$modtype}.php" ) )
						require_once( $this->dir . "{$modtype}/class-{$modtype}.php" );
					elseif ( file_exists( $this->dir . "{$modtype}/init.php" ) )
						require_once( $this->dir . "{$modtype}/init.php" );
				}
			}
			$tfilters = $this->get_config( 'theme-filters' );
			if ( !empty( $tfilters ) && is_array( $tfilters ) && !empty( $tfilters['fnspace'] ) ) {
				require_once( $this->dir . 'include/class-themes.php' );
			}
		}

		/**
		 * Get String values.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  string $key
		 * @param  string $default
		 * @return string
		 */
		public function get_string( $key, $default = '' ) {
			return Strings::get_string( $key, $default );
		}

		/**
		 * Get Config values.
		 *
		 * @since  1.0.0
		 * @access public
		 * @param  string $key    Config value to return / else return entire array
		 * @param  string $subkey Check for $subkey if config value is an array
		 * @return mixed
		 */
		public function get_config( $key = '', $subkey = '', $default = array() ) {
			if ( empty( $key ) )
				return HKConfig::$config;
			if ( ! is_string( $key ) || ! isset( HKConfig::$config[ $key ] ) )
				return $default;
			if ( empty( $subkey ) && $subkey !== 0 )
				return HKConfig::$config[ $key ];
			if ( is_string( $subkey ) || is_integer( $subkey ) ) {
				return ( isset( HKConfig::$config[ $key ][ $subkey ] ) ? HKConfig::$config[ $key ][ $subkey ] : $default );
			}
			if ( is_array( $subkey ) ) {
				$arr = HKConfig::$config[ $key ];
				foreach ( $subkey as $sub )
					if ( isset( $arr[ $sub ] ) )
						$arr = $arr[ $sub ];
					else
						return $default;
				return $arr;
			}
			return $default;
		}

		/**
		 * Get Active Modules from config
		 * Shortcut for hootkit()->get_config( 'activemods', $type )
		 *
		 * @since  2.0.0
		 */
		public function get_activemods( $type = '' ) {
			if ( $type && is_string( $type ) )
				return( ( isset( HKConfig::$config['activemods'][ $type ] ) ) ? HKConfig::$config['activemods'][ $type ] : array() );
			else
				return HKConfig::$config['activemods'];
		}

		/**
		 * Get HootKit manifest
		 *
		 * @since  1.2.0
		 * @access public
		 * @param  string $key 'modules' 'supports'
		 * @param  string|array $subkey Check for $subkey if $key value is an array
		 * @return mixed
		 */
		public function get_manifest( $key = '', $subkey = '', $default = array() ) {
			if ( empty( $key ) )
				return Manifest::$manifest;
			if ( ! is_string( $key ) || ! isset( Manifest::$manifest[ $key ] ) )
				return $default;
			if ( empty( $subkey ) && $subkey !== 0 )
				return Manifest::$manifest[ $key ];
			if ( is_string( $subkey ) || is_integer( $subkey ) ) {
				return ( is_array( Manifest::$manifest[ $key ] ) && isset( Manifest::$manifest[ $key ][ $subkey ] ) ? Manifest::$manifest[ $key ][ $subkey ] : $default );
			}
			if ( is_array( $subkey ) ) {
				$arr = Manifest::$manifest[ $key ];
				foreach ( $subkey as $sub )
					if ( is_array( $arr ) && isset( $arr[ $sub ] ) )
						$arr = $arr[ $sub ];
					else
						return $default;
				return $arr;
			}
			return $default;
		}

		/**
		 * Get Manifest modules info
		 * Shortcut for hootkit()->get_manifest( 'modules', array( $key, $subkey ) )
		 * 
		 * @since  3.0.0
		 * @param  string $key
		 * @param  string $subkey
		 * @return mixed
		 */
		public function get_mfmodules( $key = '', $subkey = '', $default = array() ) {
			if ( ! isset( Manifest::$manifest['modules'] ) || ! is_array( Manifest::$manifest['modules'] ) )
				return $default;
			if ( empty( $key ) )
				return Manifest::$manifest['modules'];
			if ( ! is_string( $key ) || ! isset( Manifest::$manifest['modules'][ $key ] ) )
				return $default;
			if ( empty( $subkey ) && $subkey !== 0 )
				return Manifest::$manifest['modules'][ $key ];
			if ( is_string( $subkey ) || is_integer( $subkey ) ) {
				return ( is_array( Manifest::$manifest['modules'][ $key ] ) && isset( Manifest::$manifest['modules'][ $key ][ $subkey ] ) ? Manifest::$manifest['modules'][ $key ][ $subkey ] : $default );
			}
			return $default;
		}

		/**
		 * Get all HootKit modules from Manifest of a specific type
		 *
		 * @since  2.0.0
		 * @param $type 'all' 'widget' 'block' 'misc'
		 * @param $list boolean
		 */
		public function get_mfmods_oftype( $type, $list = false ) {
			$modsoftype = array();
			if ( is_string( $type ) && ! empty( $type ) ) {
				foreach ( Manifest::$manifest['modules'] as $slug => $atts ) {
					if ( $type === 'all' ||
						( isset( $atts['types'] ) && \in_array( $type, $atts['types'] ) )
					) {
						if ( $list === false ) $modsoftype[ $slug ] = $atts;
						else $modsoftype[] = $slug;
					}
				}
			}
			return $modsoftype;
		}

		/**
		 * Check if theme supports a feature
		 * 
		 * @since  3.0.0
		 * @param  string $id
		 * @param  bool $iskey
		 * @return bool|mixed
		 */
		public function supports( $id, $iskey = false ) {
			if ( ! is_string( $id ) || empty( $id ) )
				return false;
			if ( $iskey ) {
				$supports = $this->get_config( 'supports' );
				return ( array_key_exists( $id, $supports ) ? $supports[ $id ] : false );
			}
			return ( in_array( $id, $this->get_config( 'supports' ) ) );
		}

		/**
		 * Returns the instance
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {

			static $instance = null;

			if ( is_null( $instance ) ) {
				$instance = new self;
			}

			return $instance;
		}

	}

	/**
	 * Gets the instance of the `HootKit` class. This function is useful for quickly grabbing data
	 * used throughout the plugin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	function hootkit() {
		return HootKit::get_instance();
	}

	// Lets roll!
	hootkit();

endif;