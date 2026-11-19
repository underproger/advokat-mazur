<?php
/** Картка кейсу. $args: post (WP_Post) */
defined( 'ABSPATH' ) || exit;
$c  = $args['post'];
$sv = mz_service_by_id( (int) mz_field( 'case_service', $c->ID ) );
?>
      <a class="case-card" href="<?php echo esc_url( get_permalink( $c ) ); ?>" data-cat="<?php echo esc_attr( $sv ? $sv['key'] : '' ); ?>">
        <span class="case-card__top">
          <span class="case-card__icon"><i class="ic ic-gold <?php echo esc_attr( $sv ? $sv['icon'] : 'ic-scales' ); ?>"></i></span>
          <span class="case-card__cat"><?php echo esc_html( $sv ? $sv['nav'] : '' ); ?></span>
        </span>
        <span class="case-card__title"><?php echo esc_html( get_the_title( $c ) ); ?></span>
        <span class="case-card__result"><i class="ic ic-gold ic-check-circle"></i><?php echo esc_html( mz_field( 'case_result_short', $c->ID ) ); ?></span>
        <span class="case-card__foot"><span><?php echo esc_html( mz_field( 'case_date', $c->ID ) ); ?></span><span class="case-card__more"><?php mz_e( 'Читати кейс' ); ?> <i class="ic ic-arrow-right"></i></span></span>
      </a>
