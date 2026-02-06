<?php
/**
 * Admin Settings class
 * This file is loaded at plugins_loaded@5 for is_admin()
 *
 * @since   1.1.0
 * @package Hootkit
 */

namespace HootKit\Admin;
use \HootKit\Inc\Manifest;
use \HootKit\Inc\Helper_Assets;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Admin\Settings' ) ) :

	class Settings {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Is this a plugin?
		 */
		private static $settingsplugin = false;

		/**
		 * Setup Admin Settings
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'loader' ), 94 );
		}

		/**
		 * Load if tools is enabled by themes
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function loader() {

			/* Get values if Settings is an embedded plugin */
			$dash = hootkit()->get_config( 'dashboard' );
			// if registered wphoot theme, sanitizeconfig has already made sure all required values exist
			// 'settings', 'tabfilter', 'tabaction', hoot_dashboard() => we can reliably use them here.
			if ( is_array( $dash ) && !empty( $dash[ 'settings' ] ) ) {
				self::$settingsplugin = array(
					'pagehook'  => hoot_dashboard( 'screen' ),
					'dashurl'   => hoot_dashboard( 'url', array( 'tab' => $dash[ 'settings' ] ) ),
					'tabfilter' => $dash['tabfilter'],
					'tabaction' => $dash['tabaction']
				);
			}

			// Add action links on Plugin Page
			add_action( 'plugin_action_links_' . hootkit()->plugin_basename, array( $this, 'plugin_action_links' ), 10, 4 );

			if ( self::$settingsplugin ) {
				// Load settings page assets
				Helper_Assets::add_adminasset( 'adminsettingsplug', array( self::$settingsplugin['pagehook'] ) );
				// Render Content
				add_filter( self::$settingsplugin['tabfilter'], array( $this, 'plug_tabs' ), 90, 2 );
				add_action( self::$settingsplugin['tabaction'], array( $this, 'plug_modblock_content' ), 90, 4 );
			} else {
				// Add settings page
				add_action( 'admin_menu', array( $this, 'add_page' ), 5 );
				// Load settings page assets
				Helper_Assets::add_adminasset( 'adminsettings', array( 'settings_page_' . hootkit()->slug ) );
			}

			// Localize Script
			add_action( 'admin_enqueue_scripts', array( $this, 'localize_script' ), 11 );

			// Add ajax callback
			add_action( 'wp_ajax_hootkitsettings', array( $this, 'admin_ajax_settings_handler' ) );

			// Footer rating text.
			add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
			add_action( 'wp_ajax_hootkit_adminsettings_rated', array( $this, 'admin_footer_textrated' ) );
			add_action( 'hootkit/deactivate', array( $this, 'deactivation' ), 10, 1 );

		}

		/**
		 * Add action links
		 * @param string[] $actions     An array of plugin action links. By default this can include 'activate',
		 *                              'deactivate', and 'delete'. With Multisite active this can also include
		 *                              'network_active' and 'network_only' items.
		 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array    $plugin_data An array of plugin data. See `get_plugin_data()`.
		 * @param string   $context     The plugin context. By default this can include 'all', 'active', 'inactive',
		 *                              'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
		 *
		 * @since  1.1.0
		 * @access public
		 * @return void
		 */
		public function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
			$url = is_array( self::$settingsplugin ) && !empty( self::$settingsplugin['dashurl'] ) ? esc_url( self::$settingsplugin['dashurl'] ) : admin_url('options-general.php?page=' . hootkit()->slug );
			if ( $url )
				$actions['manage'] = '<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'hootkit' ) . '</a>';
			return $actions;
		}

		/**
		 * Change the admin footer text for Settings page
		 * @since  3.0.0
		 * @param  string $footer_text
		 * @return string
		 */
		public function admin_footer_text( $footer_text ) {
			if ( ! current_user_can( 'manage_options' ) || get_option( 'hootkit_adminsettings_footer' ) ) {
				return $footer_text;
			}
			$screen = get_current_screen();
			$loadscreen = self::$settingsplugin && is_array( self::$settingsplugin ) && !empty( self::$settingsplugin['pagehook'] ) ? self::$settingsplugin['pagehook'] : 'settings_page_' . hootkit()->slug;
			if ( $loadscreen === $screen->id ) {
				$footer_text =
					/* Translators: The %s are placeholders for HTML, so the order can't be changed. */
					sprintf( esc_html__( 'If you like HootKit plugin, please consider rating us a %1$s %3$s on WordPress.org%2$s to help us spread the word.', 'hootkit' ), '<a class="hootkit-rateus" href="https://wordpress.org/support/plugin/hootkit/reviews/?rate=5#new-post" rel="nofollow" target="_blank" data-rated="' . esc_attr__( 'Thanks :)', 'hootkit' ) . '">', '</a>', '&#9733;&#9733;&#9733;&#9733;&#9733;' );
			}
			return $footer_text;
		}

		/**
		 * Admin Footer - Rated
		 * @since  3.0.0
		 */
		public function admin_footer_textrated() {
			check_ajax_referer( 'hootkit-adminsettings-footernonce', 'nonce' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			update_option( 'hootkit_adminsettings_footer', 1 );
			wp_die();
		}

		/**
		 * DeActivation Hook
		 * @since  3.0.0
		 */
		public function deactivation( $activation ) {
			delete_option( 'hootkit_adminsettings_footer' );
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
				hootkit()->slug . '-adminsettings',
				'hootkitSettingsData',
				array(
					'strings' => array(
						'default' => __( 'Save Changes', 'hootkit' ),
						'process' => __( 'Please Wait', 'hootkit' ),
						'success' => '<span class="dashicons dashicons-yes"></span>' . __( 'Settings Saved', 'hootkit' ),
						'error'   => '<span class="dashicons dashicons-no-alt"></span>' . __( 'Error! Please try again.', 'hootkit' )
					),
					'ajaxurl' => wp_nonce_url( admin_url('admin-ajax.php?action=hootkitsettings'), 'hootkit-settings-nonce' ),
					'ajaxfooterurl' => wp_nonce_url( admin_url('admin-ajax.php?action=hootkit_adminsettings_rated'), 'hootkit-adminsettings-footernonce' )
				)
			);
		}

		/**
		 * Add Settings Page
		 *
		 * @since  1.1.0
		 * @access public
		 * @return void
		 */
		public function add_page(){
			add_submenu_page(
				'options-general.php',
				__( 'HootKit Modules Settings', 'hootkit' ),
				__( 'HootKit', 'hootkit' ),
				'manage_options',
				hootkit()->slug,
				array( $this, 'render_admin' )
			);
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

			if ( !in_array( 'hootkit', $order ) ) array_unshift( $order, 'hootkit' );
			$tabsarray['hootkit'] = array(
				'label'   => __( 'HootKit Options', 'hootkit' ),
				'inpage'  => true,
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
			$hkblocks[ 'grid-hks' ] = array( 'type' => 'gridgen' );
			$hkblocks[ 'hks' ] = array( 'type' => 'hksettings' );
			$hkblocks[ 'grid-hksend' ] = array( 'type' => 'gridgenend' );
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
			if ( empty( $modblock ) || ! is_array( $modblock ) || ! isset( $modblock['type'] ) || $modblock['type'] !== 'hksettings' ) {
				return;
			}

			$thememods = hootkit()->get_config( 'modules' );

			$currentscreen = ( !empty( $_GET['view'] ) ) ? $_GET['view'] : 'undefined';
			$validscreens = array();
			foreach ( $thememods as $modtype => $modsarray )
				if ( !empty( $modsarray ) ) $validscreens[] = $modtype;
			if ( !\in_array( $currentscreen, $validscreens ) ) $currentscreen = ( !empty( $validscreens[0] ) ) ? $validscreens[0] : 'undefined';

			$skip = empty( $validscreens );
			?>
			<form id="hootkit-settings" class="hootkit-settings">
				<?php if ( $skip ) :
					esc_html_e( 'Nothing to show here!', 'hootkit' );
				else: ?>

					<div id="hootkit-tabs" class="hootkit-tabs">
						<?php foreach ( $validscreens as $modtype ) : ?>
							<div class="hootkit-tab <?php if ( $modtype === $currentscreen ) echo 'hootactive'; ?>" data-tabid="<?php echo esc_attr( $modtype ); ?>"><?php echo esc_html( hootkit()->get_string( 'setting-' . $modtype ) ); ?></div>
						<?php endforeach;
						?>
						<div class="hootkit-tabsubmit"><?php
							if ( empty( $_GET['settingssave'] ) ) :
								?><a href="#" id="hk-submit" class="button button-primary hk-submit disabled"><?php _e( 'Save Changes', 'hootkit' ); ?></a><?php
							else:
								?><a href="#" id="hk-submit" class="button button-primary hk-submit hootkit-ok"><span class="dashicons dashicons-yes"></span> <?php _e( 'Settings Saved', 'hootkit' ); ?></a><?php
							endif;
						?></div>
					</div>

					<?php foreach ( $thememods as $modtype => $modsarray ) :
						if ( empty( $modsarray ) || !is_array( $modsarray ) )
							continue;
						?>
						<div id="hoot-tabblock-<?php echo $modtype; ?>" class="hoot-tabblock <?php if ( $modtype === $currentscreen ) echo 'hootactive'; ?>">

							<?php // if ( $modtype == 'widget' || $modtype == 'block' )
								echo '<div class="hk-box-notice">' . sprintf( esc_html__( 'Enable/Disable HootKit %s throughout the site.', 'hootkit' ), hootkit()->get_string( 'setting-' . $modtype ) ) . '</div>';
							?>

							<div class="hk-modules">
								<?php $this->render_options( $modtype, true ); ?>
							</div><!-- .hk-modules -->

						</div>
					<?php endforeach; ?>

				<?php endif; ?>
			</form>
			<?php
		}

		/**
		 * Ajax handler for handling settings
		 *
		 * @since  1.1.0
		 * @access public
		 * @return void
		 */
		public function admin_ajax_settings_handler() {
			// Check nonce and permissions
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'hootkit-settings-nonce' ) ) {
				wp_send_json( array( 'setactivemods' => false, 'msg' => __( 'Invalid request.', 'hootkit' ) ) );
				exit;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json( array( 'setactivemods' => false, 'msg' => __( 'Insufficient permissions.', 'hootkit' ) ) );
				exit;
			}

			// Set Handle and Response
			$handle = ( !empty( $_POST['handle'] ) ) ? $_POST['handle'] : '';
			$response = array( 'setactivemods' => false, 'msg' => __( 'No handle specified.', 'hootkit' ) );

			// Handle Set active Mods request
			if ( $handle == 'setactivemods' ) {

				$store = Manifest::$modtypesarray;

				$values = array();
				parse_str( $_POST['values'], $values );
				$values = is_array( $values ) ? $values : array();

				foreach ( $store as $type => $arr ) {
					$valuetypearr = hootkit_arrayel( $values, $type );
					$valuetypearr = is_array( $valuetypearr ) ? $valuetypearr : array();
					foreach ( hootkit()->get_config( 'modules', $type ) as $check ) {
						$store[ $type ][ $check ] = \in_array( $check, $valuetypearr ) ? 'yes' : 'no';
					}
				}
				$store['disabled'] = isset( $values['disabled'] ) && is_array( $values['disabled'] ) ? $values['disabled'] : array();

				update_option( 'hootkit-activemods', $store );
				$response = array( 'setactivemods' => true, 'msg' => __( 'Settings saved.', 'hootkit' ) );
			}

			// Send response.
			wp_send_json( $response );
			exit;
		}

		/**
		 * Render Page
		 *
		 * @since  1.1.0
		 * @access public
		 * @return void
		 */
		public function render_admin(){

			$modules = hootkit()->get_mfmodules();
			$premium = hootkit()->get_config( 'premium' );
			$disabled = hootkit()->get_config( 'disabledmodtypes' );

			$thememods = hootkit()->get_config( 'modules' );
			$activemods = hootkit()->get_config( 'activemods' );
			$premiummods = array();
			foreach ( $premium as $pmod ) {
				if ( !empty( $modules[ $pmod ]['types'] ) )
					foreach ( $modules[ $pmod ]['types'] as $type ) {
						$premiummods[ $type ][] = $pmod;
					}
			}
			$wcinactivemods = hootkit()->get_config( 'wc-inactive' );
			// $wc = class_exists( 'WooCommerce' );

			$currentscreen = ( !empty( $_GET['view'] ) ) ? $_GET['view'] : 'undefined';
			$validscreens = array();
			foreach ( $thememods as $modtype => $modsarray )
				if ( !empty( $modsarray ) ) $validscreens[] = $modtype;
			if ( !\in_array( $currentscreen, $validscreens ) ) $currentscreen = ( !empty( $validscreens[0] ) ) ? $validscreens[0] : 'undefined';

			$skip = empty( $validscreens );
			?>
			<div class="hootkit-wrap wrap">

				<div class="hootkit-header">
					<div class="hk-gridbox">
						<h4><?php printf( esc_html__( 'Version: %1$s', 'hootkit' ), hootkit()->version ); ?></h4>
						<h3><?php esc_html_e( 'HootKit Settings', 'hootkit' ); ?></h3>
					</div>
				</div><!-- .hootkit-header -->

				<div class="hootkit-subheader">
					<div class="hk-gridbox"><h1></h1>
						<div class="hootkit-nav"><?php
							if ( $skip ) :
								esc_html_e( 'Nothing to show here!', 'hootkit' );
							else:
								foreach ( $validscreens as $modtype ) {
									echo '<a';
										echo ' class="hk-navitem';
											if ( $currentscreen == $modtype ) echo ' hk-currentnav';
											echo '"';
										echo ' data-view="' . esc_attr( $modtype ) . '"';
										echo ' href="' . admin_url('options-general.php?page=' . hootkit()->slug . '&view=' . esc_attr( $modtype ) ) . '"';
									echo '>' . esc_html( hootkit()->get_string( 'setting-' . $modtype ) ) . '</a>';
								}
							endif;
						?></div><!-- .hootkit-nav -->
					</div>
				</div><!-- .hootkit-subheader -->

				<?php if ( !$skip ) : ?>
				<form id="hootkit-settings" class="hootkit-settings">
					<div id="hootkit-container" class="hootkit-container hk-gridbox"><?php

						foreach ( $thememods as $modtype => $modsarray ) {
							if ( !empty( $modsarray ) ) {

								/**
								 * If modtype disabled, activemods would have been set to empty.
								 * User enables them on screen now => all display turned off.
								 * Instead they should show all on by default || or use values stored in db
								 */
								if ( \in_array( $modtype, $disabled ) ) {
									// $activemods[$modtype] = $modsarray;
									$dbvalue = get_option( 'hootkit-activemods', false );
									if ( \is_array( $dbvalue ) && !empty( $dbvalue[$modtype] ) ) {
										$activemods[$modtype] = array();
										foreach ( $dbvalue[$modtype] as $check => $active ) {
											if ( $active == 'yes' ) $activemods[$modtype][] = $check;
										}
									} else $activemods[$modtype] =  $modsarray; // This condition should never occur!
								}
								?>

								<div id="hk-<?php echo $modtype ?>" class="hk-box<?php
									echo ' hk-' . $modtype;
									if ( $modtype == $currentscreen ) echo ' hk-box-current';
									if ( \in_array( $modtype, $disabled ) ) echo ' hk-box-disabled';
									?>">

									<?php
									// if ( $modtype == 'widget' || $modtype == 'block' )
										echo '<div class="hk-box-notice">' . sprintf( esc_html__( 'Enable/Disable HootKit %s throughout the site.', 'hootkit' ), hootkit()->get_string( 'setting-' . $modtype ) ) . '</div>';
									?>

									<div class="hk-box-inner">

										<?php /* Box Navigation */ ?>
										<div class="hk-box-nav">
											<div class="hk-boxnav-title"><?php echo esc_html( hootkit()->get_string( 'setting-' . $modtype ) ); ?></div>
											<div class="hk-modtype-toggle">
												<span class="hk-modtype-enable"><?php _e( 'Enable', 'hootkit' ) ?></span> | 
												<span class="hk-modtype-disable"><?php _e( 'Disable', 'hootkit' ) ?></span>
												<input name="disabled[]" type="checkbox" value="<?php echo esc_attr( $modtype ) ?>" <?php if ( \in_array( $modtype, $disabled ) ) echo 'checked="checked"'; ?> />
											</div>
											<?php
											$displaysets = array();
											foreach ( $modsarray as $amod )
												if ( !empty( $modules[ $amod ]['displaysets'] ) )
													$displaysets = array_merge( $displaysets, $modules[ $amod ]['displaysets'] );
											$displaysets = array_unique( $displaysets );
											sort( $displaysets );
											if ( count( $displaysets ) > 1 ) : ?>
												<div class="hk-boxnav-filters">
													<div class="hk-boxnav-filter hk-currentfilter" data-displayset="all"><?php _e( 'View All', 'hootkit' ) ?></div>
													<?php foreach ( $displaysets as $filter ) {
														echo '<div class="hk-boxnav-filter" data-displayset="' . esc_attr( $filter ) . '">' . esc_html( hootkit()->get_string( $filter ) ) . '</div>';
													} ?>
												</div>
											<?php endif; ?>
										</div>

										<?php /* Box Modules */ ?>
										<div class="hk-box-modules">
											<div class="hk-modules-disabled"><?php esc_html_e( "Click 'Enable' on left to show available options.", 'hootkit' ); ?></div>
											<div class="hk-modules">

												<?php $this->render_options( $modtype ); ?>

											</div><!-- .hk-modules -->
										</div><!-- .hk-box-modules -->

									</div><!-- .hk-box-inner -->
								</div><!-- .hk-box --><?php

							} // endif
						} //endforeach
						?>

					</div><!-- .hootkit-container -->

					<div class="hk-actions">
						<div class="hk-gridbox">
							<div class="hk-save">
								<div id="hkfeedback" class="hkfeedback"></div>
								<a href="#" id="hk-submit" class="button button-primary hk-submit"><?php _e( 'Save Changes', 'hootkit' ); ?></a>
								<?php // submit_button( __( 'Save', 'hootkit' ) ); ?>
							</div>
						</div>
					</div>

				</form>
				<?php endif; ?>
			
			</div><!-- .hootkit-wrap -->

			<?php
		}

				/**
		 * Render Part
		 *
		 * @since  3.0.0
		 * @access public
		 * @return void
		 */
		public function render_options( $modtype, $bettertoggle=false ) {

			$modules = hootkit()->get_mfmodules();
			$premium = hootkit()->get_config( 'premium' );

			$thememods = hootkit()->get_config( 'modules' );
			$activemods = hootkit()->get_config( 'activemods' );
			$premiummods = array();
			foreach ( $premium as $pmod ) {
				if ( !empty( $modules[ $pmod ]['types'] ) )
					foreach ( $modules[ $pmod ]['types'] as $type ) {
						$premiummods[ $type ][] = $pmod;
					}
			}
			$wcinactivemods = hootkit()->get_config( 'wc-inactive' );
			// $wc = class_exists( 'WooCommerce' );

			$modsarray = isset( $thememods[ $modtype ] ) && is_array( $thememods[ $modtype ] ) ? $thememods[ $modtype ] : array();
			$bettertogglebox = $bettertoggle ? 'bettertogglebox' : 'hk-toggle-box';
			$bettertoggle = $bettertoggle ? 'bettertoggle' : 'hk-toggle';

			foreach ( $modsarray as $modslug ) {
				$widgetsc = $modslug === 'widgets-as-sc' ? true : false;
				$widgetscavail = $widgetsc ? \in_array( 'classic-widgets', $activemods[ $modtype ] ) : false;
				?>
				<div class="hk-module<?php
						if ( $widgetsc && ! $widgetscavail ) echo ' hk-mod-inactive';
						echo ' hk-mod-' . esc_attr( $modslug );
						if ( !empty( $modules[ $modslug ]['requires'] ) && \in_array( 'woocommerce', $modules[ $modslug ]['requires'] ) ) echo ' hk-wcmod';
						if ( !empty( $modules[ $modslug ]['displaysets'] ) )
							foreach ( $modules[ $modslug ]['displaysets'] as $dset )
								echo ' hk-set-' . esc_attr( $dset );
						?> hk-set-all">
					<?php if ( $widgetsc ) { echo '<div class="hk-modhover-msg">' . esc_html__( 'This module requires Classic Widgets to be active.', 'hootkit' ) . '</div>'; } ?>
					<div class="hk-mod-name"><?php
						$label = hootkit()->get_string( $modslug );
						$desc = '';
						if ( !empty( $modules[ $modslug ] ) && is_array( $modules[ $modslug ] ) ) {
							if ( !empty( $modules[ $modslug ]['desc'] ) ) $desc = $modules[ $modslug ]['desc'];
						}
						echo '<span>' . esc_html( $label ) . '</span>';
						if ( !empty( $desc ) ) :
							?><div class="hk-mod-descbox">
								<div class="hk-mod-descicon"></div>
								<div class="hk-mod-desc"><?php esc_html_e( $desc ) ?></div>
							</div><?php
						endif;
						?></div>
					<div class="<?php echo $bettertogglebox; ?>">
						<input name="<?php echo esc_attr( $modtype ) . '[]'; ?>" type="checkbox" value="<?php echo esc_attr( $modslug ) ?>" <?php if ( \in_array( $modslug, $activemods[ $modtype ] ) ) echo 'checked="checked"'; ?> <?php if ( !empty( $modules[ $modslug ]['refreshadmin'] ) ) echo 'data-refreshadmin="true"' ?> />
						<span class="<?php echo $bettertoggle; ?>"></span>
					</div>
				</div><!-- .hk-module -->
			<?php }

			foreach ( array( 'wcinactivemods', 'premiummods' ) as $inactive ) {
				$checkinactive = $$inactive;
				if ( !empty( $checkinactive[ $modtype ] ) ) { foreach ( $checkinactive[ $modtype ] as $modslug ) { ?>
				<div class="hk-module hk-mod-inactive<?php
						echo ' hk-mod-' . esc_attr( $modslug );
						if ( $inactive == 'wcinactivemods' ) echo ' hk-wcmod';
						elseif ( !empty( $modules[ $modslug ]['requires'] ) && \in_array( 'woocommerce', $modules[ $modslug ]['requires'] ) ) echo ' hk-wcmod';
						if ( !empty( $modules[ $modslug ]['displaysets'] ) )
							foreach ( $modules[ $modslug ]['displaysets'] as $dset )
								echo ' hk-set-' . esc_attr( $dset );
						?>  hk-set-all">
					<div class="hk-modhover-msg"><?php
						if ( $inactive == 'wcinactivemods' ) esc_html_e( 'This module requires WooCommerce plugin for Online Shops', 'hootkit' );
						if ( $inactive == 'premiummods' ) esc_html_e( 'Premium Theme Feature', 'hootkit' );
					?></div>
					<div class="hk-mod-name"><?php
						$label = hootkit()->get_string( $modslug );
						$desc = '';
						if ( !empty( $modules[ $modslug ] ) && is_array( $modules[ $modslug ] ) ) {
							if ( !empty( $modules[ $modslug ]['desc'] ) ) $desc = $modules[ $modslug ]['desc'];
						}
						echo '<span>' . esc_html( $label ) . '</span>';
						if ( !empty( $desc ) ) :
							?><div class="hk-mod-descbox">
								<div class="hk-mod-descicon"></div>
								<div class="hk-mod-desc"><?php esc_html_e( $desc ) ?></div>
							</div><?php
						endif;
					?></div>
					<div class="<?php echo "{$bettertogglebox} {$bettertogglebox}-inactive"; ?>"><span class="<?php echo $bettertoggle; ?>"></span></div>
				</div><!-- .hk-module -->
				<?php
				} }
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

	Settings::get_instance();

endif;