<?php
/**
 * Admin Tools class
 * This file is loaded at after_setup_theme@96 for is_admin()
 *
 * @since   3.0.0
 * @package Hootkit
 */

namespace HootKit\Mods;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Mods\Export' ) ) :

	class Export {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Initialize everything
		 */
		public function __construct() {
			add_action( 'hoot_manager_toolsexport', array( $this, 'print_export' ), 10, 2 );
		}

		/**
		 * Print Theme Manager Page Content
		 * @since  3.0.0
		 */
		function print_export( $sanetags, $toolsplugin ) {
			$themes = $this->themes();
			$current = get_option( 'stylesheet' );
			$showcurrent = array();
			$showthemes = array();
			foreach ( $themes as $theme_slug => $theme ) {
				$onlyhoot = apply_filters( 'hoot_exporter_onlyhoot', true );
				$addthis = !$onlyhoot || ( isset( $theme['author'] ) && stripos( $theme['author'], 'wphoot' ) !== false );
				if ( $addthis ) {
					if ( $current == $theme_slug ) {
						$showcurrent[ $theme_slug ] = !empty( $theme['name'] ) ? $theme['name'] : '';
					} else {
						$showthemes[ $theme_slug ] = !empty( $theme['name'] ) ? $theme['name'] : '';
					}
				}
			}
			if ( !empty( $showcurrent ) ) {
				echo '<div class="hoot-manager-export-themegroup"><span>' . __( 'Active Theme', 'hootkit' ) . '</span></div>';
				foreach ( $showcurrent as $theme_slug => $theme_name ) {
					$mods = $this->theme_mods( $theme_slug, $theme_name );
					echo '<h4>' . $theme_name . '</h4>';
					if ( !empty( $mods ) )
						echo '<textarea id="' . esc_attr( "hoot-$theme_slug" ) . '" class="hoot-manager-export-mod" name="hoot-manager-export[' . $theme_slug . ']" rows="3"  readonly="readonly" onclick="this.select()">' . esc_textarea( json_encode( $mods ) ) . '</textarea>';
					else
						echo '<p>' . __( 'No customizer settings available yet for this theme.', 'hootkit' ) . '</p>';
				}
			}
			if ( !empty( $showthemes ) ) {
				echo '<div class="hoot-manager-export-themegroup"><span>' . __( 'Other wpHoot Themes', 'hootkit' ) . '</span></div>';
				foreach ( $showthemes as $theme_slug => $theme_name ) {
					$mods = $this->theme_mods( $theme_slug, $theme_name );
					echo '<h4>' . $theme_name . '</h4>';
					if ( !empty( $mods ) )
						echo '<textarea id="' . esc_attr( "hoot-$theme_slug" ) . '" class="hoot-manager-export-mod" name="hoot-manager-export[' . $theme_slug . ']" rows="3"  readonly="readonly" onclick="this.select()">' . esc_textarea( json_encode( $mods ) ) . '</textarea>';
					else
						echo '<p>' . __( 'No customizer settings available yet for this theme.', 'hootkit' ) . '</p>';
				}
			}
		}

		/**
		 * Get Themes
		 * @since  3.0.0
		 */
		function themes() {

			global $wp_themes;
			$return = array();

			if ( !empty( $wp_themes ) )
				$themes = $wp_themes;
			else
				$themes = wp_get_themes();

			if ( is_array( $themes ) && !empty( $themes ) ) {
				foreach ( $themes as $theme ) {
					$slug = $theme->get_stylesheet();
					$return[ $slug ][ 'name' ] = $theme->get('Name');
					$return[ $slug ][ 'author' ] = $theme->get('Author');
				}
			}
			return $return;

		}

		/**
		 * Get Theme Mods
		 * @since  3.0.0
		 */
		function theme_mods( $theme_slug, $theme_name = '' ) {
			$mods = get_option( "theme_mods_$theme_slug" ); // Theme Slug
			return $mods;
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

	Export::get_instance();

endif;