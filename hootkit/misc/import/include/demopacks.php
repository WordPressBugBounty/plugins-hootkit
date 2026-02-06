<?php
/**
 * Theme Demo Packs Manifest
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$suffix = apply_filters( 'hootkitimport_demositetype', '' );
$suffix = $suffix === 'base' ? ' ' . __( '(Free)', 'hootkit' ) : ( $suffix === 'premium' ? ' ' . __( '(Premium)', 'hootkit' ) : '' );

return apply_filters( 'hootkitimport_demopacks_manifest', array(
	'cdn_base' => 'https://cdn.wphoot.com/',
	'cdn_url' => 'https://cdn.wphoot.com/themedemos/v5/',

	'magazine-lume' => array(
		'demos'    => array( 'lume-base', 'lume-classico', 'lume-byte', 'lume-news' ),
		'demospro' => array(  ),
		'list'     => array( 'lume-base', 'lume-classico', 'lume-byte', 'lume-news' ),
	),
	'magazine-lume-premium' => array(
		'demos'    => array( 'lume-base-premium', 'lume-classico-premium', 'lume-byte-premium', 'lume-news-premium' )
	),
	'lume-base' => array(
		'name' => __( 'Lume Base', 'hootkit' ) . $suffix,
		'img' => 'lume-base.jpg',
		'thumb' => 'lume-base-thumb.jpg',
		'preview' => 'https://demosites.wphoot.com/magazine-lume/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),
	'lume-classico' => array(
		'name' => __( 'Lume Classico', 'hootkit' ) . $suffix,
		'img' => 'lume-classico.jpg',
		'thumb' => 'lume-classico-thumb.jpg',
		'preview' => 'https://demosites.wphoot.com/lume-classico/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),
	'lume-byte' => array(
		'name' => __( 'Lume Byte', 'hootkit' ) . $suffix,
		'img' => 'lume-byte.jpg',
		'thumb' => 'lume-byte-thumb.jpg',
		'preview' => 'https://demosites.wphoot.com/lume-byte/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),
	'lume-news' => array(
		'name' => __( 'Lume News', 'hootkit' ) . $suffix,
		'img' => 'lume-news.jpg',
		'thumb' => 'lume-news-thumb.jpg',
		'preview' => 'https://demosites.wphoot.com/lume-news/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),

	'magazine-booster' => array(
		'demos'    => array( 'booster-base', 'booster-byte', 'booster-lucore', 'booster-news', 'booster-bombino' ),
		'demospro' => array(  ),
		'list'     => array( 'booster-base', 'booster-byte', 'booster-lucore', 'booster-news', 'booster-bombino' ),
	),
	'magazine-booster-premium' => array(
		'demos'    => array( 'booster-base-premium', 'booster-byte-premium', 'booster-lucore-premium', 'booster-news-premium', 'booster-bombino-premium' )
	),
	'booster-base' => array(
		'name' => __( 'Booster Base', 'hootkit' ) . $suffix,
		'img' => 'booster-base.jpg',
		'thumb' => 'booster-base-thumb.jpg',
		'preview' => 'https://demosites.wphoot.com/magazine-booster/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),
	'booster-lucore' => array(
		'name' => __( 'Booster Lucore', 'hootkit' ) . $suffix,
		'img' => 'booster-lucore.jpg',
		'thumb' => 'booster-lucore-thumb.jpg',
		'preview' => 'https://demosites.wphoot.com/booster-lucore/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),
	'booster-news' => array(
		'name' => __( 'Booster News', 'hootkit' ) . $suffix,
		'img' => 'booster-news.jpg',
		'thumb' => 'booster-news-thumb.jpg',
		'preview' => 'https://demosites.wphoot.com/booster-news/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),
	'booster-byte' => array(
		'name' => __( 'Booster Byte', 'hootkit' ) . $suffix,
		'img' => 'booster-byte.jpg',
		'thumb' => 'booster-byte-thumb.png',
		'preview' => 'https://demosites.wphoot.com/booster-byte/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),
	'booster-bombino' => array(
		'name' => __( 'Booster Bombino', 'hootkit' ) . $suffix,
		'img' => 'booster-bombino.jpg',
		'thumb' => 'booster-bombino-thumb.jpg',
		'preview' => 'https://demosites.wphoot.com/booster-bombino/',
		'plugins' => array( 'hootkit', 'contact-form-7', 'breadcrumb-navxt', 'woocommerce', 'newsletter' )
	),

) );
