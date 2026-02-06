<?php
/**
 * Run Custom Code class
 * This file is loaded at plugins_loaded@5
 *
 * @since   3.0.0
 * @package Hootkit
 */

namespace HootKit\Mods;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Mods\CustomcodeRun' ) ) :

	class CustomcodeRun {

		private $code = array();

		/**
		 * Initialize everything
		 */
		public function __construct( $codedir = '', $themeslug = '' ) {
			if ( empty( $codedir ) || empty( $themeslug ) )
				return;

			// Dont run code if this is a save
			if ( is_admin() && isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['option_page'] ) && $_POST['option_page'] === 'hootkit-code' ) {
				return;
			}

			// Get Code
			$this->code = get_option( $themeslug . '_customcode', array() );
			if ( ! is_array( $this->code ) ) {
				$this->code = array();
			}

			// Troubleshoot Broken Code
			add_action( 'init', array( $this, 'troubleshoot' ), 10 );

			// Run Code ( hooked @plugins_loaded )
			$debugmode = file_exists( $codedir . 'debug-enable.php' );
			$safemode = ! get_option( 'hootkit_customcode_disablesafemode' );
			if ( ! $debugmode ) { // dont run at all if debugmode
				if ( ! $safemode ) { // always run if not safemode
					$this->runcode();
				} elseif ( empty( $_GET['hootcmd'] ) || $_GET['hootcmd'] !== 'disablecustomcodes' ) { // else run only if cmd!==disable (default)
					$this->runcode();
				}
			}

		}

		/**
		 * Troubleshoot Broken Code
		 * @since  3.0.0
		 */
		function troubleshoot() {
			// no need to run if cmd is not set
			if ( empty( $_GET['hootcmd'] ) || $_GET['hootcmd'] !== 'disablecustomcodes' ) {
				return;
			}
			// no need to check if debug mode is on
			$debugmode = file_exists( hootkitcustomcode()->dir . 'debug-enable.php' );
			$usercheck = $debugmode ? true : ( is_admin() && current_user_can( 'manage_options' ) );
			// switch it off
			if ( $usercheck ) {
				foreach ( $this->code as $key => $value ) {
					if ( substr( $key, -8 ) === '-enabled' ) {
						$this->code[ $key ] = '';
					}
				}
				update_option( hootkitcustomcode()->themeslug . '_customcode', $this->code );
				if ( is_admin() ) {
					$codeplugin = hootkit()->supports( 'code', true );
					if ( is_array( $codeplugin ) && !empty( $codeplugin['dashurl'] ) ) {
						wp_safe_redirect( $codeplugin['dashurl'] );
						exit;
					}
				}
			}
		}

		/**
		 * Run Code
		 * @since  3.0.0
		 */
		function runcode() {

			// PHP
			if (
				!empty( $this->code['customphp-enabled'] )
				&& empty( $this->code['customphp-error'] )
				&& !empty( $this->code['customphp'] ) && is_string( $this->code['customphp'] )
			) {
				$scope = !empty( $this->code['customphp-scope'] ) && in_array( $this->code['customphp-scope'], array( 'global', 'admin', 'front' ) ) ? $this->code['customphp-scope'] : 'global';
				if (
					$scope === 'global' ||
					( $scope === 'admin' && is_admin() ) ||
					( $scope === 'front' && !is_admin() )
				) {
					// We do not display/print/echo anything as custom PHP code should not be doing that here (running during plugins_loaded@5 hook)
					$result = $this->runphp();
				}
			}

			// Header
			if ( !empty( $this->code['header-enabled'] ) && !empty( $this->code['header'] ) && is_string( $this->code['header'] ) ) {
				$priority = 10;
				$upriority = apply_filters( 'hoot_manager_customcode_header_priority', $priority );
				$priority = is_int( $upriority ) ? $upriority : $priority;
				add_action( 'wp_head', array( $this, 'runheader' ), $priority );
			}

			// Body
			if ( !empty( $this->code['body-enabled'] ) && !empty( $this->code['body'] ) && is_string( $this->code['body'] ) ) {
				$priority = -1;
				$upriority = apply_filters( 'hoot_manager_customcode_body_priority', $priority );
				$priority = is_int( $upriority ) ? $upriority : $priority;
				add_action( 'wp_body_open', array( $this, 'runbody' ), $priority );
			}

			// Footer
			if ( !empty( $this->code['footer-enabled'] ) && !empty( $this->code['footer'] ) && is_string( $this->code['footer'] ) ) {
				$priority = 99999;
				$upriority = apply_filters( 'hoot_manager_customcode_footer_priority', $priority );
				$priority = is_int( $upriority ) ? $upriority : $priority;
				add_action( 'wp_footer', array( $this, 'runfooter' ), $priority );
			}

		}

		/**
		 * Run Code
		 * Code must NOT be escaped, as it will be executed directly.
		 * @since  3.0.0
		 */
		function runheader() {
			if ( empty( $this->code['header'] ) || ! is_string( $this->code['header'] ) )
				return;
			$ops = !empty( $this->code['header-ops'] ) && is_array( $this->code['header-ops'] ) ? $this->code['header-ops'] : array();
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "\n" . $this->processcontent( $this->code['header'], $ops ) . "\n";
		}
		function runbody() {
			if ( empty( $this->code['body'] ) || ! is_string( $this->code['body'] ) )
				return;
			$ops = !empty( $this->code['body-ops'] ) && is_array( $this->code['body-ops'] ) ? $this->code['body-ops'] : array();
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "\n" . $this->processcontent( $this->code['body'], $ops ) . "\n";
		}
		function runfooter() {
			if ( empty( $this->code['footer'] ) || ! is_string( $this->code['footer'] ) )
				return;
			$ops = !empty( $this->code['footer-ops'] ) && is_array( $this->code['footer-ops'] ) ? $this->code['footer-ops'] : array();
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "\n" . $this->processcontent( $this->code['footer'], $ops ) . "\n";
		}

		/**
		 * Process Content
		 * Code must NOT be escaped, as it will be executed directly.
		 * @since  3.0.0
		 * @return string processed content
		 */
		function processcontent( $content, $ops ){
			if ( ! is_array( $ops ) )
				return $content;

			if ( !empty( $ops['runphp'] ) ) {
				$runphp = $this->runphp( $content );
				if ( is_array( $runphp ) && !empty( $runphp['output'] ) ) {
					$content = $runphp['output'];
				}
			}

			if ( !empty( $ops['runformat'] ) ) {
				$functions = [ 'wptexturize', 'convert_smilies', 'convert_chars', 'wpautop', 'capital_P_dangit' ];
				foreach ( $functions as $function ) {
					$content = call_user_func( $function, $content );
				}
			}

			if ( !empty( $ops['runshortcode'] ) ) {
				$content = do_shortcode( $content );
			}

			return ( !empty( $content ) && is_string( $content ) ) ? $content : '';
		}

		/**
		 * Run Code
		 * Code must NOT be escaped, as it will be executed directly.
		 * @since  3.0.0
		 * @return bool|array false on fail ; result of eval (error/result/output)
		 */
		function runphp( $content = false ) {
			$runcode = $content ? '?>' . $content . '<?php' : ( !empty( $this->code['customphp'] ) && is_string( $this->code['customphp'] ) ? $this->code['customphp'] : false );
			if ( ! $runcode )
				return false;

			// detection checks
			if ( preg_match_all( '/(base64_decode|error_reporting|ini_set|eval)\s*\(/i', $runcode, $matches ) ) {
				if ( count( $matches[0] ) > 2 ) {
					return false;
				}
			}
			if ( preg_match( '/dns_get_record/i', $runcode ) ) {
				return false;
			}

			// run code
			$result = false;
			$error = false;
			ob_start();
			try {
				$result = eval( $runcode ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
			} catch ( ParseError $parse_error ) {
				$error = $parse_error;
			}
			$output = ob_get_clean();
			do_action( 'hoot_manager_customcode_phprun', $content, $this->code['customphp'], $result, $error, $output );

			// Return Output
			return array(
				'result' => $result,
				'output' => $output,
				'error' => $error instanceof ParseError ? array(
								ucfirst( rtrim( $error->getMessage(), '.' ) ) . '.',
								$error->getLine(),
							) : false,
			);
		}

	}

endif;