<?php
/**
 * Products Cart Icon Widget
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class HootKit_Products_Carticon_Widget
 */
if ( file_exists(  hootkit()->dir . 'widgets/products-carticon/admin.php' ) ) {
	require_once( hootkit()->dir . 'widgets/products-carticon/admin.php' );
}

/**
 * Update view template
 */
function hootkit_products_carticon_v2_template( $widget_template, $name ) {
	// Only update the default template if theme has not already set its own template via hoot_get_widget
	if ( 'products-carticon' === $name && empty( $widget_template ) ) {
		$widget_template = hootkit()->dir . 'widgets-v2/products-carticon/view.php';
	}
	return $widget_template;
}
add_filter( 'hootkit_widget_template', 'hootkit_products_carticon_v2_template', 5, 2 );
