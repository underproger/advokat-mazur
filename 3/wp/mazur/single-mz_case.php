<?php
/**
 * Сторінка кейсу.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$id   = get_the_ID();
$site = mz_site();
$sv   = mz_service_by_id( (int) mz_field( 'case_service', $id ) );
$more = mz_cases( 0, -1, $id );
// спочатку кейси тієї ж послуги
usort(
	$more,
	function ( $a, $b ) use ( $id ) {
		$s  = (int) mz_field( 'case_service', $id );
		$ka = (int) mz_field( 'case_service', $a->ID ) === $s ? 0 : 1;
		$kb = (int) mz_field( 'case_service', $b->ID ) === $s ? 0 : 1;
		return $ka - $kb;
	}
);
$more = array_slice( $more, 0, 3 );
?>

<article class="case" data-hero>
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container case__grid">

    <!-- sidebar: адвокат -->
    <aside class="case__side">
      <div class="lawyer-card" data-hero-in>
        <div class="lawyer-card__photo"><img src="<?php echo esc_url( mz_img( 'lawyer-avatar.webp' ) ); ?>" alt="<?php echo mz_a( 'Адвокат Сергій Мазур' ); ?>" width="300" height="300" loading="lazy"></div>
        <b class="lawyer-card__name"><?php echo esc_html( $site['name'] ); ?></b>
        <span class="lawyer-card__role"><?php mz_e( 'Адвокат · Свідоцтво №' ); ?> <?php echo esc_html( $site['cert'] ); ?></span>
        <div class="lawyer-card__contacts">
          <span><?php mz_e( 'Телефон' ); ?></span>
          <a href="tel:<?php echo esc_attr( $site['phone_href'] ); ?>" data-track="case"><?php echo esc_html( $site['phone'] ); ?></a>
          <span>E-mail</span>
          <a href="mailto:<?php echo esc_attr( $site['email'] ); ?>"><?php echo esc_html( $site['email'] ); ?></a>
        </div>
        <p class="lawyer-card__text"><?php echo esc_html( $site['years'] ); ?> <?php mz_e( 'років практики. Справу веду особисто — без помічників і посередників.' ); ?></p>
        <button class="btn btn--gold btn--md btn--block" type="button" data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'Зв\'язатися зараз' ); ?></button>
      </div>
    </aside>

    <!-- article -->
    <div class="case__body">
      <nav class="crumbs" aria-label="<?php echo mz_a( 'Хлібні крихти' ); ?>" data-hero-in>
        <a href="<?php echo esc_url( mz_url( 'home' ) ); ?>"><?php mz_e( 'Головна' ); ?></a><i class="ic ic-li-diamond ic-gold"></i>
        <a href="<?php echo esc_url( mz_url( 'keysy' ) ); ?>"><?php mz_e( 'Кейси' ); ?></a><i class="ic ic-li-diamond ic-gold"></i>
        <span><?php echo esc_html( $sv ? $sv['nav'] : '' ); ?></span>
      </nav>
      <h1 class="case__title" data-chars><?php mz_e( 'Кейс:' ); ?> <?php the_title(); ?></h1>
      <div class="case__meta" data-hero-in>
        <?php if ( $sv ) : ?><a class="badge" href="<?php echo esc_url( $sv['url'] ); ?>"><i class="ic ic-gold <?php echo esc_attr( $sv['icon'] ); ?>"></i><?php echo esc_html( $sv['nav'] ); ?></a><?php endif; ?>
        <?php if ( mz_field( 'case_read' ) ) : ?><span class="badge badge--muted"><i class="ic ic-clock"></i><?php mz_e( 'Час прочитання:' ); ?> <?php echo (int) mz_field( 'case_read' ); ?> <?php mz_e( 'хв' ); ?></span><?php endif; ?>
        <?php if ( mz_field( 'case_date' ) ) : ?><span class="badge badge--muted"><?php echo esc_html( mz_field( 'case_date' ) ); ?></span><?php endif; ?>
      </div>

      <div class="case__content">
        <p class="case__lead" data-reveal><?php echo esc_html( mz_field( 'case_lead' ) ); ?></p>
        <?php if ( mz_field( 'case_quote' ) ) : ?><blockquote class="quote" data-reveal><?php echo esc_html( mz_field( 'case_quote' ) ); ?></blockquote><?php endif; ?>

        <h2 class="case__h2" data-reveal><span>01</span><?php mz_e( 'У чому полягала складність ситуації?' ); ?></h2>
        <?php foreach ( mz_paras( mz_field( 'case_problem' ) ) as $p ) : ?>
        <p data-reveal><?php echo mz_rich( $p ); // phpcs:ignore ?></p>
        <?php endforeach; ?>

        <h2 class="case__h2" data-reveal><span>02</span><?php mz_e( 'Що було зроблено' ); ?></h2>
        <p data-reveal><?php echo esc_html( mz_field( 'case_work_intro' ) ); ?></p>
        <ol class="case__steps" data-reveal-group>
          <?php foreach ( mz_lines( mz_field( 'case_work' ) ) as $i => $w ) : ?>
          <li><span><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php echo esc_html( $w ); ?></li>
          <?php endforeach; ?>
        </ol>

        <h2 class="case__h2" data-reveal><span>03</span><?php mz_e( 'Результат' ); ?></h2>
        <div class="case__result" data-reveal>
          <i class="ic ic-gold ic-check-circle"></i>
          <div>
            <?php foreach ( mz_paras( mz_field( 'case_result' ) ) as $p ) : ?>
            <p><?php echo mz_rich( $p ); // phpcs:ignore ?></p>
            <?php endforeach; ?>
          </div>
        </div>

        <p class="case__note" data-reveal><?php mz_e( 'Результат конкретної справи залежить від її обставин і не гарантує аналогічного рішення в інших випадках.' ); ?></p>

        <div class="case__cta" data-reveal>
          <div>
            <h2><?php mz_e( 'Схожа ситуація?' ); ?></h2>
            <p><?php mz_e( 'Опишіть її — проведу безкоштовну консультацію та запропоную план дій. Детальніше про послугу — на сторінці «' ); ?><?php if ( $sv ) : ?><a href="<?php echo esc_url( $sv['url'] ); ?>"><?php echo esc_html( $sv['nav'] ); ?></a><?php endif; ?>».</p>
          </div>
          <button class="btn btn--dark btn--lg" type="button" data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'Безкоштовна консультація' ); ?></button>
        </div>
      </div>
    </div>
  </div>
</article>

<?php if ( $more ) : ?>
<section class="section section--alt">
  <div class="container">
    <div class="sec-head sec-head--split">
      <div>
        <span class="eyebrow" data-reveal><?php mz_e( 'Кейси' ); ?></span>
        <h2 class="h2" data-split><?php mz_e( 'Інші' ); ?> <span class="gold-text"><?php mz_e( 'кейси' ); ?></span></h2>
      </div>
      <a class="btn btn--ghost btn--md" href="<?php echo esc_url( mz_url( 'keysy' ) ); ?>" data-reveal><?php mz_e( 'Усі кейси' ); ?> <i class="ic ic-gold ic-arrow-right"></i></a>
    </div>
    <div class="cases__list cases__list--3" data-reveal-group>
      <?php foreach ( $more as $c ) { get_template_part( 'template-parts/case-card', null, array( 'post' => $c ) ); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
get_footer();
