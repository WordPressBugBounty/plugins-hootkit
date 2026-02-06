<?php
// Set vars
$createvars = array( 'title','subtitle','before_title','after_title' );
foreach ($createvars as $key) { $$key = !empty( $$key ) ? $$key : ''; }

if ( empty( $page ) )
	return;
$page = get_post( $page );
if ( ! $page )
	return;
?>

<div class="hk-page-content-widget">

	<?php
	$title = esc_html( get_the_title( $page ) );

	/* Display Title */
	$titlemarkup = $titleclass = '';
	if ( !empty( $show_title ) ) {
		$titlemarkup .= $before_title . $title . $after_title;
		$titleclass .= ' hastitle';
	}
	$titlemarkup = ( !empty( $titlemarkup ) ) ? '<div class="widget-title-wrap' . $titleclass . '">' . $titlemarkup . '</div>' : '';
	$titlemarkup .= ( !empty( $subtitle ) ) ? '<div class="widget-subtitle hoot-subtitle">' . $subtitle . '</div>' : '';
	echo do_shortcode( wp_kses_post( apply_filters( 'hootkit_widget_title', $titlemarkup, 'page-content', $title, $before_title, $after_title, $subtitle ) ) );
	?>

	<div class="hk-page-content">
		<?php
		// Output content with normal WP formatting
		echo apply_filters( 'the_content', $page->post_content );
		?>
	</div>

</div>