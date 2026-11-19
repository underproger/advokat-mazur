<?php
/**
 * 404.
 */
defined( 'ABSPATH' ) || exit;
if ( ! headers_sent() ) {
	status_header( 404 );
}
get_header();
?>
<section class="thanks thanks--404" data-hero>
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container thanks__in">
    <span class="eyebrow" data-hero-in>404</span>
    <h1 class="thanks__title" data-chars><?php mz_e( 'Сторінку не знайдено' ); ?></h1>
    <p class="thanks__lead" data-hero-in><?php mz_e( 'Можливо, сторінку перенесли. Оберіть послугу або поверніться на головну.' ); ?></p>
    <div class="related__list thanks__services" data-hero-in>
      <?php foreach ( mz_services() as $s ) : ?>
      <a href="<?php echo esc_url( $s['url'] ); ?>"><i class="ic ic-gold <?php echo esc_attr( $s['icon'] ); ?>"></i><?php echo esc_html( $s['nav'] ); ?><i class="ic ic-arrow-up-right"></i></a>
      <?php endforeach; ?>
    </div>
    <div class="thanks__btns" data-hero-in>
      <a class="btn btn--gold btn--lg btn--shine" href="<?php echo esc_url( mz_url( 'home' ) ); ?>"><?php mz_e( 'На головну' ); ?></a>
    </div>
  </div>
</section>
<?php
get_footer();
