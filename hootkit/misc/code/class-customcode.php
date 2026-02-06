<?php
/**
 * Custom Code class
 * This file is loaded at plugins_loaded@5
 *
 * @since   3.0.0
 * @package Hootkit
 */

namespace HootKit\Mods;
use \HootKit\Inc\Helper_Assets;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Mods\Code' ) ) :

	class Code {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Is this a plugin?
		 */
		private static $codeplugin = false;

		/**
		 * Plugin subdirectory
		 */
		public $dir;
		public $uri;

		/**
		 * Theme Slug
		 */
		public $themeslug = 'hootkit';

		/**
		 * Setup Custom Code
		 */
		public function __construct() {
			// Set directories
			$this->dir             = trailingslashit( hootkit()->dir . 'misc/code' );
			$this->uri             = trailingslashit( hootkit()->uri . 'misc/code' );

			// Set slug
			$theme = wp_get_theme();
			$theme_name = is_object( $theme ) && method_exists( $theme, 'parent' ) && is_object( $theme->parent() ) ? $theme->parent()->get( 'Name' ) : $theme->get( 'Name' );
			$theme_name = is_string( $theme_name ) ? strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '_', trim( $theme_name ) ) ) : '';
			if ( $theme_name ) {
				$this->themeslug = $theme_name;
			}
			add_action( 'after_setup_theme', array( $this, 'loader' ), 94 );
			// Check if code has been disabled by user
			$isactive = false;
			$dbvalue = get_option( 'hootkit-activemods', false );
			if ( ! is_array( $dbvalue ) || empty( $dbvalue ) ) {
				$isactive = true;
			} elseif ( !empty( $dbvalue[ 'misc' ] ) && is_array( $dbvalue[ 'misc' ] ) ) {
				if ( !isset( $dbvalue[ 'misc' ][ 'code' ] ) || $dbvalue[ 'misc' ][ 'code' ] == 'yes' ) {
					$isactive = true;
				}
			} else {
				$isactive = true;
			}
			if ( $isactive &&
				! function_exists( 'olius_premium_customcode_run' ) && ! function_exists( 'strute_premium_customcode_run' ) && ! function_exists( 'nirvata_premium_checkfortheme' ) ) {
				require_once( $this->dir . 'coderun.php' );
				new CustomcodeRun( $this->dir, $this->themeslug );
			}

		}

		/**
		 * Load if code is enabled by themes
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function loader() {

			/* Get values if Code is an embedded plugin */
			$dash = hootkit()->get_config( 'dashboard' );
			// if registered wphoot theme, sanitizeconfig has already made sure all required values exist
			// 'code', 'tabfilter', 'tabaction', hoot_dashboard() => we can reliably use them here.
			if ( is_array( $dash ) && !empty( $dash[ 'code' ] ) ) {
				self::$codeplugin = array(
					'pagehook'  => hoot_dashboard( 'screen' ),
					'dashurl'   => hoot_dashboard( 'url', array( 'tab' => $dash[ 'code' ] ) ),
					'tabfilter' => $dash['tabfilter'],
					'tabaction' => $dash['tabaction']
				);
			}

			if ( self::$codeplugin ) {

				// Check if code has been disabled by user
				$activemiscmods = hootkit()->get_config( 'activemods', 'misc' );
				$isactive = is_array( $activemiscmods ) && in_array( 'code', $activemiscmods );

				if ( ! $isactive ) :
					add_filter( self::$codeplugin['tabfilter'], array( $this, 'unplug_tabs' ), 90, 2 );
				else:
					// Load code assets
					$hooks = array( self::$codeplugin['pagehook'] );
					Helper_Assets::add_adminasset( 'hootkitcode', $hooks );
					// Render Content
					add_filter( self::$codeplugin['tabfilter'], array( $this, 'plug_tabs' ), 90, 2 );
					add_action( self::$codeplugin['tabaction'], array( $this, 'plug_modblock_content' ), 90, 4 );
					// // Localize Script
					// add_action( 'admin_enqueue_scripts', array( $this, 'localize_script' ), 11 );
					// // Disable the WooCommerce Setup Wizard on Hoot Import page only
					// add_action( 'current_screen', array( $this, 'woocommerce_disable_setup_wizard' ) );
					// // Flush rewrite rules from a recent WooCommerce XML import
					// if ( get_option( 'hootkitimport_wc_flush' ) ) {
					// 	add_action( 'admin_menu', array( $this, 'woocommerce_flush' ), 5 );
					// }
					// Add Module
					if ( is_admin() && current_user_can( 'manage_options' ) ) {
						require_once( $this->dir . 'code.php' );
					}
				endif;
			}
		}

		/**
		 * Remove Tabs if exist
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function unplug_tabs( $tabsarray, $sanetags ) {
			$order = !empty( $tabsarray['order'] ) && is_array( $tabsarray['order'] ) ? $tabsarray['order'] : array();
			$order = array_values( array_diff( $order, array( 'code' ) ) ); // array_values() to reindex numerically
			$tabsarray['order'] = $order;
			if ( isset( $tabsarray['code'] ) ) unset( $tabsarray['code'] );
			return $tabsarray;
		}

		/**
		 * Load Tabs Content
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function plug_tabs( $tabsarray, $sanetags ) {
			$order = !empty( $tabsarray['order'] ) && is_array( $tabsarray['order'] ) ? $tabsarray['order'] : array();

			if ( !in_array( 'code', $order ) ) $order[] = 'code';
			$tabsarray['code'] = array(
				'label'   => __( 'Custom Code', 'hootkit' ),
				'inpage'  => true,
				'content' => $this->plug_displayarray( $sanetags ),
			);

			$tabsarray['order'] = $order;
			return $tabsarray;
		}

		/**
		 * Tabs Module Data
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function plug_displayarray( $sanetags ) {
			$cblocks = array();

			$cblocks[ 'grid-ccode' ] = array( 'type' => 'gridgen' );
			$cblocks[ 'ccode' ] = array(
				'type' => 'customcode',
			);
			$cblocks[ 'grid-ccodeend' ] = array( 'type' => 'gridgenend' );

			return $cblocks;
		}

		/**
		 * Extra Tabs Module Block Templates
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function plug_modblock_content( $blockid, $modblock, $sanetags, $tabid ) {
			if ( empty( $modblock ) || ! is_array( $modblock ) || ! isset( $modblock['type'] ) || $modblock['type'] !== 'customcode' ) {
				return;
			}
			$sanetags = is_array( $sanetags ) ? $sanetags : array();

			if ( ! current_user_can( 'manage_options' ) ) :
				echo '<p class="hootabt-notice hootabt-notice--error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'You do not have sufficient permissions to access these options. Please contact the site administrator', 'hootkit' ) . '</p>';
			else:
				echo '<div id="hoot-code" class="hoot-code">';
				do_action( 'hoot_manager_code', $sanetags, self::$codeplugin );
				echo '</div>';
			endif;
		}

		/**
		 * Returns $codeplugin
		 * @since  3.0.0
		 * @access public
		 * @return object
		 */
		public function codeplugin_data( $key = null ) {
			if ( $key && isset( self::$codeplugin[ $key ] ) ) {
				return self::$codeplugin[ $key ];
			}
			return self::$codeplugin;
		}

		/**
		 * Returns the instance
		 * @since  3.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

	}

	/**
	 * Gets the instance of the class. This function is useful for quickly grabbing data
	 * used throughout the plugin.
	 * @since  3.0.0
	 * @access public
	 * @return object
	 */
	function hootkitcustomcode() {
		return Code::get_instance();
	}

	// Lets roll!
	hootkitcustomcode();

endif;