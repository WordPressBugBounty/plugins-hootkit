<?php
/**
 * Icon List Widget
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class HootKit_Icon_List_Widget
 */
if (
	file_exists(  hootkit()->dir . 'widgets/icon-list/admin.php' )
) {
	require_once( hootkit()->dir . 'widgets/icon-list/admin.php' );
}

/**
 * Update options
 */
function hootkit_icon_list_widget_v2_settings( $settings ) {
	$fops = array();
	foreach ( $settings['form_options'] as $key => $ops ) {
		if ( $key === 'iconcolor' ) {
			$fops['bgcolor'] = array(
				'name'		=> __( 'Background Color (optional)', 'hootkit' ),
				'type'		=> 'color',
			);
		} elseif ( $key === 'items' ) {
			$fops['items'] = $ops;
			$fops['items']['fields']['tcolor'] = array(
				'name'		=> __( 'Text Color (optional)', 'hootkit' ),
				'type'		=> 'color',
			);
			$fops['items']['fields']['icolor'] = array(
				'name'		=> __( 'Icon Color (optional)', 'hootkit' ),
				'type'		=> 'color',
			);
		} else {
			$fops[$key] = $ops;
		}
	}
	$settings['form_options'] = $fops;
	return $settings;
}
add_filter( 'hootkit_icon_list_widget_settings', 'hootkit_icon_list_widget_v2_settings', 5 );
