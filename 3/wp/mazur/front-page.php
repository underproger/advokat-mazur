<?php
/**
 * Головна (дизайн 3). Усі тексти — поля ACF «Головна сторінка» (UA і RU — окремі сторінки-переклади);
 * порожнє поле → значення за замовчуванням з inc/home-defaults.php.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$id       = get_queried_object_id();
$site     = mz_site();
$services = mz_services();
$def      = mz_home_defaults();
/** Поле головної або значення за замовчуванням. */
$h = function ( $name ) use ( $id, $def ) {
	$v = mz_field( $name, $id );
	if ( null === $v || false === $v || '' === $v || array() === $v ) {
		return isset( $def[ $name ] ) ? $def[ $name ] : '';
	}
	return $v;
};
$img = function ( $name, $file, $alt, $w, $hgt, $lazy = true ) use ( $id ) {
	$aid = (int) mz_field( $name, $id );
	if ( $aid ) {
		return wp_get_attachment_image( $aid, 'mz-hero', false, array( 'alt' => $alt, 'loading' => $lazy ? 'lazy' : 'eager' ) );
	}
	return sprintf( '<img src="%s" alt="%s" width="%d" height="%d"%s>', esc_url( mz_img( $file ) ), esc_attr( $alt ), $w, $hgt, $lazy ? ' loading="lazy"' : ' fetchpriority="high"' );
};
$reviews    = mz_rows( mz_field( 'home_reviews', $id ), 'text' );
$faq        = mz_rows( mz_field( 'home_faq', $id ), 'q' );
$stats      = mz_rows( $h( 'stats' ), 'num' );
$steps      = mz_rows( $h( 'steps' ), 'text' );
$reasons    = mz_rows( $h( 'reasons' ), 'title' );
$g_rating   = mz_opt( 'google_rating', '' );
$g_count    = mz_opt( 'google_count', '' );
$g_url      = mz_opt( 'google_url', '' );
$review_url = mz_opt( 'review_url', '' );
?>

<!-- ============ 01 HERO ============ -->
<section class="h-hero" data-hero>
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container container--wide h-hero__grid">
    <div class="h-hero__content">
      <span class="eyebrow" data-hero-in><?php echo esc_html( $h( 'hero_eyebrow' ) ); ?></span>
      <h1 class="h-hero__title" data-chars><?php echo esc_html( $h( 'hero_h1' ) ); ?> <span class="gold-text"><?php echo esc_html( $h( 'hero_h1_gold' ) ); ?></span> <?php echo esc_html( $h( 'hero_h1_after' ) ); ?></h1>
      <p class="h-hero__lead" data-hero-in><?php echo esc_html( $h( 'hero_lead' ) ); ?></p>
      <div class="h-hero__btns" data-hero-in>
        <a class="btn btn--gold btn--xl btn--shine" href="#contact"><?php echo esc_html( $h( 'hero_btn' ) ); ?></a>
        <button class="btn btn--ghost btn--call" type="button" data-modal-open data-topic="<?php echo mz_a( 'Зворотний дзвінок' ); ?>"><i class="ic ic-gold ic-phone"></i><?php echo esc_html( $h( 'hero_call' ) ); ?></button>
      </div>
      <div class="h-hero__trust" data-hero-in>
        <?php foreach ( mz_list( $h( 'hero_trust' ) ) as $i => $tr ) : ?>
        <?php echo $i ? '<i class="ic ic-li-diamond ic-gold"></i>' : ''; ?><span><?php echo esc_html( $tr ); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="h-hero__media">
      <div class="h-hero__photo" data-parallax>
        <?php echo $img( 'hero_image', 'lawyer-hero.webp', mz_t( 'Адвокат Мазур' ), 979, 1322, false ); // phpcs:ignore ?>
      </div>
    </div>
  </div>
</section>

<?php if ( mz_show( 'stats', $id ) && $stats ) : ?>
<!-- ============ 02a STATS ============ -->
<div class="h-stats">
  <div class="h-stats__box">
    <div class="h-stats__grid">
      <?php
      foreach ( $stats as $st ) :
		  $num = trim( (string) $st['num'] );
		  ?>
      <div class="h-stats__item"><span class="h-stats__num">
        <?php if ( ! empty( $st['count'] ) && preg_match( '/^(\d+)(.*)$/u', $num, $m ) ) : ?>
        <span data-count="<?php echo esc_attr( $m[1] ); ?>">0</span><?php echo '' !== $m[2] ? '<span class="h-stats__gold">' . esc_html( $m[2] ) . '</span>' : ''; ?>
        <?php else : ?>
        <?php echo esc_html( $num ); ?>
        <?php endif; ?>
        <?php echo ! empty( $st['unit'] ) ? ' <span class="h-stats__unit">' . esc_html( $st['unit'] ) . '</span>' : ''; ?></span><span class="h-stats__label"><?php echo esc_html( $st['label'] ); ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ( mz_show( 'about', $id ) ) : ?>
<!-- ============ 02b ABOUT ============ -->
<section class="h-about" id="about">
  <div class="h-about__in">
    <span class="eyebrow" data-reveal><?php echo esc_html( $h( 'about_eyebrow' ) ); ?></span>
    <h2 class="h-about__title" data-split><?php echo esc_html( $h( 'about_title' ) ); ?> <span class="gold-text"><?php echo esc_html( $h( 'about_gold' ) ); ?></span></h2>
    <p class="h-about__text" data-wow-fill><?php echo esc_html( $h( 'about_text' ) ); ?></p>
  </div>
</section>
<?php endif; ?>

<?php if ( mz_show( 'services', $id ) ) : ?>
<!-- ============ 03 SERVICES ============ -->
<section class="h-services" id="services">
  <div class="h-head">
    <span class="h-head__ghost" aria-hidden="true"><?php mz_e( 'Послуги' ); ?></span>
    <h2 class="h-head__title h-head__title--gold" data-split><?php echo esc_html( $h( 'services_title' ) ); ?></h2>
    <p class="h-head__text" data-reveal><?php echo esc_html( count( $services ) . ' ' . $h( 'services_text' ) ); ?></p>
  </div>
  <div class="container h-services__grid" data-reveal-group>
    <?php foreach ( $services as $s ) : ?>
    <article class="svc spot">
      <span class="svc__tile"><span><i class="ic ic-gold <?php echo esc_attr( $s['icon'] ); ?>"></i></span></span>
      <span class="svc__label"><?php echo esc_html( $s['category'] ); ?></span>
      <h3 class="svc__title"><a href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['nav'] ); ?></a></h3>
      <i class="ic ic-li-diamond ic-gold svc__diamond"></i>
      <ul class="svc__list">
        <?php foreach ( $s['home_items'] as $it ) : ?>
        <li><i class="ic ic-li-diamond ic-gold"></i><?php echo esc_html( $it ); ?></li>
        <?php endforeach; ?>
      </ul>
      <a class="svc__more" href="<?php echo esc_url( $s['url'] ); ?>"><?php mz_e( 'Детальніше' ); ?></a>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ( mz_show( 'seo1', $id ) ) : ?>
<!-- ============ 04 SEO TEXT 1 ============ -->
<section class="h-seo">
  <div class="container">
    <div class="h-seo__in">
      <h2 class="h-seo__title" data-reveal><?php echo esc_html( $h( 'seo1_title' ) ); ?></h2>
      <article class="h-seo__article" data-more>
        <p data-reveal><?php echo esc_html( $h( 'seo1_lead' ) ); ?></p>
        <?php if ( $h( 'seo1_more' ) ) : ?>
        <div class="h-seo__more" data-more-body><div>
          <?php echo mz_html( $h( 'seo1_more' ) ); // phpcs:ignore ?>
        </div></div>
        <button class="more-btn" type="button" data-more-toggle aria-expanded="false"><span><?php mz_e( 'Читати повністю' ); ?></span> <i class="ic ic-gold ic-chevron-down"></i></button>
        <?php endif; ?>
      </article>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ( mz_show( 'steps', $id ) && $steps ) : ?>
<!-- ============ 05 STEPS ============ -->
<section class="h-steps" id="steps">
  <div class="h-head h-head--steps">
    <span class="h-head__ghost" aria-hidden="true"><?php mz_e( 'Етапи' ); ?></span>
    <h2 class="h-head__title h-head__title--white" data-split><?php echo esc_html( $h( 'steps_title' ) ); ?></h2>
  </div>
  <div class="container">
    <ol class="h-steps__grid" data-steps>
      <?php foreach ( $steps as $i => $st ) : ?>
      <li class="h-step" data-reveal>
        <?php if ( $i < count( $steps ) - 1 ) : ?>
        <span class="h-step__line" aria-hidden="true"><span></span></span>
        <?php endif; ?>
        <div class="h-step__icon">
          <span class="h-step__badge"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
          <span class="h-step__tile"><span><i class="ic ic-gold <?php echo esc_attr( $st['icon'] ); ?>"></i></span></span>
        </div>
        <p class="h-step__text"><?php echo esc_html( $st['text'] ); ?></p>
      </li>
      <?php endforeach; ?>
    </ol>
    <div class="h-steps__cta" data-reveal>
      <a class="btn btn--gold btn--xl btn--shine" href="#contact"><?php echo esc_html( $h( 'steps_btn' ) ); ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ( mz_show( 'why', $id ) ) : ?>
<!-- ============ 06 WHY ============ -->
<section class="h-why" id="why">
  <div class="container h-why__in">
    <div class="h-why__photo" data-wow-reveal>
      <div data-parallax><?php echo $img( 'why_image', 'lawyer-desk.webp', mz_t( 'Адвокат Мазур у робочому кабінеті' ), 853, 1280 ); // phpcs:ignore ?></div>
    </div>
    <div class="h-why__content">
      <span class="eyebrow" data-reveal><?php echo esc_html( $h( 'why_eyebrow' ) ); ?></span>
      <h2 class="h-why__title" data-split><?php echo esc_html( $h( 'why_title' ) ); ?> <span class="gold-text"><?php echo esc_html( $h( 'why_gold' ) ); ?></span></h2>
      <div class="h-why__grid" data-reveal-group>
        <?php foreach ( $reasons as $r ) : ?>
        <div class="reason">
          <span class="reason__head"><i class="ic ic-gold <?php echo esc_attr( $r['icon'] ); ?>"></i><span><?php echo esc_html( $r['title'] ); ?></span></span>
          <p><?php echo esc_html( $r['text'] ); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="h-why__btns" data-reveal>
        <a class="btn btn--gold btn--why btn--shine" href="#contact"><?php echo esc_html( $h( 'why_btn' ) ); ?></a>
        <button class="btn btn--ghost btn--call btn--call-sm" type="button" data-modal-open data-topic="<?php echo mz_a( 'Зворотний дзвінок' ); ?>"><i class="ic ic-gold ic-phone"></i><?php echo esc_html( $h( 'why_call' ) ); ?></button>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ 07 CONTACT ============ -->
<section class="h-contact" id="contact">
  <div class="container">
    <div class="h-contact__box" data-reveal>
      <div class="h-contact__media" data-wow-reveal>
        <div data-parallax><?php echo $img( 'contact_image', 'themis.webp', mz_t( 'Адвокат Мазур' ), 941, 941 ); // phpcs:ignore ?></div>
      </div>
      <div class="h-contact__body" data-form-wrap>
        <span class="eyebrow"><?php mz_e( 'Заявка' ); ?></span>
        <h2 class="h-contact__title" data-split><?php echo esc_html( $h( 'contact_title' ) ); ?></h2>
        <p class="h-contact__text"><?php echo esc_html( $h( 'contact_text' ) ); ?></p>
        <form class="form h-form" data-form data-form-location="home">
          <label class="field field--h"><i class="ic ic-user"></i><input type="text" name="name" placeholder="<?php echo mz_a( 'Ваше ім\'я' ); ?>" autocomplete="name" required></label>
          <label class="field field--h"><i class="ic ic-phone"></i><input type="tel" name="phone" placeholder="<?php echo mz_a( 'Номер телефону' ); ?>" autocomplete="tel" required></label>
          <div class="field field--h field--select h-form__full"><i class="ic ic-briefcase"></i>
            <select name="topic" data-select aria-label="<?php echo mz_a( 'Напрям' ); ?>">
              <option value=""><?php mz_e( 'Оберіть напрям' ); ?></option>
              <?php foreach ( $services as $s ) : ?>
              <option><?php echo esc_html( $s['nav'] ); ?></option>
              <?php endforeach; ?>
              <option><?php mz_e( 'Інше' ); ?></option>
            </select>
          </div>
          <button class="btn btn--gold btn--block h-form__full h-form__submit" type="submit"><?php echo esc_html( $h( 'contact_btn' ) ); ?></button>
          <p class="form__policy h-form__full"><?php mz_e( 'Натискаючи кнопку, ви погоджуєтеся на обробку' ); ?> <?php $pu = mz_privacy_url(); echo $pu ? '<a href="' . esc_url( $pu ) . '">' . esc_html( mz_t( 'персональних даних' ) ) . '</a>' : esc_html( mz_t( 'персональних даних' ) ); ?>.</p>
        </form>
        <div class="form-success">
          <i class="ic ic-gold ic-check-circle"></i>
          <div><b><?php mz_e( 'Дякую.' ); ?></b> <?php mz_e( 'Заявку отримано. Я зв\'яжуся з вами найближчим часом.' ); ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ( mz_show( 'reviews', $id ) && $reviews ) : ?>
<!-- ============ 08 REVIEWS ============ -->
<section class="reviews h-reviews" id="reviews">
  <div class="container reviews__head">
    <div>
      <span class="eyebrow" data-reveal><?php mz_e( 'Відгуки клієнтів' ); ?></span>
      <h2 class="h-head__title h-head__title--gold" data-split><?php echo esc_html( $h( 'reviews_title' ) ); ?></h2>
      <?php if ( $g_rating ) : ?>
      <span class="rating" data-reveal><b><?php echo esc_html( $g_rating ); ?></b><?php get_template_part( 'template-parts/stars' ); ?><?php echo esc_html( $g_count ? str_replace( '48', $g_count, mz_t( '48 відгуків у Google' ) ) : '' ); ?></span>
      <?php endif; ?>
    </div>
    <div class="arrows" data-reveal>
      <button class="arrow" type="button" aria-label="<?php echo mz_a( 'Назад' ); ?>" data-slider-prev><i class="ic ic-gold ic-arrow-left"></i></button>
      <button class="arrow arrow--gold" type="button" aria-label="<?php echo mz_a( 'Далі' ); ?>" data-slider-next><i class="ic ic-arrow-right"></i></button>
    </div>
  </div>

  <div class="reviews__track h-reviews__track" data-slider>
    <?php foreach ( $reviews as $v ) { get_template_part( 'template-parts/review', null, $v ); } ?>
  </div>

  <?php if ( $g_url || $review_url ) : ?>
  <div class="container reviews__foot" data-reveal>
    <?php if ( $g_url ) : ?><a class="link-gold link-gold--underline" href="<?php echo esc_url( $g_url ); ?>" target="_blank" rel="noopener"><?php mz_e( 'Усі відгуки в Google' ); ?> <i class="ic ic-gold ic-arrow-up-right"></i></a><?php endif; ?>
    <?php if ( $review_url ) : ?><a class="reviews__leave" href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener"><?php mz_e( 'Залишити відгук' ); ?></a><?php endif; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ( mz_show( 'faq', $id ) && $faq ) : ?>
<!-- ============ 09 FAQ ============ -->
<section class="h-faq" id="faq">
  <div class="container faq">
    <div class="faq__side">
      <span class="eyebrow" data-reveal><?php mz_e( 'Питання та відповіді' ); ?></span>
      <h2 class="h2 h-faq__title" data-split><?php echo esc_html( $h( 'faq_title' ) ); ?> <span class="gold-text"><?php echo esc_html( $h( 'faq_gold' ) ); ?></span></h2>
      <p class="sec-head__text" data-reveal><?php echo esc_html( $h( 'faq_text' ) ); ?></p>
      <button class="btn btn--ghost btn--call btn--call-sm" type="button" data-modal-open data-topic="<?php echo mz_a( 'Інше' ); ?>" data-reveal><i class="ic ic-gold ic-chat"></i><?php mz_e( 'Поставити питання' ); ?></button>
    </div>
    <div class="faq__list" data-reveal-group>
      <?php foreach ( $faq as $i => $q ) { get_template_part( 'template-parts/faq-item', null, array( 'q' => $q['q'], 'a' => $q['a'], 'open' => 0 === $i ) ); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ( mz_show( 'seo2', $id ) ) : ?>
<!-- ============ 10 SEO TEXT 2 ============ -->
<section class="h-seo h-seo--last">
  <div class="container">
    <div class="h-seo__in">
      <h2 class="h-seo__title" data-reveal><?php echo esc_html( $h( 'seo2_title' ) ); ?></h2>
      <article class="h-seo__article" data-more>
        <p data-reveal><?php echo esc_html( $h( 'seo2_lead' ) ); ?></p>
        <?php if ( $h( 'seo2_more' ) ) : ?>
        <div class="h-seo__more" data-more-body><div>
          <?php echo mz_html( $h( 'seo2_more' ) ); // phpcs:ignore ?>
        </div></div>
        <button class="more-btn" type="button" data-more-toggle aria-expanded="false"><span><?php mz_e( 'Читати повністю' ); ?></span> <i class="ic ic-gold ic-chevron-down"></i></button>
        <?php endif; ?>
      </article>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
get_footer();
