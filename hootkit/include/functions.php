<?php
/**
 * Misc Functions
 * This file is loaded at after_setup_theme@96
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Display Label
 * @since  3.0.0
 */
if ( !function_exists( 'hootkit_formatlabel' ) ) :
function hootkit_formatlabel( $label ) {
	return ucwords( str_replace( array( '-', '_' ), ' ' , $label ) );
}
endif;

/**
 * Get array element multiple levels deep
 * @param array $arr
 * @param array|string $fetch
 * @param bool $checkarrtype
 */
if ( ! function_exists( 'hootkit_arrayel' ) ) {
function hootkit_arrayel( $arr, $fetch = array(), $checkarrtype = true ) {
	$fetch = is_array( $fetch ) ? $fetch : ( is_string( $fetch ) || is_int( $fetch ) ? [ $fetch ] : false );
	if ( $checkarrtype && !is_array( $arr ) ) {
		return null;
	} elseif ( $fetch === false ) {
		return null;
	} elseif ( empty( $fetch ) ) {
		return $arr;
	} elseif ( isset( $arr[ $fetch[0] ] ) ) {
		return hootkit_arrayel( $arr[ $fetch[0] ], array_slice( $fetch, 1 ), false );
	} else {
		return null;
	}
}
}

/**
 * Retrieve a post/page by its title.
 * @since  3.0.0
 * @param string $title The title of the post/page to retrieve.
 * @return WP_Post|null The retrieved post/page object or null if not found.
 */
if ( !function_exists( 'hootkit_get_post_type_by_title' ) ):
function hootkit_get_post_type_by_title( $title, $type='post' ) {
	if ( ! $title || !is_string( $title ) ) {
		return null;
	}
	$query = new \WP_Query(
		array(
			'post_type'              => $type,
			'title'                  => $title,
			'post_status'            => 'all',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		)
	);
	if ( ! $query->have_posts() ) {
		return null;
	}
	return current( $query->posts );
}
endif;

/**
 * Recursive wp_parse_args
 * Non Strict Mode: Use $args value if present ('', 0, '0', false allowed)
 * @since  3.0.0
 * @return mixed
 */
if ( !function_exists( 'hootkit_recursive_parse_args' ) ):
function hootkit_recursive_parse_args( $args, $defaults ) {
	$return = (array) $defaults;
	foreach ( $args as $key => $value ) {
		if ( is_array( $value ) && isset( $return[ $key ] ) ) {
			$return[ $key ] = hootkit_recursive_parse_args( $value, $return[ $key ] );
		} else {
			$return[ $key ] = $value;
		}
	}
	return $return;
}
endif;
