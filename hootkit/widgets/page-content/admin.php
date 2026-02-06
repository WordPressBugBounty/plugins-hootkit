<?php
/**
 * Page Content Widget
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class HootKit_Page_Content_Widget
 */
class HootKit_Page_Content_Widget extends HK_Widget {

	function __construct() {

		$id = 'page-content';

		$settings['id'] = "hootkit-{$id}";
		$settings['name'] = hootkit()->get_string( $id );
		$settings['widget_options'] = array(
			'description'	=> __( 'Display contents of a Page', 'hootkit' ),
		);
		$settings['control_options'] = array();
		$settings['form_options'] = array(
			'show_title' => array(
				'name'		=> __( 'Display Page Title', 'hootkit' ),
				'type'		=> 'checkbox',
			),
			'page' => array(
				'name'		=> __( 'Page', 'hootkit' ),
				'type'		=> 'select',
				'std'		=> 0,
				'options'	=> array( 0 => __( '-- Select a Page --', 'hootkit' ) ) + (array)Hoot_List::pages(0),
			),
			'sepcss' => array(
				'type'		=> 'separator',
			),
			'customcss' => array(
				'name'		=> __( 'Widget Options', 'hootkit' ),
				'type'		=> 'collapse',
				'fields'	=> array(
					'class' => array(
						'name'		=> __( 'Custom CSS Class', 'hootkit' ),
						'desc'		=> __( 'Give this widget a custom css classname', 'hootkit' ),
						'type'		=> 'text',
					),
					'mt' => array(
						'name'		=> __( 'Margin Top', 'hootkit' ),
						'desc'		=> __( '(in pixels) Leave empty to load default margins.<br>Hint: You can use negative numbers also.', 'hootkit' ),
						'type'		=> 'text',
						'settings'	=> array( 'size' => 3 ),
						'sanitize'	=> 'integer',
					),
					'mb' => array(
						'name'		=> __( 'Margin Bottom', 'hootkit' ),
						'desc'		=> __( '(in pixels) Leave empty to load default margins.<br>Hint: You can use negative numbers also.', 'hootkit' ),
						'type'		=> 'text',
						'settings'	=> array( 'size' => 3 ),
						'sanitize'	=> 'integer',
					),
				),
			),
		);

		$settings = apply_filters( 'hootkit_page_content_widget_settings', $settings );

		parent::__construct( $settings['id'], $settings['name'], $settings['widget_options'], $settings['control_options'], $settings['form_options'] );

	}

	/**
	 * Display the widget content
	 */
	function display_widget( $instance, $before_title = '', $title = '', $after_title = '' ) {
		// Allow theme/child-themes to use their own template
		$widget_template = hoot_get_widget( 'page-content', false );
		// Use Hootkit template if theme does not have one
		$widget_template = apply_filters( 'hootkit_widget_template', $widget_template, 'page-content' );
		$widget_template = ( $widget_template ) ? $widget_template : hootkit()->dir . 'widgets/page-content/view.php';
		// Option to overwrite variables to keep html tags in title later sanitized during display => skips 'widget_title' filter (esc_html hooked) action on title; (Possibly redundant as html is sanitized in title during save)
		if ( apply_filters( 'hootkit_display_widget_extract_overwrite', false, 'page-content' ) ) extract( $instance, EXTR_OVERWRITE ); else extract( $instance, EXTR_SKIP );
		// Fire up the template
		if ( is_string( $widget_template ) && file_exists( $widget_template ) ) include ( $widget_template );
	}

}

/**
 * Register Widget
 */
function hootkit_page_content_widget_register(){
	register_widget( 'HootKit_Page_Content_Widget' );
}
add_action( 'widgets_init', 'hootkit_page_content_widget_register' );