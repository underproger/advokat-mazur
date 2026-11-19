<?php
/**
 * Поля ACF (реєструються кодом — у базі нічого не створюється).
 * Імена полів мають префікс mz_, ключі — field_mz_*, щоб не перетинатися з полями старої теми.
 */

defined( 'ABSPATH' ) || exit;

$GLOBALS['mz_acf_g'] = 'x'; // префікс ключів поточної групи (svc, home, thx, case, opt)

/** Поле верхнього рівня. */
function mz_acf( $name, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => 'field_mz_' . $GLOBALS['mz_acf_g'] . '_' . $name,
			'name'  => 'mz_' . $name,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

/** Підполе репітера. */
function mz_acf_sub( $parent, $name, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => 'field_mz_' . $GLOBALS['mz_acf_g'] . '_' . $parent . '__' . $name,
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

function mz_acf_tab( $id, $label ) {
	return array(
		'key'       => 'field_mz_' . $GLOBALS['mz_acf_g'] . '_tab_' . $id,
		'label'     => $label,
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);
}

function mz_acf_lines( $name, $label, $parent = null ) {
	$extra = array(
		'rows'         => 4,
		'new_lines'    => '',
		'instructions' => 'По одному пункту в рядку.',
	);
	return $parent ? mz_acf_sub( $parent, $name, $label, 'textarea', $extra ) : mz_acf( $name, $label, 'textarea', $extra );
}

function mz_acf_paras( $name, $label, $parent = null ) {
	$extra = array(
		'rows'         => 5,
		'new_lines'    => '',
		'instructions' => 'Абзаци розділяйте порожнім рядком. Можна HTML-посилання; адреса іншої сторінки сайту — {{url:ключ}}, напр. {{url:kod-95}}.',
	);
	return $parent ? mz_acf_sub( $parent, $name, $label, 'textarea', $extra ) : mz_acf( $name, $label, 'textarea', $extra );
}

function mz_acf_icon( $name, $label, $parent = null ) {
	$extra = array(
		'choices'       => mz_icon_choices(),
		'default_value' => 'ic-scales',
		'ui'            => 0,
	);
	return $parent ? mz_acf_sub( $parent, $name, $label, 'select', $extra ) : mz_acf( $name, $label, 'select', $extra );
}

function mz_acf_repeater( $name, $label, $subs, $button = 'Додати' ) {
	return mz_acf(
		$name,
		$label,
		'repeater',
		array(
			'layout'       => 'block',
			'button_label' => $button,
			'sub_fields'   => $subs,
		)
	);
}

function mz_acf_reviews( $name ) {
	return mz_acf_repeater(
		$name,
		'Відгуки',
		array(
			mz_acf_sub( $name, 'initials', 'Ініціали (аватар)', 'text', array( 'wrapper' => array( 'width' => 20 ) ) ),
			mz_acf_sub( $name, 'name', 'Ім’я', 'text', array( 'wrapper' => array( 'width' => 40 ) ) ),
			mz_acf_sub( $name, 'tag', 'Послуга / тема', 'text', array( 'wrapper' => array( 'width' => 40 ) ) ),
			mz_acf_sub( $name, 'date', 'Дата (необов’язково)', 'text', array( 'wrapper' => array( 'width' => 30 ) ) ),
			mz_acf_sub( $name, 'src', 'Джерело (Google / Відгук клієнта)', 'text', array( 'wrapper' => array( 'width' => 70 ) ) ),
			mz_acf_sub( $name, 'text', 'Текст', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
		),
		'Додати відгук'
	);
}

function mz_acf_faq( $name ) {
	return mz_acf_repeater(
		$name,
		'Питання та відповіді',
		array(
			mz_acf_sub( $name, 'q', 'Питання' ),
			mz_acf_sub( $name, 'a', 'Відповідь (можна HTML)', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
		),
		'Додати питання'
	);
}

function mz_acf_meta() {
	return array(
		mz_acf( 'meta_title', 'SEO: title', 'text', array( 'instructions' => 'Тег &lt;title&gt;. Порожньо — назва сторінки.' ) ),
		mz_acf( 'meta_desc', 'SEO: description', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
	);
}

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// ------------------------------------------------------------ Налаштування сайту
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page(
				array(
					'page_title' => 'Налаштування сайту',
					'menu_title' => 'Налаштування сайту',
					'menu_slug'  => 'mz-settings',
					'capability' => 'manage_options',
					'icon_url'   => 'dashicons-admin-settings',
					'position'   => 59,
				)
			);
		}
		$d = mz_defaults();
		$GLOBALS['mz_acf_g'] = 'opt';
		acf_add_local_field_group(
			array(
				'key'      => 'group_mz_options',
				'title'    => 'Контакти, статистика, коди',
				'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'mz-settings' ) ) ),
				'fields'   => array(
					mz_acf_tab( 'opt_contacts', 'Контакти' ),
					mz_acf( 'phone', 'Телефон (як показувати)', 'text', array( 'placeholder' => $d['phone'] ) ),
					mz_acf( 'phone_href', 'Телефон для tel: (без пробілів)', 'text', array( 'placeholder' => $d['phone_href'] ) ),
					mz_acf( 'email', 'E-mail', 'text', array( 'placeholder' => $d['email'] ) ),
					mz_acf( 'address', 'Адреса (UA)', 'text', array( 'placeholder' => $d['address'] ) ),
					mz_acf( 'address_ru', 'Адреса (RU)', 'text', array( 'placeholder' => $d['address_ru'] ) ),
					mz_acf( 'hours', 'Години роботи (UA)', 'text', array( 'placeholder' => $d['hours'] ) ),
					mz_acf( 'hours_ru', 'Години роботи (RU)', 'text', array( 'placeholder' => $d['hours_ru'] ) ),
					mz_acf( 'telegram', 'Telegram (посилання)', 'text', array( 'placeholder' => $d['telegram'] ) ),
					mz_acf( 'viber', 'Viber (посилання)', 'text', array( 'placeholder' => $d['viber'] ) ),
					mz_acf( 'whatsapp', 'WhatsApp (посилання)', 'text', array( 'placeholder' => $d['whatsapp'] ) ),
					mz_acf_tab( 'opt_lawyer', 'Адвокат' ),
					mz_acf( 'name', 'Ім’я (UA)', 'text', array( 'placeholder' => $d['name'] ) ),
					mz_acf( 'name_ru', 'Ім’я (RU)', 'text', array( 'placeholder' => $d['name_ru'] ) ),
					mz_acf( 'cert', 'Номер свідоцтва', 'text', array( 'placeholder' => $d['cert'] ) ),
					mz_acf( 'years', 'Років практики', 'text', array( 'placeholder' => $d['years'] ) ),
					mz_acf_tab( 'opt_stats', 'Статистика і Google' ),
					mz_acf( 'stat_cases', 'Кількість справ (напр. 500+)', 'text', array( 'instructions' => 'Показується лише якщо заповнено — вкажіть реальну цифру від адвоката.' ) ),
					mz_acf( 'google_rating', 'Рейтинг у Google (напр. 5.0)', 'text', array( 'instructions' => 'Показується лише якщо заповнено.' ) ),
					mz_acf( 'google_count', 'Кількість відгуків у Google (число)', 'text' ),
					mz_acf( 'google_url', 'Посилання на відгуки в Google', 'url' ),
					mz_acf( 'review_url', 'Посилання «Залишити відгук»', 'url' ),
					mz_acf_tab( 'opt_codes', 'Коди і форма' ),
					mz_acf(
						'head_code',
						'Код у &lt;head&gt;',
						'textarea',
						array(
							'rows'         => 5,
							'new_lines'    => '',
							'instructions' => 'Мета-теги верифікації тощо. GTM сюди не додавайте — його підключає плагін GTM Kit.',
						)
					),
					mz_acf( 'cf7_form', 'ID форми Contact Form 7 для заявок', 'number', array( 'instructions' => 'Створюється імпортером («Мазур: заявка з сайту»).' ) ),
				),
			)
		);

		// ------------------------------------------------------------ Сторінка послуги
		$GLOBALS['mz_acf_g'] = 'svc';
		acf_add_local_field_group(
			array(
				'key'            => 'group_mz_service',
				'title'          => 'Контент послуги',
				'location'       => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'template-service.php' ) ) ),
				'hide_on_screen' => array( 'the_content', 'excerpt', 'discussion', 'comments', 'featured_image' ),
				'fields'         => array_merge(
					array(
						mz_acf_tab( 'svc_menu', 'Меню і SEO' ),
						mz_acf( 'nav', 'Назва в меню', 'text', array( 'required' => 1 ) ),
						mz_acf_icon( 'icon', 'Іконка' ),
						mz_acf( 'category', 'Категорія (над заголовком, на головній)' ),
						mz_acf_lines( 'home_items', 'Пункти на картці послуги (головна)' ),
					),
					mz_acf_meta(),
					array(
						mz_acf_tab( 'svc_hero', 'Перший екран' ),
						mz_acf( 'eyebrow', 'Надзаголовок (порожньо — категорія)' ),
						mz_acf( 'h1', 'H1 — початок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'h1_gold', 'H1 — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'lead', 'Лід', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
						mz_acf_lines( 'bullets', 'Пункти під лідом' ),
						mz_acf( 'hero_cta', 'Головна кнопка (порожньо — «Безкоштовна консультація»)', 'text', array( 'instructions' => 'Напр. «Замовити картку — 4 200 ₴». Відкриває форму з темою першої ситуації.' ) ),
						mz_acf( 'hero_image', 'Фото першого екрана (тематичне)', 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Порожньо — фото адвоката нижче.' ) ),
						mz_acf(
							'hero_preset',
							'Фото адвоката',
							'select',
							array(
								'choices' => array(
									'lawyer-standing' => 'Адвокат біля столу (сам)',
									'lawyer-hero'     => 'Адвокат з колегою',
									'lawyer-desk'     => 'Адвокат за столом',
									'lawyer-office'   => 'Адвокат у переговорній',
								),
							)
						),
						mz_acf( 'hero_alt', 'Alt фото' ),
						mz_acf( 'years_text', 'Підпис до «20 років практики»' ),
						mz_acf( 'lawyer_badge', 'Картка «Оформлення веде адвокат» на фото', 'true_false', array( 'ui' => 1 ) ),

						mz_acf_tab( 'svc_showcase', 'Фото-блоки' ),
						mz_acf( 'showcase_title', 'Заголовок секції', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Секція показується, якщо є хоча б один блок.' ) ),
						mz_acf( 'showcase_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'showcase_text', 'Текст під заголовком', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf_repeater(
							'showcase',
							'Блоки (фото + текст)',
							array(
								mz_acf_sub( 'showcase', 'image', 'Фото', 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail', 'wrapper' => array( 'width' => 30 ) ) ),
								mz_acf_sub( 'showcase', 'eyebrow', 'Надзаголовок', 'text', array( 'wrapper' => array( 'width' => 70 ) ) ),
								mz_acf_sub( 'showcase', 'title', 'Заголовок' ),
								mz_acf_paras( 'p', 'Текст', 'showcase' ),
								mz_acf_lines( 'list', 'Список', 'showcase' ),
								mz_acf_sub( 'showcase', 'cta', 'Кнопка', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_sub( 'showcase', 'topic', 'Тема заявки з кнопки', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
							),
							'Додати блок'
						),

						mz_acf_tab( 'svc_sit', 'Ситуації' ),
						mz_acf( 'situations_text', 'Текст біля заголовка', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf_repeater(
							'situations',
							'Ситуації (остання — акцентна)',
							array(
								mz_acf_icon( 'icon', 'Іконка', 'situations' ),
								mz_acf_sub( 'situations', 'title', 'Заголовок' ),
								mz_acf_sub( 'situations', 'text', 'Текст', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
								mz_acf_sub( 'situations', 'topic', 'Тема заявки' ),
							),
							'Додати ситуацію'
						),

						mz_acf_tab( 'svc_help', 'Допомога (вкладки)' ),
						mz_acf( 'help_gold', 'Заголовок — золота частина' ),
						mz_acf( 'help_text', 'Текст під заголовком', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf_repeater(
							'tabs',
							'Вкладки',
							array(
								mz_acf_sub( 'tabs', 'label', 'Назва вкладки', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_icon( 'icon', 'Іконка', 'tabs' ),
								mz_acf_sub( 'tabs', 'h3', 'Заголовок' ),
								mz_acf_paras( 'p', 'Текст', 'tabs' ),
								mz_acf_lines( 'list', 'У межах допомоги', 'tabs' ),
								mz_acf_icon( 'note_icon', 'Іконка примітки', 'tabs' ),
								mz_acf_sub( 'tabs', 'note', 'Примітка', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
								mz_acf_sub( 'tabs', 'cta', 'Кнопка', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_sub( 'tabs', 'topic', 'Тема заявки', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
							),
							'Додати вкладку'
						),

						mz_acf_tab( 'svc_steps', 'Етапи' ),
						mz_acf_repeater(
							'steps',
							'Етапи (4)',
							array(
								mz_acf_icon( 'icon', 'Іконка', 'steps' ),
								mz_acf_sub( 'steps', 'title', 'Заголовок' ),
								mz_acf_sub( 'steps', 'text', 'Текст', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
							),
							'Додати етап'
						),

						mz_acf_tab( 'svc_price', 'Вартість' ),
						mz_acf( 'price_text', 'Текст біля заголовка (порожньо — стандартний)', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf( 'price_docs_title', 'Картка 2: назва' ),
						mz_acf( 'price_docs_value', 'Картка 2: ціна (порожньо — «Фіксована ціна»)', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'price_docs_hint', 'Картка 2: примітка до ціни', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf_lines( 'price_docs', 'Картка 2: пункти' ),
						mz_acf( 'price_full_title', 'Картка 3: назва (порожньо — «Комплексний супровід»)' ),
						mz_acf( 'price_full_value', 'Картка 3: ціна (порожньо — «За договором»)', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'price_full_hint', 'Картка 3: примітка до ціни', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf_lines( 'price_full', 'Картка 3: пункти' ),

						mz_acf_tab( 'svc_about', 'Про адвоката' ),
						mz_acf( 'about_text', 'Текст про адвоката для цієї послуги', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),

						mz_acf_tab( 'svc_reviews', 'Відгуки' ),
						mz_acf( 'reviews_label', 'Надзаголовок (порожньо — «Відгуки Google» + рейтинг)', 'text', array( 'instructions' => 'Блок відгуків показується, лише якщо є відгуки. Додавайте тільки реальні.' ) ),
						mz_acf( 'reviews_gold', 'Заголовок — золота частина' ),
						mz_acf_reviews( 'reviews' ),

						mz_acf_tab( 'svc_seo', 'Стаття' ),
						mz_acf( 'seo_h2', 'Заголовок статті' ),
						mz_acf( 'seo_intro', 'Вступ (виділений)', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
						mz_acf( 'seo_p', 'Абзац', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
						mz_acf( 'seo_when_title', 'Заголовок «Коли звернутися»' ),
						mz_acf_lines( 'seo_when', 'Пункти «Коли звернутися»' ),
						mz_acf( 'seo_quote', 'Цитата', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf_repeater(
							'seo_sections',
							'Розділи статті',
							array(
								mz_acf_sub( 'seo_sections', 'sid', 'Якір (латиницею)', 'text', array( 'wrapper' => array( 'width' => 30 ) ) ),
								mz_acf_sub( 'seo_sections', 'toc', 'Пункт змісту', 'text', array( 'wrapper' => array( 'width' => 70 ) ) ),
								mz_acf_sub( 'seo_sections', 'h3', 'Заголовок' ),
								mz_acf_paras( 'p', 'Текст', 'seo_sections' ),
							),
							'Додати розділ'
						),
						mz_acf( 'seo_tips_title', 'Заголовок порад' ),
						mz_acf_repeater(
							'seo_tips',
							'Поради',
							array(
								mz_acf_sub( 'seo_tips', 't', 'Заголовок', 'text', array( 'wrapper' => array( 'width' => 35 ) ) ),
								mz_acf_sub( 'seo_tips', 'x', 'Текст', 'text', array( 'wrapper' => array( 'width' => 65 ) ) ),
							),
							'Додати пораду'
						),

						mz_acf_tab( 'svc_faq', 'FAQ' ),
						mz_acf_faq( 'faq' ),

						mz_acf_tab( 'svc_final', 'Заявка' ),
						mz_acf( 'final_h2', 'Заголовок — початок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'final_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf_lines( 'docs', 'Що підготувати до консультації' ),
					)
				),
			)
		);

		// ------------------------------------------------------------ Головна
		$GLOBALS['mz_acf_g'] = 'home';
		acf_add_local_field_group(
			array(
				'key'            => 'group_mz_home',
				'title'          => 'Головна сторінка',
				'location'       => array(
					array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ),
					array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'template-home.php' ) ),
				),
				'hide_on_screen' => array( 'the_content', 'excerpt', 'discussion', 'comments', 'featured_image' ),
				'fields'         => array_merge(
					mz_acf_meta(),
					array(
						mz_acf_tab( 'home_reviews', 'Відгуки' ),
						mz_acf_reviews( 'home_reviews' ),
						mz_acf_tab( 'home_faq', 'FAQ' ),
						mz_acf_faq( 'home_faq' ),
					)
				),
			)
		);

		// ------------------------------------------------------------ Подяка
		$GLOBALS['mz_acf_g'] = 'thx';
		acf_add_local_field_group(
			array(
				'key'            => 'group_mz_thanks',
				'title'          => 'Сторінка подяки',
				'location'       => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'template-thanks.php' ) ) ),
				'hide_on_screen' => array( 'the_content', 'excerpt', 'discussion', 'comments', 'featured_image' ),
				'fields'         => array(
					mz_acf( 'meta_title', 'SEO: title' ),
					mz_acf( 'h1', 'Заголовок — початок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'h1_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'lead', 'Текст', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
					mz_acf_lines( 'thanks_steps', 'Що буде далі (кроки)' ),
				),
			)
		);

		// ------------------------------------------------------------ Кейс
		$GLOBALS['mz_acf_g'] = 'case';
		acf_add_local_field_group(
			array(
				'key'      => 'group_mz_case',
				'title'    => 'Кейс',
				'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'mz_case' ) ) ),
				'fields'   => array(
					mz_acf(
						'case_service',
						'Послуга',
						'post_object',
						array(
							'post_type'     => array( 'page' ),
							'return_format' => 'id',
							'ui'            => 1,
							'required'      => 1,
							'instructions'  => 'Сторінка послуги тієї ж мови. На сторінці послуги показуються до 3 її кейсів.',
						)
					),
					mz_acf( 'case_date', 'Дата (як показувати)', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'case_read', 'Час прочитання, хв', 'number', array( 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'case_result_short', 'Результат коротко (картка)', 'text' ),
					mz_acf( 'case_lead', 'Вступ', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
					mz_acf( 'case_quote', 'Цитата клієнта', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
					mz_acf_paras( 'case_problem', 'У чому складність' ),
					mz_acf( 'case_work_intro', 'Що зроблено — вступ', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
					mz_acf_lines( 'case_work', 'Що зроблено — кроки' ),
					mz_acf_paras( 'case_result', 'Результат' ),
					mz_acf( 'meta_desc', 'SEO: description', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
				),
			)
		);
	}
);
