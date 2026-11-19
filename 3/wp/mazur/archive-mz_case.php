<?php
/**
 * Розділ «Кейси» (/keysy/).
 */
defined( 'ABSPATH' ) || exit;
get_header();
$cases    = mz_cases();
$services = mz_services();
$count    = array();
foreach ( $cases as $c ) {
	$sv = mz_service_by_id( (int) mz_field( 'case_service', $c->ID ) );
	if ( $sv ) {
		$count[ $sv['key'] ] = isset( $count[ $sv['key'] ] ) ? $count[ $sv['key'] ] + 1 : 1;
	}
}
?>

<!-- ============ CASES HERO ============ -->
<section class="cases-hero" data-hero>
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container">
    <nav class="crumbs" aria-label="<?php echo mz_a( 'Хлібні крихти' ); ?>" data-hero-in>
      <a href="<?php echo esc_url( mz_url( 'home' ) ); ?>"><?php mz_e( 'Головна' ); ?></a><i class="ic ic-li-diamond ic-gold"></i>
      <span><?php mz_e( 'Кейси' ); ?></span>
    </nav>
    <div class="cases-hero__head">
      <div>
        <span class="eyebrow" data-hero-in><?php mz_e( 'Практика' ); ?></span>
        <h1 class="cases-hero__title" data-chars><?php mz_e( 'Кейси' ); ?> <span class="gold-text"><?php mz_e( 'з практики' ); ?></span></h1>
      </div>
      <p class="cases-hero__text" data-hero-in><?php mz_e( 'У цьому розділі — приклади справ, у яких я надавав правову допомогу. Кожна справа індивідуальна, але приклади показують, як я працюю і яких результатів можна досягти.' ); ?></p>
    </div>
  </div>
</section>

<!-- ============ CASES LIST ============ -->
<section class="section section--tight cases" data-cases>
  <div class="container cases__grid">
    <aside class="cases__filter" data-reveal>
      <span class="mini-label"><?php mz_e( 'Напрями' ); ?></span>
      <div class="cases__chips" role="tablist">
        <button class="chip is-active" type="button" data-filter="all"><?php mz_e( 'Усі кейси' ); ?> <b><?php echo count( $cases ); ?></b></button>
        <?php foreach ( $services as $s ) : if ( empty( $count[ $s['key'] ] ) ) { continue; } ?>
        <button class="chip" type="button" data-filter="<?php echo esc_attr( $s['key'] ); ?>"><?php echo esc_html( $s['nav'] ); ?> <b><?php echo (int) $count[ $s['key'] ]; ?></b></button>
        <?php endforeach; ?>
      </div>
      <div class="cases__cta">
        <i class="ic ic-gold ic-consult"></i>
        <p><?php mz_e( 'Маєте схожу ситуацію? Розкажіть про неї — першу консультацію проводжу безкоштовно.' ); ?></p>
        <button class="btn btn--gold btn--md btn--block" type="button" data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'Безкоштовна консультація' ); ?></button>
      </div>
    </aside>

    <div class="cases__list" data-reveal-group>
      <?php foreach ( $cases as $c ) { get_template_part( 'template-parts/case-card', null, array( 'post' => $c ) ); } ?>
    </div>
  </div>
</section>

<?php
get_footer();
