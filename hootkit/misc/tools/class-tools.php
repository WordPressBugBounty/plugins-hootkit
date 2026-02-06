<?php
/**
 * Admin Tools class
 * This file is loaded at plugins_loaded@5 for is_admin()
 *
 * @since   3.0.0
 * @package Hootkit
 */

namespace HootKit\Mods;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Mods\Tools' ) ) :

	class Tools {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Is this a plugin?
		 */
		private static $toolsplugin = false;

		/**
		 * Plugin subdirectory
		 */
		public $dir;
		public $uri;

		/**
		 * Setup Tools
		 */
		public function __construct() {
			// Set directories
			$this->dir             = trailingslashit( hootkit()->dir . 'misc/tools' );
			$this->uri             = trailingslashit( hootkit()->uri . 'misc/tools' );
			add_action( 'after_setup_theme', array( $this, 'loader' ), 94 );
		}

		/**
		 * Load if tools is enabled by themes
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function loader() {

			/* Get values if Tools is an embedded plugin */
			$dash = hootkit()->get_config( 'dashboard' );
			// if registered wphoot theme, sanitizeconfig has already made sure all required values exist
			// 'tools', 'tabfilter', 'tabaction', hoot_dashboard() => we can reliably use them here.
			if ( is_array( $dash ) && !empty( $dash[ 'tools' ] ) ) {
				self::$toolsplugin = array(
					'pagehook'  => hoot_dashboard( 'screen' ),
					'dashurl'   => hoot_dashboard( 'url', array( 'tab' => $dash[ 'tools' ] ) ),
					'tabfilter' => $dash['tabfilter'],
					'tabaction' => $dash['tabaction']
				);
			}

			if ( self::$toolsplugin ) {

				// Check if tools has been disabled by user
				$activemiscmods = hootkit()->get_config( 'activemods', 'misc' );
				$isactive = is_array( $activemiscmods ) && in_array( 'tools', $activemiscmods );

				if ( ! $isactive ) :
					add_filter( self::$toolsplugin['tabfilter'], array( $this, 'unplug_tabs' ), 90, 2 );
				else:
					// Render Content
					add_filter( self::$toolsplugin['tabfilter'], array( $this, 'plug_tabs' ), 90, 2 );
					add_action( self::$toolsplugin['tabaction'], array( $this, 'plug_modblock_content' ), 90, 4 );
					// Add Module
					if ( is_admin() && current_user_can( 'edit_theme_options' ) ) {
						require_once( $this->dir . 'import.php' );
						require_once( $this->dir . 'export.php' );
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
			$order = array_values( array_diff( $order, array( 'tools' ) ) ); // array_values() to reindex numerically
			$tabsarray['order'] = $order;
			if ( isset( $tabsarray['tools'] ) ) unset( $tabsarray['tools'] );
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

			if ( !in_array( 'tools', $order ) ) $order[] = 'tools';
			$tabsarray['tools'] = array(
				'label'   => __( 'Tools', 'hootkit' ),
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
			$tblocks = array();

			$tblocks[ 'notice' ] = array(
				'type' => 'notice',
				'gridtype' => 'gen',
				'src' => 'action',
				'action' => 'hoot_manager_toolsimport_notice',
			);

			$tblocks[ 'grid-imp' ] = array( 'type' => 'gridconbox' );
			$tblocks[ 'imp' ] = array(
				'type' => 'import',
				'name' => esc_html__( 'Import Customizer Settings', 'hootkit' ),
				/* Translators: The %s are placeholders for HTML, so the order can't be changed. */
				'desc' => sprintf( esc_html__( 'Paste your exported code here from another theme/installation, and click the Import button. %1$s%7$s%10$s %3$sWARNING:%4$s All your current customizer settings will be overwritten.%8$s%9$s%5$s(It is recommended to backup your database before making any changes to your site; or at the very least copy and save the customizer settings for current active theme below to a text file on your computer.)%6$s%2$s', 'hootkit' ), '<p class="hootabt-warning">', '</p>', '<strong>', '</strong>', '<em>', '</em>', '<span>', '</span>', '<br />', '<span class="dashicons dashicons-warning"></span>' ),
			);
			$tblocks[ 'grid-impend' ] = array( 'type' => 'gridconboxend' );

			$tblocks[ 'grid-exp' ] = array( 'type' => 'gridconbox' );
			$tblocks[ 'exp' ] = array(
				'type' => 'export',
				'name' => esc_html__( 'Export Customizer Settings', 'hootkit' ),
				/* Translators: The %s are placeholders for HTML, so the order can't be changed. */
				'desc' => sprintf( esc_html__( 'Copy this code to export Customizer Settings for a particular theme%5$s%1$s%3$s(only wphoot themes are displayed here)%2$s%4$s', 'hootkit' ), '<strong>', '</strong>', '<em>', '</em>', '<br />' ),
			);
			$tblocks[ 'grid-expend' ] = array( 'type' => 'gridconboxend' );

			return $tblocks;
		}

		/**
		 * Extra Tabs Module Block Templates
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function plug_modblock_content( $blockid, $modblock, $sanetags, $tabid ) {
			if ( empty( $modblock ) || ! is_array( $modblock ) || ! isset( $modblock['type'] ) ) {
				return;
			}
			$sanetags = is_array( $sanetags ) ? $sanetags : array();

			if ( $modblock['type'] === 'import' ) {
				if ( !empty( $modblock['name'] ) || !empty( $modblock['desc'] ) ) :
					?><div class="hootabt-blocktext"><?php
						if ( !empty( $modblock['name'] ) )
							echo '<h4 class="hootabt-blocktitle">' . wp_kses_post( $modblock['name'] ) . '</h4>';
						if ( !empty( $modblock['desc'] ) )
							echo '<div class="hootabt-blockdesc">' . wp_kses_post( $modblock['desc'] ) . '</div>';
					?></div><?php
				endif;
				if ( ! current_user_can( 'edit_theme_options' ) ) :
					echo '<p class="hootabt-notice hootabt-notice--error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'You do not have sufficient permissions to access these options. Please contact the site administrator', 'hootkit' ) . '</p>';
				else:
					do_action( 'hoot_manager_toolsimport', $sanetags, self::$toolsplugin );
				endif;
			}

			if ( $modblock['type'] === 'export' ) {
				if ( !empty( $modblock['name'] ) || !empty( $modblock['desc'] ) ) :
					?><div class="hootabt-blocktext"><?php
						if ( !empty( $modblock['name'] ) )
							echo '<h4 class="hootabt-blocktitle">' . wp_kses_post( $modblock['name'] ) . '</h4>';
						if ( !empty( $modblock['desc'] ) )
							echo '<div class="hootabt-blockdesc">' . wp_kses_post( $modblock['desc'] ) . '</div>';
					?></div><?php
				endif;
				if ( ! current_user_can( 'edit_theme_options' ) ) :
					echo '<p class="hootabt-notice hootabt-notice--error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'You do not have sufficient permissions to access these options. Please contact the site administrator', 'hootkit' ) . '</p>';
				else:
					do_action( 'hoot_manager_toolsexport', $sanetags, self::$toolsplugin );
				endif;
			}
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
	function hootkittools() {
		return Tools::get_instance();
	}

	// Lets roll!
	hootkittools();

endif;