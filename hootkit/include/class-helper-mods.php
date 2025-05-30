<?php
/**
 * HootKit Modules
 *
 * @package Hootkit
 */

namespace HootKit\Inc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Inc\Helper_Mods' ) ) :

	class Helper_Mods {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Mods
		 */
		public static $mods = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			if ( null === self::$mods ) {
				self::$mods = apply_filters( 'hootkit_default_mods', self::defaults() );
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
				foreach ( self::$mods['modules'] as $mod => $atts )
					if ( ( $key = array_search( 'widget', $atts['types'] ) ) !== false ) {
						unset( self::$mods['modules'][ $mod ]['types'][ $key ] );
						if ( empty( self::$mods['modules'][ $mod ]['types'] ) )
							unset( self::$mods['modules'][ $mod ] );
					}
		}

		/**
		 * Default Module Atts
		 */
		public static function defaults() {
			return array(

				'supports'    => array(
					'cta-styles', 'content-blocks-style5', 'content-blocks-style6', 'slider-styles', 'widget-subtitle',
					'content-blocks-iconoptions', 'social-icons-altcolor', 'social-icons-align',
					'slider-style3', 'slider-subtitles',
					'post-grid-firstpost-category',
					'grid-widget', // JNES@deprecated <= HootKit v1.1.3 @9.20 postgrid=>grid-widget
					'list-widget', // JNES@deprecated <= HootKit v1.1.3 @9.20 postslist=>list-widget
				),

				'modules' => array(

					// DISPLAY SET: Sliders
					'slider-image' => array(
						'types'       => array( 'widget' ),                      // Module Types available
						'displaysets' => array( 'sliders' ),                     // Settings Set
						'requires'    => array(),                                // Required plugins/components
						'desc'        => '',                                     // Settings info popover
						'assets'      => array( 'lightslider', 'font-awesome' ), // Assets required
						'adminassets' => array( 'wp-media' ),                    // Admin assets required
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
					),

					// DISPLAY SET: Posts
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

					// DISPLAY SET: Content
					'announce' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
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
					),
					'notice' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'content' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
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

					// DISPLAY SET: Display
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
					),
					'social-icons' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'display' ),
					),

					// DISPLAY SET: Misc
					'top-banner' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'content' ),
						'requires'    => array( 'customizer' ),
					),
					'shortcode-timer' => array(
						'types'       => array( 'misc' ),
						'displaysets' => array( 'shortcode' ),
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
						'displaysets' => array( 'widgets' ),
					),

					// DISPLAY SET: WooCom
					'products-carticon' => array(
						'types'       => array( 'widget' ),
						'displaysets' => array( 'woocom' ),
						'requires'    => array( 'woocommerce' ),
						'assets'      => array( 'font-awesome' ),
						'adminassets' => array( 'font-awesome' ),
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

	Helper_Mods::get_instance();

endif;

/**
 * Theme Customizer settings mods
 *
 * @since 2.0.16
 * @param array $options
 * @return array
 */
if ( !function_exists( 'HootKit\Inc\hootkit_theme_customizer_options' ) ):
function hootkit_theme_customizer_options( $options ) {
	if ( !is_array( $options ) || empty( $options['settings'] ) || !is_array( $options['settings'] ) )
		return $options;
	if ( !empty( $options['settings']['topann_content'] ) && is_array( $options['settings']['topann_content'] ) && !empty( $options['settings']['topann_content']['type'] ) ) {
		if ( $options['settings']['topann_content']['type'] === 'text' )
			$options['settings']['topann_content']['type'] = 'textarea';
	}
	if ( !empty( $options['settings']['header_image_text'] ) && is_array( $options['settings']['header_image_text'] ) && !empty( $options['settings']['header_image_text']['type'] ) ) {
		if ( $options['settings']['header_image_text']['type'] === 'text' )
			$options['settings']['header_image_text']['type'] = 'textarea';
	}
	$ar = array(
		'colorspnote' => esc_html__( 'The premium version comes with color and background options for different sections of your site like header, menu dropdown, content area, logo background, footer etc.', 'hootkit' ),
		'typopnote' => esc_html__( 'The premium version offers complete typography control (color, style, size) for various headings, header, menu, footer, widgets, content sections etc (over 600 Google Fonts to chose from)', 'hootkit' ),
		'archivetypepnote' => sprintf( esc_html__( 'The premium version comes with additional archive Layout styles including %1$sMosaic layouts%2$s.', 'hootkit' ), '<strong>', '</strong>' ),
		'singlemetapnote' => esc_html__( 'The premium version comes with control to hide meta information for each individual page/post.', 'hootkit' ),
		'article_background_pnote' => esc_html__( 'The premium version allows selecting article background for each individual page/post.', 'hootkit' ),
		'article_maxwidth_pnote' => esc_html__( 'The premium version allows selecting article max-width for each individual page/post.', 'hootkit' ),
	);
	foreach ( $ar as $key => $text ) {
		if ( !empty( $options['settings'][ $key ] ) && is_array( $options['settings'][ $key ] ) && !empty( $options['settings'][ $key ]['type'] ) && $options['settings'][ $key ]['type'] === 'note' ) {
			$options['settings'][ $key ]['type'] = 'pnote';
			$options['settings'][ $key ]['content'] = $text;
		}
	}
	if ( !empty( $options['settings']['sidebar_tabs'] ) && is_array( $options['settings']['sidebar_tabs'] ) && !empty( $options['settings']['sidebar_tabs']['options'] ) && is_array( $options['settings']['sidebar_tabs']['options'] ) && !empty( $options['settings']['sidebar_tabs']['options']['layout'] ) && is_array( $options['settings']['sidebar_tabs']['options']['layout'] ) && !empty( $options['settings']['sidebar_tabs']['options']['layout']['sblayoutpnote'] ) && is_array( $options['settings']['sidebar_tabs']['options']['layout']['sblayoutpnote'] ) && !empty( $options['settings']['sidebar_tabs']['options']['layout']['sblayoutpnote']['type'] ) && $options['settings']['sidebar_tabs']['options']['layout']['sblayoutpnote']['type'] === 'note' ) {
		$options['settings']['sidebar_tabs']['options']['layout']['sblayoutpnote']['type'] = 'pnote';
		$options['settings']['sidebar_tabs']['options']['layout']['sblayoutpnote']['content'] = esc_html__( 'The premium version allows selecting layout for each individual page/post.', 'hootkit' );
	}

	return $options;
};
endif;
add_filter( 'hoot_customize_pattern_pnote', '__return_true', 2 );
add_filter( 'olius_customizer_options', 'HootKit\Inc\hootkit_theme_customizer_options', 7 );
add_filter( 'strute_customizer_options', 'HootKit\Inc\hootkit_theme_customizer_options', 7 );
add_filter( 'nirvata_customizer_options', 'HootKit\Inc\hootkit_theme_customizer_options', 7 );

/**
 * Update Theme fullshot
 * @access public
 * @return mixed
 */
if ( !function_exists( 'HootKit\Inc\hootkit_theme_abouttags' ) ):
function hootkit_theme_abouttags( $tags ) {
	if ( is_array( $tags ) && isset( $tags['fullshot'] ) && empty( $tags['fullshot'] ) ) {
		$slug = !empty( $tags['slug'] ) ? $tags['slug'] : false;
		if ( $slug ) {
			$tags['fullshot'] = ( file_exists( hootkit()->dir . 'admin/assets/images/screenshot-' . $slug . '.jpg' ) ) ? hootkit()->uri . 'admin/assets/images/screenshot-' . $slug . '.jpg' : (
				( file_exists( hootkit()->dir . 'admin/assets/images/screenshot-' . $slug . '.png' ) ) ? hootkit()->uri . 'admin/assets/images/screenshot-' . $slug . '.png' : ''
				);
		}
	}
	return $tags;
}
endif;
add_filter( 'olius_abouttags', 'HootKit\Inc\hootkit_theme_abouttags', 7 );
add_filter( 'strute_abouttags', 'HootKit\Inc\hootkit_theme_abouttags', 7 );
add_filter( 'nirvata_abouttags', 'HootKit\Inc\hootkit_theme_abouttags', 7 );
