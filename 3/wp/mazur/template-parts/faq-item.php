<?php
/** Питання FAQ. $args: q, a, open */
defined( 'ABSPATH' ) || exit;
$open = ! empty( $args['open'] );
?>
      <div class="faq__item<?php echo $open ? ' is-open' : ''; ?>">
        <button class="faq__q" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"><h3><?php echo esc_html( $args['q'] ); ?></h3><span class="faq__icon"><i class="ic ic-gold ic-plus"></i></span></button>
        <div class="faq__a"><div><?php echo mz_html( $args['a'] ); // phpcs:ignore ?></div></div>
      </div>
