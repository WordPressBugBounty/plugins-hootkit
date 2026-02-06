<?php
/**
 * Content Grid Widget
 *
 * @package Hootkit
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class HootKit_Content_Grid_Widget
 */
class HootKit_Content_Grid_Widget extends HK_Widget {

	function __construct() {

		$id = 'content-grid';

		$settings['id'] = "hootkit-{$id}";
		$settings['name'] = hootkit()->get_string( $id );
		$settings['widget_options'] = array(
			'description'	=> __( 'Display Content in a Grid', 'hootkit' ),
		);
		$settings['control_options'] = array();
		$settings['form_options'] = array(
			'title' => array(
				'name'		=> __( 'Title (Optional)', 'hootkit' ),
				'type'		=> 'text',
			),
			'subtitle' => array(
				'name'		=> __( 'Sub Title (optional)', 'hootkit' ),
				'type'		=> 'text',
			),
			'columns' => array(
				'name'		=> __( 'Number Of Columns', 'hootkit' ),
				'type'		=> 'smallselect',
				'std'		=> '4',
				'options'	=> array(
					'1'  => __( '1', 'hootkit' ),
					'2'  => __( '2', 'hootkit' ),
					'3'  => __( '3', 'hootkit' ),
					'4'  => __( '4', 'hootkit' ),
					'5'  => __( '5', 'hootkit' ),
					'6'  => __( '6', 'hootkit' ),
					'7'  => __( '7', 'hootkit' ),
					'8'  => __( '8', 'hootkit' ),
					'9'  => __( '9', 'hootkit' ),
					'10' => __( '10', 'hootkit' ),
					'11' => __( '11', 'hootkit' ),
					'12' => __( '12', 'hootkit' ),
				),
			),
			'unitheight' => array(
				'name'		=> __( 'Grid Unit (Image) Size', 'hootkit' ),
				'desc'		=> __( 'Default: 215 (in pixels)', 'hootkit' ),
				'type'		=> 'text',
				'settings'	=> array( 'size' => 3, ),
				'sanitize'	=> 'absint',
			),
			'content_bg' => array(
				'name'		=> __( 'Text Styling', 'hootkit' ),
				'type'		=> 'select',
				'std'		=> 'light',
				'options'	=> array(
					'dark'			=> __( 'Dark Font', 'hootkit' ),
					'light'			=> __( 'Light Font', 'hootkit' ),
					'dark-on-light'	=> __( 'Dark Font / Light Background', 'hootkit' ),
					'light-on-dark'	=> __( 'Light Font / Dark Background', 'hootkit' ),
				),
			),
			'boxes' => array(
				'name'		=> __( 'Grid Units', 'hootkit' ),
				'type'		=> 'group',
				'options'	=> array(
					'item_name'	=> __( 'Grid Unit', 'hootkit' ),
					'dellimit'	=> true,
					'sortable'	=> true,
				),
				'fields'	=> array(
					'image' => array(
						'name'		=> __( 'Image', 'hootkit' ),
						'type'		=> 'image',
					),
					'title' => array(
						'name'		=> __( 'Title', 'hootkit' ),
						'type'		=> 'text',
					),
					'subtitle' => array(
						'name'		=> __( 'Sub Title (optional)', 'hootkit' ),
						'type'		=> 'text',
					),
					'url'=> array(
						'name'		=> __( 'Box Link URL (optional)', 'hootkit' ),
						'desc'		=> __( 'Link entire Content Box', 'hootkit' ),
						'type'		=> 'text',
						'sanitize'	=> 'url',
					),
					'target' => array(
						'name'		=> __( 'Open Link In New Window', 'hootkit' ),
						'type'		=> 'checkbox',
						'boxdivi'	=> 'div-ve0',
					),
					'button1' => array(
						'name'		=> sprintf( __( 'Button %1$s Text', 'hootkit' ), '1' ),
						'type'		=> 'text',
						'settings'	=> array( 'size' => 16 ),
					),
					'buttonurl1'=> array(
						'name'		=> sprintf( __( 'Button %1$s URL', 'hootkit' ), '1' ),
						'type'		=> 'text',
						'sanitize'	=> 'url',
						'settings'	=> array( 'size' => 16 ),
					),
					'target1' => array(
						'name'		=> __( 'Open Link 1 In New Window', 'hootkit' ),
						'type'		=> 'checkbox',
						'boxdivi'	=> 'div-ve0',
					),
					'buttoncolor1' => array(
						'name'		=> sprintf( __( 'Button %1$s Color', 'hootkit' ), '1' ),
						'type'		=> 'color',
					),
					'buttonfont1' => array(
						'name'		=> sprintf( __( 'Button %1$s Text Color', 'hootkit' ), '1' ),
						'type'		=> 'color',
					),
					'button2' => array(
						'name'		=> sprintf( __( 'Button %1$s Text', 'hootkit' ), '2' ),
						'type'		=> 'text',
						'settings'	=> array( 'size' => 16 ),
					),
					'buttonurl2'=> array(
						'name'		=> sprintf( __( 'Button %1$s URL', 'hootkit' ), '2' ),
						'type'		=> 'text',
						'sanitize'	=> 'url',
						'settings'	=> array( 'size' => 16 ),
					),
					'target2' => array(
						'name'		=> __( 'Open Link 2 In New Window', 'hootkit' ),
						'type'		=> 'checkbox',
						'boxdivi'	=> 'div-ve0',
					),
					'buttoncolor2' => array(
						'name'		=> sprintf( __( 'Button %1$s Color', 'hootkit' ), '2' ),
						'type'		=> 'color',
					),
					'buttonfont2' => array(
						'name'		=> sprintf( __( 'Button %1$s Text Color', 'hootkit' ), '2' ),
						'type'		=> 'color',
					),
				),
			),
			'firstgrid' => array(
				'name'		=> __( 'Big Grid Unit', 'hootkit' ),
				'type'		=> 'collapse',
				'settings'	=> array( 'state' => 'open' ),
				'fields'	=> array(
					'rowsize' => array(
						'mergefieldbelow' => true,
						'name'		=> __( 'Big Grid Unit Size', 'hootkit' ),
						'type'		=> 'smallselect',
						'smallselectlabel' => __( 'Rows', 'hootkit' ),
						'std'		=> '2',
						'options'	=> array(
							'1'	=> __( '1', 'hootkit' ),
							'2'	=> __( '2', 'hootkit' ),
							'3'	=> __( '3', 'hootkit' ),
							'4'	=> __( '4', 'hootkit' ),
							'5'	=> __( '5', 'hootkit' ),
							'6'	=> __( '6', 'hootkit' ),
							'7'	=> __( '7', 'hootkit' ),
						),
					),
					'colsize' => array(
						'mergefieldtop'   => true,
						'type'		=> 'smallselect',
						'smallselectlabel' => __( 'Columns', 'hootkit' ),
						'desc'		=> __( 'Set this to 1x1 to make it the same size as rest of the grid units.', 'hootkit' ),
						'std'		=> '2',
						'options'	=> array(
							'1'	=> __( '1', 'hootkit' ),
							'2'	=> __( '2', 'hootkit' ),
							'3'	=> __( '3', 'hootkit' ),
							'4'	=> __( '4', 'hootkit' ),
							'5'	=> __( '5', 'hootkit' ),
							'6'	=> __( '6', 'hootkit' ),
							'7'	=> __( '7', 'hootkit' ),
						),
					),
					'align' => array(
						'name'		=> __( 'Big Unit Location', 'hootkit' ),
						'type'		=> 'smallselect',
						'std'		=> 'left',
						'options'	=> array(
							'left'		=> __( 'Left', 'hootkit' ),
							'center'	=> __( 'Center', 'hootkit' ),
							'right'		=> __( 'Right', 'hootkit' ),
						),
					),
					'count' => array(
						'name'		=> __( 'Number of Units', 'hootkit' ),
						'desc'		=> __( 'Selecting more than 1 unit will <strong>convert the first grid into a SLIDER</strong>', 'hootkit' ),
						'type'		=> 'smallselect',
						'std'		=> '1',
						'options'	=> array(
							'1'	=> __( '1', 'hootkit' ),
							'2'	=> __( '2', 'hootkit' ),
							'3'	=> __( '3', 'hootkit' ),
							'4'	=> __( '4', 'hootkit' ),
							'5'	=> __( '5', 'hootkit' ),
							'6'	=> __( '6', 'hootkit' ),
							'7'	=> __( '7', 'hootkit' ),
							'8'	=> __( '8', 'hootkit' ),
							'9'	=> __( '9', 'hootkit' ),
							'10'=> __( '10', 'hootkit' ),
						),
					),
					'fix' => array(
						'type'		=> '<input type="hidden" name="%name%" id="%id%" value="na" class="%class%">',
						// Bugfix: This field is added since all the fields in collapsible are checkboxes. So when all checkbox are unchecked, value for "widget-hoot-content-grid-widget[N][firstgrid]" in the instance is returned as false by the browsers instead of an array with all emements = 0 (empty string value is ok, but we still add a dummy value)
					),
				),
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

		if ( ! hootkit()->supports( 'widget-subtitle' ) ) {
			unset( $settings['form_options']['subtitle'] );
		}

		$settings = apply_filters( 'hootkit_content_grid_widget_settings', $settings );

		parent::__construct( $settings['id'], $settings['name'], $settings['widget_options'], $settings['control_options'], $settings['form_options'] );

	}

	/**
	 * Display the widget content
	 */
	function display_widget( $instance, $before_title = '', $title = '', $after_title = '' ) {
		// Allow theme/child-themes to use their own template
		$widget_template = hoot_get_widget( 'content-grid', false );
		// Use Hootkit template if theme does not have one
		$widget_template = apply_filters( 'hootkit_widget_template', $widget_template, 'content-grid' );
		$widget_template = ( $widget_template ) ? $widget_template : hootkit()->dir . 'widgets-v3/content-grid/view.php';
		// Option to overwrite variables to keep html tags in title later sanitized during display => skips 'widget_title' filter (esc_html hooked) action on title; (Possibly redundant as html is sanitized in title during save)
		if ( apply_filters( 'hootkit_display_widget_extract_overwrite', false, 'content-grid' ) ) extract( $instance, EXTR_OVERWRITE ); else extract( $instance, EXTR_SKIP );
		// Fire up the template
		if ( is_string( $widget_template ) && file_exists( $widget_template ) ) include ( $widget_template );
	}

}

/**
 * Register Widget
 */
function hootkit_content_grid_widget_register(){
	register_widget( 'HootKit_Content_Grid_Widget' );
}
add_action( 'widgets_init', 'hootkit_content_grid_widget_register' );