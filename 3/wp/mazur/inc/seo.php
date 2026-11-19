<?php
/**
 * <title>, meta description, Open Graph, фавікони, коди з налаштувань.
 * (На сайті немає SEO-плагіна. Якщо поставите Yoast/RankMath — вимкніть mz_seo_meta нижче.)
 */

defined( 'ABSPATH' ) || exit;

/** SEO-заголовок/опис поточної сторінки з полів ACF. */
function mz_seo() {
	static $seo = null;
	if ( null !== $seo ) {
		return $seo;
	}
	$seo  = array( 'title' => '', 'desc' => '' );
	$kind = mz_kind();
	$id   = get_queried_object_id();
	if ( is_singular() && $id ) {
		$seo['title'] = (string) mz_field( 'meta_title', $id );
		$seo['desc']  = (string) mz_field( 'meta_desc', $id );
		if ( 'case' === $kind && ! $seo['desc'] ) {
			$seo['desc'] = wp_html_excerpt( (string) mz_field( 'case_lead', $id ), 180, '…' );
		}
		if ( 'case' === $kind && ! $seo['title'] ) {
			$seo['title'] = mz_t( 'Кейс:' ) . ' ' . get_the_title( $id ) . ' | ' . mz_t( 'Адвокат Сергій Мазур' );
		}
	} elseif ( 'cases' === $kind ) {
		$seo['title'] = mz_t( 'Кейси адвоката Сергія Мазура — приклади справ з практики' );
		$seo['desc']  = mz_t( 'Приклади справ з практики адвоката Сергія Мазура: трудові спори, ТЦК, корупційні справи, штрафи Укртрансбезпеки, ліцензії перевізників.' );
	}
	return $seo;
}

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		$s = mz_seo();
		return $s['title'] ? $s['title'] : $title;
	}
);

function mz_seo_meta() {
	$s = mz_seo();
	if ( $s['desc'] ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $s['desc'] ) ) );
	}
	$title = $s['title'] ? $s['title'] : wp_get_document_title();
	$url   = is_singular() ? get_permalink() : ( is_post_type_archive( 'mz_case' ) ? get_post_type_archive_link( 'mz_case' ) : home_url( add_query_arg( array() ) ) );
	$img   = mz_img( 'lawyer-standing.webp' );
	if ( 'service' === mz_kind() && ( $hid = mz_field( 'hero_image' ) ) ) {
		$src = wp_get_attachment_image_url( $hid, 'large' );
		$img = $src ? $src : $img;
	}
	printf( '<meta property="og:type" content="website">' . "\n" );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $s['desc'] ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $s['desc'] ) ) );
	}
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $img ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", 'ru' === mz_lang() ? 'ru_UA' : 'uk_UA' );
}
add_action( 'wp_head', 'mz_seo_meta', 2 );

// <html lang> за мовою сторінки (адмінка сайту — ru_RU, тож без цього було б ru-RU)
add_filter(
	'language_attributes',
	function ( $output ) {
		return preg_replace( '/lang="[^"]*"/', 'lang="' . ( 'ru' === mz_lang() ? 'ru' : 'uk' ) . '"', $output );
	}
);

// Подяка — не індексується
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( 'thanks' === mz_kind() ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
);

// Фавікони (з медіатеки чинного сайту) і коди з «Налаштування сайту» (it-rating тощо)
add_action(
	'wp_head',
	function () {
		if ( ! has_site_icon() ) {
			$up = content_url( 'uploads/2023/06/' );
			echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $up . 'apple-touch-icon.png' ) . '">' . "\n";
			echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $up . 'favicon-32x32-1.png' ) . '">' . "\n";
			echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( $up . 'favicon-16x16-1.png' ) . '">' . "\n";
		}
		$code = mz_opt( 'head_code', '' );
		if ( $code ) {
			echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- код задає адміністратор
		}
	},
	3
);

// JSON-LD: адвокат / юридичні послуги
add_action(
	'wp_footer',
	function () {
		if ( ! is_front_page() ) {
			return;
		}
		$s    = mz_site();
		$data = array(
			'@context'  => 'https://schema.org',
			'@type'     => 'LegalService',
			'name'      => mz_t( 'Адвокат Мазур' ),
			'url'       => mz_url( 'home' ),
			'telephone' => $s['phone_href'],
			'email'     => $s['email'],
			'image'     => mz_img( 'lawyer-standing.webp' ),
			'address'   => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => 'ru' === mz_lang() ? 'ул. Симона Петлюры, 8' : 'вул. Симона Петлюри, 8',
				'addressLocality' => 'ru' === mz_lang() ? 'Киев' : 'Київ',
				'addressCountry'  => 'UA',
			),
			'founder'   => array( '@type' => 'Person', 'name' => $s['name'], 'jobTitle' => mz_t( 'Адвокат' ) ),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
);
