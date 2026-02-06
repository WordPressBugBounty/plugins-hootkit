<?php
/**
 * Admin Main Menu class
 * This file is loaded at plugins_loaded@5 for is_admin()
 *
 * @since   3.0.0
 * @package Hootkit
 */

namespace HootKit\Admin;
use \HootKit\Inc\Manifest;
use \HootKit\Inc\Helper_Assets;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Admin\DashMenu' ) ) :

	class DashMenu {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Is this a plugin?
		 */
		private static $dashmenu = false;

		/**
		 * Setup Admin DashMenu
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'loader' ), 92 );
		}

		/**
		 * Load if dashmenu is enabled by themes
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function loader() {

			/* Get values if DashMenu is an embedded plugin */
			$dash = hootkit()->get_config( 'dashboard' );
			// if registered wphoot theme, sanitizeconfig has already made sure all required values exist
			// 'dashmenu', 'aboutfilter', hoot_dashboard() => we can reliably use them here.
			if ( is_array( $dash ) && !empty( $dash['dashmenu'] ) && !empty( $dash['aboutfilter'] ) ) {
				self::$dashmenu = array(
					'dashmenu'    => $dash['dashmenu'],
					'aboutfilter' => $dash['aboutfilter']
				);
			}

			if ( self::$dashmenu ) {
				global $hootkitmenu;
				if ( $hootkitmenu === false )
					return;
				$hootkitmenu = true;
				add_action( 'admin_menu', array( $this, 'add_dashboard_menu' ) );
				add_filter( self::$dashmenu['aboutfilter'], array( $this, 'abouttags' ) );
			}
		}

		/**
		 * Add Menu
		 */
		public static function add_dashboard_menu() {
			$label = trim( str_ireplace( 'dashboard', '', hoot_dashboard( 'label' ) ) );
			$icon = '';
			if ( function_exists( 'hoot_data' ) ) {
				$dir = hoot_data( 'incdir' );
				$uri = hoot_data( 'incuri' );
				$icon = !empty( $dir ) && is_string( $dir ) && !empty( $uri ) && is_string( $uri ) && file_exists( $dir . 'admin/images/logoicon.png' ) ? $uri . 'admin/images/logoicon.png' : 'dashicons-image-filter';
			}
			add_menu_page(
				esc_html( hoot_dashboard( 'label' ) ), // Page Title
				$label, // Menu Title
				'edit_theme_options', // capability
				sanitize_html_class( hoot_dashboard( 'slug' ) ), // menu-slug
				self::$dashmenu['dashmenu'], // function name
				$icon, // icon_url
				60 // position
			);
		}

		/**
		 * Update abouttags
		 */
		public static function abouttags( $tags ) {
			$tags['dashslug'] = 'hoot-dashboard';
			$tags['dashurl'] = 'admin.php?page=hoot-dashboard';
			$tags['dashscreen'] = 'toplevel_page_hoot-dashboard';
			return $tags;
		}

		/**
		 * Returns the instance
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

	}

	DashMenu::get_instance();

endif;