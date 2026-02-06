<?php
/**
 * Miscellaneous template and utility helper functions
 * This file is loaded at after_setup_theme@96
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Array Walk/Map function
 *
 * @since 1.0.13
 * @access public
 * @param string $s
 * @return array
 */
if ( !function_exists( 'hootkit_append_negative' ) ):
function hootkit_append_negative( $s ) {
	if ( is_string( $s ) ) return '-' . $s;
	else return '';
}
endif;

/**
 * Utility function to extract border class for widget based on user option.
 *
 * @since 1.0.0
 * @access public
 * @param string $val string value separated by spaces
 * @param int $index index for value to extract from $val
 * @prefix string $prefix prefixer for css class to return
 * @return  void
 */
if ( !function_exists( 'hootkit_widget_borderclass' ) ):
function hootkit_widget_borderclass( $val, $index=0, $prefix='' ) {
	$val = explode( " ", trim( $val ) );
	if ( isset( $val[ $index ] ) )
		return $prefix . trim( $val[ $index ] );
	else
		return '';
}
endif;

/**
 * Utility function to create style string attribute.
 *
 * @since 1.0.0
 * @access public
 * @param string $mt margin top
 * @param string $mb margin bottom
 * @return string
 */
if ( !function_exists( 'hootkit_widget_marginstyle' ) ):
function hootkit_widget_marginstyle( $mt='', $mb='' ) {
	$return = '';
	if ( $mt===0 || $mt==='0' ) {
		$return .= " margin-top:0px;";
	} else {
		$margin = intval( $mt );
		if ( !empty( $margin ) ) $return .= " margin-top:{$margin}px;";
	}
	if ( $mb===0 || $mb==='0' ) {
		$return .= " margin-bottom:0px;";
	} else {
		$margin = intval( $mb );
		if ( !empty( $margin ) ) $return .= " margin-bottom:{$margin}px;";
	}
	if ( !empty( $return ) ) $return = ' style="'.$return.'" ';
	return $return;
}
endif;

/**
 * Add custom widget css class and styles
 *
 * @since 1.0.0
 * @access public
 * @param array $string
 * @param array $instance
 * @return string
 */
if ( !function_exists( 'hootkit_add_widgetstyle' ) ):
function hootkit_add_widgetstyle( $string, $instance ) {

	if ( !empty( $instance ) && !empty( $instance['customcss'] ) ) {
		$customcss = $instance['customcss'];
		$newstring = 'class="widget';
		$newstring .= ( !empty( $customcss['class'] ) ) ? ' ' . hoot_sanitize_html_classes( $customcss['class'] ) . ' ' : '';
		$mt = ( !isset( $customcss['mt'] ) ) ? '' : $customcss['mt'];
		$mb = ( !isset( $customcss['mb'] ) ) ? '' : $customcss['mb'];
		$newstring = hootkit_widget_marginstyle( $mt, $mb ) . $newstring;
		return str_replace( 'class="widget', $newstring, $string );
	}

	return $string;
}
endif;
add_filter( 'hootkit_before_widget', 'hootkit_add_widgetstyle', 10, 2 );

/**
 * Return Skype contact button code
 * Ref: https://www.skype.com/en/developer/create-contactme-buttons/
 *
 * @since 1.0.0
 * @access public
 * @param string $username Skype Username to create the Skype button
 * @return void
 */
if ( !function_exists( 'hootkit_get_skype_button' ) ) :
function hootkit_get_skype_button( $username ) {
	static $script = false;
	static $id = 1;
	$code = '';
	$action = apply_filters( 'hootkit_skype_button_action', 'call' );

	if ( !$script )
		$code .= '<script type="text/javascript"' .
				 ' src="' . esc_url('https://secure.skypeassets.com/i/scom/js/skype-uri.js') . '"'.
				 '></script>';

	$code .= '<div id="SkypeButton_Call_' . esc_attr( $username ) . '_' . $id . '" class="hoot-skype-call-button">';
	$code .= '<script type="text/javascript">';
	$code .=  'Skype.ui({'
			. '"name": "' . esc_attr( $action ) . '",' // dropdown (doesnt work well), call, chat
			. '"element": "SkypeButton_Call_' . esc_attr( $username ) . '_' . $id . '",'
			. '"participants": ["' . esc_attr( $username ) . '"],'
			//. '"imageColor": "white",' // omit for blue
			. '"imageSize": 24' // 10, 12, 14, 16 (omit), 24, 32
			. '});';
	$code .= '</script>';
	$code .= '</div>';

	$code = apply_filters( 'hootkit_get_skype_button', $code, $script, $id, $action );
	$script = true;
	$id++;
	return $code;
}
endif;

/**
 * Display the meta information HTML for single post/page
 *
 * @since 1.0.0
 * @access public
 * @param array $args
 * @return void
 */
if ( !function_exists( 'hootkit_display_meta_info' ) ):
function hootkit_display_meta_info( $args = array() ) {

	$args = wp_parse_args( apply_filters( 'hootkit_display_meta_info_args', $args ), array(
		'display'       => array( 'author', 'date', 'cats', 'tags', 'comments' ),
		'context'       => '',
		'editlink'      => true,
		'wrapper'       => '',
		'wrapper_id'    => '',
		'wrapper_class' => '',
		'empty'         => '<div class="entry-byline empty"></div>',
	) );
	extract( $args, EXTR_SKIP );

	$wrapper = preg_replace( '/[^a-z]/i', '', $wrapper ); // keeps letters only - remove everything else
	$wrapperend = "</{$wrapper}>";
	if ( !empty( $wrapper ) ) {
		$wrapper_id = ( !empty( $wrapper_id ) ) ? ' id="' . hoot_sanitize_html_classes( $wrapper_id ) . '"' : '';
		$wrapper_class = ( !empty( $wrapper_class ) ) ? ' class="' . hoot_sanitize_html_classes( $wrapper_class ) . '"' : '';
		$wrapper = "<{$wrapper}{$wrapper_id}{$wrapper_class}>";
	}

	// Hoot Framework >=v3.0.1
	if ( hoot_meta_info( $display, $context, true ) ) {
		echo $wrapper;
		hoot_display_meta_info( $display, $context, $editlink );
		echo $wrapperend;
	}

}
endif;

/**
 * Social Icons Widget - Icons
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @param string $context
 * @return array
 */
if ( !function_exists( 'hootkit_attr_social_icons_icon' ) ):
function hootkit_attr_social_icons_icon( $attr, $context ) {
	$attr['class'] = ( empty( $attr['class'] ) ) ? '' : $attr['class'];

	$attr['class'] .= ' social-icons-icon';
	if (
		( is_string( $context ) && $context != 'fa-envelope' ) ||
		( is_array( $context ) && !empty( $context['icon'] ) && $context['icon'] != 'fa-envelope' )
	)
		$attr['target'] = '_blank';

	return $attr;
}
endif;
add_filter( 'hoot_attr_social-icons-icon', 'hootkit_attr_social_icons_icon', 10, 2 );

/**
 * Skip slider image from Jetpack's Lazy Load
 * Alternately, we can also add css class 'skip-lazy' to the images
 *
 * @since 1.0.0
 * @access public
 * @param array $attr
 * @param string $context
 * @return array
 */
if ( !function_exists( 'hootkit_jetpack_lazy_load_exclude' ) ):
function hootkit_jetpack_lazy_load_exclude( $classes ) {
	if ( !is_array( $classes ) ) $classes = array();
	$classes[] = 'hootkitslide-img';
	$classes[] = 'hootkitcarousel-img';
	return $classes;
}
endif;

/**
 * Common template function to display view all link in widgets
 *
 * @since 1.1.0
 * @access public
 * @return string
 */
if ( !function_exists( 'hootkit_get_viewall' ) ):
function hootkit_get_viewall( $echo = false, $post_type = 'post' ) {
	global $hoot_data;
	$html = '';
	if ( !empty( $hoot_data->currentwidget['instance'] ) )
		extract( $hoot_data->currentwidget['instance'], EXTR_SKIP );
	if ( !empty( $viewall ) ) {
		switch ( $post_type ) {
			case 'product':
				$base_url = '';
				if ( !empty( $category ) && is_array( $category ) && count( $category ) == 1 ) { // If more than 1 cat selected, show shop url
					$category[0] = (int)$category[0]; // convert string to integer else get_term_link gives error
					$base_url = get_term_link( $category[0], 'product_cat' );
					$base_url = ( !is_wp_error( $base_url ) ) ? esc_url( $base_url ) : '';
				}
				if ( empty( $base_url ) ) {
					$base_url = ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ) ? // returns -1 when no shop page has been set yet
								esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) :
								esc_url( home_url('/') );
				}
				break;
			default:
				if ( !empty( $category ) && is_array( $category ) && count( $category ) == 1 ) { // If more than 1 cat selected, show blog url
					$base_url = esc_url( get_category_link( $category[0] ) );
				} elseif ( !empty( $category ) && !is_array( $category ) ) { // Pre 1.0.10 compatibility with 'select' type
					$base_url = esc_url( get_category_link( $category ) );
				} else {
					$base_url = ( get_option( 'page_for_posts' ) ) ?
								esc_url( get_permalink( get_option( 'page_for_posts' ) ) ) :
								esc_url( home_url('/') );
				}
				break;
		}
		$class = sanitize_html_class( 'viewall-' . $viewall );
		$html = apply_filters( 'hootkit_get_viewall', '<div class="viewall ' . $class . '"><a href="' . $base_url . '">' . __( 'View All', 'hootkit' ) . '</a></div>', $viewall, $class, $base_url );
	}
	if ( $echo ) echo $html;
	else return $html;
}
endif;
