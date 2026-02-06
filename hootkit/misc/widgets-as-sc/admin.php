<?php
/**
 * Widgets As Shortcodes
 * This file is loaded at after_setup_theme@96 via class-miscmods
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$hootkitmiscmodsactive = hootkit()->get_config( 'activemods', 'misc' );
if ( ! is_array( $hootkitmiscmodsactive ) && !in_array( 'classic-widgets', $hootkitmiscmodsactive ) ) {
	return;
}

add_action( 'widgets_init', 'hootkit_register_sc_sidebar' );
if ( ! function_exists('hootkit_register_sc_sidebar' ) ) :
function hootkit_register_sc_sidebar(){
	if ( ! function_exists('hoot_register_sidebar' ) )
		return;
	$linkstart = function_exists( 'hootkit_theme_abouttags' ) ? hootkit_theme_abouttags('urldocs') : '';
	$linkstart = esc_url($linkstart);
	$linkend = '';
	if ( ! empty( $linkstart ) ) {
		$linkstart = '<a href="' . esc_url($linkstart) . '#docs-section-widgets_as_sc" target="_blank">';
		$linkend = '</a>';
	}
	hoot_register_sidebar(
		array(
			'id'           => 'hootkit-widgets-sc',
			'name'         => _x( 'HootKit - Widget Shortcodes', 'sidebar', 'hootkit' ),
			/* Translators: The %s are HTML links, so the order can't be changed. */
			'description'  => sprintf( esc_html__( 'Add widgets here to %1$suse them anywhere (pages, posts etc) via shortcodes.%2$s', 'hootkit' ), $linkstart, $linkend),
		)
	);
}
endif;

add_shortcode( 'hootkitwidget', 'hootkit_widget_sc' );
function hootkit_widget_sc( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'id' => '',
	), $atts );

	global $wp_registered_widgets, $wp_registered_sidebars;
	$widget_id = $atts['id'];
	if ( empty( $widget_id ) || ! isset( $wp_registered_widgets[ $widget_id ] ) ) {
		return '';
	}

	$widget_data = $wp_registered_widgets[ $widget_id ];
	$sidebar_args = array(
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	);
	foreach ( wp_get_sidebars_widgets() as $sidebar_id => $widgets ) {
		if ( is_array( $widgets ) && in_array( $widget_id, $widgets, true ) ) {
			if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
				$sidebar_args = $wp_registered_sidebars[ $sidebar_id ];
			}
			break;
		}
	}

	$callback = $widget_data['callback'];
	if ( ! is_array( $callback ) || ! is_object( $callback[0] ) ) {
		return '';
	}

	$widget_obj   = $callback[0];
	$widget_class = get_class( $widget_obj );
	$id_base      = $widget_obj->id_base;
	$html_class   = 'widget_' . $id_base;
	$settings     = get_option( 'widget_' . $id_base );

	// Get the widget number
	$parts = explode( '-', $widget_id );
	$number = end( $parts );
	if ( empty( $settings[$number] ) ) {
		return '';
	}

	$instance = $settings[$number];
	$args = array(
		'widget_id'   => $widget_id,
		'widget_name' => $widget_obj->name,
		'before_widget' => sprintf(
			$sidebar_args['before_widget'], 
			$widget_id, 
			$html_class
		),
		'after_widget'  => $sidebar_args['after_widget'],
		'before_title'  => $sidebar_args['before_title'],
		'after_title'   => $sidebar_args['after_title'],
	);

	ob_start();
	the_widget( $widget_class, $instance, $args );
	return ob_get_clean();
}
