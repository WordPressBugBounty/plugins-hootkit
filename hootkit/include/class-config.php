<?php
/**
 * HootKit Config
 * This file is loaded during plugin load
 *
 * @package Hootkit
 */

namespace HootKit\Inc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Inc\HKConfig' ) ) :

	class HKConfig {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Config
		 */
		public static $config = array();

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'themeregister' ), 90 );
		}

		/**
		 * Register theme config
		 */
		public function themeregister() {
			$themeconfig = apply_filters( 'hootkit_register', array() );

			if ( !empty( $themeconfig ) && \is_array( $themeconfig ) ) {
				$themeconfig = $this->maybe_restructure( $themeconfig );
				self::$config = wp_parse_args( $themeconfig, self::defaults() );
				$this->sanitizeconfig();
				$this->setactivemodules();
				do_action( 'hootkit_config_registered' );
			}

		}

		/**
		 * Restructure config array from theme if needed
		 */
		private function maybe_restructure( $themeconfig ) {
			if ( !empty( $themeconfig['modules'] ) ) {
				if ( !\is_array( $themeconfig['modules'] ) ) {
					unset( $themeconfig['modules'] );
				} else {
					// 1. Rename slider slugs
					if ( !empty( $themeconfig['modules']['sliders'] ) && is_array( $themeconfig['modules']['sliders'] ) ) {
						foreach ( $themeconfig['modules']['sliders'] as $slkey => $name ) {
							if ( \in_array( $name, array( 'image', 'postimage' ) ) )
								$themeconfig['modules']['sliders'][$slkey] = 'slider-' . $name;
						}
					}
					// 2. Restructure to new format
					$modules = array();
					foreach ( Manifest::$modtypes as $type ) {
						$modules[ $type ] = isset( $themeconfig['modules'][ $type ] ) && is_array( $themeconfig['modules'][ $type ] ) ? $themeconfig['modules'][ $type ] : array();
					}
					if ( isset( $modules['widget'] ) && is_array( $modules['widget'] ) ) {
						foreach ( array( 'widgets', 'sliders', 'woocommerce' ) as $oldkeys ) {
							if ( isset( $themeconfig['modules'][ $oldkeys ] ) && is_array( $themeconfig['modules'][ $oldkeys ] ) ) {
								$modules['widget'] = array_merge( $modules['widget'], $themeconfig['modules'][ $oldkeys ] );
							}
						}
					}
					$themeconfig['modules'] = $modules;
				}
			}
			$themeconfig['supports_version'] = isset( $themeconfig['supports_version'] ) ? (
					$themeconfig['supports_version'] === 'v2' ? array( 'widgets-v2' ) : (
						\is_array( $themeconfig['supports_version'] ) ? $themeconfig['supports_version'] : array()
					)
				) : array();
			if ( !defined( 'HOOT_PREMIUM_VERSION' ) && empty( $themeconfig['theme-filters'] ) ) {
				$slug = false;
				if ( function_exists( 'olius_abouttag' ) ) $slug = 'olius';
				elseif ( function_exists( 'strute_abouttag' ) ) $slug = 'strute';
				elseif ( function_exists( 'nirvata_abouttag' ) ) $slug = 'nirvata';
				if ( $slug ) {
					$themeconfig['theme-filters'] = array(
						'fnspace' => $slug,
						'abouttags' => array( 'fullshot' ),
						'customizer' => array( 'pattern_pnote', 'sblayoutpnote', 'colorspnote', 'typopnote', 'archivetypepnote', 'singlemetapnote', 'article_background_pnote', 'article_maxwidth_pnote', 'topann_content', 'header_image_title', 'header_image_subtitle', 'header_image_text' ),
					);
				}
			}

			return $themeconfig;
		}

		/**
		 * Sanitize config values from theme and/or the default config values
		 */
		public function sanitizeconfig() {
			/*
			* Sanitize Theme Supported Modules against HootKit manifest modules
			* Arrange in order of HK manifest modules
			* Add woocommerce modules only if plugin is active - Else add to 'wc-inactive'
			*/
			self::$config['wc-inactive'] = Manifest::$modtypesarray;
			$wc = class_exists( 'WooCommerce' );
			$store = Manifest::$modtypesarray;
			if ( !empty( self::$config['modules'] ) && \is_array( self::$config['modules'] ) ) {
				foreach ( self::$config['modules'] as $type => $thememods ) {
					if ( is_array( $thememods ) ) {
						// Arrange in order of HK manifest modules
						$hkmodules = hootkit()->get_mfmods_oftype( $type );
						foreach ( $hkmodules as $modid => $modatts ) {
							if ( \in_array( $modid, $thememods ) ) {
								// If this is a WC module - add to 'modules' only if WC is available
								// Else add to 'wc-inactive'
								if ( isset( $modatts['requires'] ) && \in_array( 'woocommerce', $modatts['requires'] ) ) {
									if ( $wc ) { $store[ $type ][] = $modid; }
									else { self::$config['wc-inactive'][ $type ][] = $modid; }
								} else {
									$store[ $type ][] = $modid;
								}
							}
						}
					}
				}
			}
			self::$config['modules'] = $store;

			/* Sanitize Theme Supported Premium Modules against HootKit Manifest modules */
			if ( !empty( self::$config['premium'] ) && \is_array( self::$config['premium'] ) ) {
				$hkmodules = hootkit()->get_mfmods_oftype( 'all', true );
				foreach ( self::$config['premium'] as $modkey => $modid ) {
					if ( ! in_array( $modid, $hkmodules ) )
						unset( self::$config['premium'][$modkey] );
				}
			}

			/* Sanitize Theme specific supported settings against HootKit supported settings */
			if ( !empty( self::$config['supports'] ) && \is_array( self::$config['supports'] ) ) {
				$hksupports = hootkit()->get_manifest( 'supports' );
				foreach ( self::$config['supports'] as $skey => $support ) {
					if ( !in_array( $support, $hksupports ) ) {
						unset( self::$config['supports'][ $skey ] );
					}
				}
			}

			/* Sanitize Theme specific dashboard plugs against HootKit supported dashboard plugs */
			$store = hootkit()->get_manifest( 'dashboard' );
			if ( !empty( self::$config['dashboard'] ) && \is_array( self::$config['dashboard'] ) ) {
				foreach ( $store as $key => $value ) {
					if ( isset( self::$config['dashboard'][ $key ] ) && is_scalar( self::$config['dashboard'][ $key ] ) ) {
						$store[ $key ] = self::$config['dashboard'][ $key ];
					}
				}
				/* Basic dependency checks for dashboard */
				$store['dashmenu'] = !empty( $store['dashmenu'] ) && is_string( $store['dashmenu'] ) && !empty( $store['aboutfilter'] ) && is_string( $store['aboutfilter'] ) ? $store['dashmenu'] : '';
				$hasrequired = ( function_exists( 'hoot_dashboard' )
								&& !empty( $store['tabfilter'] ) && is_string( $store['tabfilter'] )
								&& !empty( $store['tabaction'] ) && is_string( $store['tabaction'] )
							);
				foreach ( array( 'settings', 'code', 'tools', 'import' ) as $checkid ) {
					$store[ $checkid ] = $hasrequired && !empty( $store[ $checkid ] ) && is_string( $store[ $checkid ] ) ? $store[ $checkid ] : '';
				}
				$store['import'] = !empty( $store['import'] ) && is_string( $store['import'] ) && !empty( $store['import_id'] ) && is_string( $store['import_id'] ) ? $store['import'] : '';
			}
			self::$config['dashboard'] = $store;

		}

		/**
		 * Set User Activated modules
		 *
		 * @since  1.1.0
		 */
		public function setactivemodules() {

			$dbvalue = get_option( 'hootkit-activemods', false );
			$dbvalue = \is_array( $dbvalue ) && !empty( $dbvalue ) ? $dbvalue : array();
			$disabled = !empty( $dbvalue[ 'disabled' ] ) && \is_array( $dbvalue[ 'disabled' ] ) ? $dbvalue[ 'disabled' ] : array();

			$store = Manifest::$modtypesarray;
			foreach ( $store as $type => $arr ) {

				// User has not modified any default settings yet
				// Hence set default active modules => all deactive if (bool) false ; all active if empty
				if ( empty( $dbvalue ) ) {
					$store[ $type ] = hootkit()->get_config( 'modules', $type );
					$themeactivemods = self::$config['activemods'];
					if ( $themeactivemods === false ) {
						$store[ $type ] = array();
					} elseif ( is_array( $themeactivemods ) && isset( $themeactivemods[ $type ] ) ) {
						if ( $themeactivemods[ $type ] === false ) {
							$store[ $type ] = array();
						} elseif ( !empty( $themeactivemods[ $type ] ) && \is_array( $themeactivemods[ $type ] ) ) {
							$store[ $type ] = $themeactivemods[ $type ];
						}
					}
				} else {
					if ( \in_array( $type, $disabled ) ) {
						$store[ $type ] = array();
					} elseif ( !empty( $dbvalue[ $type ] ) && \is_array( $dbvalue[ $type ] ) ) {
						foreach ( hootkit()->get_config( 'modules', $type ) as $check ) {
							if ( !isset( $dbvalue[ $type ][ $check ] ) || $dbvalue[ $type ][ $check ] == 'yes' )
								$store[ $type ][] = $check;
						}
					} else {
						$store[ $type ] = hootkit()->get_config( 'modules', $type );
					}
				}

			}
			self::$config['activemods'] = apply_filters( 'hootkit_active_modules', $store );

			/*
			* Sanitize Active Modules against HootKit manifest modules
			* Arrange in order of HK manifest modules
			* Add woocommerce modules only if plugin is active (Ex: User saves settings with WC active; later disabled WC)
			*/
			$wc = class_exists( 'WooCommerce' );
			$store = Manifest::$modtypesarray;
			foreach ( $store as $type => $arr ) {
				if ( !empty( self::$config['activemods'][ $type ] ) ) {
						// Arrange in order of HK manifest modules
						$hkmodules = hootkit()->get_mfmods_oftype( $type );
						foreach ( $hkmodules as $modid => $modatts ) {
							if ( \in_array( $modid, self::$config['activemods'][ $type ] ) ) {
								// If this is a WC module - add to 'activemods' only if WC is available
								// Else skip it
								if ( isset( $modatts['requires'] ) && \in_array( 'woocommerce', $modatts['requires'] ) ) {
									if ( $wc ) { $store[ $type ][] = $modid; }
								} else {
									$store[ $type ][] = $modid;
								}
							}
						}
				}
			}
			self::$config['activemods'] = $store;
			self::$config['disabledmodtypes'] = $disabled;

		}

		/**
		 * Config Structure (Defaults)
		 */
		public static function defaults() {
			return array(

				/** Required - Themes should register these for best performance **/

				// Theme Supported Modules - @see 'mods' for available list
				'modules'   => array(
					'widget' => array(),
					'block' => array(),
					'misc' => array(),
				),
				// Theme supports - @see 'mods' for available list
				'supports' => array(),
				// Version Support
				'supports_version' => array(),
				// Premium modules list
				'premium' => array(),
				// Theme filters to be applied [fnspace,abouttags,customizer]
				'theme-filters' => array(),

				/** Optional / Plugin Generated */

				// Extracted list from 'modules' if WC is inactive
				'wc-inactive' => array(
					'widget' => array(),
					'block' => array(),
					'misc' => array(),
				),
				// Active Modules (user settings)
				// Optional: Themes can pass an array here to set them as defaults (before user settings saved)
				//           Set to false for all deactive by default. Anything else is all active by default.
				'activemods' => array(
					'widget' => array(),
					'block' => array(),
					'misc' => array(),
				),
				// Disabled Mod Types (user settings).
				'disabledmodtypes' => array(),
				// Default Styles
				'presets'   => array(
					'white'        => __( 'White',           'hootkit' ),
					'black'        => __( 'Black',           'hootkit' ),
					'brown'        => __( 'Brown',           'hootkit' ),
					'blue'         => __( 'Blue',            'hootkit' ),
					'cyan'         => __( 'Cyan',            'hootkit' ),
					'green'        => __( 'Green',           'hootkit' ),
					'yellow'       => __( 'Yellow',          'hootkit' ),
					'amber'        => __( 'Amber',           'hootkit' ),
					'orange'       => __( 'Orange',          'hootkit' ),
					'red'          => __( 'Red',             'hootkit' ),
					'pink'         => __( 'Pink',            'hootkit' ),
				),
				// Default Styles
				'presetcombo'   => array(
					'white'        => __( 'White',           'hootkit' ),
					'black'        => __( 'Black',           'hootkit' ),
					'brown'        => __( 'Brown',           'hootkit' ),
					'brownbright'  => __( 'Brown (Bright)',  'hootkit' ),
					'blue'         => __( 'Blue',            'hootkit' ),
					'bluebright'   => __( 'Blue (Bright)',   'hootkit' ),
					'cyan'         => __( 'Cyan',            'hootkit' ),
					'cyanbright'   => __( 'Cyan (Bright)',   'hootkit' ),
					'green'        => __( 'Green',           'hootkit' ),
					'greenbright'  => __( 'Green (Bright)',  'hootkit' ),
					'yellow'       => __( 'Yellow',          'hootkit' ),
					'yellowbright' => __( 'Yellow (Bright)', 'hootkit' ),
					'amber'        => __( 'Amber',           'hootkit' ),
					'amberbright'  => __( 'Amber (Bright)',  'hootkit' ),
					'orange'       => __( 'Orange',          'hootkit' ),
					'orangebright' => __( 'Orange (Bright)', 'hootkit' ),
					'red'          => __( 'Red',             'hootkit' ),
					'redbright'    => __( 'Red (Bright)',    'hootkit' ),
					'pink'         => __( 'Pink',            'hootkit' ),
					'pinkbright'   => __( 'Pink (Bright)',   'hootkit' ),
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

	HKConfig::get_instance();

endif;