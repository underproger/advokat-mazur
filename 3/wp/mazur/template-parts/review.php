<?php
/** Картка відгуку. $args: initials, name, tag, date, text, src */
defined( 'ABSPATH' ) || exit;
$v = $args;
?>
    <article class="review">
      <div class="review__top"><?php get_template_part( 'template-parts/stars' ); ?><span class="review__src"><?php echo esc_html( ! empty( $v['src'] ) ? $v['src'] : 'Google' ); ?></span></div>
      <p class="review__text"><?php echo esc_html( $v['text'] ); ?></p>
      <div class="review__author"><span class="avatar"><?php echo esc_html( $v['initials'] ); ?></span><div><b><?php echo esc_html( $v['name'] ); ?></b><span><?php echo esc_html( $v['tag'] ); ?><?php echo ! empty( $v['date'] ) ? ' · ' . esc_html( $v['date'] ) : ''; ?></span></div></div>
    </article>
