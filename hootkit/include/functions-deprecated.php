<?php
/**
 * Deprecated Functions
 * This file is loaded at after_setup_theme@89
 */

if ( !function_exists( 'hoot_dashboard' ) ):
function hoot_dashboard( $key, $args=array() ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_dashboard()' );
	return '';
}
endif;

/* template-functions.php */

if ( !function_exists( 'hootkit_thumbnail_size' ) ):
function hootkit_thumbnail_size( $size = '', $crop = NULL, $default = 'full' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_thumbnail_size()' );
	if ( function_exists( 'hoot_thumbnail_size' ) )
		return hoot_thumbnail_size( $size, $crop );
	return $default;
}
endif;
if ( !function_exists( 'hoot_thumbnail_size' ) ):
function hoot_thumbnail_size( $size = '', $crop = NULL ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_thumbnail_size()' );
	return 'full';
}
endif;

if ( !function_exists( 'hootkit_post_thumbnail' ) ):
function hootkit_post_thumbnail( $classes = '', $size = '', $microdata = false, $link = '', $crop = NULL, $default = 'full' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_post_thumbnail()' );
	if ( function_exists( 'hoot_post_thumbnail' ) )
		hoot_post_thumbnail( $classes, $size, $microdata, $link, $crop );
}
endif;
if ( !function_exists( 'hoot_post_thumbnail' ) ):
function hoot_post_thumbnail( $classes = '', $size = '', $microdata = false, $link = '', $crop = NULL ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_post_thumbnail()' );
}
endif;

if ( !function_exists( 'hoot_meta_info' ) ):
function hoot_meta_info( $args = '', $context = '', $bool = false ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_meta_info()' );
	return $bool ? false : array();
}
endif;
if ( !function_exists( 'hoot_display_meta_info' ) ):
function hoot_display_meta_info( $args = '', $context = '', $editlink = true, $classes='' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_display_meta_info()' );
}
endif;

/* hoot-library-icons.php */

if ( !function_exists( 'hoot_sanitize_fa' ) ):
function hoot_sanitize_fa( $icon ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_sanitize_fa()' );
	return '';
}
endif;
if ( !function_exists( 'hoot_enum_icons' ) ):
function hoot_enum_icons( $return = 'list' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_enum_icons()' );
	return array();
}
endif;
if ( !function_exists( 'hoot_enum_social_profiles' ) ):
function hoot_enum_social_profiles( $skype = false, $email = true ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_enum_social_profiles()' );
	return array();
}
endif;
if ( !function_exists( 'hoot_fonticons_list' ) ):
function hoot_fonticons_list( $return = 'icons' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_fonticons_list()' );
	return array();
}
endif;

/* hoot-library.php */

if ( !function_exists( 'hoot_sanitize_html_classes' ) ):
function hoot_sanitize_html_classes( $class, $fallback = null ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_sanitize_html_classes()' );
	return '';
}
endif;
if ( !function_exists( 'hoot_attr' ) ):
function hoot_attr( $slug, $context = '', $attr = '' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_attr()' );
}
endif;
if ( !function_exists( 'hoot_get_attr' ) ):
function hoot_get_attr( $slug, $context = '', $attr = '' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_get_attr()' );
	return '';
}
endif;
if ( !function_exists( 'hoot_get_excerpt' ) ):
function hoot_get_excerpt( $words ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_get_excerpt()' );
	return apply_filters( 'the_excerpt', get_the_excerpt() );
}
endif;
if ( !function_exists( 'hoot_getexcerpt_customlength' ) ):
function hoot_getexcerpt_customlength( $length ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_getexcerpt_customlength()' );
	return $length;
}
endif;
global $hoot_data;
if ( !isset( $hoot_data ) || !is_object( $hoot_data ) )
	$hoot_data = new stdClass();
if ( !function_exists( 'hoot_data'  ) ):
function hoot_data( $key = '', $subkey = '' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_data()' );
	return '';
}
endif;
if ( !function_exists( 'hoot_get_data'  ) ):
function hoot_get_data( $key = '', $subkey = '' ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_get_data()' );
	return null;
}
endif;
if ( !function_exists( 'hoot_set_data'  ) ):
function hoot_set_data( $key, $value, $override = true ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_set_data()' );
}
endif;
if ( !function_exists( 'hoot_unset_data'  ) ):
function hoot_unset_data( $key ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_unset_data()' );
}
endif;
if ( !function_exists( 'hoot_get_widget'  ) ):
function hoot_get_widget( $name, $load = true ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_get_widget()' );
	return array();
}
endif;
if ( !function_exists( 'hoot_trim_content' ) ):
function hoot_trim_content( $raw, $words ) {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_trim_content()' );
	return $raw;
}
endif;
if ( !function_exists( 'hoot_list_products_category' ) ):
function hoot_list_products_category() {
	_deprecated_function( __FUNCTION__, '3.0.0', 'hoot_list_products_category()' );
	return array();
}
endif;
if ( !class_exists( 'Hoot_List' ) ):
class Hoot_List {
	static function listlength(){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_listlength()' );
		return 99;
	}
	static function countval( $number ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_countval()' );
		return 99;
	}
	static function get_pages( $number = 0, $post_type = 'page' ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_get_pages()' );
		return array();
	}
	static function get_posts( $number = 0 ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_get_posts()' );
		return array();
	}
	static function get_terms( $number = 0, $taxonomy = 'category' ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_get_terms()' );
		return array();
	}
	static function categories( $number = false ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_get_categories()' );
		return array();
	}
	static function tags( $number = false ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_get_tags()' );
		return array();
	}
	static function pages( $number = false ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_pages()' );
		return array();
	}
	static function posts( $number = false ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_posts()' );
		return array();
	}
	static function cpt( $post_type = 'page', $number = false ){
		_deprecated_function( __FUNCTION__, '3.0.0', 'hootlist_cpt()' );
		return array();
	}
}
endif;
