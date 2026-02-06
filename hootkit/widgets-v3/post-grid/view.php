<?php
// Set vars
$createvars = array( 'title','subtitle','before_title','after_title' );
foreach ($createvars as $key) { $$key = !empty( $$key ) ? $$key : ''; }
$viewall = ( !empty( $viewall ) ) ? $viewall : '';

// Get total rows/columns and set row/column counter
$columns = !empty( $columns ) && absint( $columns ) >= 1 ? absint( $columns ) : 4;
$rows = !empty( $rows ) && absint( $rows ) >= 1 ? absint( $rows ) : 2;

// Feature unit
$funitcount = !empty( $firstpost['count'] ) && absint( $firstpost['count'] ) >= 1 ? absint( $firstpost['count'] ) : 1;
$funitrow = !empty( $firstpost['rowsize'] ) && absint( $firstpost['rowsize'] ) >= 1 ? absint( $firstpost['rowsize'] ) : 2;
$funitcol = !empty( $firstpost['colsize'] ) && absint( $firstpost['colsize'] ) >= 1 ? absint( $firstpost['colsize'] ) : 2;
// edge case
$funitcol = $funitcol > $columns ? $columns : $funitcol;
$funitrow = $funitrow > $rows ? $rows : $funitrow;

// Feature unit location
$funitalign = !empty( $firstpost['align'] ) ? $firstpost['align'] : 'left';
$funitindex = 1;
if ( $funitalign == 'center' ) {
	$funitindex = ceil( ( ( $columns - $funitcol ) / 2 ) + 1 );
} elseif ( $funitalign == 'right' ) {
	$funitindex = $columns - $funitcol + 1;
}
$funitindex = apply_filters( 'hootkit_post_grid_funitindex', $funitindex, ( ( !isset( $instance ) ) ? array() : $instance ), $funitcol, $funitrow );
$funitindex = absint( $funitindex );
$funitindex = $funitindex < 1 ? 1 : $funitindex;



/*** Posts Query ***/
$query_args = array();
// Create category array from main options -  these vals are undefined if none selected in multiselect
$exccategory = ( !empty( $exccategory ) && is_array( $exccategory ) ) ? array_map( 'hootkit_append_negative', $exccategory ) : array();
$category = ( !empty( $category ) && is_array( $category ) ) ? array_merge( $category, $exccategory) : $exccategory;
if ( !empty( $category ) )
	$query_args['category'] = implode( ',', $category );
// Count
$count = ( $rows * $columns ) - ( $funitrow * $funitcol ) + $funitcount;
$query_args['posts_per_page'] = $count;
// Offset
if ( !empty( $offset ) )
	$query_args['offset'] = absint( $offset );
// Skip posts without image
$query_args['meta_query'] = array(
	array(
		'key' => '_thumbnail_id',
		'compare' => 'EXISTS'
	),
);
// Create Query
$query_args = apply_filters( 'hootkit_post_grid_stdquery', $query_args, ( ( !isset( $instance ) ) ? array() : $instance ) );
$post_grid_query = get_posts( $query_args );


/*** Template Functions ***/

// Display Grid Function
if ( !function_exists( 'hootkit_post_grid_displayunit' ) ):
function hootkit_post_grid_displayunit( $gridindex, $columns, $content_bg, $show_title, $metadisplay ){
	$gridimg_attr = array();

	$img_size = $columns > 1 ? 'hoot-large-thumb' : 'hoot-extra-wide-thumb';
	$img_size = apply_filters( 'hootkit_gridwidget_imgsize', $img_size, 'post-grid', $gridindex );
	$thumbnail_size = hoot_thumbnail_size( $img_size );
	$thumbnail_url = get_the_post_thumbnail_url( null, $thumbnail_size );
	if ( $thumbnail_url ) $gridimg_attr['style'] = "--hkimgbg: url(" . esc_url($thumbnail_url) . ");";

	?>
	<div <?php echo hoot_get_attr( 'hk-gridunit-image', 'post-grid', $gridimg_attr ) ?>>
		<?php hoot_post_thumbnail( 'hk-gridunit-img', $img_size ); // Redundant, but we use it for SEO (related images) ?>
	</div>

	<?php echo '<a href="' . esc_url( get_permalink() ) . '" ' . hoot_get_attr( 'hk-gridunit-imglink', ( ( !isset( $instance ) ) ? array() : $instance ) ) . '></a>'; ?>

	<div class="hk-gridunit-content textstyle-<?php echo sanitize_html_class( $content_bg ); ?>">
		<?php
		if ( in_array( 'cats', $metadisplay ) && apply_filters( 'hootkit_post_grid_display_catblock', false ) ) {
			hootkit_display_meta_info( array(
				'display' => array( 'cats' ),
				'context' => 'hk-gridunit',
				'editlink' => false,
				'wrapper' => 'div',
				'wrapper_class' => 'hk-gridunit-suptitle small',
				'empty' => '',
			) );
			$catkey = array_search ( 'cats', $metadisplay );
			unset( $metadisplay[ $catkey] );
		}
		?>
		<div class="hk-gridunit-text">
			<?php if ( !empty( $show_title ) ) : ?>
				<h4 class="hk-gridunit-title"><?php echo '<a href="' . esc_url( get_permalink() ) . '" ' . hoot_get_attr( 'hk-gridunit-link', ( ( !isset( $instance ) ) ? array() : $instance ) ) . '>';
					the_title();
					echo '</a>'; ?></h4>
			<?php endif; ?>
			<?php
			hootkit_display_meta_info( array(
				'display' => $metadisplay,
				'context' => 'hk-gridunit',
				'editlink' => false,
				'wrapper' => 'div',
				'wrapper_class' => 'hk-gridunit-subtitle small',
				'empty' => '',
			) );
			?>
		</div>
	</div>
	<?php
}
endif;

/*** START TEMPLATE ***/

// Template modification Hook
do_action( 'hootkit_gridwidget_wrap', 'post-grid', ( ( !isset( $instance ) ) ? array() : $instance ), $post_grid_query, $query_args );
?>

<div class="hk-grid-widget post-grid-widget">

	<?php
	/* Display Title */
	$titlemarkup = $titleclass = '';
	if ( !empty( $title ) ) {
		$titlemarkup .= $before_title . $title . $after_title;
		$titleclass .= ' hastitle';
	}
	if ( $viewall == 'top' ) {
		$titlemarkup .= hootkit_get_viewall();
		$titleclass .= ' hasviewall';
	}
	$titlemarkup = ( !empty( $titlemarkup ) ) ? '<div class="widget-title-wrap' . $titleclass . '">' . $titlemarkup . '</div>' : '';
	$titlemarkup .= ( !empty( $subtitle ) ) ? '<div class="widget-subtitle hoot-subtitle">' . $subtitle . '</div>' : '';
	echo do_shortcode( wp_kses_post( apply_filters( 'hootkit_widget_title', $titlemarkup, 'post-grid', $title, $before_title, $after_title, $subtitle, $viewall ) ) );

	// Template modification Hook
	do_action( 'hootkit_gridwidget_start', 'post-grid', ( ( !isset( $instance ) ) ? array() : $instance ), $post_grid_query, $query_args );

	// Set vars
	global $post;
	$gridindex = 1;
	$slideindex = 0;
	$show_title = ( !empty( $show_title ) ) ? true : false;
	$unitheight = ( empty( $unitheight ) ) ? 0 : ( intval( $unitheight ) );
	$unitmetadisplay = array();
	if ( !empty( $show_author ) )   $unitmetadisplay[] = 'author';
	if ( !empty( $show_date ) )     $unitmetadisplay[] = 'date';
	if ( !empty( $show_comments ) ) $unitmetadisplay[] = 'comments';
	if ( !empty( $show_cats ) )     $unitmetadisplay[] = 'cats';
	if ( !empty( $show_tags ) )     $unitmetadisplay[] = 'tags';
	$funitmetadisplay = array();
	if ( !empty( $firstpost['author'] ) )   $funitmetadisplay[] = 'author';
	if ( !empty( $firstpost['date'] ) )     $funitmetadisplay[] = 'date';
	if ( !empty( $firstpost['comments'] ) ) $funitmetadisplay[] = 'comments';
	if ( !empty( $firstpost['cats'] ) )     $funitmetadisplay[] = 'cats';
	if ( !empty( $firstpost['tags'] ) )     $funitmetadisplay[] = 'tags';

	$gridbox_attr = array( 'style' => '' );
	$gridbox_attr['style'] .= "--hkgridcols:{$columns};";
	if ( $unitheight )
		$gridbox_attr['style'] .= "--hkgridunitheight:{$unitheight}px;";
	?>

	<div <?php echo hoot_get_attr( 'hk-gridbox', 'post-gridbox', $gridbox_attr ) ?>>
		<?php
		$content_bg = !empty( $content_bg ) ? $content_bg : 'light';
		foreach ( $post_grid_query as $post ) :
			$gridunit_attr = array( 'class' => 'hk-gridunit', 'style' => '' );
			$isfeature = $gridindex >= $funitindex && $gridindex < ( $funitindex + $funitcount ) ? true : false;
			$isbig = $isfeature && ( $funitcol > 1 || $funitrow > 1 ) ? true : false;
			if ( $isbig ) {
				$gridunit_attr['class'] .= ' hk-gridunit-big';
				$gridunit_attr['class'] .= $funitcol > 1 ? ' hk-gridunit-bigcol' : ( $funitrow > 1 ? ' hk-gridunit-bigrow' : '' );
				$gridunit_attr['style'] .= "--hkgridunitcol:{$funitcol};--hkgridunitrow:{$funitrow};";
			}
			if ( $isfeature && $funitcount > 1 ) {
				$slideindex++;
				$isfslider = true;
				$isfirstslide = $slideindex == 1 ? true : false;
				$islastslide = $slideindex == $funitcount ? true : false;
			} else {
				$slideindex = 0;
				$isfslider = false;
				$isfirstslide = false;
				$islastslide = false;
			}

			$metadisplay = $isfeature ? $funitmetadisplay : $unitmetadisplay;

			// Feature Slider
			if ( $isfeature && $isfslider ) :
				if ( $isfirstslide ) :
					$gridunit_attr['class'] .= ' hk-gridunit-hasslider';
					?><div <?php echo hoot_get_attr( 'hk-gridunit', array( 'type' => 'post-grid', 'counter' => $gridindex ), $gridunit_attr ) ?>><?php
					?><div <?php echo hoot_get_attr( 'hk-gridslider', 'post-grid', 'lightSlider' ) ?>><?php
				endif;
					echo '<div class="hk-grid-slide">';
						setup_postdata( $post );
						hootkit_post_grid_displayunit( $gridindex, $columns, $content_bg, $show_title, $metadisplay );
					echo '</div>';
				if ( $islastslide ) :
					?></div><?php
					?></div><?php
				endif;

			// Feature (non slider) / Normal units
			else:
				?><div <?php echo hoot_get_attr( 'hk-gridunit', array( 'type' => 'post-grid', 'counter' => $gridindex ), $gridunit_attr ) ?>><?php
					setup_postdata( $post );
					hootkit_post_grid_displayunit( $gridindex, $columns, $content_bg, $show_title, $metadisplay );
				?></div><?php
			endif;

			$gridindex++;
		endforeach;

		wp_reset_postdata();
		?>
	</div>

	<?php
	// View All link
	if ( !empty( $viewall ) && $viewall == 'bottom' ) hootkit_get_viewall( true );

	// Template modification Hook
	do_action( 'hootkit_gridwidget_end', 'post-grid', ( ( !isset( $instance ) ) ? array() : $instance ), $post_grid_query, $query_args );
	?>

</div>