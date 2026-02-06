<?php
// Set vars
$createvars = array( 'title','subtitle','before_title','after_title' );
foreach ($createvars as $key) { $$key = !empty( $$key ) ? $$key : ''; }

// Return if no boxes to show
if ( empty( $boxes ) || !is_array( $boxes ) )
	return;

// Get total rows/columns and set row/column counter
$columns = !empty( $columns ) && absint( $columns ) >= 1 ? absint( $columns ) : 4;

// Feature unit
$funitcount = !empty( $firstgrid['count'] ) && absint( $firstgrid['count'] ) >= 1 ? absint( $firstgrid['count'] ) : 1;
$funitrow = !empty( $firstgrid['rowsize'] ) && absint( $firstgrid['rowsize'] ) >= 1 ? absint( $firstgrid['rowsize'] ) : 2;
$funitcol = !empty( $firstgrid['colsize'] ) && absint( $firstgrid['colsize'] ) >= 1 ? absint( $firstgrid['colsize'] ) : 2;
// edge case
$funitcol = $funitcol > $columns ? $columns : $funitcol;

// Feature unit location
$funitalign = !empty( $firstgrid['align'] ) ? $firstgrid['align'] : 'left';
$funitindex = 1;
if ( $funitalign == 'center' ) {
	$funitindex = ceil( ( ( $columns - $funitcol ) / 2 ) + 1 );
} elseif ( $funitalign == 'right' ) {
	$funitindex = $columns - $funitcol + 1;
}
$funitindex = apply_filters( 'hootkit_content_grid_funitindex', $funitindex, ( ( !isset( $instance ) ) ? array() : $instance ), $funitcol, $funitrow );
$funitindex = absint( $funitindex );
$funitindex = $funitindex < 1 ? 1 : $funitindex;


/*** Template Functions ***/

// Display Grid Function
if ( !function_exists( 'hootkit_content_grid_displayunit' ) ):
function hootkit_content_grid_displayunit( $box, $gridindex, $columns, $content_bg ){
	// Box Vars
	if ( ! is_array( $box ) ) return;
	$createvars = array(
		'image','title','subtitle','url','target',
		'button1','buttonurl1','target1','buttoncolor1','buttonfont1',
		'button2','buttonurl2','target2','buttoncolor2','buttonfont2'
	);
	foreach ($createvars as $key) { $$key = !empty( $box[$key] ) ? $box[$key] : ''; }

	$gridimg_attr = array();

	$image = intval( $image );
	$img_size = $columns > 1 ? 'hoot-large-thumb' : 'hoot-extra-wide-thumb';
	$img_size = apply_filters( 'hootkit_gridwidget_imgsize', $img_size, 'content-grid', $gridindex );
	$thumbnail_size = hoot_thumbnail_size( $img_size );
	$img_src = ( $image ) ? wp_get_attachment_image_src( $image, $thumbnail_size ) : array();
	$thumbnail_url = ( !empty( $img_src[0] ) ) ? $img_src[0] : '';
	if ( $thumbnail_url ) $gridimg_attr['style'] = "--hkimgbg: url(" . esc_url($thumbnail_url) . ");";
	// else return; // no image, no display

	?>
	<div <?php echo hoot_get_attr( 'hk-gridunit-image', 'content-grid', $gridimg_attr ) ?>>
		<?php echo '<div class="entry-featured-img-wrap"><img src="' . esc_url( $img_src[0] ) . '" class="hk-gridunit-img" decoding="async" itemprop="image"></div>'; // Redundant, but we use it for SEO (related images) ?>
	</div>

	<?php if ( !empty( $url ) ) echo '<a href="' . esc_url( $url ) . '" ' . hoot_get_attr( 'hk-gridunit-imglink', ( ( !isset( $instance ) ) ? array() : $instance ) ) . ( !empty( $target ) ? ' target="_blank"' : '' ) . '></a>'; ?>

	<?php if ( !empty( $title ) || !empty( $subtitle ) || !empty( $buttonurl1 ) || !empty( $buttonurl2 ) ) : ?>
		<div class="hk-gridunit-content textstyle-<?php echo sanitize_html_class( $content_bg ); ?>">
			<div class="hk-gridunit-text">
				<?php if ( !empty( $title ) ) : ?>
					<h4 class="hk-gridunit-title"><?php
						if ( !empty( $url ) ) echo '<a href="' . esc_url( $url ) . '" ' . ( !empty( $target ) ? ' target="_blank"' : '' ) . '>';
						echo esc_html( $title );
						if ( !empty( $url ) ) echo '</a>';
						?></h4>
				<?php endif; ?>
				<?php if ( !empty( $subtitle ) ) : ?>
					<div class="hk-gridunit-subtitle hoot-subtitle small"><?php echo do_shortcode( wp_kses_post( $subtitle ) ); ?></div>
				<?php endif; ?>
				<?php if ( !empty( $buttonurl1 ) || !empty( $buttonurl2 ) ) : ?>
					<div class="hk-gridunit-buttons"><?php
						$invertbutton = apply_filters( 'hootkit_gridunit_inverthoverbuttons', false );
						for ( $b=1; $b <=2 ; $b++ ) { if ( !empty( ${"buttonurl{$b}"} ) ) {
							$buttonattr = array();
							if ( !empty( ${"buttoncolor{$b}"} ) || !empty( ${"buttonfont{$b}"} ) ) {
								$buttonattr['style'] = '';
								if ( $invertbutton ) $buttonattr['onMouseOver'] = $buttonattr['onMouseOut'] = '';
								if ( !empty( ${"buttoncolor{$b}"} ) ) {
									$buttonattr['style'] .= 'background:' . sanitize_hex_color( ${"buttoncolor{$b}"} ) . ';';
									$buttonattr['style'] .= 'border-color:' . sanitize_hex_color( ${"buttoncolor{$b}"} ) . ';';
									if ( $invertbutton ) $buttonattr['onMouseOver'] .= "this.style.color='" . sanitize_hex_color( ${"buttoncolor{$b}"} ) . "';";
									if ( $invertbutton ) $buttonattr['onMouseOut'] .= "this.style.background='" . sanitize_hex_color( ${"buttoncolor{$b}"} ) . "';";
								}
								if ( !empty( ${"buttonfont{$b}"} ) ) {
									$buttonattr['style'] .= 'color:' . sanitize_hex_color( ${"buttonfont{$b}"} ) . ';';
									if ( $invertbutton ) $buttonattr['onMouseOver'] .= "this.style.background='" . sanitize_hex_color( ${"buttonfont{$b}"} ) . "';";
									if ( $invertbutton ) $buttonattr['onMouseOut'] .= "this.style.color='" . sanitize_hex_color( ${"buttonfont{$b}"} ) . "';";
								}
							}
							$buttonattr['class'] = 'hk-gridunit-button button button-small';
							$buttonattr['data-button'] = $b;
							echo '<a href="' . esc_url( ${"buttonurl{$b}"} ) .'" ' . hoot_get_attr( 'content-grid-button', $box, $buttonattr ) . ( !empty( ${"target{$b}"} ) ? ' target="_blank"' : '' ) . '>';
								echo esc_html( ${"button{$b}"} );
							echo '</a>';
						} }
					?></div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif;

}
endif;

/*** START TEMPLATE ***/

// Template modification Hook
do_action( 'hootkit_gridwidget_wrap', 'content-grid', ( ( !isset( $instance ) ) ? array() : $instance ) );
?>

<div class="hk-grid-widget content-grid-widget">

	<?php
	/* Display Title */
	$titlemarkup = $titleclass = '';
	if ( !empty( $title ) ) {
		$titlemarkup .= $before_title . $title . $after_title;
		$titleclass .= ' hastitle';
	}
	$titlemarkup = ( !empty( $titlemarkup ) ) ? '<div class="widget-title-wrap' . $titleclass . '">' . $titlemarkup . '</div>' : '';
	$titlemarkup .= ( !empty( $subtitle ) ) ? '<div class="widget-subtitle hoot-subtitle">' . $subtitle . '</div>' : '';
	echo do_shortcode( wp_kses_post( apply_filters( 'hootkit_widget_title', $titlemarkup, 'content-grid', $title, $before_title, $after_title, $subtitle ) ) );

	// Template modification Hook
	do_action( 'hootkit_gridwidget_start', 'content-grid', ( ( !isset( $instance ) ) ? array() : $instance ) );

	// Set vars
	$gridindex = 1;
	$slideindex = 0;
	$unitheight = ( empty( $unitheight ) ) ? 0 : ( intval( $unitheight ) );

	$gridbox_attr = array( 'style' => '' );
	$gridbox_attr['style'] .= "--hkgridcols:{$columns};";
	if ( $unitheight )
		$gridbox_attr['style'] .= "--hkgridunitheight:{$unitheight}px;";
	?>

	<div <?php echo hoot_get_attr( 'hk-gridbox', 'content-gridbox', $gridbox_attr ) ?>>
		<?php
		$content_bg = !empty( $content_bg ) ? $content_bg : 'light';
		foreach ( $boxes as $box ) :
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

			// Feature Slider
			if ( $isfeature && $isfslider ) :
				if ( $isfirstslide ) :
					$gridunit_attr['class'] .= ' hk-gridunit-hasslider';
					?><div <?php echo hoot_get_attr( 'hk-gridunit', array( 'type' => 'content-grid', 'counter' => $gridindex ), $gridunit_attr ) ?>><?php
					?><div <?php echo hoot_get_attr( 'hk-gridslider', 'content-grid', 'lightSlider' ) ?>><?php
				endif;
					echo '<div class="hk-grid-slide">';
						hootkit_content_grid_displayunit( $box, $gridindex, $columns, $content_bg );
					echo '</div>';
				if ( $islastslide ) :
					?></div><?php
					?></div><?php
				endif;

			// Feature (non slider) / Normal units
			else:
				?><div <?php echo hoot_get_attr( 'hk-gridunit', array( 'type' => 'content-grid', 'counter' => $gridindex ), $gridunit_attr ) ?>><?php
					hootkit_content_grid_displayunit( $box, $gridindex, $columns, $content_bg );
				?></div><?php
			endif;

			$gridindex++;
		endforeach;
		?>
	</div>

	<?php
	// Template modification Hook
	do_action( 'hootkit_gridwidget_end', 'content-grid', ( ( !isset( $instance ) ) ? array() : $instance ) );
	?>

</div>