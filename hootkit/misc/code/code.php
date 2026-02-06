<?php
/**
 * Admin Custom Code class
 * This file is loaded at after_setup_theme@96
 *
 * @since   3.0.0
 * @package Hootkit
 */

namespace HootKit\Mods;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\HootKit\Mods\Customcode' ) ) :

class Customcode {

	/**
	 * Class Instance
	 */
	private static $instance;

	/**
	 * Setting's Option Name
	 */
	public $option = 'hootkit_customcode';

	/**
	 * Initialize everything
	 */
	public function __construct() {

		add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Perform action and populate messages and admin page content */
		add_action( 'hoot_manager_code', array( $this, 'print_code' ), 10, 2 );

		/* Register Setting */
		add_action( 'admin_init', array( $this, 'register_setting' ) );

	}

	/**
	 * Register Settings
	 * @since  3.0.0
	 */
	function register_setting() {
		// Save seperately for each theme
		$this->option = hootkitcustomcode()->themeslug . '_customcode';

		register_setting( 'hootkit-code', $this->option, array( 'sanitize_callback' => array( $this, 'sanitize_setting' ) ) );
	}

	/**
	 * Sanitize Settings
	 * @since  3.0.0
	 */
	function sanitize_setting( $raw ) {
		$setting = array();
		$raw = is_array( $raw ) ? $raw : array();
		$types = array( 'customphp', 'header', 'body', 'footer' );

		// Enabled + Editor Text
		foreach ( $types as $key ) {
			$setting[ $key ] = isset( $raw[ $key ] ) && is_string( $raw[ $key ] ) ? $raw[ $key ] : '';
			$setting[ $key . '-enabled' ] = !empty( $raw[ $key . '-enabled' ] ) ? '1' : '';
		}
		$setting['customphp'] = preg_replace( '|^\s*<\?(php)?|', '', $setting['customphp'] );
		$setting['customphp'] = preg_replace( '|\?>\s*$|', '', $setting['customphp'] );

		// Scope + Options
		$setting['customphp-scope'] = isset( $raw['customphp-scope'] ) && in_array( $raw['customphp-scope'], array( 'global', 'admin', 'front' ) ) ? $raw['customphp-scope'] : 'global';
		foreach ( array( 'header', 'body', 'footer' ) as $key ) {
			$setting[ $key . '-ops' ] = array();
			foreach ( array( 'runphp', 'runformat', 'runshortcode' ) as $opkey ) {
				$setting[ $key . '-ops' ][ $opkey ] = !empty( $raw[ $key . '-ops' ] ) && is_array( $raw[ $key . '-ops' ] ) && !empty( $raw[ $key . '-ops' ][ $opkey ] ) ? '1' : '';
			}
		}

		// Validate php code
		$setting['validate'] = array();
		foreach ( $types as $key ) {
			if (
				!empty( $setting[ $key ] ) &&
				( $key === 'customphp' || !empty( $setting[ $key . '-ops' ]['runphp'] ) )
			) {
				if ( ! class_exists( 'HootCodeValidator' ) && function_exists( '\HootKit\Mods\hootkitcustomcode' ) && !empty( \HootKit\Mods\hootkitcustomcode()->dir ) ) {
					require_once \HootKit\Mods\hootkitcustomcode()->dir . 'class-validator.php';
				}
				if ( class_exists( 'HootCodeValidator' ) ) {
					$codecheck = $key === 'customphp' ? $setting[ $key ] : '?>' . $setting[ $key ] . '<?php';
					$validator = new \HootCodeValidator( $codecheck );
					$validate_error = $validator->validate();
					if ( $validate_error ) {
						$setting[ $key . '-enabled' ] = '';
						$errorline = !empty( $validate_error['line'] ) ? intval( $validate_error['line'] ) - 1 : '';
						$errormsg = !empty( $validate_error['message'] ) ? $validate_error['message'] : '';
						$setting['validate'][ $key ] = array( 'invalid' => true );
						if ( $errorline || $errormsg ) {
							$setting['validate'][ $key ]['errorline'] = $errorline;
							$setting['validate'][ $key ]['errormsg'] = $errormsg;
						}
					}
				}
			}
		}

		/*
		*/

		return $setting;
	}

	/**
	 * Enqueue Scripts
	 * @since  3.0.0
	 */
	function enqueue_scripts( $hook ) {
		if ( $hook === hootkitcustomcode()->codeplugin_data('pagehook') ) {
			// Enqueue CodeMirror scripts
			// @ref. https://developer.wordpress.org/reference/functions/wp_enqueue_code_editor/
			$codemirror = wp_enqueue_code_editor(['type' => 'application/x-httpd-php']);
			$codemirroropen = wp_enqueue_code_editor(['type' => 'application/x-httpd-php-open']);

			if ( $codemirror && is_array( $codemirror ) ) {
				// Enqueue WP's script to initialize CodeMirror
				wp_enqueue_script('wp-theme-plugin-editor');
				// Enqueue WP CodeMirror CSS (optional, but recommended)
				wp_enqueue_style('wp-codemirror');

				// Linting
				if ( $codemirroropen && is_array( $codemirroropen ) && !empty( $codemirroropen['codemirror'] ) && is_array( $codemirroropen['codemirror'] ) ) {
					$codemirroropen['codemirror']['lint'] = true;
				}
				if ( $codemirror && is_array( $codemirror ) && !empty( $codemirror['codemirror'] ) && is_array( $codemirror['codemirror'] ) ) {
					$codemirror['codemirror']['lint'] = false;
				}

				// Localize script to pass CodeMirror settings
				$handle = hootkit()->slug . '-code';
				wp_localize_script( $handle, 'hootCodeMirrorSettings', $codemirror );
				wp_localize_script( $handle, 'hootCodeMirrorSettingsOpen', $codemirroropen );
			}

		}
	}

	/**
	 * Print Theme Manager Page Content
	 * @since  3.0.0
	 */
	function print_code( $sanetags, $codeplugin ) {
		$supports = array(
			'customphp' => __( 'Custom PHP', 'hootkit' ),
			'header'    => __( 'Header Code', 'hootkit' ),
			'body'      => __( 'Body Code', 'hootkit' ),
			'footer'    => __( 'Footer Code', 'hootkit' ),
		);

		$activetab = !empty( $_GET['codetab'] ) && array_key_exists( $_GET['codetab'], $supports ) ? $_GET['codetab'] : array_key_first( $supports );
		?><form method="post" action="options.php">
			<?php settings_fields( 'hootkit-code' ); ?>

			<div class="hootabt-notice hootabt-notice--tip hoot-codetab-toggle">
				<div class="hoot-codetab-togglehead"><?php
					/* Translators: The %s are placeholders for HTML, so the order can't be changed. */
					printf( esc_html__( '%3$s %1$sIMPORTANT%2$s - Read before adding PHP Code %4$s', 'hootkit' ), '<strong>', '</strong>', '<span class="dashicons dashicons-flag"></span>', '<span class="dashicons dashicons-arrow-down"></span>' );
				?></div>
				<div class="hoot-codetab-togglebox"><?php
					echo '<strong>' . esc_html__( '> Only add trusted code:', 'hootkit' ) . '</strong>';
					echo '<br />';
					echo esc_html__( 'While powerful, adding PHP code comes with a risk: a faulty snippet can break your site and make WordPress admin inaccessible.', 'hootkit' );
					echo '<div class="hrdivider"></div>';

					echo '<strong>' . esc_html__( '> If your custom code broke your site and you can\'t access your site:', 'hootkit' ) . '</strong>';
					echo '<br />';
					/* Translators: The %s are placeholders for HTML, so the order can't be changed. */
					echo esc_html__( 'Visit the Disable Custom Code link below - this will turn off all custom code you added.', 'hootkit' );
					echo '<pre>' . esc_url( add_query_arg( 'hootcmd', 'disablecustomcodes', admin_url() ) ) . '</pre>';
					echo '<div class="hrdivider"></div>';

					echo '<strong>' . esc_html__( '> If your custom code broke your site and you can\'t log in:', 'hootkit' ) . '</strong>';
					echo '<br />';
					/* Translators: The %s are placeholders for HTML, so the order can't be changed. */
					printf( esc_html__( 'Follow the %1$sdocumentation%2$s to temporarily switch on the Safe mode. Then use the Disable link to turn off the Custom Code.', 'hootkit' ), '<a href="https://wphoot.com/support/custom-code-troubleshooting/" target="_blank">', '</a>' );
					?>
				</div>
			</div>

			<div id="hoot-codetabs" class="hoot-codetabs">
				<?php foreach ( $supports as $tab => $label ) { ?>
					<div class="hoot-codetab <?php if ( $tab === $activetab ) echo 'hootactive'; ?>" data-codeid="<?php echo esc_attr( $tab ); ?>"><?php echo esc_html( $label ); ?></div>
				<?php } ?>
				<div class="hoot-codesubmit"><?php submit_button( esc_html__( 'Save Code', 'hootkit' ) ); ?></div>
			</div>

			<?php
			$customcode = get_option( $this->option, array() );
			foreach ( $supports as $tab => $label ) {
				$tab = esc_attr( $tab );
				$blockid = 'hoot-codeblock-' . $tab;
				$editorid = 'hoot-code-' . $tab;
				$enablename = $this->option . '[' . $tab . '-enabled]';
				$editorname = $this->option . '[' . $tab . ']';

				$enablevalue = !empty( $customcode[ $tab . '-enabled' ] ) ? true : false;
				$editorvalue = isset( $customcode[ $tab ] ) ? $customcode[ $tab ] : '';
				$validate = !empty( $customcode['validate'] ) && is_array( $customcode['validate'] ) && !empty( $customcode['validate'][ $tab ] ) && is_array( $customcode['validate'][ $tab ] ) ? $customcode['validate'][ $tab ] : array();

				$phpscope = isset( $customcode['customphp-scope'] ) && in_array( $customcode['customphp-scope'], array( 'global', 'admin', 'front' ) ) ? $customcode['customphp-scope'] : 'front';
				$ops = isset( $customcode[ $tab . '-ops' ] ) && is_array( $customcode[ $tab . '-ops' ] ) ? $customcode[ $tab . '-ops' ] : array();
				?>
					<div id="<?php echo $blockid; ?>" class="hoot-codeblock <?php if ( $tab === $activetab ) echo 'hootactive'; ?>">

						<?php $blockdesc = '';
						if ( $tab === 'customphp' )
							$blockdesc = sprintf( esc_html__( 'This is the same as adding PHP code in theme\'s %3$s%1$sfunction.php%2$s%4$s file', 'hootkit' ), '<code>', '</code>', '<strong>', '</strong>' );
						elseif ( $tab === 'header' )
							$blockdesc = sprintf( esc_html__( 'This will be printed in the %3$s%1$s<head>%2$s%4$s section in the frontend. Add code like %3$sGoogle Analytics%4$s or %3$smeta tags%4$s here.', 'hootkit' ), '<code>', '</code>', '<strong>', '</strong>' );
						elseif ( $tab === 'body' )
							$blockdesc = sprintf( esc_html__( 'This will be printed just below the opening %3$s%1$s<body>%2$s%4$s tag in the frontend.', 'hootkit' ), '<code>', '</code>', '<strong>', '</strong>' );
						elseif ( $tab === 'footer' )
							$blockdesc = sprintf( esc_html__( 'This will be printed just above the closing %3$s%1$s</body>%2$s%4$s tag in the frontend.', 'hootkit' ), '<code>', '</code>', '<strong>', '</strong>' );
						if ( $blockdesc )
							echo '<div class="hoot-codeblock-sub hoot-codeblock-desc">' . $blockdesc . '</div>';
						?>

						<?php if ( !empty( $validate['invalid'] ) ): ?>
							<div class="hootabt-notice hootabt-notice--error"><?php
								if ( $tab === 'customphp' ) {
									printf( esc_html__( '%1$sPHP Code Validation Failed.%2$s%3$sIt looks like your PHP code contains errors.%3$sPlease fix the issues and save again.', 'hootkit' ), '<strong>', '</strong>','<br>' );
								} else {
									printf( esc_html__( '%1$sPHP Code Validation Failed.%2$s%3$sIt looks like you are running PHP code in your %4$s code.%3$sPlease fix the issues and save again, or uncheck the %1$s"Evaluate php code within"%2$s option below.', 'hootkit' ), '<strong>', '</strong>','<br>', ucfirst( $tab ) );
								}
								if ( !empty( $validate['errorline'] ) && !empty( $validate['errormsg'] ) ) {
									echo '<div style="margin:15px 0 10px"><code>' . sprintf( esc_html__( 'Error on line %1$s: %2$s', 'hootkit' ), $validate['errorline'], $validate['errormsg'] ) . '</code></div>';
								}
								printf( esc_html__( 'Note: Your custom code below is set to %1$sINACTIVE%2$s until the errors are fixed.', 'hootkit' ), '<strong>', '</strong>','<br>' );
							?></div>
						<?php endif; ?>

						<div class="hoot-codeblock-enable">
							<div class="bettertogglebox">
								<input type="checkbox" value="1" name="<?php echo $enablename; ?>" <?php checked( $enablevalue ); ?> />
								<span class="bettertoggle"></span>
							</div>
							<label><?php echo esc_html( __( 'Enable', 'hootkit' ) . ' ' . $label ); ?></label>
						</div>

						<div class="hoot-codeblock-sub hoot-codeblock-ops">
							<?php /*
							*/
							if ( $tab === 'customphp' ): ?>
								<label>
									<input type="radio" name="<?php echo esc_attr( "{$this->option}[customphp-scope]" ); ?>" value="global" <?php checked( 'global', $phpscope ); ?>>
									<span class="dashicons dashicons-admin-site"></span> <?php echo esc_html__( 'Run snippet everywhere', 'hootkit' ); ?>
								</label>
								<label>
									<input type="radio" name="<?php echo esc_attr( "{$this->option}[customphp-scope]" ); ?>" value="admin" <?php checked( 'admin', $phpscope ); ?>>
									<span class="dashicons dashicons-admin-tools"></span> <?php echo esc_html__( 'Only run in administration area', 'hootkit' ); ?>
								</label>
								<label>
									<input type="radio" name="<?php echo esc_attr( "{$this->option}[customphp-scope]" ); ?>" value="front" <?php checked( 'front', $phpscope ); ?>>
									<span class="dashicons dashicons-admin-appearance"></span> <?php echo esc_html__( 'Only run on site front-end', 'hootkit' ); ?>
								</label>
							<?php elseif ( $tab === 'header' ): ?>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( "{$this->option}[header-ops][runphp]" ); ?>" value="1" <?php $op = !empty( $ops['runphp'] ); checked( $op ); ?>>
									<?php echo esc_html__( 'Evaluate php code within', 'hootkit' ); ?>
								</label>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( "{$this->option}[header-ops][runshortcode]" ); ?>" value="1" <?php $op = !empty( $ops['runshortcode'] ); checked( $op ); ?>>
									<?php echo esc_html__( 'Run shortcodes within code', 'hootkit' ); ?>
								</label>
							<?php elseif ( $tab === 'body' ): ?>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( "{$this->option}[body-ops][runphp]" ); ?>" value="1" <?php $op = !empty( $ops['runphp'] ); checked( $op ); ?>>
									<?php echo esc_html__( 'Evaluate php code within', 'hootkit' ); ?>
								</label>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( "{$this->option}[body-ops][runformat]" ); ?>" value="1" <?php $op = !empty( $ops['runformat'] ); checked( $op ); ?>>
									<?php echo esc_html__( 'Apply auto paragraphs and formatting', 'hootkit' ); ?>
								</label>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( "{$this->option}[body-ops][runshortcode]" ); ?>" value="1" <?php $op = !empty( $ops['runshortcode'] ); checked( $op ); ?>>
									<?php echo esc_html__( 'Run shortcodes within code', 'hootkit' ); ?>
								</label>
							<?php elseif ( $tab === 'footer' ): ?>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( "{$this->option}[footer-ops][runphp]" ); ?>" value="1" <?php $op = !empty( $ops['runphp'] ); checked( $op ); ?>>
									<?php echo esc_html__( 'Evaluate php code within', 'hootkit' ); ?>
								</label>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( "{$this->option}[footer-ops][runformat]" ); ?>" value="1" <?php $op = !empty( $ops['runformat'] ); checked( $op ); ?>>
									<?php echo esc_html__( 'Apply auto paragraphs and formatting', 'hootkit' ); ?>
								</label>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( "{$this->option}[footer-ops][runshortcode]" ); ?>" value="1" <?php $op = !empty( $ops['runshortcode'] ); checked( $op ); ?>>
									<?php echo esc_html__( 'Run shortcodes within code', 'hootkit' ); ?>
								</label>
							<?php endif; ?>
						</div>

						<div class="hoot-codeblock-editor">
							<textarea id="<?php echo $editorid; ?>" name="<?php echo $editorname; ?>" rows="20" class="hoot-codeeditor"><?php echo esc_textarea( $editorvalue ); ?></textarea>
						</div>

					</div>
				<?php
			}
			?>

		</form><?php
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

Customcode::get_instance();

endif;