<?php
/**
 * Заявки: форми теми відправляються в Contact Form 7 через його REST API
 * (листи, отримувач, антиспам — у налаштуваннях форми CF7 «Мазур: заявка з сайту»).
 * Після успішної відправки — подія generate_lead у dataLayer і перехід на сторінку подяки
 * (/thanks/ — як на старому сайті, на ній можуть бути налаштовані конверсії).
 */

defined( 'ABSPATH' ) || exit;

function mz_cf7_form_id() {
	$id = (int) mz_opt( 'cf7_form', 0 );
	if ( ! $id ) {
		$id = (int) get_option( 'mz_cf7_form' );
	}
	return $id;
}

/** Дані для assets/js/site.js */
function mz_js_config() {
	$form = mz_cf7_form_id();
	$s    = mz_site();
	return array(
		'lang'     => mz_lang(),
		'endpoint' => $form ? esc_url_raw( rest_url( 'contact-form-7/v1/contact-forms/' . $form . '/feedback' ) ) : '',
		'formId'   => $form,
		'unitTag'  => 'wpcf7-f' . $form . '-p' . (int) get_queried_object_id() . '-o1',
		'postId'   => (int) get_queried_object_id(),
		'nonce'    => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
		'thanks'   => mz_thanks_url(),
		'page'     => mz_kind(),
		'service'  => 'service' === mz_kind() ? (string) mz_field( 'nav' ) : '',
		'i18n'     => array(
			'sending' => mz_t( 'Надсилаємо…' ),
			'error'   => mz_t( 'Не вдалося надіслати заявку. Перевірте ім’я і телефон або зателефонуйте:' ) . ' ' . $s['phone'],
			'name'    => mz_t( 'Вкажіть ім’я' ),
			'phone'   => mz_t( 'Вкажіть номер телефону' ),
			'topic'   => mz_t( 'Безкоштовна консультація' ),
		),
	);
}

function mz_thanks_url() {
	$p = mz_page_by_key( 'thanks' );
	if ( ! $p ) {
		$p = get_page_by_path( 'thanks' );
	}
	return $p ? get_permalink( $p ) : '';
}
