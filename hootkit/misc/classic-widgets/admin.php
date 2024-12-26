<?php
/**
 * Classic Widgets
 * This file is loaded in HootKit->loadplugin() via 'after_setup_theme' action @priority 95
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Remove widgets Blocks screen and switch back to classic widgets screen **/
remove_theme_support( 'widgets-block-editor' );
