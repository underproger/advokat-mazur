<?php
/**
 * Службові сторінки WordPress зі старого сайту, які не мають змісту
 * (архіви автора/рубрик/дат, запис «Привет, мир!») — 301 на головну,
 * щоб старі адреси не віддавали 404 і не потрапляли в індекс порожніми.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'template_redirect',
	function () {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}
		$junk = is_author() || is_category() || is_tag() || is_date() || ( is_singular( 'post' ) && 'privet-mir' === get_post_field( 'post_name', get_queried_object_id() ) );
		if ( $junk ) {
			wp_safe_redirect( mz_url( 'home' ), 301 );
			exit;
		}
	},
	5
);

// Карта сайту WP: без користувачів, рубрик і записів (на сайті лише сторінки та кейси)
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_taxonomies',
	function ( $taxonomies ) {
		unset( $taxonomies['category'], $taxonomies['post_tag'] );
		return $taxonomies;
	}
);
add_filter(
	'wp_sitemaps_post_types',
	function ( $types ) {
		unset( $types['post'] );
		return $types;
	}
);
// подяка — не в карті сайту
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$ids = array();
			foreach ( array( 'uk', 'ru' ) as $l ) {
				$p = mz_page_by_key( 'thanks', $l );
				if ( $p ) {
					$ids[] = $p->ID;
				}
			}
			$args['post__not_in'] = $ids;
		}
		return $args;
	},
	10,
	2
);
