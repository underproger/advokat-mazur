<?php
/**
 * Шапка: head, прелоадер, header з мегаменю.
 */
defined( 'ABSPATH' ) || exit;
$site     = mz_site();
$kind     = mz_kind();
$langs    = mz_lang_links();
$cur_lang = 'UA';
foreach ( $langs as $l ) {
	if ( $l['active'] ) {
		$cur_lang = $l['label'];
	}
}
$cur_id = get_queried_object_id();
?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-motion="force">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- прелоадер (повний — лише при першому заході в сесії) і шторка переходу між сторінками -->
<div class="preloader" data-preloader aria-hidden="true">
  <div class="preloader__panel preloader__panel--top"></div>
  <div class="preloader__panel preloader__panel--bottom"></div>
  <div class="preloader__inner">
    <svg class="preloader__mono" viewBox="0 0 200 220"><defs><linearGradient id="monoGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f8e2a6"/><stop offset=".55" stop-color="#e2b85e"/><stop offset="1" stop-color="#b9883a"/></linearGradient></defs><text x="100" y="170" text-anchor="middle"><?php mz_e( 'М' ); ?></text></svg>
    <span class="preloader__line"><i></i></span>
    <span class="preloader__meta"><span><?php mz_e( 'Адвокат Мазур' ); ?></span><b data-preloader-count>0</b></span>
  </div>
</div>
<div class="page-curtain" aria-hidden="true" data-curtain></div>

<!-- ============ HEADER ============ -->
<header class="hdr" id="top">
  <div class="container container--wide hdr__in">
    <a class="logo" href="<?php echo esc_url( mz_url( 'home' ) ); ?>">
      <span class="logo__name"><?php mz_e( 'Адвокат Мазур' ); ?></span>
      <span class="logo__sub"><?php mz_e( 'Кримінальні, адміністративні та транспортні справи' ); ?></span>
    </a>

    <nav class="hdr__nav" aria-label="<?php echo mz_a( 'Головне меню' ); ?>">
      <div class="hdr__menu">
        <button class="hdr__link hdr__link--drop<?php echo 'service' === $kind ? ' is-current' : ''; ?>" type="button" data-mega-toggle aria-expanded="false"><?php mz_e( 'Послуги' ); ?> <i class="ic ic-chevron-down"></i></button>
        <a class="hdr__link<?php echo 'home' === $kind ? ' is-current' : ''; ?>" href="<?php echo esc_url( mz_url( 'home', '#about' ) ); ?>"><?php mz_e( 'Про мене' ); ?></a>
        <?php if ( mz_has_cases() ) : ?>
        <a class="hdr__link<?php echo in_array( $kind, array( 'cases', 'case' ), true ) ? ' is-current' : ''; ?>" href="<?php echo esc_url( mz_url( 'keysy' ) ); ?>"><?php mz_e( 'Кейси' ); ?></a>
        <?php endif; ?>
        <a class="hdr__link" href="<?php echo esc_url( mz_url( 'home', '#reviews' ) ); ?>"><?php mz_e( 'Відгуки' ); ?></a>
        <a class="hdr__link" href="#contacts"><?php mz_e( 'Контакти' ); ?></a>
      </div>
      <div class="hdr__actions">
        <?php if ( count( $langs ) > 1 ) : ?>
        <div class="lang" data-lang>
          <button class="lang__btn" type="button" aria-haspopup="true" aria-expanded="false" aria-label="<?php echo mz_a( 'Мова сайту' ); ?>"><?php echo esc_html( $cur_lang ); ?> <i class="ic ic-chevron-down"></i></button>
          <div class="lang__menu" role="menu">
            <?php foreach ( $langs as $l ) : ?>
            <a class="lang__item<?php echo $l['active'] ? ' is-active' : ''; ?>" role="menuitem" href="<?php echo esc_url( $l['url'] ); ?>" hreflang="<?php echo esc_attr( $l['code'] ); ?>" lang="<?php echo esc_attr( $l['code'] ); ?>"><b><?php echo esc_html( $l['label'] ); ?></b><?php echo esc_html( $l['name'] ); ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <a class="hdr__phone" href="tel:<?php echo esc_attr( $site['phone_href'] ); ?>" data-track="header"><i class="ic ic-gold ic-phone"></i><?php echo esc_html( $site['phone'] ); ?></a>
        <?php if ( mz_has_contact_section() ) : ?>
        <a class="btn btn--gold btn--xs" href="#contact"><?php mz_e( 'Консультація' ); ?></a>
        <?php else : ?>
        <button class="btn btn--gold btn--xs" type="button" data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'Консультація' ); ?></button>
        <?php endif; ?>
      </div>
    </nav>

    <button class="burger" type="button" aria-label="<?php echo mz_a( 'Меню' ); ?>" data-burger><span></span><span></span><span></span></button>
  </div>

  <div class="mega" data-mega>
    <div class="container container--wide">
      <div class="mega__grid">
        <?php foreach ( mz_services() as $s ) : ?>
        <a class="mega__item<?php echo (int) $s['id'] === (int) $cur_id ? ' is-active' : ''; ?>" href="<?php echo esc_url( $s['url'] ); ?>"><i class="ic ic-gold <?php echo esc_attr( $s['icon'] ); ?>"></i><?php echo esc_html( $s['nav'] ); ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</header>

<main>
