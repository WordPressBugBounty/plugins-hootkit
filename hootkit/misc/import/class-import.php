<?php
/**
 * Admin Import class
 * This file is loaded at plugins_loaded@5
 *
 * @since   3.0.0
 * @package Hootkit
 */

namespace HootKit\Import;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Import\Import' ) ) :

	class Import {

		public $dir;
		public $uri;
		public $demopack_dir;
		public $demopack_url;

		/**
		 * Constructor method.
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			// Set directories
			$this->dir             = trailingslashit( hootkit()->dir . 'misc/import' );
			$this->uri             = trailingslashit( hootkit()->uri . 'misc/import' );

			// Setup Demopack Directory
			$import_dir = '/hootkitimport-demofiles/';
			$upload_dir = wp_upload_dir( null, false );
			$this->demopack_dir = $upload_dir['basedir'] . $import_dir;
			$this->demopack_url = $upload_dir['baseurl'] . $import_dir;

			// DeActivation Hook
			add_action( 'hootkit/deactivate', array( $this, 'deactivation' ), 10, 1 );

			// Load Plugin Files and Helpers
			require_once( $this->dir . 'include/functions.php' );
			if ( is_admin() ) {
				require_once( $this->dir . 'include/class-importer.php' );
				require_once( $this->dir . 'include/class-admin.php' );
			}
		}

		/**
		 * DeActivation Hook
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function deactivation( $activation ) {
			$this->cleanup( true );
		}

		/**
		 * Cleanup
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function cleanup( $forcecleanup = false ) {
			$demopack_dir = $this->demopack_dir;
			if (
				empty( $demopack_dir ) || !is_string( $demopack_dir )
				|| !function_exists( 'current_user_can' )
				|| !is_dir( $demopack_dir )
			)
				return;

			// Cleanup demo pack directory
			$is_fresh = get_transient( 'hootkitimport_freshpack' );
			if ( empty( $is_fresh ) || $forcecleanup ) {
				if ( current_user_can( 'manage_options' ) ) {
					// Initialize the WP Filesystem API
					if ( ! function_exists( 'wp_filesystem' ) ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}
					global $wp_filesystem;
					WP_Filesystem();
					// Check if the directory exists and remove the directory and all its contents
					if ( $wp_filesystem->is_dir( $demopack_dir ) ) {
						$wp_filesystem->rmdir( $demopack_dir, true );
					}
				}
			}
		}

		/**
		 * Returns the instance
		 * @since  3.0.0
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
	 * Gets the instance of the class. This function is useful for quickly grabbing data
	 * used throughout the plugin.
	 * @since  3.0.0
	 * @access public
	 * @return object
	 */
	function hootimport() {
		return Import::get_instance();
	}

	// Lets roll!
	hootimport();

endif;