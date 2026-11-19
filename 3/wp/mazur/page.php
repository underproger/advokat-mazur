<?php
/**
 * Звичайна сторінка (напр. політика конфіденційності).
 */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
<section class="section text-page" data-hero>
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container text-page__in">
    <nav class="crumbs" aria-label="<?php echo mz_a( 'Хлібні крихти' ); ?>" data-hero-in>
      <a href="<?php echo esc_url( mz_url( 'home' ) ); ?>"><?php mz_e( 'Головна' ); ?></a><i class="ic ic-li-diamond ic-gold"></i>
      <span><?php the_title(); ?></span>
    </nav>
    <h1 class="h2" data-hero-in><?php the_title(); ?></h1>
    <div class="text-page__content" data-hero-in><?php the_content(); ?></div>
  </div>
</section>
	<?php
endwhile;
get_footer();
