<?php
// Return if no icons to show
if ( empty( $icons ) || !is_array( $icons ) )
	return;

// Set vars
$subtitle = ( !empty( $subtitle ) ) ? $subtitle : '';
?>

<div class="social-icons-widget <?php
	echo 'social-icons-' . esc_attr( $size );
	if ( in_array( 'social-icons-altcolor', hootkit()->get_config( 'supports' ) ) && !empty( $altcolor ) )
		echo ' social-icons-style' . esc_attr( $altcolor );
	?>"><?php

	/* Display Title */
	$titlemarkup = $titleclass = '';
	if ( !empty( $title ) ) {
		$titlemarkup .= $before_title . $title . $after_title;
		$titleclass .= ' hastitle';
	}
	$titlemarkup = ( !empty( $titlemarkup ) ) ? '<div class="widget-title-wrap' . $titleclass . '">' . $titlemarkup . '</div>' : '';
	$titlemarkup .= ( !empty( $subtitle ) ) ? '<div class="widget-subtitle hoot-subtitle">' . $subtitle . '</div>' : '';
	echo do_shortcode( wp_kses_post( apply_filters( 'hootkit_widget_title', $titlemarkup, 'social-icons', $title, $before_title, $after_title, $subtitle ) ) );

	if ( in_array( 'social-icons-align', hootkit()->get_config( 'supports' ) ) && !empty( $align ) )
		echo '<div class="social-icons-align social-icons-align' . esc_attr( $align ) . '">';

	$counter = 1;
	foreach( $icons as $key => $icon ) :
		if ( !empty( $icon['url'] ) && !empty( $icon['icon'] ) ) :

			// @NU
			if ( $icon['icon'] == 'fa-skype' && function_exists( 'hootkit_get_skype_button' ) ) :
				echo '<div class="social-icons-icon fa-skype-block">'
					. '<i class="' . hoot_sanitize_fa( $icon['icon'] ) . '"></i>'
					. hootkit_get_skype_button ( $icon['url'] )
					. '</div>';
			else :

				$icon_class = sanitize_html_class( $icon['icon'] ) . '-block';
				$url = ( $icon['icon'] == 'fa-envelope' ) ? 'mailto:' . antispambot( sanitize_email( $icon['url'] ) ) : esc_url( $icon['url'] );
				?><a href="<?php echo $url; ?>" <?php hoot_attr( 'social-icons-icon', array( 'icon' => $icon['icon'], 'counter' => $counter ), $icon_class ); ?>>
					<i class="<?php echo hoot_sanitize_fa( $icon['icon'] ); ?>"></i>
				</a><?php

				$counter++;
			endif;

		endif;
	endforeach;

	if ( in_array( 'social-icons-align', hootkit()->get_config( 'supports' ) ) && !empty( $align ) )
		echo '</div>';
	?>
</div>