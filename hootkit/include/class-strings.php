<?php
/**
 * HootKit Strings
 *
 * @package Hootkit
 */

namespace HootKit\Inc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Inc\Strings' ) ) :

	class Strings {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Strings
		 */
		public static $strings = null;

		/**
		 * Constructor
		 */
		public function __construct() {}

		/**
		 * Get String values.
		 *
		 * @param  string $key
		 * @param  string|bool $default
		 * @return string
		 */
		public static function get_string( $key, $default = false ) {
			$return = '';
			if ( !is_array( self::$strings ) ) {
				self::set_strings();
			}
			if ( !is_array( self::$strings ) ) {
				$return = '';
			} else {
				$return = ( !empty( self::$strings[ $key ] ) ? self::$strings[ $key ] : '' );
			}

			if ( !empty( $return ) && is_string( $return ) ) {
				return esc_html( $return );
			} elseif ( is_string( $default ) ) {
				// allow empty string default
				return esc_html( $default );
			} elseif ( function_exists( 'hootkit_formatlabel' ) ) {
				return esc_html( hootkit_formatlabel( $key ) );
			} else {
				return esc_html( ucwords( str_replace( array( '-', '_' ), ' ' , $key ) ) );
			}
		}

		/**
		 * Set strings upon first call
		 */
		public static function set_strings() {
			if ( null === self::$strings ) {
				$strings = self::defaults();
				self::$strings = wp_parse_args( apply_filters( 'hootkit_strings', array() ), $strings );
			}
		}

		/**
		 * Default Strings
		 */
		public static function defaults() {
			return apply_filters( 'hootkit_inc_strings', array(

				// Settings

				'setting-widget' => __( 'Widgets',              'hootkit' ),
				'setting-block'  => __( 'Gutenberg Blocks',     'hootkit' ),
				'setting-misc'   => __( 'Miscellaneous',        'hootkit' ),
				'setting-scgen'  => __( 'Shortcode Generator',  'hootkit' ),

				// Settings Display sets filters

				'sliders'        => __( 'Sliders',              'hootkit' ),
				'content'        => __( 'Content',              'hootkit' ),
				'post'           => __( 'Post',                 'hootkit' ),
				'display'        => __( 'Display',              'hootkit' ),
				'woocom'         => __( 'WooCommerce',          'hootkit' ),
				'misc'           => __( 'Misc. Features',       'hootkit' ),

				// Widgets

				'widget-prefix'           => __( 'HK > ',                      'hootkit' ),

				// Manifest : modules [Settings, Widget Title] :: 'types' => 'widget'

				'slider-image'            => __( 'Slider Images',              'hootkit' ),
				'carousel'                => __( 'Carousel',                   'hootkit' ),
				'ticker'                  => __( 'Ticker',                     'hootkit' ),
				'image'                   => __( 'Slider Images',              'hootkit' ),
				'postimage'               => __( 'Posts Slider',               'hootkit' ),

				'content-posts-blocks'    => __( 'Posts Blocks',               'hootkit' ),
				'post-grid'               => __( 'Posts Grid',                 'hootkit' ),
				'post-list'               => __( 'Posts List',                 'hootkit' ),
				'postcarousel'            => __( 'Posts Carousel',             'hootkit' ),
				'postlistcarousel'        => __( 'Posts List Carousel',        'hootkit' ),
				'ticker-posts'            => __( 'Posts Ticker',               'hootkit' ),
				'slider-postimage'        => __( 'Posts Slider',               'hootkit' ),
				'page-content'            => __( 'Page Content',               'hootkit' ),

				'announce'                => __( 'Announce',                   'hootkit' ),
				'profile'                 => __( 'About/Profile',              'hootkit' ),
				'cta'                     => __( 'Call To Action',             'hootkit' ),
				'content-blocks'          => __( 'Content Blocks',             'hootkit' ),
				'content-grid'            => __( 'Content Grid',               'hootkit' ),
				'contact-info'            => __( 'Contact Info',               'hootkit' ),
				'icon-list'               => __( 'Icon List',                  'hootkit' ),
				'notice'                  => __( 'Notice Box',                 'hootkit' ),
				'number-blocks'           => __( 'Number Blocks',              'hootkit' ),
				'tabs'                    => __( 'Tabs',                       'hootkit' ),
				'toggle'                  => __( 'Toggle',                     'hootkit' ),
				'vcards'                  => __( 'Vcards',                     'hootkit' ),

				'buttons'                 => __( 'Buttons',                    'hootkit' ),
				'cover-image'             => __( 'Cover Image',                'hootkit' ),
				'icon'                    => __( 'Icon',                       'hootkit' ),
				'social-icons'            => __( 'Social Icons',               'hootkit' ),

				'products-carticon'       => __( 'Products Cart Icon',         'hootkit' ),
				'content-products-blocks' => __( 'Products Blocks',            'hootkit' ),
				'product-list'            => __( 'Products List',              'hootkit' ),
				'productcarousel'         => __( 'Products Carousel',          'hootkit' ),
				'productlistcarousel'     => __( 'Products List Carousel',     'hootkit' ),
				'products-ticker'         => __( 'Products Ticker',            'hootkit' ),
				'products-search'         => __( 'Products Search',            'hootkit' ),

				// Manifest : modules [Settings] :: 'types' => 'misc'

				'import'                  => __( 'Demo Import',                'hootkit' ),
				'code'                    => __( 'Custom Code',                'hootkit' ),
				'tools'                   => __( 'Tools (Import/Export)',      'hootkit' ),
				'top-banner'              => __( 'Top Banner',                 'hootkit' ),
				'shortcode-timer'         => __( 'Timer (shortcode)',          'hootkit' ),
				'fly-cart'                => __( 'Offscreen WooCommerce Cart', 'hootkit' ),
				'classic-widgets'         => __( 'Classic Widgets',            'hootkit' ),
				'widgets-as-sc'           => __( 'Widgets as Shortcodes',      'hootkit' ),

			) );
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

endif;