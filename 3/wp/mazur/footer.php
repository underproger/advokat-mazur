<?php
/**
 * Футер, плаваючі кнопки, мобільне меню, модальне вікно заявки.
 */
defined( 'ABSPATH' ) || exit;
$site    = mz_site();
$kind    = mz_kind();
$cur_id  = get_queried_object_id();
$langs   = mz_lang_links();
$privacy = mz_privacy_url();
?>
</main>

<!-- ============ FOOTER ============ -->
<footer class="ftr" id="contacts">
  <div class="container ftr__grid">
    <div class="ftr__brand">
      <span class="ftr__logo"><?php mz_e( 'Адвокат Мазур' ); ?></span>
      <p><?php mz_e( 'Індивідуальна адвокатська практика. Свідоцтво про право на заняття адвокатською діяльністю №' ); ?> <?php echo esc_html( $site['cert'] ); ?>.</p>
      <div class="socials">
        <a href="<?php echo esc_url( $site['telegram'], array( 'https', 'http' ) ); ?>" target="_blank" rel="noopener" aria-label="Telegram"><i class="ic ic-gold ic-telegram"></i></a>
        <a href="<?php echo esc_url( $site['viber'], array( 'viber', 'https' ) ); ?>" aria-label="Viber"><i class="ic ic-gold ic-viber"></i></a>
        <a href="<?php echo esc_url( $site['whatsapp'], array( 'https', 'whatsapp' ) ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="ic ic-gold ic-whatsapp"></i></a>
      </div>
    </div>
    <div class="ftr__col">
      <span class="ftr__title"><?php mz_e( 'Послуги' ); ?></span>
      <?php foreach ( mz_services() as $s ) : ?>
      <a href="<?php echo esc_url( $s['url'] ); ?>"<?php echo (int) $s['id'] === (int) $cur_id ? ' class="is-active"' : ''; ?>><?php echo esc_html( $s['nav'] ); ?></a>
      <?php endforeach; ?>
    </div>
    <div class="ftr__col">
      <span class="ftr__title"><?php mz_e( 'Контакти' ); ?></span>
      <a class="ftr__phone" href="tel:<?php echo esc_attr( $site['phone_href'] ); ?>" data-track="footer"><?php echo esc_html( $site['phone'] ); ?></a>
      <a href="mailto:<?php echo esc_attr( $site['email'] ); ?>"><?php echo esc_html( $site['email'] ); ?></a>
      <span><?php echo esc_html( $site['address'] ); ?></span>
      <span><?php echo esc_html( $site['hours'] ); ?></span>
      <button class="btn btn--gold btn--sm" type="button" data-modal-open data-topic="<?php echo mz_a( 'Зворотний дзвінок' ); ?>"><?php mz_e( 'Замовити дзвінок' ); ?></button>
    </div>
  </div>
  <div class="container ftr__bottom">
    <span><?php echo esc_html( str_replace( '2026', gmdate( 'Y' ), mz_t( '© 2026 Адвокат Сергій Мазур. Всі права захищені.' ) ) ); ?></span>
    <div><?php if ( $privacy ) : ?><a href="<?php echo esc_url( $privacy ); ?>"><?php mz_e( 'Політика конфіденційності' ); ?></a><?php endif; ?><span class="ftr__dev"><?php mz_e( 'Створення та просування сайтів' ); ?> <a href="https://impulse-design.com.ua/" rel="nofollow" target="_blank">Impulse Design</a></span></div>
  </div>
</footer>

<!-- scroll to top -->
<button class="totop" type="button" aria-label="<?php echo mz_a( 'Прокрутити нагору' ); ?>" data-totop>
  <svg class="totop__ring" viewBox="0 0 56 56" aria-hidden="true"><circle class="totop__track" cx="28" cy="28" r="26"/><circle class="totop__bar" cx="28" cy="28" r="26"/></svg>
  <i class="ic ic-gold ic-arrow-up"></i>
</button>

<!-- floating call -->
<button class="fab" type="button" aria-label="<?php echo mz_a( 'Передзвонити' ); ?>" data-modal-open data-topic="<?php echo mz_a( 'Зворотний дзвінок' ); ?>"><i class="ic ic-phone-fill"></i></button>

<!-- mobile bottom bar -->
<div class="mbar">
  <a class="mbar__call" href="tel:<?php echo esc_attr( $site['phone_href'] ); ?>" aria-label="<?php echo mz_a( 'Подзвонити' ); ?>" data-track="mobile_bar"><i class="ic ic-phone-fill"></i></a>
  <button class="btn btn--gold mbar__btn" type="button" data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'Безкоштовна консультація' ); ?></button>
</div>

<!-- mobile menu -->
<div class="drawer" data-drawer>
  <div class="drawer__panel">
    <button class="drawer__close" type="button" aria-label="<?php echo mz_a( 'Закрити' ); ?>" data-drawer-close><i class="ic ic-close"></i></button>
    <a class="logo__name" href="<?php echo esc_url( mz_url( 'home' ) ); ?>"><?php mz_e( 'Адвокат Мазур' ); ?></a>
    <?php if ( count( $langs ) > 1 ) : ?>
    <div class="drawer__langs">
      <?php foreach ( $langs as $l ) : ?>
      <a class="<?php echo $l['active'] ? 'is-active' : ''; ?>" href="<?php echo esc_url( $l['url'] ); ?>" hreflang="<?php echo esc_attr( $l['code'] ); ?>" lang="<?php echo esc_attr( $l['code'] ); ?>"><?php echo esc_html( $l['label'] ); ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <nav class="drawer__nav">
      <span class="drawer__label"><?php mz_e( 'Послуги' ); ?></span>
      <?php foreach ( mz_services() as $s ) : ?>
      <a href="<?php echo esc_url( $s['url'] ); ?>"<?php echo (int) $s['id'] === (int) $cur_id ? ' class="is-active"' : ''; ?>><?php echo esc_html( $s['nav'] ); ?></a>
      <?php endforeach; ?>
      <span class="drawer__label"><?php mz_e( 'Сайт' ); ?></span>
      <a href="<?php echo esc_url( mz_url( 'home', '#about' ) ); ?>" data-drawer-close><?php mz_e( 'Про мене' ); ?></a>
      <?php if ( mz_has_cases() ) : ?>
      <a href="<?php echo esc_url( mz_url( 'keysy' ) ); ?>"<?php echo 'cases' === $kind ? ' class="is-active"' : ''; ?>><?php mz_e( 'Кейси' ); ?></a>
      <?php endif; ?>
      <a href="<?php echo esc_url( mz_url( 'home', '#steps' ) ); ?>" data-drawer-close><?php mz_e( 'Етапи роботи' ); ?></a>
      <a href="<?php echo esc_url( mz_url( 'home', '#reviews' ) ); ?>" data-drawer-close><?php mz_e( 'Відгуки' ); ?></a>
      <a href="#contacts" data-drawer-close><?php mz_e( 'Контакти' ); ?></a>
    </nav>
    <a class="hdr__phone" href="tel:<?php echo esc_attr( $site['phone_href'] ); ?>" data-track="menu"><i class="ic ic-gold ic-phone"></i><?php echo esc_html( $site['phone'] ); ?></a>
    <?php if ( mz_has_contact_section() ) : ?>
    <a class="btn btn--gold btn--md btn--block" href="#contact" data-drawer-close><?php mz_e( 'Безкоштовна консультація' ); ?></a>
    <?php else : ?>
    <button class="btn btn--gold btn--md btn--block" type="button" data-drawer-close data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'Безкоштовна консультація' ); ?></button>
    <?php endif; ?>
  </div>
</div>

<!-- modal -->
<div class="modal" data-modal role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <div class="modal__box">
    <button class="modal__close" type="button" aria-label="<?php echo mz_a( 'Закрити' ); ?>" data-modal-close><i class="ic ic-close"></i></button>
    <div class="modal__form">
      <span class="eyebrow"><?php mz_e( 'Безкоштовно · конфіденційно' ); ?></span>
      <h3 class="modal__title" id="modalTitle"><?php mz_e( 'Безкоштовна консультація' ); ?></h3>
      <p class="modal__text"><?php mz_e( 'Залиште ім\'я та номер — я передзвоню і безкоштовно оціню вашу ситуацію.' ); ?></p>
      <form class="form" data-form data-form-location="modal">
        <div class="field field--select"><i class="ic ic-briefcase"></i>
          <select name="topic" data-topic-select data-select aria-label="<?php echo mz_a( 'Тема звернення' ); ?>">
            <?php foreach ( mz_topics() as $t ) : ?>
            <option><?php echo esc_html( $t ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label class="field"><i class="ic ic-user"></i><input type="text" name="name" placeholder="<?php echo mz_a( 'Ваше ім\'я' ); ?>" autocomplete="name" required></label>
        <label class="field"><i class="ic ic-phone"></i><input type="tel" name="phone" placeholder="<?php echo mz_a( 'Номер телефону' ); ?>" autocomplete="tel" required></label>
        <button class="btn btn--gold btn--block btn--lg" type="submit"><?php mz_e( 'Передзвоніть мені' ); ?></button>
      </form>
    </div>
    <div class="modal__done">
      <i class="ic ic-gold ic-check-circle"></i>
      <h3 class="modal__title"><?php mz_e( 'Дякую.' ); ?></h3>
      <p class="modal__text"><?php mz_e( 'Скоро передзвоню з номера' ); ?> <?php echo esc_html( $site['phone'] ); ?>.</p>
    </div>
  </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
