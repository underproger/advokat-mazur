<?php
/**
 * Тема «Адвокат Мазур» (дизайн 3).
 *
 * Потрібні плагіни: ACF PRO (поля сторінок), Contact Form 7 (відправка заявок),
 * Polylang (UA/RU). Без Polylang тема працює однією мовою (UA).
 * Коди аналітики (GTM) підключає плагін GTM Kit через wp_head / wp_body_open.
 */

defined( 'ABSPATH' ) || exit;

define( 'MZ_VER', '1.0.0' );
define( 'MZ_DIR', get_template_directory() );
define( 'MZ_URI', get_template_directory_uri() );

require MZ_DIR . '/inc/i18n.php';
require MZ_DIR . '/inc/helpers.php';
require MZ_DIR . '/inc/setup.php';
require MZ_DIR . '/inc/cpt.php';
require MZ_DIR . '/inc/acf-fields.php';
require MZ_DIR . '/inc/seo.php';
require MZ_DIR . '/inc/forms.php';
require MZ_DIR . '/inc/redirects.php';

if ( is_admin() ) {
	require MZ_DIR . '/inc/importer.php';
}
