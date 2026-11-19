<?php
/**
 * Підтримка теми, стилі та скрипти.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'script', 'style', 'gallery', 'caption' ) );
		add_image_size( 'mz-hero', 1100, 1400, false );
		add_image_size( 'mz-block', 1200, 900, false );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$v = MZ_VER;
		wp_enqueue_style( 'mz-fonts', 'https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&family=Oranienbaum&display=swap&subset=cyrillic', array(), null );
		wp_enqueue_style( 'mz-main', MZ_URI . '/assets/css/main.css', array(), $v );
		wp_enqueue_style( 'mz-wow', MZ_URI . '/assets/css/wow.css', array( 'mz-main' ), $v );
		wp_enqueue_style( 'mz-wp', MZ_URI . '/assets/css/wp.css', array( 'mz-wow' ), $v );

		// GSAP + ScrollTrigger + Lenis — локально (у статичній версії були з CDN)
		wp_enqueue_script( 'gsap', MZ_URI . '/assets/vendor/gsap.min.js', array(), '3.12.5', true );
		wp_enqueue_script( 'gsap-st', MZ_URI . '/assets/vendor/ScrollTrigger.min.js', array( 'gsap' ), '3.12.5', true );
		wp_enqueue_script( 'lenis', MZ_URI . '/assets/vendor/lenis.min.js', array(), '1.1.13', true );
		wp_enqueue_script( 'mz-main', MZ_URI . '/assets/js/main.js', array(), $v, true );
		wp_enqueue_script( 'mz-wow', MZ_URI . '/assets/js/wow.js', array( 'gsap', 'gsap-st', 'lenis', 'mz-main' ), $v, true );
		wp_enqueue_script( 'mz-site', MZ_URI . '/assets/js/site.js', array( 'mz-main' ), $v, true );
		wp_localize_script( 'mz-site', 'MZ', mz_js_config() );

		// у темі немає блоків Gutenberg — не вантажимо їхні стилі
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	},
	20
);

// Contact Form 7: скрипти/стилі не потрібні — форми теми відправляються через REST API CF7 (assets/js/site.js)
add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

// прелоадер: швидка шторка при повторних заходах (як у статичній версії)
add_action(
	'wp_head',
	function () {
		echo "<script>try{if(sessionStorage.getItem('mz-v'))document.documentElement.classList.add('pl-quick')}catch(e){}</script>\n";
	},
	1
);

add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'wow';
		$classes[] = 'page-' . mz_kind();
		return $classes;
	}
);

// емодзі, shortlink (?p=ID), RSS коментарів сторінок, RSD — не потрібні
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_generator' );
