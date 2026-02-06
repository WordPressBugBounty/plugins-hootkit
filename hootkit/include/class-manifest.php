<?php
/**
 * HootKit Modules
 * This file is loaded during plugin load
 *
 * @package Hootkit
 */

namespace HootKit\Inc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Inc\Manifest' ) ) :

	class Manifest {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Manifest
		 */
		public static $manifest = null;

		/**
		 * Available Module Types
		 */
		public static $modtypes = array();

		/**
		 * Default array of Module Types
		 */
		public static $modtypesarray = array();

		/**
		 * Constructor
		 */
		public function __construct() {
			if ( null === self::$manifest ) {
				self::$manifest = apply_filters( 'hootkit_default_manifest', self::origin() );
				self::$modtypes = apply_filters( 'hootkit_default_modtypes', self::origin( 'modtypes' ) );
				foreach ( self::$modtypes as $type ) {
					self::$modtypesarray[ $type ] = array();
				}
				add_action( 'after_setup_theme', array( $this, 'remove_deprecated' ), 12 );
			}
		}

		/**
		 * Remove Deprecated Modules
		 * Placeholder Function - Not currently used (may be deleted later)
		 */
		public function remove_deprecated() {
			// Remove all widgets
			if ( apply_filters( 'hootkit_deprecate_widgets', false ) )
				foreach ( self::$manifest['modules'] as $mod => $atts )
					if ( ( $key = array_search( 'widget', $atts['types'] ) ) !== false ) {
						unset( self::$manifest['modules'][ $mod ]['types'][ $key ] );
						if ( empty( self::$manifest['modules'][ $mod ]['types'] ) )
							unset( self::$manifest['modules'][ $mod ] );
					}
		}

		/**
		 * Default Module Atts
		 */
		public static function origin( $id='' ) {
			if ( $id === 'modtypes' )
				return array( 'widget', 'block', 'misc' );

			return array(

				'supports'    => array(
					'cta-styles',
					'content-blocks-style5', 'content-blocks-style6', 'content-blocks-iconoptions',
					'slider-styles', 'slider-style3', 'slider-subtitles',
					'widget-subtitle',
					'social-icons-altcolor', 'social-icons-altcoloraccent', 'social-icons-shape', 'social-icons-align',

					'list-evenspacecol',
					'cbox-evenspacecol',
					'imgbg-cssvars',
					'content-blocks-emptyblocks', 'content-blocks-style5-nojs',
					'linktarget', 'vcard-imgstyles',
				),
				'dashboard' => array( 'dashmenu' => '', 'aboutfilter' => '', 'tabfilter' => '', 'tabaction' => '', 'settings' => '', 'code' => '', 'tools' => '', 'import' => '', 'import_id' => '' ),

				'modules' => array(

					// TYPE: Widgets DISPLAY SET: Sliders
					'slider-image' => array(
						'types'       => array( 'widget' ),                         // Module's Type(s) available
						'displaysets' => array( 'sliders' ),                        // Settings Set
						'requires'    => array(),                                   // Required plugins/components
						'assets'      => array( 'lightslider', 'font-awesome' ),    // Assets required
						'adminassets' => array( 'wp-media' ),                       // Admin assets required
					),
					'carousel' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'wp-media' ),
					),
					'ticker' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
						'localize'    => array( 'font-awesome' => 'adminwidgets' ),
					),

					// TYPE: Widgets DISPLAY SET: Posts
					'content-posts-blocks' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'post' ),
						'adminassets' => array( 'select2' ),
					),
					'post-grid' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'post' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'select2' ),
					),
					'post-list' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'post' ),
						'adminassets' => array( 'select2' ),
					),
					'postcarousel' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders', 'post' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'select2' ),
					),
					'postlistcarousel' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders', 'post' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'select2' ),
					),
					'ticker-posts' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders', 'post' ),
						'adminassets' => array( 'select2' ),
					),
					'slider-postimage' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders', 'post' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'select2' ),
					),
					// TYPE: Widgets DISPLAY SET: Page
					'page-content' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'post' ),
						'adminassets' => array( 'select2' ),
					),

					// TYPE: Widgets DISPLAY SET: Content
					'announce' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
						'localize'    => array( 'font-awesome' => 'adminwidgets' ),
					),
					'profile' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'adminassets' => array( 'wp-media' ),
					),
					'cta' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
					),
					'content-blocks' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome', 'wp-media' ),
						'localize'    => array( 'font-awesome' => 'adminwidgets' ),
					),
					'content-grid' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'wp-media' ),
					),
					'contact-info' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
					),
					'icon-list' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
						'localize'    => array( 'font-awesome' => 'adminwidgets' ),
					),
					'notice' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
						'localize'    => array( 'font-awesome' => 'adminwidgets' ),
					),
					'number-blocks' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'assets'      => array( 'circliful' ),
					),
					'tabs' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
					),
					'toggle' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
					),
					'vcards' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'adminassets' => array( 'wp-media' ),
					),

					// TYPE: Widgets DISPLAY SET: Display
					'buttons' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'display' ),
					),
					'cover-image' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'display' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'wp-media' ),
					),
					'icon' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'display' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
						'localize'    => array( 'font-awesome' => 'adminwidgets' ),
					),
					'social-icons' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'display' ),
					),

					// TYPE: Widgets DISPLAY SET: WooCom
					'products-carticon' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'woocom' ),
						'requires'    => array( 'woocommerce' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
						// 'localize'    => array( 'font-awesome' => 'adminwidgets' ),
					),
					'content-products-blocks' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'woocom' ),
						'requires'    => array( 'woocommerce' ),
						'adminassets' => array( 'select2' ),
					),
					'product-list' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'woocom' ),
						'requires'    => array( 'woocommerce' ),
						'adminassets' => array( 'select2' ),
					),
					'productcarousel' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders', 'woocom' ),
						'requires'    => array( 'woocommerce' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'select2' ),
					),
					'productlistcarousel' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders', 'woocom' ),
						'requires'    => array( 'woocommerce' ),
						'assets'      => array( 'lightslider' ), // 'font-awesome'
						'adminassets' => array( 'select2' ),
					),
					'products-ticker' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'sliders', 'woocom' ),
						'requires'    => array( 'woocommerce' ),
						'adminassets' => array( 'select2' ),
					),
					'products-search' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'woocom' ),
						'requires'    => array( 'woocommerce' ),
					),

					// TYPE: Misc DISPLAY SET: Misc
					'import' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'content' ),
						'refreshadmin'=> true,
					),
					'code' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'content' ),
						'refreshadmin'=> true,
					),
					'tools' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'content' ),
						'refreshadmin'=> true,
					),
					'top-banner' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'content' ),
						'requires'    => array( 'customizer' ),
					),
					'shortcode-timer' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'misc' ),
					),
					'fly-cart' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'woocom' ),
						'requires'    => array( 'woocommerce', 'customizer' ),
						'assets'      => array( 'font-awesome' ),
						// 'adminassets' => array( 'font-awesome' ), // @todo: load font-awesome in customizer
					),
					'classic-widgets' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'misc' ),
					),
					'widgets-as-sc' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'misc' ),
					),

				),

			);
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

	Manifest::get_instance();

endif;

