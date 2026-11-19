<?php
/** Іконки месенджерів. $args: class (socials | band__msg), gold (bool) */
defined( 'ABSPATH' ) || exit;
$site = mz_site();
$g    = ! empty( $args['gold'] ) ? 'ic-gold ' : '';
?>
<a href="<?php echo esc_url( $site['telegram'], array( 'https', 'http', 'tg' ) ); ?>" target="_blank" rel="noopener" aria-label="Telegram"><i class="ic <?php echo esc_attr( $g ); ?>ic-telegram"></i></a>
<a href="<?php echo esc_url( $site['viber'], array( 'viber', 'https' ) ); ?>" aria-label="Viber"><i class="ic <?php echo esc_attr( $g ); ?>ic-viber"></i></a>
<a href="<?php echo esc_url( $site['whatsapp'], array( 'https', 'whatsapp' ) ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="ic <?php echo esc_attr( $g ); ?>ic-whatsapp"></i></a>
