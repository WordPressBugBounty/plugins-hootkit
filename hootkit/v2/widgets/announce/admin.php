<?php
/**
 * Announce Widget
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class HootKit_Announce_Widget
 */
if (
	file_exists(  hootkit()->dir . 'widgets/announce/admin.php' )
) {
	require_once( hootkit()->dir . 'widgets/announce/admin.php' );
}

/**
 * Update view template
 */
function hootkit_announce_v2_template( $widget_template, $name ) {
	// Only update the default template if theme has not already set its own template via hoot_get_widget
	if ( 'announce' === $name && empty( $widget_template ) ) {
		$widget_template = hootkit()->dir . 'v2/widgets/announce/view.php';
	}
	return $widget_template;
}
add_filter( 'hootkit_widget_template', 'hootkit_announce_v2_template', 5, 2 );
