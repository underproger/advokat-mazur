<?php
/**
 * Template Name: Подяка
 * Сторінка після відправки заявки (/thanks/, RU — /ru/spasibo/). Не індексується.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$site  = mz_site();
$steps = mz_lines( mz_field( 'thanks_steps' ) );
?>

<section class="thanks" data-hero>
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container thanks__in">
    <span class="thanks__icon" data-hero-in><i class="ic ic-gold ic-check-circle"></i></span>
    <span class="eyebrow" data-hero-in><?php mz_e( 'Дякую за заявку' ); ?></span>
    <h1 class="thanks__title" data-chars><?php echo esc_html( mz_field( 'h1' ) ?: mz_t( 'Дякую.' ) ); ?> <span class="gold-text"><?php echo esc_html( mz_field( 'h1_gold' ) ); ?></span></h1>
    <p class="thanks__lead" data-hero-in><?php echo esc_html( mz_field( 'lead' ) ); ?></p>

    <?php if ( $steps ) : ?>
    <div class="thanks__steps" data-hero-in>
      <span class="mini-label"><?php mz_e( 'Що буде далі' ); ?></span>
      <ol>
        <?php foreach ( $steps as $i => $st ) : ?>
        <li><span><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php echo esc_html( $st ); ?></li>
        <?php endforeach; ?>
      </ol>
    </div>
    <?php endif; ?>

    <div class="thanks__direct" data-hero-in>
      <span><?php mz_e( 'Терміново? Зв’яжіться напряму' ); ?></span>
      <a class="final__phone" href="tel:<?php echo esc_attr( $site['phone_href'] ); ?>" data-track="thanks"><i class="ic ic-gold ic-phone"></i><?php echo esc_html( $site['phone'] ); ?></a>
      <div class="socials"><?php get_template_part( 'template-parts/messengers', null, array( 'gold' => true ) ); ?></div>
    </div>

    <div class="thanks__btns" data-hero-in>
      <a class="btn btn--gold btn--lg btn--shine" href="<?php echo esc_url( mz_url( 'home' ) ); ?>"><?php mz_e( 'На головну' ); ?></a>
      <a class="btn btn--ghost btn--lg" href="<?php echo esc_url( mz_url( 'home', '#services' ) ); ?>"><?php mz_e( 'Усі послуги' ); ?></a>
    </div>
  </div>
</section>

<?php
get_footer();
