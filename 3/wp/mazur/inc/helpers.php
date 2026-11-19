<?php
/**
 * Допоміжні функції: налаштування сайту, перелік послуг, посилання між сторінками, тексти.
 */

defined( 'ABSPATH' ) || exit;

/** Значення за замовчуванням (контакти з чинного сайту). Змінюються в «Налаштування сайту». */
function mz_defaults() {
	return array(
		'name'       => 'Сергій Мазур',
		'name_ru'    => 'Сергей Мазур',
		'cert'       => '4073',
		'years'      => '20',
		'phone'      => '+38 096 912 48 00',
		'phone_href' => '+380969124800',
		'email'      => 'advocatemazur@gmail.com',
		'address'    => 'м. Київ, вул. Симона Петлюри, 8',
		'address_ru' => 'г. Киев, ул. Симона Петлюры, 8',
		'hours'      => 'Пн–Пт 9:00–19:00',
		'hours_ru'   => 'Пн–Пт 9:00–19:00',
		'telegram'   => 'https://t.me/+380969124800',
		'viber'      => 'viber://chat?number=%2B380969124800',
		'whatsapp'   => 'https://wa.me/380969124800',
	);
}

/** Опція з «Налаштування сайту» (ACF options) з fallback на значення за замовчуванням. */
function mz_opt( $name, $default = null ) {
	static $cache = array();
	if ( array_key_exists( $name, $cache ) ) {
		return $cache[ $name ];
	}
	$val = function_exists( 'get_field' ) ? get_field( 'mz_' . $name, 'option' ) : null;
	if ( null === $val || '' === $val || false === $val ) {
		$d   = mz_defaults();
		$val = null !== $default ? $default : ( isset( $d[ $name ] ) ? $d[ $name ] : '' );
	}
	$cache[ $name ] = $val;
	return $val;
}

/** Контакти та дані сайту з урахуванням мови. */
function mz_site() {
	$ru = 'ru' === mz_lang();
	return array(
		'name'       => mz_opt( $ru ? 'name_ru' : 'name' ),
		'cert'       => mz_opt( 'cert' ),
		'years'      => mz_opt( 'years' ),
		'phone'      => mz_opt( 'phone' ),
		'phone_href' => mz_opt( 'phone_href' ),
		'email'      => mz_opt( 'email' ),
		'address'    => mz_opt( $ru ? 'address_ru' : 'address' ),
		'hours'      => mz_opt( $ru ? 'hours_ru' : 'hours' ),
		'telegram'   => mz_opt( 'telegram' ),
		'viber'      => mz_opt( 'viber' ),
		'whatsapp'   => mz_opt( 'whatsapp' ),
	);
}

/** Поле ACF сторінки з префіксом mz_. */
function mz_field( $name, $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	return get_field( 'mz_' . $name, $post_id ? $post_id : get_the_ID() );
}

/**
 * Рядки репітера ACF (порожнє значення → []).
 * $need — поле, без якого рядок не показується (напр. неперекладений рядок, доданий в іншій мові).
 */
function mz_rows( $v, $need = '' ) {
	if ( ! is_array( $v ) ) {
		return array();
	}
	return array_values(
		array_filter(
			$v,
			function ( $r ) use ( $need ) {
				return is_array( $r ) && ( '' === $need || ( isset( $r[ $need ] ) && '' !== trim( (string) $r[ $need ] ) ) );
			}
		)
	);
}

/**
 * Список із поля-репітера (рядки з підполем text). Підтримує і старий формат — текст «по рядку на пункт».
 * $v — значення поля (масив рядків або рядок).
 */
function mz_list( $v ) {
	if ( is_array( $v ) ) {
		$out = array();
		foreach ( $v as $row ) {
			$t = is_array( $row ) ? ( isset( $row['text'] ) ? $row['text'] : '' ) : $row;
			$t = trim( (string) $t );
			if ( '' !== $t ) {
				$out[] = $t;
			}
		}
		return $out;
	}
	return mz_lines( $v );
}

/** Список з поля сторінки (з підстраховкою для значень, збережених до переходу на репітери). */
function mz_list_field( $name, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$list    = mz_list( mz_field( $name, $post_id ) );
	if ( ! $list ) {
		$raw = get_post_meta( $post_id, 'mz_' . $name, true );
		if ( is_string( $raw ) && '' !== $raw && ! is_numeric( $raw ) ) {
			$list = mz_lines( $raw );
		}
	}
	return $list;
}

/**
 * HTML з візуального редактора. Старий формат (абзаци через порожній рядок) теж підтримується.
 */
function mz_html( $v ) {
	$v = trim( (string) $v );
	if ( '' === $v ) {
		return '';
	}
	if ( ! preg_match( '/<(p|ul|ol|h[1-6]|blockquote|div|table)[\s>]/i', $v ) ) {
		$v = implode( "\n", array_map( function ( $p ) { return '<p>' . $p . '</p>'; }, mz_paras( $v ) ) );
	}
	return mz_rich( $v );
}

/** Вміст одного абзацу без обгортки <p> (для елементів, де <p> із класом уже є в розмітці). */
function mz_inline( $v ) {
	$h = mz_rich( trim( (string) $v ) );
	$h = preg_replace( '#</p>\s*<p[^>]*>#i', '<br>', $h );
	return trim( preg_replace( '#</?p[^>]*>#i', '', $h ) );
}

/** Чи показувати секцію (вкладка «Секції сторінки»). Не збережено — так. */
function mz_show( $name, $post_id = null ) {
	$v = mz_field( 'show_' . $name, $post_id );
	return null === $v || '' === $v || (bool) $v;
}

/** Текст «по рядку на пункт» → масив. */
function mz_lines( $text ) {
	if ( is_array( $text ) ) {
		return $text;
	}
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $l ) {
		$l = trim( $l );
		if ( '' !== $l ) {
			$out[] = $l;
		}
	}
	return $out;
}

/** Текст з абзацами (розділені порожнім рядком) → масив абзаців. */
function mz_paras( $text ) {
	$out = array();
	foreach ( preg_split( '/\R\s*\R/', trim( (string) $text ) ) as $p ) {
		$p = trim( $p );
		if ( '' !== $p ) {
			$out[] = $p;
		}
	}
	return $out;
}

/**
 * HTML з полів: дозволені теги + посилання {{url:ключ}} → адреса сторінки поточної мови.
 * Ключі: home, keysy, thanks або ключ послуги (chip-karta-vodiya, kod-95, ...).
 */
function mz_rich( $html ) {
	$html = preg_replace_callback(
		'/\{\{url:([a-z0-9-]+)\}\}/',
		function ( $m ) {
			return esc_url( mz_url( $m[1] ) );
		},
		(string) $html
	);
	return wp_kses_post( $html );
}

/** URL зображення теми. */
function mz_img( $file ) {
	return MZ_URI . '/assets/img/' . $file;
}

/** Сторінка за ключем (meta mz_key) у потрібній мові. */
function mz_page_by_key( $key, $lang = null ) {
	static $cache = array();
	$lang = $lang ? $lang : mz_lang();
	$ck   = $key . '|' . $lang;
	if ( array_key_exists( $ck, $cache ) ) {
		return $cache[ $ck ];
	}
	$args = array(
		'post_type'        => 'page',
		'post_status'      => 'publish',
		'numberposts'      => 1,
		'meta_key'         => 'mz_key',
		'meta_value'       => $key,
		'suppress_filters' => false,
		'no_found_rows'    => true,
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$args['lang'] = $lang;
	}
	$p              = get_posts( $args );
	$cache[ $ck ]   = $p ? $p[0] : null;
	return $cache[ $ck ];
}

/** Послуги поточної мови в порядку меню. */
function mz_services() {
	static $cache = array();
	$lang = mz_lang();
	if ( isset( $cache[ $lang ] ) ) {
		return $cache[ $lang ];
	}
	$args = array(
		'post_type'        => 'page',
		'post_status'      => 'publish',
		'numberposts'      => -1,
		'meta_key'         => '_wp_page_template',
		'meta_value'       => 'template-service.php',
		'orderby'          => 'menu_order',
		'order'            => 'ASC',
		'suppress_filters' => false,
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$args['lang'] = $lang;
	}
	$out = array();
	foreach ( get_posts( $args ) as $p ) {
		$out[] = array(
			'id'         => $p->ID,
			'key'        => get_post_meta( $p->ID, 'mz_key', true ),
			'nav'        => mz_field( 'nav', $p->ID ) ?: get_the_title( $p ),
			'icon'       => mz_field( 'icon', $p->ID ) ?: 'ic-scales',
			'category'   => mz_field( 'category', $p->ID ),
			'home_items' => mz_list_field( 'home_items', $p->ID ),
			'url'        => get_permalink( $p ),
		);
	}
	$cache[ $lang ] = $out;
	return $out;
}

function mz_service_by_key( $key ) {
	foreach ( mz_services() as $s ) {
		if ( $s['key'] === $key ) {
			return $s;
		}
	}
	return null;
}

/** Послуга за ID сторінки (будь-якої мови → поточна мова). */
function mz_service_by_id( $id ) {
	if ( function_exists( 'pll_get_post' ) ) {
		$t = pll_get_post( $id, mz_lang() );
		if ( $t ) {
			$id = $t;
		}
	}
	foreach ( mz_services() as $s ) {
		if ( (int) $s['id'] === (int) $id ) {
			return $s;
		}
	}
	return null;
}

/** Адреса сторінки за ключем у поточній мові. */
function mz_url( $key, $hash = '' ) {
	$url = '';
	if ( 'home' === $key ) {
		$url = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
	} elseif ( 'keysy' === $key ) {
		$url = get_post_type_archive_link( 'mz_case' );
	} else {
		$s = mz_service_by_key( $key );
		if ( $s ) {
			$url = $s['url'];
		} else {
			$p = mz_page_by_key( $key );
			if ( $p ) {
				$url = get_permalink( $p );
			}
		}
	}
	if ( ! $url ) {
		$url = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
	}
	return $url . $hash;
}

/** Чи є опубліковані кейси поточної мови (інакше розділ «Кейси» не показується). */
function mz_has_cases() {
	static $cache = array();
	$lang = mz_lang();
	if ( ! isset( $cache[ $lang ] ) ) {
		$args = array(
			'post_type'        => 'mz_case',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
		);
		if ( function_exists( 'pll_current_language' ) ) {
			$args['lang'] = $lang;
		}
		$cache[ $lang ] = (bool) get_posts( $args );
	}
	return $cache[ $lang ];
}

/** Опубліковані кейси поточної мови (необов'язково — лише для послуги $service_id). */
function mz_cases( $service_id = 0, $limit = -1, $exclude = 0 ) {
	$args = array(
		'post_type'        => 'mz_case',
		'post_status'      => 'publish',
		'numberposts'      => $limit,
		'orderby'          => 'date',
		'order'            => 'DESC',
		'suppress_filters' => false,
		'exclude'          => $exclude ? array( $exclude ) : array(),
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$args['lang'] = mz_lang();
	}
	if ( $service_id ) {
		$args['meta_key']   = 'mz_case_service';
		$args['meta_value'] = $service_id;
	}
	return get_posts( $args );
}

/** Перемикач мов: [code, label, name, url, active]. */
function mz_lang_links() {
	$labels = array(
		'uk' => array( 'UA', 'Українська' ),
		'ru' => array( 'RU', 'Русский' ),
	);
	$out = array();
	if ( function_exists( 'pll_the_languages' ) ) {
		$langs = pll_the_languages(
			array(
				'raw'                    => 1,
				'hide_if_empty'          => 0,
				'hide_if_no_translation' => 0,
				'hide_current'           => 0,
			)
		);
		foreach ( (array) $langs as $l ) {
			$code  = $l['slug'];
			$out[] = array(
				'code'   => $code,
				'label'  => isset( $labels[ $code ] ) ? $labels[ $code ][0] : strtoupper( $code ),
				'name'   => isset( $labels[ $code ] ) ? $labels[ $code ][1] : $l['name'],
				'url'    => $l['url'],
				'active' => ! empty( $l['current_lang'] ),
			);
		}
	}
	return $out;
}

/** Тип сторінки для класу body та логіки шаблонів. */
function mz_kind() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_post_type_archive( 'mz_case' ) ) {
		return 'cases';
	}
	if ( is_singular( 'mz_case' ) ) {
		return 'case';
	}
	if ( is_page_template( 'template-service.php' ) ) {
		return 'service';
	}
	if ( is_page_template( 'template-thanks.php' ) ) {
		return 'thanks';
	}
	return 'page';
}

/** Чи є на сторінці блок заявки #contact (інакше кнопки «Консультація» відкривають модалку). */
function mz_has_contact_section() {
	return in_array( mz_kind(), array( 'home', 'service' ), true );
}

/** Теми звернень для модального вікна. */
function mz_topics() {
	$generic = array( 'Безкоштовна консультація', 'Зворотний дзвінок', 'Захист у суді', 'Інше питання' );
	$generic = array_map( 'mz_t', $generic );
	$topics  = array();
	$kind    = mz_kind();
	if ( 'service' === $kind ) {
		$other = mz_t( 'Інше питання' );
		foreach ( mz_rows( mz_field( 'situations' ), 'title' ) as $it ) {
			if ( ! empty( $it['topic'] ) && $it['topic'] !== $other ) {
				$topics[] = $it['topic'];
			}
		}
		foreach ( mz_rows( mz_field( 'tabs' ), 'label' ) as $it ) {
			if ( ! empty( $it['topic'] ) && $it['topic'] !== $other ) {
				$topics[] = $it['topic'];
			}
		}
		$topics = array_merge( $topics, $generic );
	} elseif ( 'home' === $kind ) {
		$topics = array_merge( wp_list_pluck( mz_services(), 'nav' ), $generic, array( mz_t( 'Інше' ) ) );
	} else {
		$topics = array_merge( $generic, wp_list_pluck( mz_services(), 'nav' ) );
	}
	return array_values( array_unique( $topics ) );
}

/** Іконки (класи ic-*) для вибору в адмінці. */
function mz_icon_choices() {
	$icons = array( 'ic-alert', 'ic-appeal', 'ic-briefcase', 'ic-chat', 'ic-check-circle', 'ic-clock', 'ic-coins', 'ic-consult', 'ic-court', 'ic-documents', 'ic-eye', 'ic-factory', 'ic-folder', 'ic-globe', 'ic-id-card', 'ic-lock', 'ic-medical', 'ic-phone', 'ic-question', 'ic-quill', 'ic-receipt', 'ic-scales', 'ic-search', 'ic-shield-star', 'ic-smartphone', 'ic-steering', 'ic-target', 'ic-truck', 'ic-user', 'ic-user-focus', 'ic-weight' );
	return array_combine( $icons, $icons );
}

/** Посилання на політику конфіденційності (якщо сторінка опублікована). */
function mz_privacy_url() {
	$id = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		if ( function_exists( 'pll_get_post' ) ) {
			$t = pll_get_post( $id, mz_lang() );
			if ( $t && 'publish' === get_post_status( $t ) ) {
				$id = $t;
			}
		}
		return get_permalink( $id );
	}
	return '';
}

/**
 * Ключ сторінки послуги (meta mz_key) — для посилань {{url:ключ}} між сторінками.
 * Нові послуги, створені в адмінці, отримують ключ із перекладу (якщо є) або зі слагу.
 */
add_action(
	'save_post_page',
	function ( $id, $post ) {
		if ( wp_is_post_revision( $id ) || 'template-service.php' !== get_post_meta( $id, '_wp_page_template', true ) || get_post_meta( $id, 'mz_key', true ) ) {
			return;
		}
		$key = '';
		if ( function_exists( 'pll_get_post_translations' ) ) {
			foreach ( pll_get_post_translations( $id ) as $tid ) {
				if ( (int) $tid !== (int) $id && get_post_meta( $tid, 'mz_key', true ) ) {
					$key = get_post_meta( $tid, 'mz_key', true );
					break;
				}
			}
		}
		$key = $key ? $key : $post->post_name;
		if ( $key ) {
			update_post_meta( $id, 'mz_key', $key );
		}
	},
	20,
	2
);
