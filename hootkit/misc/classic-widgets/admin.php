<?php
/**
 * Classic Widgets
 * This file is loaded at after_setup_theme@96 via class-miscmods
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Remove widgets Blocks screen and switch back to classic widgets screen **/
remove_theme_support( 'widgets-block-editor' );
