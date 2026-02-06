<?php
/**
 * Hoot Themes
 * This file is loaded at after_setup_theme@96
 *
 * @package Hootkit
 */

namespace HootKit\Inc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Inc\Themes' ) ) :

	class Themes {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Customizer Filters
		 */
		public static $customizefilters = array();

		/**
		 * Tag Filters
		 */
		public static $tagfilters = array();

		/**
		 * Constructor
		 */
		public function __construct() {
			$filters = hootkit()->get_config( 'theme-filters' );
			if ( ! is_array( $filters ) )
				return;
			$fnspace = ! empty( $filters['fnspace'] ) && is_string( $filters['fnspace'] ) ? $filters['fnspace'] : false;
			if ( empty( $fnspace ) )
				return;

			if ( ! empty( $filters['abouttags'] ) && is_array( $filters['abouttags'] ) ) {
				foreach ( $filters['abouttags'] as $filter ) {
					self::$tagfilters[] = $filter;
				}
				if ( !empty( self::$tagfilters ) ) {
					add_filter( "{$fnspace}_abouttags", array( $this, 'abouttags' ), 7 );
				}
			}

			if ( ! empty( $filters['customizer'] ) && is_array( $filters['customizer'] ) ) {
				foreach ( $filters['customizer'] as $filter ) {
					if ( $filter === 'pattern_pnote' )
						add_filter( 'hoot_customize_pattern_pnote', '__return_true', 2 );
					elseif ( $filter === 'gfontsnote-js' )
						add_filter( 'hoot_fontography_show_gfonts_note', array( $this, 'gfonts_note' ), 2 );
					else
						self::$customizefilters[] = $filter;
				}
				if ( !empty( self::$customizefilters ) ) {
					add_filter( "{$fnspace}_customizer_options", array( $this, 'customizer_options' ), 7 );
				}
			}
		}

		/**
		 * Add gfonts note to select dropdown
		 * @return string
		 */
		function gfonts_note( $note ) {
			$note = esc_html__( 'Over 1600+ Google Fonts in Premium', 'hootkit' );
			return $note;
		}

		/**
		 * Update Theme fullshot
		 * 'hootkit_theme_abouttags' function replaced with class function @since 3.0.0
		 * @access public
		 * @return mixed
		 */
		function abouttags( $tags ) {
			if ( ! is_array( $tags ) )
				return $tags;

			foreach ( self::$tagfilters as $filter ) {
				switch ( $filter ) {
					case 'fullshot':
						$slug = !empty( $tags['slug'] ) ? $tags['slug'] : false;
						if ( $slug && empty( $tags['fullshot'] ) && !empty( $tags['urlcdn'] ) ) {
							$tags['fullshot'] = $tags['urlcdn'] . 'images/themes/' . $slug . '.jpg';
						}
						break;
					default: break;
				}
			}

			return $tags;
		}

		/**
		 * Theme Customizer settings mods
		 * 'hootkit_theme_customizer_options' function replaced with class function @since 3.0.0
		 *
		 * @since 2.0.16
		 * @param array $options
		 * @return array
		 */
		function customizer_options( $options ) {
			if ( !is_array( $options ) || empty( $options['settings'] ) || !is_array( $options['settings'] ) )
				return $options;

			foreach ( self::$customizefilters as $filter ) {
				switch ( $filter ) {
					case 'topann_content':
						$type = hootkit_arrayel( $options['settings'], array( 'topann_ops', 'options', 'content', 'topann_content', 'type' ) );
						if ( $type && $type === 'text' ) {
							$options['settings']['topann_ops']['options']['content']['topann_content']['type'] = 'textarea';
						}
						break;
					case 'header_image_title':
					case 'header_image_subtitle':
					case 'header_image_text':
						$type = hootkit_arrayel( $options['settings'], array( 'header_image_ops', 'options', 'content', $filter, 'type' ) );
						if ( $type && $type === 'text' ) {
							$options['settings']['header_image_ops']['options']['content'][ $filter ]['type'] = 'textarea';
						}
						break;
					case 'sblayoutpnote':
						$placeholder = hootkit_arrayel( $options['settings'], array( 'sidebar_tabs', 'options', 'layout', $filter ) );
						if ( !empty( $placeholder ) && is_array( $placeholder ) && ( !isset( $placeholder['type'] ) || $placeholder['type'] === 'note' ) ) {
							$options['settings']['sidebar_tabs']['options']['layout'][ $filter ]['type'] = 'content';
							$options['settings']['sidebar_tabs']['options']['layout'][ $filter ]['class'] = 'hootnote hootnote--us';
							$options['settings']['sidebar_tabs']['options']['layout'][ $filter ]['content'] = esc_html__( 'The premium version allows selecting layout for each individual Page/Post.', 'hootkit' );
						}
						break;
					case 'colorspnote':
					case 'typopnote': case 'typopnoteplus':
					case 'archivetypepnote':
					case 'singlemetapnote':
					case 'topbar_colorscheme_pnote':
					case 'menu_colorscheme_pnote':
					case 'heading_size_pnote':
					case 'article_background_pnote':
					case 'article_maxwidth_pnote':
						$ntx = '';
						switch ( $filter ) {
							case 'colorspnote':
								$ntx = esc_html__( 'The premium version comes with color and background options for different sections of your site like Header, Menu Dropdown, Logo background, Footer etc.', 'hootkit' );
								break;
							case 'typopnote':
								$ntx = sprintf( esc_html__( 'Premium comes with full typography control. Select size, color, style etc for different headings, tagline, menus, footer, sidebar, content sections and more... Choose from %1$sover 600+ Google Fonts%2$s to match your design.', 'hootkit' ), '<strong>', '</strong>' );
								break;
							case 'typopnoteplus':
							$ntx = sprintf( esc_html__( 'Premium comes with full typography control. Select size, color, style etc for different %3$sheadings%4$s , %3$stagline%4$s , %3$smenus%4$s , %3$sfooter%4$s , %3$ssidebar%4$s , %3$scontent sections%4$s and more...', 'hootkit' ), '<strong>', '</strong>', '<span style="text-decoration:underline">', '</span>', '<hr>' );
								break;
							case 'archivetypepnote':
								$ntx = sprintf( esc_html__( 'The premium version comes with additional archive Layout styles including %1$sMosaic layouts%2$s.', 'hootkit' ), '<strong>', '</strong>' );
								break;
							case 'singlemetapnote':
								$ntx = esc_html__( 'The premium version comes with control to hide meta information for each individual Page/Post.', 'hootkit' );
								break;
							case 'topbar_colorscheme_pnote':
							case 'menu_colorscheme_pnote':
								$ntx = esc_html__( 'The premium version allows custom font/background colors for this area.', 'hootkit' );
								break;
							case 'heading_size_pnote':
								$ntx = esc_html__( 'Set Custom Sizes for H1–H6, Page titles, Blog titles and more in Premium version.', 'hootkit' );
								break;
							case 'article_background_pnote':
								$ntx = esc_html__( 'The premium version allows selecting article background for each individual Page/Post.', 'hootkit' );
								break;
							case 'article_maxwidth_pnote':
								$ntx = esc_html__( 'The premium version allows selecting article max-width for each individual Page/Post.', 'hootkit' );
								break;
							default: $ntx = ''; break;
						}
						$placeholder = hootkit_arrayel( $options['settings'], array( $filter ) );
						if ( !empty( $placeholder ) && is_array( $placeholder ) && ( !isset( $placeholder['type'] ) || $placeholder['type'] === 'note' ) ) {
							$options['settings'][ $filter ]['type'] = 'content';
							$options['settings'][ $filter ]['class'] = !empty( $placeholder['class'] ) && is_string( $placeholder['class'] ) ? $placeholder['class'] : 'hootnote hootnote--us';
							$options['settings'][ $filter ]['content'] = $ntx;
						}
						break;

					default: break;
				}
			}

			return $options;
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

	/* Initialize class */
	global $hootkit_themes;
	$hootkit_themes = Themes::get_instance();

endif;
