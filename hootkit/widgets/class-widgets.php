<?php
/**
 * HootKit Widgets Module
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

if ( ! class_exists( '\HootKit\Mods\Widgets' ) ) :

	class Widgets {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Active widgets array
		 */
		private $activewidgets = array();

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->activewidgets = hootkit()->get_config( 'activemods', 'widget' );
			if ( !empty( $this->activewidgets ) ) {

				require_once( hootkit()->dir . 'widgets/class-hk-widget.php' );

				$this->load_assets();
				$this->load_widgets();

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
			$localize = array();

			foreach ( $this->activewidgets as $widget ) {
				$modassets = hootkit()->get_mfmodules( $widget, 'assets', false );
				if ( \is_array( $modassets ) )
					$assets = array_merge( $assets, $modassets );
				$modassets = hootkit()->get_mfmodules( $widget, 'adminassets', false );
				if ( \is_array( $modassets ) )
					$adminassets = array_merge( $adminassets, $modassets );
				$modassets = hootkit()->get_mfmodules( $widget, 'localize', false );
				if ( \is_array( $modassets ) ) {
					foreach ( $modassets as $modasset => $hook ) {
						if ( !isset( $localize[ $hook ] ) || !is_array( $localize[ $hook ] ) )
							$localize[ $hook ] = array();
						if ( ! in_array( $modasset, $localize[ $hook ] ) )
							$localize[ $hook ][] = $modasset;
					}
				}
			}

			/* Frontend */
			foreach ( $assets as $asset )
				Helper_Assets::add_asset( $asset );
			Helper_Assets::add_asset( 'widgets' );

			/* Admin */
			$hooks = ( defined( 'SITEORIGIN_PANELS_VERSION' ) && version_compare( SITEORIGIN_PANELS_VERSION, '2.0' ) >= 0 ) ?
						array( 'widgets.php', 'post.php', 'post-new.php' ):
						array( 'widgets.php' );
			foreach ( $adminassets as $adminasset )
				Helper_Assets::add_adminasset( $adminasset, $hooks );
			Helper_Assets::add_adminasset( 'wp-color-picker', $hooks );
			$localizeadminwidgets = !empty( $localize['adminwidgets'] ) && is_array( $localize['adminwidgets'] ) ? $localize['adminwidgets'] : array();
			Helper_Assets::add_adminasset( 'adminwidgets', $hooks, $localizeadminwidgets );

		}

		/**
		 * Load individual widgets
		 *
		 * @since  2.0.0
		 */
		private function load_widgets() {
			$locations = array( 'widgets' );
			$tmplver = hootkit()->get_config( 'supports_version' );
			if ( is_array( $tmplver ) ) {
				if ( in_array( 'widgets-v2', $tmplver ) )
					array_unshift( $locations, 'widgets-v2' );
				if ( in_array( 'widgets-v3', $tmplver ) )
					array_unshift( $locations, 'widgets-v3' );
			}
			foreach ( $this->activewidgets as $widget ) {
				foreach ( $locations as $location ) {
					if ( file_exists(  hootkit()->dir . $location . '/' . sanitize_file_name( $widget ) . '/admin.php' ) ) {
						require_once( hootkit()->dir . $location . '/' . sanitize_file_name( $widget ) . '/admin.php' );
						break;
					}
				}
			}
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

	Widgets::get_instance();

endif;