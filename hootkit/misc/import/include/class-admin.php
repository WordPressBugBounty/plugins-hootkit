<?php
/**
 * HootKit Import Admin
 * This file is loaded at plugins_loaded@5
 */


namespace HootKit\Import;
use \HootKit\Inc\Helper_Assets;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Import\Admin' ) ) :

	class Admin {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Is this a plugin?
		 */
		private static $importplugin = false;

		/**
		 * Mult Demo - Pack Manifest
		 * @since  3.0.0
		 * @access public
		 */
		public $multipack = array();
		public $hasmulti = false;

		/**
		 * Single Demo - Pack Manifest
		 * @since  3.0.0
		 * @access public
		 */
		public $demopack = array();

		/**
		 * Single Demo - slug identifier
		 * @since  3.0.0
		 * @access public
		 */
		public $demoslug = '';

		/**
		 * Setup Admin
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'loader' ), 94 );
		}

		/**
		 * Load if import is enabled by themes
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function loader() {

			/* Get values if Import is an embedded plugin */
			$dash = hootkit()->get_config( 'dashboard' );
			// if registered wphoot theme, sanitizeconfig has already made sure all required values exist
			// 'import', 'tabfilter', 'tabaction', hoot_dashboard() => we can reliably use them here.
			if ( is_array( $dash ) && !empty( $dash[ 'import' ] ) ) {
				self::$importplugin = array(
					'id'        => $dash['import_id'],
					'pagehook'  => hoot_dashboard( 'screen' ),
					'dashurl'   => hoot_dashboard( 'url', array( 'tab' => $dash[ 'import' ] ) ),
					'tabfilter' => $dash['tabfilter'],
					'tabaction' => $dash['tabaction']
				);
			}

			if ( self::$importplugin ) {

				// Check if import has been disabled by user
				$activemiscmods = hootkit()->get_config( 'activemods', 'misc' );
				$isactive = is_array( $activemiscmods ) && in_array( 'import', $activemiscmods );

				if ( ! $isactive ) :
					add_filter( self::$importplugin['tabfilter'], array( $this, 'unplug_tabs' ), 90, 2 );
				else:
					// Load import assets
					$hooks = array( self::$importplugin['pagehook'] );
					Helper_Assets::add_adminasset( 'hootkitimport', $hooks );
					Helper_Assets::add_adminasset( 'jquery-confirm', $hooks );
					// Render Content
					add_filter( self::$importplugin['tabfilter'], array( $this, 'plug_tabs' ), 90, 2 );
					add_action( self::$importplugin['tabaction'], array( $this, 'plug_modblock_content' ), 90, 4 );
					// Localize Script
					add_action( 'admin_enqueue_scripts', array( $this, 'localize_script' ), 11 );
					// Disable the WooCommerce Setup Wizard on Hoot Import page only
					add_action( 'current_screen', array( $this, 'woocommerce_disable_setup_wizard' ) );
					// Flush rewrite rules from a recent WooCommerce XML import
					if ( get_option( 'hootkitimport_wc_flush' ) ) {
						add_action( 'admin_menu', array( $this, 'woocommerce_flush' ), 5 );
					}
				endif;
			}
		}

		/**
		 * Pass script data
		 *
		 * @since  1.1.0
		 * @access public
		 * @return void
		 */
		public function localize_script() {
			wp_localize_script(
				hootkit()->slug . '-import',
				'hootkitimportData',
				array(
					'ajaxurl'   => admin_url( 'admin-ajax.php' ),
					'nonce'     => wp_create_nonce( 'hootkitimportnonce' ),
					'import_action' => 'hootkitimport_process',
					'strings' => array(
						'processing_plugin' => esc_html__( 'Processing...', 'hootkit' ),
						'active_process_alert' => esc_html__( 'Please wait. Another process in being performed.', 'hootkit' ),
						'confirm_msg' => '<h2>' . esc_html__( 'Please Note:', 'hootkit' ) . '</h2>'
										. esc_html__( 'Before you import the demo content, please note the following points:', 'hootkit' )
										. '<ol>'
											. '<li class="hootimp-highlightbg"><strong>' . esc_html__( 'The import process will automatically fetch the required files and images from wpHoot servers.', 'hootkit' ) . '</strong></li>'
											. '<li class="hootimp-highlight"><strong>' . esc_html__( 'It is highly recommended to import demo on a fresh WordPress installation to replicate it exactly like the theme demo.', 'hootkit' ) . '</strong></li>'
											. '<li><strong>' . esc_html__( 'None of the existing posts, pages, attachments, menus and other data on your site will be deleted during the import.', 'hootkit' ) . '</strong></li>'
											. '<li>' . esc_html__( 'Please click the Import button and wait. This process can take a few minutes depending upon your server.', 'hootkit' ) . '</li>'
										. '</ol>',
						'confirm_primarybtn' => esc_html__( 'Start Import', 'hootkit' ),
						'confirm_cancelbtn' => esc_html__( 'Cancel', 'hootkit' ),
						'loading_step' => esc_html__( 'Step', 'hootkit' ),
						'loading_plugin' => esc_html__( 'Installing', 'hootkit' ),
						'loading_prepare' => esc_html__( 'Fetching required files', 'hootkit' ),
						'loading_content' => esc_html__( 'Importing', 'hootkit' ),
						'loading_xml' => esc_html__( 'Please Wait. This step may take a few minutes.', 'hootkit' ),
						'stillloading_xml' => esc_html__( 'Still working... Please wait...', 'hootkit' ),
						'loading_final' => esc_html__( 'Finalizing Settings...', 'hootkit' ),
					),
				)
			);
		}

		/**
		 * Disable the WooCommerce Setup Wizard on Hoot Import page only
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function woocommerce_disable_setup_wizard( $screen ) {
			if ( is_object( $screen ) && !empty( $screen->id ) && self::$importplugin['pagehook'] === $screen->id ) {
				add_filter( 'woocommerce_enable_setup_wizard', '__return_false', 1 );
			}
		}

		/**
		 * Flush rewrite rules from a recent WooCommerce XML import
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function woocommerce_flush(){
			flush_rewrite_rules();
			delete_option( 'hootkitimport_wc_flush' );
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
			$order = array_values( array_diff( $order, array( 'demoimport' ) ) ); // array_values() to reindex numerically
			$tabsarray['order'] = $order;
			if ( isset( $tabsarray['demoimport'] ) ) unset( $tabsarray['demoimport'] );
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

			if ( !in_array( 'demoimport', $order ) ) array_unshift( $order, 'demoimport' );
			$tabsarray['demoimport'] = array(
				'label'   => __( 'Pre-built Demo Import', 'hootkit' ),
				'inpage'  => true,
				'widen'   => true,
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
			$hkblocks = array();

			$hkblocks[ 'grid-hki' ] = array( 'type' => 'gridconbox' );
			$hkblocks[ 'hki' ] = array( 'type' => 'hkimport' );
			$hkblocks[ 'grid-hkiend' ] = array( 'type' => 'gridconboxend' );
			$current_url = !empty( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			if ( strpos( $current_url, '_wpnonce' ) === false ) {
				$current_url = add_query_arg( 'refreshdemo', 'true', $current_url );
				$current_url = wp_nonce_url( $current_url, 'hootkitimport_refresh_demo_data_nonce' );
			}
			$hkblocks[ 'grid-mnote' ] = array( 'type' => 'gridgen', 'class' => 'hootabt-footnote hootimp-footer' );
			$hkblocks[ 'mnote' ] = array(
				'type' => 'columns',
				'columns' => 1,
				'blocks' => array(
					array(
						'name' => '',
						/* Translators: 1 is link start 2 is link end 3 is the dashicon */
						'desc' => sprintf( esc_html__( '%1$s%3$s Refetch Demo Data Files%2$s', 'hootkit' ), '<a href="' . esc_url( $current_url ) . '">', '</a>', '<span class="dashicons dashicons-update"></span>' ),
					),
				),
			);
			$hkblocks[ 'grid-mnoteend' ] = array( 'type' => 'gridgenend' );

			return $hkblocks;
		}

		/**
		 * Extra Tabs Module Block Templates
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function plug_modblock_content( $blockid, $modblock, $sanetags, $tabid ) {
			if ( empty( $modblock ) || ! is_array( $modblock ) || ! isset( $modblock['type'] ) || $modblock['type'] !== 'hkimport' ) {
				return;
			}
			$sanetags = is_array( $sanetags ) ? $sanetags : array();

			// Set multislug and multipack/hasmulti
			$multislug = self::$importplugin['id'];
			$multislug = !empty( $multislug ) && is_string( $multislug ) ? strtolower( $multislug ) : '';
			if ( !empty( $multislug ) ) {
				$this->set_multipack( $multislug, $sanetags );
			}

			// Render Screen
			if ( $this->hasmulti ) {
				?><div id="hootkitimp-multi" class="hootkitimp-multi"><?php
					$activedemo = !empty( $_GET['demo'] ) && array_key_exists( $_GET['demo'], $this->multipack ) ? $_GET['demo'] : false;
					// Render Multdemo screen
					$this->render_multi_idx( $activedemo );
					// Render containers for single demos
					foreach ( $this->multipack as $demoslug => $demopack ) {
						 ?><div id="<?php echo sanitize_html_class( 'hootkitimp-' . $demoslug ); ?>" class="hootkitimp-single <?php if ( $activedemo === $demoslug ) echo 'hootkit-active'; ?>"><?php
							if ( $activedemo === $demoslug ) {
								$this->render_singledemo( $activedemo );
							}
						?></div><?php
					}
				?></div><?php
			} else {
				$this->render_singledemo( self::$importplugin['id'] );
			}
		}

		/**
		 * Get manifest and set $multipack
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function set_multipack( $multislug, $sanetags=array() ) {
			$this->multipack = array();
			$manifest = include( hootimport()->dir . 'include/demopacks.php' );
			if ( !empty( $manifest ) && is_array( $manifest ) ) {
				// 1. Check for multislug in manifest
				$multislug = isset( $manifest[ $multislug ] ) && is_array( $manifest[ $multislug ] ) ? $multislug : str_ireplace( '-premium', '', $multislug );
				if ( !empty( $manifest[ $multislug ] ) && is_array( $manifest[ $multislug ] ) ) {
					// Collate a demos list
					$demos = !empty( $manifest[ $multislug ][ 'demos' ] ) && is_array( $manifest[ $multislug ][ 'demos' ] ) ? $manifest[ $multislug ][ 'demos' ] : array();
					$demospro = !empty( $manifest[ $multislug ][ 'demospro' ] ) && is_array( $manifest[ $multislug ][ 'demospro' ] ) ? $manifest[ $multislug ][ 'demospro' ] : array();
					$list = !empty( $manifest[ $multislug ]['list'] ) && is_array( $manifest[ $multislug ]['list'] ) ? $manifest[ $multislug ]['list'] : array_merge( $demos, $demospro );
					foreach ( $list as $lslug ) {
						$nonmodslug = $lslug;
						// 2. Check for each demo slug in manifest
						$lslug = isset( $manifest[ $lslug ] ) && is_array( $manifest[ $lslug ] ) ? $lslug : str_ireplace( '-premium', '', $lslug );
						if ( !empty( $manifest[ $lslug ] ) && is_array( $manifest[ $lslug ] ) ) {
							$this->multipack[ $nonmodslug ] = array(
								'name' => !empty( $manifest[ $lslug ]['name'] ) && is_string( $manifest[ $lslug ]['name'] ) ? $manifest[ $lslug ]['name'] : __( 'Theme Demo', 'hootkit' ),
								'img' => trailingslashit( $manifest['cdn_base'] ) . 'images/hootkit/' . ( !empty( $manifest[ $lslug ]['thumb'] ) ? $manifest[ $lslug ]['thumb'] : $lslug . '-thumb.jpg' ),
								'islocked' => in_array( $nonmodslug, $demospro ) ? ( is_array( $sanetags ) && !empty( $sanetags['urltheme'] ) ? $sanetags['urltheme'] : true ) : false,
								'preview' => !empty( $manifest[ $lslug ]['preview'] ) && is_string( $manifest[ $lslug ]['preview'] ) ? $manifest[ $lslug ]['preview'] : '',
							);
						}
					}
				}
			}
			$this->hasmulti = !empty( $this->multipack );
		}

		/**
		 * Get manifest and set $demopack
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function set_demopack() {
			$manifest = include( hootimport()->dir . 'include/demopacks.php' );
			if ( empty( $manifest ) || !is_array( $manifest ) || empty( $manifest['cdn_url'] ) ) {
				$this->demopack = array( 'error' => 'invalid_manifest' );
			} else {
				$demoslug = isset( $manifest[ $this->demoslug ] ) && is_array( $manifest[ $this->demoslug ] ) ? $this->demoslug : str_ireplace( '-premium', '', $this->demoslug );
				if ( !empty( $manifest[ $demoslug ] ) && is_array( $manifest[ $demoslug ] ) ) {
					if ( array_key_exists( 'demos', $manifest[ $demoslug ] ) || array_key_exists( 'demospro', $manifest[ $demoslug ] ) ) :
						$this->demopack = array( 'error' => 'ismultipack' );
					else :
					$this->demopack = array(
						'pack' => trailingslashit( $manifest['cdn_url'] ) . $this->demoslug . '.zip',
						'img' => trailingslashit( $manifest['cdn_base'] ) . 'images/hootkit/' . ( !empty( $manifest[ $demoslug ]['img'] ) ? $manifest[ $demoslug ]['img'] : $demoslug . '.jpg' ),
						'plugins' => !empty( $manifest[ $demoslug ]['plugins'] ) ? $manifest[ $demoslug ]['plugins'] : array(),
					);
					endif;
				} else {
					$this->demopack = array( 'error' => 'incompatible_theme' );
				}
			}
		}

		/**
		 * Render a single Demo Pack
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function render_multi_idx( $activedemo=false ) {
			$pageurl = self::$importplugin['dashurl']; ?>
			<div id="hootkitimp_idx" class="hootkitimp_idx <?php if ( !$activedemo ) echo 'hootkit-active'; ?>">
				<?php foreach ( $this->multipack as $demoslug => $demopack ) :
					$demoname = !empty( $demopack['name'] ) ? $demopack['name'] : '';
					$demoimg = !empty( $demopack['img'] ) ? $demopack['img'] : '';
					$islocked = !empty( $demopack['islocked'] );
					$preview = !empty( $demopack['preview'] ) ? $demopack['preview'] : '';
					$url = add_query_arg( 'demo', $demoslug, $pageurl );
					?>
					<div class="hootkitimp_idx-item <?php if ( $islocked ) echo 'hootkitimp_idx-locked'; ?>">
						<div class="hootkitimp_idx-ss">
							<?php
							if ( !empty( $demoimg ) ) {
								echo '<img src="' . esc_url( $demoimg ) . '" alt="' . esc_attr( $demoname ) . '" />';
							}
							if ( !empty( $preview ) ) {
								echo '<a class="hootkitimp_idx-previewlink" href="' . esc_url( $preview ) . '" target="_blank" rel="noopener noreferrer">' . '<em><span class="dashicons dashicons-welcome-view-site"></span> ' . __( 'Preview', 'hootkit' ) . '</em></a>';
							}
							?>
						</div>
						<div class="hootkitimp_idx-foot">
							<?php if ( !empty( $demoname ) ) : ?>
								<div class="hootkitimp_idx-label"><?php
									if ( $islocked ) { echo '<span class="dashicons dashicons-lock"></span> '; }
									echo esc_html( $demoname );
								?></div>
							<?php endif; ?>
							<a class="hootkitimp_idx-btn button hootabt-highlight" href="<?php echo esc_url( $url ); ?>" data-demoslug="<?php echo esc_attr( $demoslug ); ?>"><?php if ( $islocked ) { esc_html_e( 'Details', 'hootkit' ); } else { esc_html_e( 'Import', 'hootkit' ); } ?></a>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if ( apply_filters( 'hootkitimport_render_comingsoon', true ) ) : ?>
					<div class="hootkitimp_idx-item hootkitimp_idx-more">
						<div class="hootkitimp_idx-ss">
							<div class="hootkitimp_idx-moremsg"><?php esc_html_e( 'Coming Soon', 'hootkit' ); ?></div>
						</div>
						<div class="hootkitimp_idx-foot">
							<div class="hootkitimp_idx-label"><?php esc_html_e( 'More Demos...', 'hootkit' ); ?></div>
						</div>
					</div>
				<?php endif; ?>
			</div><?php
		}

		/**
		 * Render a single Demo Pack
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function render_singledemo( $demoslug ) {
			// Set demoslug and demopack
			$this->demoslug = !empty( $demoslug ) && is_string( $demoslug ) ? strtolower( $demoslug ) : '';
			if ( !empty( $this->demoslug ) ) {
				$this->set_demopack();
			}

			// Set compatibility
			$is_compatible = !empty( $this->demopack ) && is_array( $this->demopack ) && !empty( $this->demopack['pack'] );

			// Check if available
			$islocked = hootkit_arrayel( $this->multipack, array( $this->demoslug, 'islocked' ) );
			$is_available = $this->hasmulti ? empty( $islocked ) : true;

			// Regular Maintenance tasks
			if ( $is_compatible ) {
				$force_cleanup = isset( $_GET['refreshdemo'] ) && $_GET['refreshdemo'] === 'true' && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['_wpnonce'] ) ), 'hootkitimport_refresh_demo_data_nonce' ) ? true : false;
				hootimport()->cleanup( $force_cleanup );
			}
			?>
					<?php if ( $this->hasmulti ) : ?>
						<div class="hootkitimp-single-header">
							<a class="hootkitimp-backtomulti" href="<?php echo esc_url( self::$importplugin['dashurl'] ); ?>"><span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e( 'Back', 'hootkit' ); ?></a>
						</div>
					<?php endif; ?>
					<?php if ( !$is_compatible ) : ?>
						<div class="hootimp-content"><?php
							$icnotice = !empty( self::$importplugin['incompatible'] ) ? self::$importplugin['incompatible'] : false;
							if ( !empty( $icnotice ) && is_string( $icnotice ) ) {
								echo '<div class="notice notice-warning">' . wp_kses_post( wpautop( $icnotice ) ) . '</div>';
							} elseif ( empty( $this->demopack )
								|| !is_array( $this->demopack )
								|| ( isset( $this->demopack['error'] ) && $this->demopack['error'] === 'incompatible_theme' )
							) {
								$activetmpl = wp_get_theme();
								$activetmpl_author = ($activetmpl->parent()) ? $activetmpl->parent()->get('Author') : $activetmpl->get('Author');
								echo '<div class="notice notice-info">';
									if ( stripos( $activetmpl_author, 'wphoot' ) !== false ) {
										echo '<p>' . esc_html__( 'The current theme is not supported by this version of HootKit Import.', 'hootkit' ) . '</p>';
										echo '<p>' . esc_html__( 'Please update your current theme and HootKit plugin to their latest versions.', 'hootkit' ) . '</p>';
									} else {
										echo '<p>' . esc_html__( 'The current theme is not supported by HootKit Import.', 'hootkit' ) . '</p>';
										/* Translators: The %s are placeholders for HTML, so the order can't be changed. */
										echo '<p>' . sprintf( esc_html__( 'Please make sure you are using a compatible %1$swpHoot Theme%2$s. Not all themes are compatible with HootKit Import.', 'hootkit' ), '<a href="https://wordpress.org/themes/author/wphoot/" rel="nofollow">', '</a>' ) . '</p>';
									}
								echo '</div>';
							} else {
								echo '<div class="notice notice-error">';
									if ( isset( $this->demopack['error'] ) && $this->demopack['error'] === 'invalid_manifest' ) {
										echo '<p>' . esc_html__( 'The theme demos manifest is not formatted properly.', 'hootkit' ) . '</p>';
									} elseif ( isset( $this->demopack['error'] ) && $this->demopack['error'] === 'ismultipack' ) {
										echo '<p>' . esc_html__( 'This looks like a multi demo pack. The theme demos manifest is not formatted properly.', 'hootkit' ) . '</p>';
									} else {
										echo '<p>' . esc_html__( 'An unknown error occurred.', 'hootkit' ) . '</p>';
									}
								echo '</div>';
							}
						?></div>
					<?php else: ?>
						<div class="hootimp-content hootimp-content-install">
							<div class="hootimp-screenshots">
								<div class="hootimp-screenshot">
									<?php
									if ( !empty( $this->demopack['img'] ) ) {
										echo '<img src="' . esc_url( $this->demopack['img'] ) . '" alt="' . esc_attr__( 'Import Demo', 'hootkit' ) . '" />';
									} ?>
								</div>
							</div>
							<div class="hootimp-theme-info">
								<?php if ( $this->hasmulti ) {
									$dname = hootkit_arrayel( $this->multipack, array( $this->demoslug, 'name' ) );
									if ( $dname ) {
										echo '<h2>' . esc_html( $dname ) . '</h2>';
									}
								} ?>
								<p><?php esc_html_e( 'Importing demo data makes your website look similar to the theme demo. Users often find it easier to start with the demo content and then edit it to fit their needs rather than starting from scratch.', 'hootkit' ); ?></p>

								<?php if ( ! $is_available ) : ?>
									<div class="hootkitimp-single-notice"><?php
										/* Translators: %s are placeholders for HTML, so the order can't be changed. */
										printf( esc_html( '%1$sUpgrade required%2$s%3$sThis is a premium demo.%5$sPlease upgrade to the premium pack to import this demo.%4$s', 'hootkit' ), '<h5>', '</h5>', '<p>', '</p>', '<br />' );
										if ( is_string( $islocked ) ) echo '<a href="' . esc_url( $islocked ) . '" class="button button-primary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get Premium', 'hootkit' ) . '</a>';
									?></div>
								<?php else :
									$demotype = apply_filters( 'hootkitimport_demositetype', '' );
									if ( $demotype === 'base' ) {
										$mflogentry = get_option( 'hootkitimport_mflogs' );
										if ( !empty( $mflogentry ) && is_array( $mflogentry ) && empty( $mflogentry['proversion'] ) )
											echo '<p class="hootkitimp-single-warning"><span class="dashicons dashicons-info-outline"></span> ' . esc_html__( 'You are importig the free version of the demo site. This may not include some of the premium widgets.', 'hootkit' ) . '</p>';
									}
								?>

								<form id="hootimp-form" class="hootimp-form<?php echo ( class_exists( 'WooCommerce' ) ? '' : ' hootimp--nowc' ); ?>">
									<div class="hootimp-noloader">

										<?php $plugin_ops = $this->get_plugins_info();

										// Set plugin status
										$activeplugins = array();
										foreach ( $plugin_ops as $id => $option ) {
											if ( is_array( $option ) && !empty( $option['data'] ) && is_array( $option['data'] ) ) {
												if ( !empty( $option['data']['class'] ) && class_exists( $option['data']['class'] ) ) {
													$activeplugins[ $id ] = $plugin_ops[ $id ];
													$activeplugins[ $id ]['status'] = 'active';
													unset( $plugin_ops[ $id ] );
												} elseif ( !empty( $option['data']['const'] ) && defined( $option['data']['const'] ) ) {
													$activeplugins[ $id ] = $plugin_ops[ $id ];
													$activeplugins[ $id ]['status'] = 'active';
													unset( $plugin_ops[ $id ] );
												} elseif ( !empty( $option['data']['file'] ) && file_exists( WP_PLUGIN_DIR . "/{$option['data']['file']}" ) ) {
													$plugin_ops[ $id ]['status'] = 'installed';
												} else {
													$plugin_ops[ $id ]['status'] = 'unavailable';
												}
											} else { // this shouldn't have happened
												$plugin_ops[ $id ]['status'] = 'unavailable';
											}
										}

										// Divide remaning non active into rcmd categories
										$required = array_filter( $plugin_ops, function( $item ) {
											return !empty( $item['rcmd'] ) && $item['rcmd'] === 'reqd';
										} );
										$recommended = array_filter( $plugin_ops, function( $item ) {
											return !empty( $item['rcmd'] ) && $item['rcmd'] !== 'reqd';
										} );
										$optional = array_filter( $plugin_ops, function( $item ) {
											return empty( $item['rcmd'] );
										} );
										$show_subhead = !empty( $activeplugins ) || ( count(array_filter(array($required, $recommended, $optional))) >= 2 );

										if ( ( !empty( $plugin_ops ) && is_array( $plugin_ops ) ) || ( !empty( $activeplugins ) && is_array( $activeplugins) ) ) : ?>
											<div class="hootimp-op-group">
												<h4><?php esc_html_e( 'Plugins:', 'hootkit' ); ?></h4>
												<div class="hootimp-h4desc"><?php esc_html_e( 'These plugins have been used on the demo site and are required to replicate the demo content.', 'hootkit' ); ?></div>
												<?php if ( !empty( $activeplugins ) ) : ?>
													<?php if ( $show_subhead ) : ?><h5><span><?php esc_html_e( 'Active Plugins:', 'hootkit' ); ?></span></h5><?php endif; ?>
													<?php foreach ( $activeplugins as $id => $plugin ) {
														$this->render_option( 'plugin', $id, $plugin );
													} ?>
												<?php endif; ?>
												<?php if ( !empty( $required ) ) : ?>
													<?php if ( $show_subhead ) : ?><h5><span><?php esc_html_e( 'Required Plugins:', 'hootkit' ); ?></span></h5><?php endif; ?>
													<?php foreach ( $required as $id => $plugin ) {
														$this->render_option( 'plugin', $id, $plugin );
													} ?>
												<?php endif; ?>
												<?php if ( !empty( $recommended ) ) : ?>
													<?php if ( $show_subhead ) : ?><h5><span><?php esc_html_e( 'Highly Recommended', 'hootkit' ); ?></span></h5><?php endif; ?>
													<?php foreach ( $recommended as $id => $plugin ) {
														$this->render_option( 'plugin', $id, $plugin );
													} ?>
												<?php endif; ?>
												<?php if ( !empty( $optional ) ) : ?>
													<?php if ( $show_subhead ) : ?><h5><span><?php esc_html_e( 'Optional', 'hootkit' ); ?></span></h5><?php endif; ?>
													<?php foreach ( $optional as $id => $plugin ) {
														$this->render_option( 'plugin', $id, $plugin );
													} ?>
												<?php endif; ?>
											</div>
										<?php endif; ?>

										<div class="hootimp-op-group">
											<h4><?php esc_html_e( 'Import Content:', 'hootkit' ); ?></h4>
											<?php
												$this->render_option( 'content', 'xml', array(
													'name' => esc_html__( 'Content XML', 'hootkit' ),
													'desc' => esc_html__( 'posts, pages, categories, menus, images etc.', 'hootkit' ),
												) );
												$this->render_option( 'content', 'wcxml', array(
													'name' => esc_html__( 'WooCommerce XML', 'hootkit' ),
													'desc' => esc_html__( 'products, categories, shop pages etc.', 'hootkit' ),
													'checked' => class_exists( 'WooCommerce' ),
												) );
												$this->render_option( 'content', 'dat', array(
													'name' => esc_html__( 'Customizer DAT', 'hootkit' ),
													'desc' => esc_html__( 'Customizer Settings', 'hootkit' ),
												) );
												$this->render_option( 'content', 'wie', array(
													'name' => esc_html__( 'Widgets WIE', 'hootkit' ),
												) );
											?>

											<div class="hootimp-action">
												<?php if ( !empty( $this->demoslug ) ) : ?>
													<input type="hidden" name="demo" value="<?php echo esc_attr( $this->demoslug ) ?>" />
												<?php endif; ?>
												<?php if ( !empty( $this->demopack['pack'] ) ) : ?>
													<input type="hidden" name="pack" value="<?php echo esc_attr( $this->demopack['pack'] ) ?>" />
												<?php endif; ?>
												<a href="#" id="hootimp-submit" class="button button-primary button-hero hootimp-submit"><?php esc_html_e( 'Import Demo', 'hootkit' ) ?></a>
											</div>
										</div>

									</div>
									<div class="hootimp-loader">
										<div class="hootimp-loaderbar"><div></div></div>
										<div id="hootimp-loadermsg"><?php esc_html_e( 'Importing Demo...', 'hootkit' ); ?></div>
									</div>
									<div class="hootimp-complete">
										<div>
											<div class="hootimp-complete-icon"></div>
											<div><strong><?php esc_html_e( 'All Done.', 'hootkit' ); ?></strong></div>
										</div>
										<ol>
											<li><?php
											/* Translators: %s are placeholders for HTML, so the order can't be changed. */
											printf( esc_html__( 'To edit Settings - %1$sVisit Customizer%2$s', 'hootkit' ), '<a href="' . esc_url( admin_url( 'customize.php' ) ) . '" rel="nofollow">', '</a>' );
											?></li>
											<li><?php
											/* Translators: %s are placeholders for HTML, so the order can't be changed. */
											printf( esc_html__( 'To edit Widgets - %1$sVisit Widgets screen%2$s', 'hootkit' ), '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '" rel="nofollow">', '</a>' );
											?></li>
											<li><?php
											/* Translators: %s are placeholders for HTML, so the order can't be changed. */
											printf( esc_html__( 'To see the installed demo content - %1$sView Site%2$s', 'hootkit' ), '<a href="' . esc_url( get_home_url() ) . '" rel="nofollow">', '</a>' );
											?></li>
										</ol>
										<p><a href="#" class="hootimp-show-log"><?php esc_html_e( 'View Log', 'hootkit' ); ?></a></p>
									</div>
									<div class="hootimp-loaderror notice notice-error">
										<p><?php esc_html_e( 'Import process finished with errors. Please try again later.', 'hootkit' ); ?></p>
										<div id="hootimp-loaderror-details"></div>
										<?php
										?>
										<p><a href="#" class="hootimp-show-log"><?php esc_html_e( 'View Log', 'hootkit' ); ?></a></p>
									</div>
									<div class="hootimp-load-details"></div>
								</form>

								<?php endif; // $is_available ?>
							</div>
						</div>
						<?php $current_url = ''; /*
						*/ ?>
					<?php endif; ?>
			<?php
		}

		/**
		 * Render Option
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function render_option( $type, $id, $option ){
			if ( is_array( $option ) ) :
				$isplugin = $type === 'plugin' ? true : false;

				$opname = !empty( $option['name'] ) ? $option['name'] : $id;
				$opdesc = !empty( $option['desc'] ) ? $option['desc'] : '';
				$checked = isset( $option['checked'] ) ? !empty( $option['checked'] ) : true;
				$opboxclasses = '';
				// Plugin Stuff
				$opreqd = false;
				$opdata = array();
				$pluginstatus = '';

				if ( ! $isplugin ) {
					$opboxclasses = 'hootimp-opbox hootimp-opbox--content hootimp-opbox--' . $id;
				} else {
					$pluginstatus = !empty( $option['status'] ) && is_string( $option['status'] ) ? $option['status'] : 'unavailable';
					$opreqd = !empty( $option['rcmd'] ) && $option['rcmd'] === 'reqd';
					$opdata = !empty( $option['data'] ) && is_array( $option['data'] ) ? $option['data'] : array();
					if ( $pluginstatus === 'active' || $opreqd ) {
						$checked = true;
					}
					$opboxclasses = 'hootimp-opbox hootimp-opbox--plugin hootimp-opbox--plugin_' . $pluginstatus;
					if ( $opreqd )
						$opboxclasses .= ' hootimp-opbox--plugin_reqd';
					if ( ! $checked )
						$opboxclasses .= ' hootimp-opbox--plugin_noaction';
				}
				?>
				<div class="<?php echo hoot_sanitize_html_classes( $opboxclasses ) ?>">

					<div class="hootimp-optoggle bettertogglebox">
						<?php if ( $isplugin && $pluginstatus === 'active' ) : // active plugins
							?><span class="dashicons dashicons-yes"></span>
						<?php else : // inactive/not-installed plugin OR Not a plugin ?>
							<input type="checkbox"<?php
								echo ' name="' . esc_attr( $type ) . '[]"';
								echo ' value="' . esc_attr( $id ) . '"';
								echo ' data-name="' . esc_attr( $opname ) . '"';
								if ( $checked ) echo ' checked="checked"';
								if ( $isplugin ) {
									foreach ( $opdata as $datakey => $dataval ) {
										echo ' data-' . sanitize_key( $datakey ) . '="' . esc_attr( $dataval ) . '"';
									}
								}
							?> />
							<span class="hootimp-toggle bettertoggle"></span>
						<?php endif; ?>
					</div>

					<div class="hootimp-oplabel">
						<strong><?php echo esc_html( $opname ) ?></strong>
						<?php if ( $opdesc ) {
							echo '<em>(' . esc_html( $opdesc ) . ')</em>';
						} elseif ( $isplugin ) {
							echo '<em><a href="' . esc_url( 'https://wordpress.org/plugins/' . $id . '/' ) . '" target="_blank" style="color:inherit">' . esc_html( 'View details', 'hootkit' ) . '</a></em>';
						} ?>
					</div>

					<?php if ( $isplugin ) : ?>
						<div class="hootimp-opnote">
							<div class="hootimp-opnote--active"><span class="dashicons dashicons-yes"></span><?php
								esc_html_e( 'Active', 'hootkit' );
							?></div>
							<div class="hootimp-opnote--installed"><span class="dashicons dashicons-marker"></span><?php
								esc_html_e( 'Activate', 'hootkit' );
								if ( $opreqd ) echo ' <strong>' . esc_html__( '(Required)', 'hootkit' ) . '</strong>';
							?></div>
							<div class="hootimp-opnote--unavailable"><span class="dashicons dashicons-plus"></span><?php
								esc_html_e( 'Install', 'hootkit' );
								if ( $opreqd ) echo ' <strong>' . esc_html__( '(Required)', 'hootkit' ) . '</strong>';
							?></div>
						</div>
					<?php endif; ?>

				</div>
			<?php endif;
		}

		/**
		 * Common Plugins
		 * @since  3.0.0
		 * @access public
		 * @return array
		 */
		public function get_plugins_info(){
			$common = array(
				'hootkit' => array(
					'name' => esc_html__( 'HootKit', 'hootkit' ),
					'rcmd' => true,
					'data' => array( 'class' => 'HootKit', 'file' => 'hootkit/hootkit.php' ), // class || const || func
				),
				'contact-form-7' => array(
					'name' => esc_html__( 'Contact Form 7', 'hootkit' ),
					'data' => array( 'const' => 'WPCF7_VERSION', 'file' => 'contact-form-7/wp-contact-form-7.php' ),
				),
				'breadcrumb-navxt' => array(
					'name' => esc_html__( 'Breadcrumb NavXT', 'hootkit' ),
					'data' => array( 'class' => 'breadcrumb_navxt', 'file' => 'breadcrumb-navxt/breadcrumb-navxt.php' ),
				),
				'woocommerce' => array(
					'name' => esc_html__( 'Woocommerce - eCommerce Shop', 'hootkit' ),
					'checked' => false,
					'data' => array( 'class' => 'WooCommerce', 'file' => 'woocommerce/woocommerce.php' ),
				),
				'newsletter' => array(
					'name' => esc_html__( 'Newsletter', 'hootkit' ),
					'checked' => false,
					'data' => array( 'const' => 'NEWSLETTER_VERSION', 'file' => 'newsletter/plugin.php' ),
				),
				'mappress-google-maps-for-wordpress' => array(
					'name' => esc_html__( 'MapPress - Google Maps', 'hootkit' ),
					'checked' => false,
					'data' => array( 'class' => 'Mappress', 'file' => 'mappress-google-maps-for-wordpress/mappress.php' ),
				),
			);
			$plugins = array();
			$demoplugins = !empty( $this->demopack['plugins'] ) && is_array( $this->demopack['plugins'] ) ? $this->demopack['plugins'] : array();
			foreach ( $demoplugins as $check ) {
				if ( is_string( $check ) ) {
					if ( !empty( $common[ $check ] ) ) {
						$plugins[ $check ] = $common[ $check ];
					}
				} elseif( is_array( $check ) && !empty( $check['slug'] ) ) {
					$slug = $check['slug'];
					$data = !empty( $check['data'] ) && is_array( $check['data'] ) ? $check['data'] : array();
					$plugins[ $slug ] = !empty( $common[ $slug ] ) ? hootkit_recursive_parse_args( $data, $common[ $slug ] ) : $data;
				}
			}
			// if ( isset( $plugins['hootkit'] ) )
			// 	unset( $plugins['hootkit'] );
			return $plugins;
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

	Admin::get_instance();

endif;