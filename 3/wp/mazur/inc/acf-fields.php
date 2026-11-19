<?php
/**
 * Поля ACF PRO (реєструються кодом — у базі нічого не створюється).
 *
 * Розкладка як у старій темі: вкладки за блоками сторінки в порядку виводу («Банер», «Відгуки», «FAQ»…).
 * Списки — репітери (рядки перетягуються), абзаци — візуальний редактор з кнопкою «посилання»,
 * іконки — візуальний вибір, репітери згортаються за заголовком і мають ліміти під верстку,
 * на вкладці «Секції сторінки» блоки можна вимкнути.
 *
 * Імена полів мають префікс mz_, ключі — field_mz_<група>_* (групи: svc, home, thx, case, opt).
 */

defined( 'ABSPATH' ) || exit;

$GLOBALS['mz_acf_g'] = 'x'; // префікс ключів поточної групи

/** Ключ поля: $path — ім'я або «батько__підполе». */
function mz_acf_key( $path ) {
	return 'field_mz_' . $GLOBALS['mz_acf_g'] . '_' . $path;
}

/** Поле верхнього рівня. */
function mz_acf( $name, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => mz_acf_key( $name ),
			'name'  => 'mz_' . $name,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

/** Підполе репітера. $parent — шлях батька («tabs» або «tabs__list» для вкладених). */
function mz_acf_sub( $parent, $name, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => mz_acf_key( $parent . '__' . $name ),
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

/** Поле верхнього рівня або підполе — залежно від $parent. */
function mz_acf_any( $parent, $name, $label, $type, $extra = array() ) {
	return $parent ? mz_acf_sub( $parent, $name, $label, $type, $extra ) : mz_acf( $name, $label, $type, $extra );
}

function mz_acf_tab( $id, $label ) {
	return array(
		'key'       => mz_acf_key( 'tab_' . $id ),
		'label'     => $label,
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);
}

/** Візуальний редактор з кнопкою «посилання» (ініціалізується по кліку — сторінка в адмінці не гальмує). */
function mz_acf_rich( $name, $label, $parent = null, $toolbar = 'basic', $extra = array() ) {
	return mz_acf_any(
		$parent,
		$name,
		$label,
		'wysiwyg',
		array_merge(
			array(
				'tabs'         => 'all',
				'toolbar'      => $toolbar,
				'media_upload' => 0,
				'delay'        => 1,
				'instructions' => 'Посилання — кнопкою «ланцюжок»: можна знайти сторінку сайту за назвою.',
			),
			$extra
		)
	);
}

/** Список: репітер з одним полем — рядки додаються і перетягуються. */
function mz_acf_list( $name, $label, $parent = null, $max = 0, $button = 'Додати пункт' ) {
	$path = $parent ? $parent . '__' . $name : $name;
	return mz_acf_any(
		$parent,
		$name,
		$label,
		'repeater',
		array(
			'layout'       => 'table',
			'button_label' => $button,
			'max'          => $max,
			'sub_fields'   => array( mz_acf_sub( $path, 'text', 'Пункт' ) ),
		)
	);
}

/** Візуальний вибір іконки (стилі — assets/css/admin.css). */
function mz_acf_icon( $name, $label, $parent = null ) {
	$choices = array();
	foreach ( mz_icon_choices() as $ic ) {
		$choices[ $ic ] = '<i class="ic ' . $ic . '" aria-hidden="true"></i><span>' . substr( $ic, 3 ) . '</span>';
	}
	return mz_acf_any(
		$parent,
		$name,
		$label,
		'radio',
		array(
			'choices'       => $choices,
			'default_value' => 'ic-scales',
			'layout'        => 'horizontal',
			'return_format' => 'value',
			'wrapper'       => array( 'class' => 'mz-icon-picker' ),
		)
	);
}

/**
 * Репітер. $collapsed — ім'я підполя, яке видно в згорнутому рядку.
 */
function mz_acf_repeater( $name, $label, $subs, $button = 'Додати', $collapsed = '', $extra = array() ) {
	return mz_acf(
		$name,
		$label,
		'repeater',
		array_merge(
			array(
				'layout'       => 'block',
				'button_label' => $button,
				'collapsed'    => $collapsed ? mz_acf_key( $name . '__' . $collapsed ) : '',
				'sub_fields'   => $subs,
			),
			$extra
		)
	);
}

function mz_acf_image( $name, $label, $parent = null, $extra = array() ) {
	return mz_acf_any( $parent, $name, $label, 'image', array_merge( array( 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all' ), $extra ) );
}

/** Перемикач «Показувати секцію». */
function mz_acf_show( $name, $label ) {
	return mz_acf(
		'show_' . $name,
		$label,
		'true_false',
		array(
			'ui'            => 1,
			'default_value' => 1,
			'ui_on_text'    => 'Так',
			'ui_off_text'   => 'Ні',
			'wrapper'       => array( 'width' => 33 ),
		)
	);
}

function mz_acf_reviews( $name ) {
	return mz_acf_repeater(
		$name,
		'Відгуки',
		array(
			mz_acf_sub( $name, 'name', 'Ім’я', 'text', array( 'wrapper' => array( 'width' => 35 ) ) ),
			mz_acf_sub( $name, 'initials', 'Ініціали (аватар)', 'text', array( 'wrapper' => array( 'width' => 15 ) ) ),
			mz_acf_sub( $name, 'tag', 'Послуга / тема', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
			mz_acf_sub( $name, 'date', 'Дата (необов’язково)', 'text', array( 'wrapper' => array( 'width' => 35 ) ) ),
			mz_acf_sub( $name, 'src', 'Джерело (Google / Відгук клієнта)', 'text', array( 'wrapper' => array( 'width' => 65 ) ) ),
			mz_acf_sub( $name, 'text', 'Текст', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
		),
		'Додати відгук',
		'name'
	);
}

function mz_acf_faq( $name ) {
	return mz_acf_repeater(
		$name,
		'Питання та відповіді',
		array(
			mz_acf_sub( $name, 'q', 'Питання' ),
			mz_acf_rich( 'a', 'Відповідь', $name ),
		),
		'Додати питання',
		'q'
	);
}

function mz_acf_meta() {
	return array(
		mz_acf( 'meta_title', 'SEO: title', 'text', array( 'instructions' => 'Тег &lt;title&gt;. Порожньо — назва сторінки.' ) ),
		mz_acf( 'meta_desc', 'SEO: description', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
	);
}

/**
 * Polylang Pro (інтеграція з ACF): поведінка кожного поля при перекладі сторінки.
 *  - тексти верхнього рівня — translate (копіюються в новий переклад, далі перекладаються; йдуть в експорт XLIFF);
 *  - фото, іконки, перемикачі, зв'язки — sync (однакові в UA і RU; «Послуга» кейсу — сторінка потрібної мови);
 *  - усе всередині репітерів — copy_once: копіюється при створенні перекладу, далі UA і RU незалежні
 *    (інакше Polylang вирівнює кількість рядків і видалення рядка в UA зсуває рядки в RU).
 * Без Polylang Pro налаштування ігнорується.
 */
function mz_acf_pll( $fields, $in_repeater = false ) {
	foreach ( $fields as &$f ) {
		if ( 'tab' === $f['type'] ) {
			continue;
		}
		if ( 'repeater' === $f['type'] ) {
			$f['sub_fields'] = mz_acf_pll( $f['sub_fields'], true );
			continue;
		}
		if ( isset( $f['translations'] ) ) {
			continue;
		}
		$is_text           = in_array( $f['type'], array( 'text', 'textarea', 'wysiwyg', 'url', 'email' ), true );
		$f['translations'] = $in_repeater ? 'copy_once' : ( $is_text ? 'translate' : 'sync' );
	}
	return $fields;
}

/** acf_add_local_field_group + налаштування перекладу полів для Polylang Pro. */
function mz_acf_group( $group ) {
	$group['fields'] = mz_acf_pll( $group['fields'] );
	acf_add_local_field_group( $group );
}

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$hide = array( 'the_content', 'excerpt', 'discussion', 'comments', 'featured_image' );

		// ============================================================ Налаштування сайту
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
		$d                   = mz_defaults();
		$GLOBALS['mz_acf_g'] = 'opt';
		acf_add_local_field_group(
			array(
				'key'      => 'group_mz_options',
				'title'    => 'Контакти, статистика, коди',
				'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'mz-settings' ) ) ),
				'fields'   => array(
					mz_acf_tab( 'contacts', 'Контакти' ),
					mz_acf( 'phone', 'Телефон (як показувати)', 'text', array( 'placeholder' => $d['phone'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'phone_href', 'Телефон для tel: (без пробілів)', 'text', array( 'placeholder' => $d['phone_href'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'email', 'E-mail', 'text', array( 'placeholder' => $d['email'] ) ),
					mz_acf( 'address', 'Адреса (UA)', 'text', array( 'placeholder' => $d['address'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'address_ru', 'Адреса (RU)', 'text', array( 'placeholder' => $d['address_ru'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'hours', 'Години роботи (UA)', 'text', array( 'placeholder' => $d['hours'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'hours_ru', 'Години роботи (RU)', 'text', array( 'placeholder' => $d['hours_ru'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'telegram', 'Telegram (посилання)', 'text', array( 'placeholder' => $d['telegram'] ) ),
					mz_acf( 'viber', 'Viber (посилання)', 'text', array( 'placeholder' => $d['viber'] ) ),
					mz_acf( 'whatsapp', 'WhatsApp (посилання)', 'text', array( 'placeholder' => $d['whatsapp'] ) ),
					mz_acf_tab( 'lawyer', 'Адвокат' ),
					mz_acf( 'name', 'Ім’я (UA)', 'text', array( 'placeholder' => $d['name'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'name_ru', 'Ім’я (RU)', 'text', array( 'placeholder' => $d['name_ru'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'cert', 'Номер свідоцтва', 'text', array( 'placeholder' => $d['cert'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'years', 'Років практики', 'text', array( 'placeholder' => $d['years'], 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf_tab( 'stats', 'Цифри і Google' ),
					mz_acf( 'stat_cases', 'Кількість справ (блок «Про адвоката» на послугах)', 'text', array( 'instructions' => 'Напр. 500+. Порожньо — замість цього показуються роки практики.' ) ),
					mz_acf( 'google_rating', 'Рейтинг у Google', 'text', array( 'instructions' => 'Напр. 5.0. Порожньо — блок рейтингу не показується.', 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'google_count', 'Кількість відгуків у Google', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'google_url', 'Посилання на відгуки в Google', 'url' ),
					mz_acf( 'review_url', 'Посилання «Залишити відгук»', 'url' ),
					mz_acf_tab( 'codes', 'Коди і форма' ),
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

		// ============================================================ Сторінка послуги
		$GLOBALS['mz_acf_g'] = 'svc';
		mz_acf_group(
			array(
				'key'            => 'group_mz_service',
				'title'          => 'Контент послуги',
				'location'       => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'template-service.php' ) ) ),
				'hide_on_screen' => $hide,
				'fields'         => array_merge(
					array(
						// ---- Банер
						mz_acf_tab( 'hero', 'Банер' ),
						mz_acf( 'eyebrow', 'Надзаголовок', 'text', array( 'instructions' => 'Порожньо — категорія послуги.' ) ),
						mz_acf( 'h1', 'Заголовок H1 — початок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'h1_gold', 'Заголовок H1 — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'lead', 'Текст під заголовком', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
						mz_acf_list( 'bullets', 'Пункти під текстом', null, 5 ),
						mz_acf( 'hero_cta', 'Головна кнопка', 'text', array( 'instructions' => 'Напр. «Замовити картку — 4 200 ₴» — відкриває форму з темою першої ситуації. Порожньо — «Безкоштовна консультація».' ) ),
						mz_acf_image( 'hero_image', 'Тематичне фото', null, array( 'instructions' => 'Порожньо — фото адвоката нижче.', 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf(
							'hero_preset',
							'Фото адвоката (якщо немає тематичного)',
							'radio',
							array(
								'choices' => array(
									'lawyer-standing' => 'Адвокат біля столу (сам)',
									'lawyer-hero'     => 'Адвокат з колегою',
									'lawyer-desk'     => 'Адвокат за столом',
									'lawyer-office'   => 'Адвокат у переговорній',
								),
								'wrapper' => array( 'width' => 50 ),
							)
						),
						mz_acf( 'hero_alt', 'Alt фото' ),
						mz_acf( 'lawyer_badge', 'Картка «Оформлення веде адвокат» на фото', 'true_false', array( 'ui' => 1, 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Інакше — «20 років практики».' ) ),
						mz_acf( 'years_text', 'Підпис до «20 років практики»', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),

						// ---- Як це працює
						mz_acf_tab( 'showcase', 'Як це працює (фото)' ),
						mz_acf( 'showcase_title', 'Заголовок', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Блок показується, якщо є хоча б один рядок.' ) ),
						mz_acf( 'showcase_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'showcase_text', 'Текст біля заголовка', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf_repeater(
							'showcase',
							'Блоки «фото + текст»',
							array(
								mz_acf_image( 'image', 'Фото', 'showcase', array( 'preview_size' => 'thumbnail', 'wrapper' => array( 'width' => 25 ) ) ),
								mz_acf_sub( 'showcase', 'eyebrow', 'Надзаголовок', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
								mz_acf_sub( 'showcase', 'title', 'Заголовок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_rich( 'p', 'Текст', 'showcase' ),
								mz_acf_list( 'list', 'Список', 'showcase', 6 ),
								mz_acf_sub( 'showcase', 'cta', 'Кнопка', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_sub( 'showcase', 'topic', 'Тема заявки з кнопки', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
							),
							'Додати блок',
							'title',
							array( 'max' => 4 )
						),

						// ---- Ситуації
						mz_acf_tab( 'sit', 'Ситуації' ),
						mz_acf( 'situations_text', 'Текст біля заголовка', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf_repeater(
							'situations',
							'Картки ситуацій (по 3 в ряд, остання — акцентна)',
							array(
								mz_acf_sub( 'situations', 'title', 'Заголовок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_sub( 'situations', 'topic', 'Тема заявки', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_sub( 'situations', 'text', 'Текст', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
								mz_acf_icon( 'icon', 'Іконка', 'situations' ),
							),
							'Додати ситуацію',
							'title',
							array( 'max' => 9 )
						),

						// ---- Допомога
						mz_acf_tab( 'help', 'Допомога (вкладки)' ),
						mz_acf( 'help_gold', 'Заголовок — золота частина', 'text', array( 'instructions' => '«Яку допомогу надаю …»' ) ),
						mz_acf( 'help_text', 'Текст під заголовком', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf_repeater(
							'tabs',
							'Вкладки',
							array(
								mz_acf_sub( 'tabs', 'label', 'Назва вкладки', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_sub( 'tabs', 'h3', 'Заголовок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_rich( 'p', 'Текст', 'tabs' ),
								mz_acf_list( 'list', 'У межах допомоги', 'tabs', 8 ),
								mz_acf_sub( 'tabs', 'note', 'Примітка (справа)', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
								mz_acf_sub( 'tabs', 'cta', 'Кнопка', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_sub( 'tabs', 'topic', 'Тема заявки', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_icon( 'icon', 'Іконка вкладки', 'tabs' ),
								mz_acf_icon( 'note_icon', 'Іконка примітки', 'tabs' ),
							),
							'Додати вкладку',
							'label',
							array( 'max' => 8 )
						),

						// ---- Етапи
						mz_acf_tab( 'steps', 'Етапи' ),
						mz_acf_repeater(
							'steps',
							'Етапи (до 4 — під верстку)',
							array(
								mz_acf_sub( 'steps', 'title', 'Заголовок' ),
								mz_acf_sub( 'steps', 'text', 'Текст', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
								mz_acf_icon( 'icon', 'Іконка', 'steps' ),
							),
							'Додати етап',
							'title',
							array( 'min' => 2, 'max' => 4 )
						),

						// ---- Вартість
						mz_acf_tab( 'price', 'Вартість' ),
						mz_acf( 'price_text', 'Текст біля заголовка', 'textarea', array( 'rows' => 2, 'new_lines' => '', 'instructions' => 'Порожньо — стандартний текст.' ) ),
						mz_acf( 'price_docs_title', 'Картка 2: назва', 'text', array( 'wrapper' => array( 'width' => 40 ) ) ),
						mz_acf( 'price_docs_value', 'Картка 2: ціна', 'text', array( 'wrapper' => array( 'width' => 25 ), 'instructions' => 'Порожньо — «Фіксована ціна».' ) ),
						mz_acf( 'price_docs_hint', 'Картка 2: примітка до ціни', 'text', array( 'wrapper' => array( 'width' => 35 ) ) ),
						mz_acf_list( 'price_docs', 'Картка 2: пункти', null, 6 ),
						mz_acf( 'price_full_title', 'Картка 3: назва', 'text', array( 'wrapper' => array( 'width' => 40 ), 'instructions' => 'Порожньо — «Комплексний супровід».' ) ),
						mz_acf( 'price_full_value', 'Картка 3: ціна', 'text', array( 'wrapper' => array( 'width' => 25 ), 'instructions' => 'Порожньо — «За договором».' ) ),
						mz_acf( 'price_full_hint', 'Картка 3: примітка до ціни', 'text', array( 'wrapper' => array( 'width' => 35 ) ) ),
						mz_acf_list( 'price_full', 'Картка 3: пункти', null, 6 ),

						// ---- Про адвоката
						mz_acf_tab( 'about', 'Про адвоката' ),
						mz_acf( 'about_text', 'Текст про адвоката для цієї послуги', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),

						// ---- Відгуки
						mz_acf_tab( 'reviews', 'Відгуки' ),
						mz_acf( 'reviews_label', 'Надзаголовок', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Порожньо — «Відгуки Google» з рейтингом із налаштувань.' ) ),
						mz_acf( 'reviews_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => '«Відгуки клієнтів …»' ) ),
						mz_acf_reviews( 'reviews' ),

						// ---- Стаття
						mz_acf_tab( 'seo', 'Стаття' ),
						mz_acf( 'seo_h2', 'Заголовок статті' ),
						mz_acf_rich( 'seo_intro', 'Вступ (виділений абзац)' ),
						mz_acf_rich( 'seo_p', 'Абзац після вступу' ),
						mz_acf( 'seo_when_title', 'Заголовок «Коли звернутися»' ),
						mz_acf_list( 'seo_when', 'Пункти «Коли звернутися»', null, 8 ),
						mz_acf( 'seo_quote', 'Цитата', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf_repeater(
							'seo_sections',
							'Розділи статті',
							array(
								mz_acf_sub( 'seo_sections', 'h3', 'Заголовок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
								mz_acf_sub( 'seo_sections', 'toc', 'Пункт змісту (зліва)', 'text', array( 'wrapper' => array( 'width' => 30 ) ) ),
								mz_acf_sub( 'seo_sections', 'sid', 'Якір (латиницею)', 'text', array( 'wrapper' => array( 'width' => 20 ) ) ),
								mz_acf_rich( 'p', 'Текст', 'seo_sections' ),
							),
							'Додати розділ',
							'h3',
							array( 'max' => 8 )
						),
						mz_acf( 'seo_tips_title', 'Заголовок порад' ),
						mz_acf_repeater(
							'seo_tips',
							'Поради (4 картки)',
							array(
								mz_acf_sub( 'seo_tips', 't', 'Заголовок', 'text', array( 'wrapper' => array( 'width' => 35 ) ) ),
								mz_acf_sub( 'seo_tips', 'x', 'Текст', 'text', array( 'wrapper' => array( 'width' => 65 ) ) ),
							),
							'Додати пораду',
							't',
							array( 'max' => 4, 'layout' => 'table' )
						),

						// ---- FAQ
						mz_acf_tab( 'faq', 'FAQ' ),
						mz_acf_faq( 'faq' ),

						// ---- Заявка
						mz_acf_tab( 'final', 'Заявка' ),
						mz_acf( 'final_h2', 'Заголовок — початок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'final_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf_list( 'docs', 'Що підготувати до консультації', null, 6 ),

						// ---- Секції
						mz_acf_tab( 'sections', 'Секції сторінки' ),
						mz_acf_show( 'showcase', 'Як це працює (фото)' ),
						mz_acf_show( 'situations', 'Ситуації' ),
						mz_acf_show( 'help', 'Допомога (вкладки)' ),
						mz_acf_show( 'band', 'Смуга «Перша консультація — 0 ₴»' ),
						mz_acf_show( 'steps', 'Етапи' ),
						mz_acf_show( 'price', 'Вартість' ),
						mz_acf_show( 'about', 'Про адвоката' ),
						mz_acf_show( 'cases', 'Кейси' ),
						mz_acf_show( 'reviews', 'Відгуки' ),
						mz_acf_show( 'seo', 'Стаття' ),
						mz_acf_show( 'faq', 'FAQ' ),
						mz_acf_show( 'related', 'Інші напрями (під формою)' ),

						// ---- Меню і SEO
						mz_acf_tab( 'menu', 'Меню і SEO' ),
						mz_acf( 'nav', 'Назва в меню', 'text', array( 'required' => 1, 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'category', 'Категорія', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Над заголовком і на картці на головній.' ) ),
						mz_acf_list( 'home_items', 'Пункти на картці послуги (головна)', null, 4 ),
						mz_acf_icon( 'icon', 'Іконка послуги (меню, головна)' ),
					),
					mz_acf_meta()
				),
			)
		);

		// ============================================================ Головна
		$GLOBALS['mz_acf_g'] = 'home';
		mz_acf_group(
			array(
				'key'            => 'group_mz_home',
				'title'          => 'Головна сторінка',
				'location'       => array(
					array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ),
					array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'template-home.php' ) ),
				),
				'hide_on_screen' => $hide,
				'fields'         => array_merge(
					array(
						mz_acf_tab( 'hero', 'Банер' ),
						mz_acf( 'hero_eyebrow', 'Надзаголовок' ),
						mz_acf( 'hero_h1', 'Заголовок H1 — початок', 'text', array( 'wrapper' => array( 'width' => 34 ) ) ),
						mz_acf( 'hero_h1_gold', 'H1 — золота частина', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
						mz_acf( 'hero_h1_after', 'H1 — після золотої', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
						mz_acf( 'hero_lead', 'Текст під заголовком', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf( 'hero_btn', 'Кнопка (до форми)', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'hero_call', 'Кнопка «Передзвоніть мені»', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf_list( 'hero_trust', 'Рядок під кнопками', null, 4 ),
						mz_acf_image( 'hero_image', 'Фото', null, array( 'instructions' => 'Порожньо — фото адвоката з колегою.' ) ),

						mz_acf_tab( 'stats', 'Цифри' ),
						mz_acf_repeater(
							'stats',
							'Цифри під банером (4)',
							array(
								mz_acf_sub( 'stats', 'num', 'Число', 'text', array( 'instructions' => 'Напр. 20, 500+, 5.0' ) ),
								mz_acf_sub( 'stats', 'unit', 'Одиниця поруч', 'text', array( 'instructions' => 'Напр. «років»' ) ),
								mz_acf_sub( 'stats', 'label', 'Підпис' ),
								mz_acf_sub( 'stats', 'count', 'Анімувати', 'true_false', array( 'ui' => 1 ) ),
							),
							'Додати цифру',
							'label',
							array( 'max' => 4, 'layout' => 'table' )
						),

						mz_acf_tab( 'about', 'Про адвоката' ),
						mz_acf( 'about_eyebrow', 'Надзаголовок' ),
						mz_acf( 'about_title', 'Заголовок — початок', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf( 'about_gold', 'Заголовок — золота частина' ),
						mz_acf( 'about_text', 'Текст', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),

						mz_acf_tab( 'services', 'Послуги' ),
						mz_acf( 'services_title', 'Заголовок' ),
						mz_acf( 'services_text', 'Текст', 'textarea', array( 'rows' => 2, 'new_lines' => '', 'instructions' => 'Кількість послуг підставляється на початок автоматично. Картки послуг — зі сторінок послуг (вкладка «Меню і SEO»).' ) ),

						mz_acf_tab( 'seo1', 'SEO-текст 1' ),
						mz_acf( 'seo1_title', 'Заголовок' ),
						mz_acf( 'seo1_lead', 'Перший абзац (видно завжди)', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
						mz_acf_rich( 'seo1_more', 'Продовження (за кнопкою «Читати повністю»)', null, 'full' ),

						mz_acf_tab( 'steps', 'Етапи' ),
						mz_acf( 'steps_title', 'Заголовок' ),
						mz_acf_repeater(
							'steps',
							'Етапи (6 — під верстку)',
							array(
								mz_acf_sub( 'steps', 'text', 'Текст' ),
								mz_acf_icon( 'icon', 'Іконка', 'steps' ),
							),
							'Додати етап',
							'text',
							array( 'max' => 6 )
						),
						mz_acf( 'steps_btn', 'Кнопка' ),

						mz_acf_tab( 'why', 'Чому я' ),
						mz_acf( 'why_eyebrow', 'Надзаголовок' ),
						mz_acf( 'why_title', 'Заголовок — початок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'why_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf_repeater(
							'reasons',
							'Переваги (6)',
							array(
								mz_acf_sub( 'reasons', 'title', 'Заголовок', 'text', array( 'wrapper' => array( 'width' => 35 ) ) ),
								mz_acf_sub( 'reasons', 'text', 'Текст', 'text', array( 'wrapper' => array( 'width' => 65 ) ) ),
								mz_acf_icon( 'icon', 'Іконка', 'reasons' ),
							),
							'Додати перевагу',
							'title',
							array( 'max' => 6 )
						),
						mz_acf( 'why_btn', 'Кнопка', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'why_call', 'Кнопка «Передзвоніть мені»', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf_image( 'why_image', 'Фото', null, array( 'instructions' => 'Порожньо — адвокат за столом.' ) ),

						mz_acf_tab( 'contact', 'Заявка' ),
						mz_acf( 'contact_title', 'Заголовок', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						mz_acf( 'contact_text', 'Текст' ),
						mz_acf( 'contact_btn', 'Кнопка' ),
						mz_acf_image( 'contact_image', 'Фото', null, array( 'instructions' => 'Порожньо — Феміда.' ) ),

						mz_acf_tab( 'reviews', 'Відгуки' ),
						mz_acf( 'reviews_title', 'Заголовок' ),
						mz_acf_reviews( 'home_reviews' ),

						mz_acf_tab( 'faq', 'FAQ' ),
						mz_acf( 'faq_title', 'Заголовок — початок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'faq_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
						mz_acf( 'faq_text', 'Текст під заголовком' ),
						mz_acf_faq( 'home_faq' ),

						mz_acf_tab( 'seo2', 'SEO-текст 2' ),
						mz_acf( 'seo2_title', 'Заголовок' ),
						mz_acf( 'seo2_lead', 'Перший абзац (видно завжди)', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
						mz_acf_rich( 'seo2_more', 'Продовження (за кнопкою «Читати повністю»)', null, 'full' ),

						mz_acf_tab( 'sections', 'Секції сторінки' ),
						mz_acf_show( 'stats', 'Цифри' ),
						mz_acf_show( 'about', 'Про адвоката' ),
						mz_acf_show( 'services', 'Послуги' ),
						mz_acf_show( 'seo1', 'SEO-текст 1' ),
						mz_acf_show( 'steps', 'Етапи' ),
						mz_acf_show( 'why', 'Чому я' ),
						mz_acf_show( 'reviews', 'Відгуки' ),
						mz_acf_show( 'faq', 'FAQ' ),
						mz_acf_show( 'seo2', 'SEO-текст 2' ),

						mz_acf_tab( 'meta', 'SEO' ),
					),
					mz_acf_meta()
				),
			)
		);

		// ============================================================ Подяка
		$GLOBALS['mz_acf_g'] = 'thx';
		mz_acf_group(
			array(
				'key'            => 'group_mz_thanks',
				'title'          => 'Сторінка подяки',
				'location'       => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'template-thanks.php' ) ) ),
				'hide_on_screen' => $hide,
				'fields'         => array(
					mz_acf( 'meta_title', 'SEO: title' ),
					mz_acf( 'h1', 'Заголовок — початок', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'h1_gold', 'Заголовок — золота частина', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
					mz_acf( 'lead', 'Текст', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
					mz_acf_list( 'thanks_steps', 'Що буде далі (кроки)', null, 5, 'Додати крок' ),
				),
			)
		);

		// ============================================================ Кейс
		$GLOBALS['mz_acf_g'] = 'case';
		mz_acf_group(
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
					mz_acf_rich( 'case_problem', 'У чому складність' ),
					mz_acf( 'case_work_intro', 'Що зроблено — вступ', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
					mz_acf_list( 'case_work', 'Що зроблено — кроки', null, 0, 'Додати крок' ),
					mz_acf_rich( 'case_result', 'Результат' ),
					mz_acf( 'meta_desc', 'SEO: description', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
				),
			)
		);
	}
);

// Візуальний вибір іконок і дрібні стилі полів в адмінці
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || false !== strpos( (string) $hook, 'mz-settings' ) ) {
			wp_enqueue_style( 'mz-admin', MZ_URI . '/assets/css/admin.css', array(), MZ_VER );
		}
	}
);
