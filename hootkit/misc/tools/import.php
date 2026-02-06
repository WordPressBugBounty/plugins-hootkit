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

if ( ! class_exists( '\HootKit\Mods\Import' ) ) :

	class Import {

		/**
		 * Class Instance
		 */
		private static $instance;

		/**
		 * Initialize everything
		 */
		public function __construct() {
			add_action( 'hoot_manager_toolsimport_notice', array( $this, 'do_import' ), 5, 4 );
			add_action( 'hoot_manager_toolsimport', array( $this, 'print_import' ), 10, 2 );
		}

		/**
		 * Do Import and add messages
		 * @since  3.0.0
		 */
		function do_import( $blockid, $block, $sanetags, $tabid ) {

			if ( isset( $_POST['hoot-import-mod'] ) && !empty( $_POST['hoot-import-mod'] ) && is_string( $_POST['hoot-import-mod'] ) ) :
				if ( ! isset( $_POST['hoot_import_mod_nonce'] ) 
					|| ! wp_verify_nonce( $_POST['hoot_import_mod_nonce'], 'hoot_import_mod_action' ) 
				) {
					?><p class="hootabt-notice hootabt-notice--error"><span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Something went wrong. Customizer settings were not imported. Please try again later.', 'hootkit' ); ?></p><?php
					return;
				}
				$json_data = wp_unslash( $_POST['hoot-import-mod'] );
				$mods = json_decode( $json_data, true );
				if ( json_last_error() === JSON_ERROR_NONE && is_array( $mods ) ){
					remove_theme_mods();
					$done = 0;
					foreach ( $mods as $key => $value ) {
						$key = sanitize_key( $key );
						if ( $key !== 'sidebars_widgets' ) {
							set_theme_mod( $key, $value );
							$done++;
						}
					}
					?><p class="hootabt-notice hootabt-notice--success"><span class="dashicons dashicons-yes"></span> <?php printf( esc_html__( '%s Customizer Settings imported successfully.', 'hootkit' ), $done ); ?></p><?php
				} else {
					?><p class="hootabt-notice hootabt-notice--error"><span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Something went wrong. Customizer settings were not imported. Please try again later.', 'hootkit' ); ?></p><?php
				}
			endif;

		}

		/**
		 * Print Theme Manager Page Content
		 * @since  3.0.0
		 */
		function print_import( $sanetags, $toolsplugin ) {
			?>
				<form action="<?php echo esc_url( $toolsplugin['dashurl'] ); ?>" method="post">
					<?php wp_nonce_field( 'hoot_import_mod_action', 'hoot_import_mod_nonce' ); ?>
					<textarea id="hoot-import-mod" name="hoot-import-mod" rows="6"></textarea>
					<br />
					<input class="button button-primary hoot-import-mod-button" type="submit" value="<?php _e( 'Import Settings', 'hootkit' ); ?>" />
				</form>
			<?php
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

	Import::get_instance();

endif;