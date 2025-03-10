<?php
// Return if no message to show
if ( empty( $message ) && empty ( $icon ) )
	return;

// Backward compatibility for widgets which do not have this option
$iconsize = ( empty( $iconsize ) ) ? false : $iconsize;
$headline = ( empty( $headline ) ) ? false : $headline;
$headlinesize = ( empty( $headlinesize ) ) ? false : $headlinesize;

$inlinestyle = $styleclass = $iconstyle = $iconclass = $headlinestyle = $headlineclass = '';
if ( $background || $fontcolor ) {
	$styleclass .= ' announce-userstyle';
	$inlinestyle .= ( $background ) ? 'background:' . sanitize_hex_color( $background ) . ';' : '';
	$inlinestyle .= ( $fontcolor ) ? 'color:' . sanitize_hex_color( $fontcolor ) . ';' : '';
}
$styleclass .= ( $background ) ? ' announce-withbg' : '';
$styleclass .= ( !$headline && !$message ) ? ' announce-nomsg' : '';
$styleclass .= ( !$icon ) ? ' announce-noicon' : '';
if ( $iconcolor || $iconsize ) {
	$iconclass .= ' icon-userstyle';
	$iconstyle .= ' style="';
	$iconstyle .= ( $iconcolor ) ? 'color:' . sanitize_hex_color( $iconcolor ) . ';' : '';
	$iconstyle .= ( $iconsize ) ? 'font-size:' . intval( $iconsize ) . 'px;' : '';
	$iconstyle .= '"';
};
if ( $headlinesize ) {
	$headlineclass .= ' announce-headline-userstyle';
	$headlinestyle .= ' style="font-size:' . intval( $headlinesize ) . 'px;"';
}
?>

<div <?php hoot_attr( 'announce-widget', '', array( 'classes' => $styleclass, 'style' => $inlinestyle ) ); ?>>
	<?php if ( !empty( $url ) ) echo '<a href="' . esc_url( $url ) . '" ' . hoot_get_attr( 'announce-link', ( ( !isset( $instance ) ) ? array() : $instance ) ) . '><span>' . __( 'Click Here', 'hootkit' ) . '</span></a>'; ?>
	<div class="announce-box hootflex hootflex--nor">
		<?php if ( !empty( $icon ) ) : ?>
			<div class="announce-box-icon"><i class="<?php echo hoot_sanitize_fa( $icon ) . $iconclass; ?>"<?php echo $iconstyle ?>></i></div>
		<?php endif; ?>
		<?php if ( !empty( $message ) || !empty( $headline ) ) : ?>
			<div class="announce-box-content">
				<?php if ( !empty( $headline ) ) { ?>
					<h5 class="announce-headline<?php echo $headlineclass; ?>"<?php echo $headlinestyle ?>><?php echo do_shortcode( wp_kses_post( $headline ) ); ?></h5>
				<?php } ?>
				<?php if ( !empty( $message ) ) { ?>
					<div class="announce-message"><?php echo do_shortcode( wp_kses_post( $message ) ); ?></div>
				<?php } ?>
			</div>
		<?php endif; ?>
	</div>
</div>