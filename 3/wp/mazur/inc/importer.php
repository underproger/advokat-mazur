<?php
/**
 * Імпорт контенту дизайну 3 (Інструменти → «Мазур: імпорт контенту»).
 *
 * Що робить (можна запускати повторно — оновлює, не дублює):
 *  1. Polylang: мови UA (за замовчуванням, без префікса в URL) і RU (/ru/), без редиректу за мовою браузера.
 *  2. Contact Form 7: форма «Мазур: заявка з сайту» (отримувач — як у старій формі).
 *  3. Послуги: наявні сторінки оновлюються НА ТИХ САМИХ адресах (шаблон «Послуга» + поля ACF),
 *     нові — створюються; RU-переклади створюються і зв'язуються з UA.
 *  4. Головна, подяка (/thanks/), їхні RU-переклади.
 *  5. Кейси — чернетками (у макеті вони демонстраційні).
 *  6. Налаштування сайту: ID форми, код it-rating у <head>.
 * Дані: import/content.json (генерується 3/_build/export_wp.py).
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_management_page( 'Мазур: імпорт контенту', 'Мазур: імпорт контенту', 'manage_options', 'mz-import', 'mz_import_page' );
	}
);

function mz_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>Мазур: імпорт контенту (дизайн 3)</h1>';
	if ( isset( $_POST['mz_import'] ) && check_admin_referer( 'mz_import' ) ) {
		$log = mz_import( array( 'overwrite' => ! empty( $_POST['mz_overwrite'] ) ) );
		echo '<h2>Результат</h2><pre style="background:#fff;padding:16px;max-height:600px;overflow:auto;border:1px solid #ccd0d4">' . esc_html( implode( "\n", $log ) ) . '</pre>';
	}
	$checks = array(
		'ACF PRO'        => function_exists( 'acf_add_local_field_group' ) && class_exists( 'acf_field_repeater' ),
		'Contact Form 7' => class_exists( 'WPCF7_ContactForm' ),
		'Polylang'       => function_exists( 'PLL' ),
	);
	echo '<p>Перед імпортом зробіть резервну копію (Duplicator). Наявні сторінки послуг залишаться на своїх адресах, їхній контент буде замінено новим.</p><ul>';
	foreach ( $checks as $name => $ok ) {
		echo '<li>' . ( $ok ? '✅' : '⚠️' ) . ' ' . esc_html( $name ) . ( $ok ? '' : ' — не активний' ) . '</li>';
	}
	echo '</ul><form method="post">';
	wp_nonce_field( 'mz_import' );
	echo '<p><label><input type="checkbox" name="mz_overwrite" value="1"> Перезаписати поля, які вже змінювали в адмінці (за замовчуванням оновлюються лише сторінки, ще не імпортовані)</label></p>';
	submit_button( 'Імпортувати', 'primary', 'mz_import' );
	echo '</form></div>';
}

/** Головна функція імпорту. Повертає журнал. */
function mz_import( $opts = array() ) {
	$opts = wp_parse_args( $opts, array( 'overwrite' => false ) );
	$log  = array();
	$file = MZ_DIR . '/import/content.json';
	$data = json_decode( (string) file_get_contents( $file ), true );
	if ( ! $data ) {
		return array( 'Помилка: не прочитано ' . $file );
	}
	if ( ! function_exists( 'update_field' ) ) {
		return array( 'Помилка: потрібен ACF PRO.' );
	}
	// поля ACF теми мають бути зареєстровані (acf/init)
	if ( ! did_action( 'acf/init' ) ) {
		do_action( 'acf/init' );
	}

	$pll   = mz_import_polylang( $log );
	$langs = $pll ? array( 'uk', 'ru' ) : array( 'uk' );

	$form_id = mz_import_cf7( $log );
	if ( $form_id && ! get_field( 'mz_cf7_form', 'option' ) ) {
		update_field( 'field_mz_opt_cf7_form', $form_id, 'option' );
	}
	update_option( 'mz_cf7_form', $form_id );
	if ( ! get_field( 'mz_head_code', 'option' ) ) {
		// код зі старої теми (header.php)
		update_field( 'field_mz_opt_head_code', '<meta name="it-rating" content="it-rat-1320b27f41502b183e5f4961dd00e5c0" />', 'option' );
		$log[] = 'Налаштування: у «Код у <head>» додано it-rating зі старої теми.';
	}

	// старі групи полів ACF (тема impulsedesign) — вимикаються, щоб не плутали в редакторі;
	// дані старих полів лишаються в базі, групу можна знову увімкнути в ACF → Групи полів
	foreach ( get_posts( array( 'post_type' => 'acf-field-group', 'post_status' => 'publish', 'numberposts' => -1 ) ) as $g ) {
		wp_update_post( array( 'ID' => $g->ID, 'post_status' => 'acf-disabled' ) );
		$log[] = 'ACF: вимкнено стару групу полів «' . $g->post_title . '»';
	}

	// ---------------------------------------------------------------- послуги
	$svc_ids = array();
	foreach ( $data['services'] as $s ) {
		$tr = array();
		foreach ( $langs as $lang ) {
			$slug = $s['slug'][ $lang ];
			$d    = $s[ $lang ];
			$id   = mz_import_find( 'page', $s['key'], $lang, $slug );
			$new  = ! $id;
			$arr  = array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $d['nav'],
				'post_name'   => $slug,
				'menu_order'  => (int) $s['order'],
				'post_parent' => 0,
			);
			if ( $new ) {
				$arr['post_content'] = '';
				$id                  = wp_insert_post( $arr, true );
			} else {
				$arr['ID'] = $id;
				wp_update_post( $arr );
			}
			if ( is_wp_error( $id ) ) {
				$log[] = 'Помилка (' . $s['key'] . ' ' . $lang . '): ' . $id->get_error_message();
				continue;
			}
			if ( $pll ) {
				pll_set_post_language( $id, $lang );
			}
			update_post_meta( $id, '_wp_page_template', 'template-service.php' );
			$was = get_post_meta( $id, 'mz_imported', true );
			update_post_meta( $id, 'mz_key', $s['key'] );
			if ( ! $was || $opts['overwrite'] ) {
				mz_import_service_fields( $id, $d, $log );
				update_post_meta( $id, 'mz_imported', gmdate( 'c' ) );
			}
			$tr[ $lang ] = $id;
			$log[]       = sprintf( '%s %s: %s → /%s%s/ (ID %d)', $new ? '+ створено' : '✓ оновлено', strtoupper( $lang ), $d['nav'], 'ru' === $lang ? 'ru/' : '', get_post_field( 'post_name', $id ), $id );
		}
		if ( $pll && count( $tr ) > 1 ) {
			pll_save_post_translations( $tr );
		}
		$svc_ids[ $s['key'] ] = $tr;
	}

	// ---------------------------------------------------------------- головна
	$front = (int) get_option( 'page_on_front' );
	if ( ! $front || 'page' !== get_option( 'show_on_front' ) ) {
		$front = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Головна' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front );
	}
	$home_tr = array( 'uk' => $front );
	foreach ( $langs as $lang ) {
		$id = 'uk' === $lang ? $front : mz_import_find( 'page', 'home', $lang, $data['pages']['home'][ $lang ]['slug'] );
		if ( ! $id ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $data['pages']['home'][ $lang ]['title'],
					'post_name'   => $data['pages']['home'][ $lang ]['slug'],
				)
			);
		}
		if ( $pll ) {
			pll_set_post_language( $id, $lang );
		}
		update_post_meta( $id, 'mz_key', 'home' );
		update_post_meta( $id, '_wp_page_template', 'template-home.php' );
		if ( ! get_post_meta( $id, 'mz_imported', true ) || $opts['overwrite'] ) {
			$h = $data['home'][ $lang ];
			mz_update( 'home', 'meta_title', $h['meta_title'], $id );
			mz_update( 'home', 'meta_desc', $h['meta_desc'], $id );
			mz_update( 'home', 'home_reviews', mz_import_reviews( $h['reviews'] ), $id );
			mz_update( 'home', 'home_faq', $h['faq'], $id );
			update_post_meta( $id, 'mz_imported', gmdate( 'c' ) );
		}
		$home_tr[ $lang ] = $id;
		$log[]            = sprintf( '✓ Головна %s (ID %d)', strtoupper( $lang ), $id );
	}
	if ( $pll && count( $home_tr ) > 1 ) {
		pll_save_post_translations( $home_tr );
	}

	// ---------------------------------------------------------------- подяка
	$th_tr = array();
	foreach ( $langs as $lang ) {
		$p  = $data['pages']['thanks'][ $lang ];
		$id = mz_import_find( 'page', 'thanks', $lang, $p['slug'] );
		if ( ! $id ) {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $p['title'], 'post_name' => $p['slug'] ) );
			$log[] = '+ створено подяку ' . strtoupper( $lang ) . ' (ID ' . $id . ')';
		}
		if ( $pll ) {
			pll_set_post_language( $id, $lang );
		}
		update_post_meta( $id, 'mz_key', 'thanks' );
		update_post_meta( $id, '_wp_page_template', 'template-thanks.php' );
		if ( ! get_post_meta( $id, 'mz_imported', true ) || $opts['overwrite'] ) {
			mz_update( 'thx', 'meta_title', $p['meta_title'], $id );
			mz_update( 'thx', 'h1', $p['h1'], $id );
			mz_update( 'thx', 'h1_gold', $p['h1_gold'], $id );
			mz_update( 'thx', 'lead', $p['lead'], $id );
			mz_update( 'thx', 'thanks_steps', implode( "\n", $p['steps'] ), $id );
			update_post_meta( $id, 'mz_imported', gmdate( 'c' ) );
		}
		$th_tr[ $lang ] = $id;
		$log[]          = sprintf( '✓ Подяка %s → /%s%s/', strtoupper( $lang ), 'ru' === $lang ? 'ru/' : '', get_post_field( 'post_name', $id ) );
	}
	if ( $pll && count( $th_tr ) > 1 ) {
		pll_save_post_translations( $th_tr );
	}

	// ---------------------------------------------------------------- кейси (чернетки)
	foreach ( $data['cases'] as $c ) {
		$tr = array();
		foreach ( $langs as $lang ) {
			$d  = $c[ $lang ];
			$id = mz_import_find( 'mz_case', 'case-' . $c['key'], $lang, $c['slug'][ $lang ] );
			if ( ! $id ) {
				$id = wp_insert_post(
					array(
						'post_type'   => 'mz_case',
						'post_status' => 'draft', // ДЕМО-кейси з макета — не публікуються
						'post_title'  => $d['title'],
						'post_name'   => $c['slug'][ $lang ],
					)
				);
			}
			if ( $pll ) {
				pll_set_post_language( $id, $lang );
			}
			update_post_meta( $id, 'mz_key', 'case-' . $c['key'] );
			if ( ! get_post_meta( $id, 'mz_imported', true ) || $opts['overwrite'] ) {
				$svc = isset( $svc_ids[ $c['service'] ][ $lang ] ) ? $svc_ids[ $c['service'] ][ $lang ] : 0;
				mz_update( 'case', 'case_service', $svc, $id );
				mz_update( 'case', 'case_date', $d['date'], $id );
				mz_update( 'case', 'case_read', $d['read'], $id );
				mz_update( 'case', 'case_result_short', $d['result_short'], $id );
				mz_update( 'case', 'case_lead', $d['lead'], $id );
				mz_update( 'case', 'case_quote', $d['quote'], $id );
				mz_update( 'case', 'case_problem', implode( "\n\n", $d['problem'] ), $id );
				mz_update( 'case', 'case_work_intro', $d['work_intro'], $id );
				mz_update( 'case', 'case_work', implode( "\n", $d['work'] ), $id );
				mz_update( 'case', 'case_result', implode( "\n\n", $d['result'] ), $id );
				update_post_meta( $id, 'mz_imported', gmdate( 'c' ) );
			}
			$tr[ $lang ] = $id;
		}
		if ( $pll && count( $tr ) > 1 ) {
			pll_save_post_translations( $tr );
		}
	}
	$log[] = '✓ Кейси: ' . count( $data['cases'] ) . ' (чернетки — демо з макета; опублікуйте реальні, і розділ «Кейси» з’явиться в меню).';

	if ( $pll ) {
		// усе, що лишилося без мови (старі записи, службові сторінки), — українською
		if ( method_exists( PLL()->model, 'set_language_in_mass' ) ) {
			PLL()->model->set_language_in_mass();
		}
	}
	// кеш мов Polylang (містить ID головної кожної мови) і правила перезапису
	// перебудуються на наступному запиті
	if ( $pll && method_exists( PLL()->model, 'clean_languages_cache' ) ) {
		PLL()->model->clean_languages_cache();
	}
	delete_transient( 'pll_languages_list' );
	delete_option( 'rewrite_rules' );
	$log[] = 'Готово. Перевірте сторінки послуг і «Налаштування сайту».';
	return $log;
}

/** update_field за ключем поля групи. */
function mz_update( $group, $name, $value, $post_id ) {
	return update_field( 'field_mz_' . $group . '_' . $name, $value, $post_id );
}

/** Знайти запис: за mz_key + мовою, інакше за слагом (UA — наявні сторінки старого сайту). */
function mz_import_find( $type, $key, $lang, $slug ) {
	$args = array(
		'post_type'        => $type,
		'post_status'      => array( 'publish', 'draft', 'private', 'pending' ),
		'numberposts'      => 1,
		'fields'           => 'ids',
		'meta_key'         => 'mz_key',
		'meta_value'       => $key,
		'suppress_filters' => false,
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$args['lang'] = $lang;
	}
	$ids = get_posts( $args );
	if ( $ids ) {
		return (int) $ids[0];
	}
	if ( $slug ) {
		$p = get_page_by_path( $slug, OBJECT, $type );
		if ( $p && ( ! function_exists( 'pll_get_post_language' ) || ! pll_get_post_language( $p->ID ) || pll_get_post_language( $p->ID ) === $lang ) ) {
			return (int) $p->ID;
		}
	}
	return 0;
}

/** ID вкладення за шляхом у uploads (зображення з медіатеки чинного сайту). */
function mz_import_attachment( $path, &$log ) {
	if ( ! $path ) {
		return 0;
	}
	global $wpdb;
	$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1", $path ) );
	if ( ! $id ) {
		$log[] = '  ! не знайдено зображення в медіатеці: ' . $path;
	}
	return $id;
}

function mz_import_reviews( $rows ) {
	$out = array();
	foreach ( (array) $rows as $r ) {
		$out[] = array(
			'initials' => $r['initials'],
			'name'     => $r['name'],
			'tag'      => $r['tag'],
			'date'     => $r['date'],
			'src'      => $r['src'],
			'text'     => $r['text'],
		);
	}
	return $out;
}

function mz_import_service_fields( $id, $d, &$log ) {
	$u     = function ( $name, $value ) use ( $id ) {
		mz_update( 'svc', $name, $value, $id );
	};
	$lines = function ( $a ) {
		return implode( "\n", (array) $a );
	};
	$paras = function ( $a ) {
		return implode( "\n\n", (array) $a );
	};
	$u( 'nav', $d['nav'] );
	$u( 'icon', $d['icon'] );
	$u( 'category', $d['category'] );
	$u( 'home_items', $lines( $d['home_items'] ) );
	$u( 'meta_title', $d['title'] );
	$u( 'meta_desc', $d['description'] );
	$u( 'eyebrow', isset( $d['eyebrow'] ) ? $d['eyebrow'] : '' );
	$u( 'h1', $d['h1'] );
	$u( 'h1_gold', $d['h1_gold'] );
	$u( 'lead', $d['lead'] );
	$u( 'bullets', $lines( $d['bullets'] ) );
	$u( 'hero_cta', isset( $d['hero_cta'] ) ? $d['hero_cta'] : '' );
	$u( 'hero_image', isset( $d['hero_image'] ) ? mz_import_attachment( $d['hero_image'], $log ) : '' );
	$u( 'hero_preset', $d['hero_preset'] );
	$u( 'hero_alt', $d['hero_alt'] );
	$u( 'years_text', $d['years_text'] );
	$u( 'lawyer_badge', ! empty( $d['lawyer_badge'] ) ? 1 : 0 );

	$rows = array();
	foreach ( isset( $d['showcase'] ) ? $d['showcase'] : array() as $b ) {
		$rows[] = array(
			'image'   => mz_import_attachment( $b['image'], $log ),
			'eyebrow' => $b['eyebrow'],
			'title'   => $b['title'],
			'p'       => $b['p'],
			'list'    => $lines( $b['list'] ),
			'cta'     => $b['cta'],
			'topic'   => $b['topic'],
		);
	}
	$u( 'showcase_title', isset( $d['showcase_title'] ) ? $d['showcase_title'] : '' );
	$u( 'showcase_gold', isset( $d['showcase_gold'] ) ? $d['showcase_gold'] : '' );
	$u( 'showcase_text', isset( $d['showcase_text'] ) ? $d['showcase_text'] : '' );
	$u( 'showcase', $rows );

	$u( 'situations_text', $d['situations_text'] );
	$u( 'situations', $d['situations'] );
	$u( 'help_gold', $d['help_gold'] );
	$u( 'help_text', $d['help_text'] );
	$rows = array();
	foreach ( $d['tabs'] as $t ) {
		$rows[] = array(
			'label'     => $t['label'],
			'icon'      => $t['icon'],
			'h3'        => $t['h3'],
			'p'         => $paras( $t['p'] ),
			'list'      => $lines( $t['list'] ),
			'note_icon' => $t['note_icon'],
			'note'      => $t['note'],
			'cta'       => $t['cta'],
			'topic'     => $t['topic'],
		);
	}
	$u( 'tabs', $rows );
	$u( 'steps', $d['steps'] );

	$p = $d['price'];
	$u( 'price_text', isset( $p['text'] ) ? $p['text'] : '' );
	foreach ( array( 'docs', 'full' ) as $k ) {
		$u( 'price_' . $k . '_title', isset( $p[ $k . '_title' ] ) ? $p[ $k . '_title' ] : '' );
		$u( 'price_' . $k . '_value', isset( $p[ $k . '_value' ] ) ? $p[ $k . '_value' ] : '' );
		$u( 'price_' . $k . '_hint', isset( $p[ $k . '_hint' ] ) ? $p[ $k . '_hint' ] : '' );
		$u( 'price_' . $k, $lines( isset( $p[ $k ] ) ? $p[ $k ] : array() ) );
	}
	$u( 'about_text', $d['about_text'] );
	$u( 'reviews_label', $d['reviews_label'] );
	$u( 'reviews_gold', $d['reviews_gold'] );
	$u( 'reviews', mz_import_reviews( $d['reviews'] ) );

	$seo = $d['seo'];
	$u( 'seo_h2', $seo['h2'] );
	$u( 'seo_intro', $seo['intro'] );
	$u( 'seo_p', $seo['p'] );
	$u( 'seo_when_title', $seo['when_title'] );
	$u( 'seo_when', $lines( $seo['when'] ) );
	$u( 'seo_quote', $seo['quote'] );
	$rows = array();
	foreach ( $seo['sections'] as $sec ) {
		$rows[] = array(
			'sid' => $sec['id'],
			'toc' => $sec['toc'],
			'h3'  => $sec['h3'],
			'p'   => $paras( $sec['p'] ),
		);
	}
	$u( 'seo_sections', $rows );
	$u( 'seo_tips_title', $seo['tips_title'] );
	$u( 'seo_tips', $seo['tips'] );
	$u( 'faq', $d['faq'] );
	$u( 'final_h2', $d['final_h2'] );
	$u( 'final_gold', $d['final_gold'] );
	$u( 'docs', $lines( $d['docs'] ) );
}

/** Polylang: мови та налаштування URL. Повертає true, якщо Polylang активний. */
function mz_import_polylang( &$log ) {
	if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_set_post_language' ) ) {
		$log[] = '! Polylang не активний — імпортується лише українська версія.';
		return false;
	}
	$model = PLL()->model;
	$want  = array(
		array( 'name' => 'Українська', 'slug' => 'uk', 'locale' => 'uk', 'rtl' => false, 'term_group' => 0, 'flag' => 'ua' ),
		array( 'name' => 'Русский', 'slug' => 'ru', 'locale' => 'ru_RU', 'rtl' => false, 'term_group' => 1, 'flag' => 'ru' ),
	);
	foreach ( $want as $l ) {
		$exists = in_array( $l['slug'], (array) pll_languages_list( array( 'fields' => 'slug' ) ), true );
		if ( $exists ) {
			continue;
		}
		$r = isset( $model->languages ) && method_exists( $model->languages, 'add' ) ? $model->languages->add( $l ) : $model->add_language( $l );
		$log[] = is_wp_error( $r ) ? '! Polylang: ' . $r->get_error_message() : '+ Polylang: мова ' . $l['name'];
	}
	// UA — за замовчуванням і без /uk/ в адресах (старі URL не змінюються), RU — /ru/
	$set = array(
		'default_lang'  => 'uk',
		'hide_default'  => true,
		'force_lang'    => 1,
		'rewrite'       => true,
		'redirect_lang' => true,  // головна RU — /ru/ (а не /ru/glavnaya/)
		'browser'       => false, // без перемикання мови за налаштуваннями браузера
		'media_support' => false,
	);
	$opt = PLL()->options;
	if ( is_object( $opt ) && method_exists( $opt, 'set' ) ) {
		foreach ( $set as $k => $v ) {
			$opt->set( $k, $v );
		}
		if ( method_exists( $opt, 'save' ) ) {
			$opt->save();
		}
	} else {
		$o = get_option( 'polylang', array() );
		update_option( 'polylang', array_merge( (array) $o, $set ) );
	}
	if ( method_exists( $model, 'clean_languages_cache' ) ) {
		$model->clean_languages_cache();
	}
	$log[] = '✓ Polylang: UA за замовчуванням (без префікса), RU — /ru/, без автоперемикання за мовою браузера.';
	return true;
}

/** CF7: форма для заявок теми. */
function mz_import_cf7( &$log ) {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		$log[] = '! Contact Form 7 не активний — заявки не відправлятимуться.';
		return 0;
	}
	$existing = get_posts(
		array(
			'post_type'   => 'wpcf7_contact_form',
			'numberposts' => 1,
			'meta_key'    => 'mz_key',
			'meta_value'  => 'mz-lead',
			'fields'      => 'ids',
		)
	);
	if ( $existing ) {
		$log[] = '✓ CF7: форма вже є (ID ' . $existing[0] . ').';
		return (int) $existing[0];
	}
	// отримувач і відправник — як у старій формі «Контактная форма 1»
	$recipient = 'advocatemazur@gmail.com';
	$sender    = '[_site_title] <advokatmazur@advokatmazur.com.ua>';
	$old       = get_posts( array( 'post_type' => 'wpcf7_contact_form', 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
	if ( $old ) {
		$m = get_post_meta( $old[0]->ID, '_mail', true );
		if ( ! empty( $m['recipient'] ) ) {
			$recipient = $m['recipient'];
		}
		if ( ! empty( $m['sender'] ) ) {
			$sender = $m['sender'];
		}
	}
	$form = WPCF7_ContactForm::get_template(
		array(
			'title'  => 'Мазур: заявка з сайту',
			'locale' => 'uk',
		)
	);
	$form->set_properties(
		array(
			'form' => "[text* your-name]\n[tel* your-phone]\n[text your-topic]\n[textarea your-message]\n[text form-location]\n[text page-url]\n[text page-title]\n[text lang]\n[text utm]\n[submit \"Надіслати\"]",
			'mail' => array(
				'active'             => true,
				'subject'            => 'Заявка з сайту: [your-topic]',
				'sender'             => $sender,
				'recipient'          => $recipient,
				'body'               => "Ім'я: [your-name]\nТелефон: [your-phone]\nТема: [your-topic]\n\nПовідомлення:\n[your-message]\n\n--\nФорма: [form-location]\nСторінка: [page-title]\n[page-url]\nМова: [lang]\nРеклама / UTM: [utm]\n\nНадіслано з сайту [_site_title] ([_site_url])",
				'additional_headers' => '',
				'attachments'        => '',
				'use_html'           => false,
				'exclude_blank'      => true,
			),
			'messages' => array(
				'mail_sent_ok'    => 'Дякую. Заявку отримано.',
				'mail_sent_ng'    => 'Не вдалося надіслати заявку. Зателефонуйте, будь ласка.',
				'validation_error'=> 'Перевірте ім’я і телефон.',
				'invalid_required'=> 'Заповніть поле.',
				'invalid_tel'     => 'Перевірте номер телефону.',
			),
		)
	);
	$id = $form->save();
	update_post_meta( $id, 'mz_key', 'mz-lead' );
	$log[] = '+ CF7: створено форму «Мазур: заявка з сайту» (ID ' . $id . '), листи → ' . $recipient;
	return (int) $id;
}
