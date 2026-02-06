<?php
/**
 * Misc Functions
 * This file is loaded at plugins_loaded@5
 */

/**
 * Add placeholder shortcodes for imported content (third party plugins) displaying a helpful message
 */
if ( !function_exists( 'hootkit_helper_imported_plugin_content' ) ):
function hootkit_helper_imported_plugin_content() {
	if ( current_user_can( 'activate_plugins' ) ) {
		if ( !shortcode_exists( 'mappress' ) ) {
			add_shortcode( 'mappress', 'hootkit_mappress_placeholder' );
		}
		if ( !shortcode_exists( 'contact-form-7' ) ) {
			add_shortcode( 'contact-form-7', 'hootkit_contact_form_7_placeholder' );
		}
	}
}
function hootkit_mappress_placeholder() {
	/* Translators: 1 is link start 2 is link end */
	return '<div>[mappress]<br>' . sprintf( esc_html__( 'MapPress is not installed. Please install and activate the %1$sMapPress plugin%2$s for above shortcode to work.', 'hootkit' ), '<a href="https://wordpress.org/plugins/mappress-google-maps-for-wordpress/" target="_blank" rel="noopener noreferrer">', '</a>' ) . '</div>';
}

function hootkit_contact_form_7_placeholder() {
	return '<div>[contact-form-7]<br>' . sprintf( esc_html__( 'Contact Form 7 is not installed. Please install and activate the %1$sContact Form 7 plugin%2$s for above shortcode to work.', 'hootkit' ), '<a href="https://wordpress.org/plugins/contact-form-7/" target="_blank" rel="noopener noreferrer">', '</a>' ) . '</div>';
}
endif;
add_action( 'wp', 'hootkit_helper_imported_plugin_content', 999 ); // As late as possible
