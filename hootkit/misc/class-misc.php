<?php
/**
 * HootKit Misc Module
 * This file is loaded at after_setup_theme@96
 *
 * @since   2.0.0
 * @package Hootkit
 */

namespace HootKit\Mods;
use \HootKit\Inc\Helper_Assets;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Mods\MiscMods' ) ) :

	class MiscMods {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Active miscmods array
		 */
		private $activemiscmods = array();

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->activemiscmods = hootkit()->get_config( 'activemods', 'misc' );
			if ( !empty( $this->activemiscmods ) ) {

				foreach ( $this->activemiscmods as $miscmod ) {
					$requires = hootkit()->get_mfmodules( $miscmod, 'requires', false );
					if ( \is_array( $requires ) && \in_array( 'customizer', $requires ) ) {
						require_once( hootkit()->dir . 'misc/customizer.php' );
						break;
					}
				}

				$this->load_assets();
				$this->load_miscmods();

			}

		}

		/**
		 * Load assets
		 *
		 * @since  2.0.0
		 */
		private function load_assets() {

			$assets = array();
			$adminassets = array();

			foreach ( $this->activemiscmods as $miscmod ) {
				$modassets = hootkit()->get_mfmodules( $miscmod, 'assets', false );
				if ( \is_array( $modassets ) )
					$assets = array_merge( $assets, $modassets );
				$modassets = hootkit()->get_mfmodules( $miscmod, 'adminassets', false );
				if ( \is_array( $modassets ) )
					$adminassets = array_merge( $adminassets, $modassets );
			}

			/* Frontend */
			foreach ( $assets as $asset )
				Helper_Assets::add_asset( $asset );
			Helper_Assets::add_asset( 'miscmods' );
			add_action( 'wp_enqueue_scripts', array( $this, 'localize_script' ), 11 );

			/* Admin */
			// @todo: load font-awesome in customizer (example: Manifest::$mods['fly-cart'] )
			$hooks = array();
			foreach ( $adminassets as $adminasset )
				Helper_Assets::add_adminasset( $adminasset, $hooks );

		}

		/**
		 * Pass script data
		 *
		 * @since  2.0.0
		 */
		public function localize_script() {
			wp_localize_script(
				hootkit()->slug . '-miscmods',
				'hootkitMiscmodsData',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' )
				)
			);
		}

		/**
		 * Load individual miscmods
		 *
		 * @since  2.0.0
		 */
		private function load_miscmods() {

			foreach ( $this->activemiscmods as $miscmod )
				if ( file_exists( hootkit()->dir . 'misc/' . sanitize_file_name( $miscmod ) . '/admin.php' ) )
					require_once( hootkit()->dir . 'misc/' . sanitize_file_name( $miscmod ) . '/admin.php' );

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

	MiscMods::get_instance();

endif;