<?php
/**
 * Кейси: тип записів mz_case, архів /keysy/ (RU — /ru/keysy/).
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'mz_case',
			array(
				'labels'        => array(
					'name'          => 'Кейси',
					'singular_name' => 'Кейс',
					'add_new'       => 'Додати кейс',
					'add_new_item'  => 'Новий кейс',
					'edit_item'     => 'Редагувати кейс',
					'all_items'     => 'Усі кейси',
				),
				'public'        => true,
				'has_archive'   => 'keysy',
				'rewrite'       => array( 'slug' => 'keysy', 'with_front' => false ),
				'menu_icon'     => 'dashicons-portfolio',
				'menu_position' => 21,
				'supports'      => array( 'title', 'revisions' ),
				'show_in_rest'  => false,
			)
		);
	}
);

// Кейси перекладаються (Polylang)
add_filter(
	'pll_get_post_types',
	function ( $types, $is_settings ) {
		$types['mz_case'] = 'mz_case';
		return $types;
	},
	10,
	2
);

// Архів кейсів недоступний, поки немає жодного опублікованого кейсу
add_action(
	'template_redirect',
	function () {
		if ( is_post_type_archive( 'mz_case' ) && ! mz_has_cases() ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
		}
	}
);
