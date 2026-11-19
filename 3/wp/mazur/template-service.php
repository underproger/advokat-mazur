<?php
/**
 * Template Name: Послуга
 *
 * Сторінка послуги (дизайн 3). Контент — поля ACF «Контент послуги».
 */
defined( 'ABSPATH' ) || exit;
get_header();

$id    = get_the_ID();
$f     = function ( $n ) use ( $id ) {
	return mz_field( $n, $id );
};
$site  = mz_site();
$nav   = $f( 'nav' ) ?: get_the_title();
$key   = get_post_meta( $id, 'mz_key', true );
$sits  = mz_rows( $f( 'situations' ), 'title' );
$tabs  = mz_rows( $f( 'tabs' ), 'label' );
$steps = mz_rows( $f( 'steps' ), 'title' );
$revs  = mz_rows( $f( 'reviews' ), 'text' );
$faq   = mz_rows( $f( 'faq' ), 'q' );
$show  = mz_rows( $f( 'showcase' ), 'title' );
$secs  = mz_rows( $f( 'seo_sections' ), 'h3' );
$tips  = mz_rows( $f( 'seo_tips' ), 't' );
$hero_img    = (int) $f( 'hero_image' );
$hero_preset = $f( 'hero_preset' ) ?: 'lawyer-standing';
$hero_cta    = $f( 'hero_cta' );
$first_topic = ! empty( $sits[0]['topic'] ) ? $sits[0]['topic'] : $nav;
$lawyer_card = (bool) $f( 'lawyer_badge' );
$g_rating    = mz_opt( 'google_rating', '' );
$g_count     = mz_opt( 'google_count', '' );
$g_url       = mz_opt( 'google_url', '' );
$review_url  = mz_opt( 'review_url', '' );
$stat_cases  = mz_opt( 'stat_cases', '' );
$num         = 0; // нумерація секцій (eyebrow)
$n           = function () use ( &$num ) {
	return sprintf( '%02d', ++$num );
};

// інші напрями: спочатку тієї ж категорії, потім сусідні за списком — разом 3
$services = mz_services();
$idx      = 0;
foreach ( $services as $i => $s ) {
	if ( (int) $s['id'] === (int) $id ) {
		$idx = $i;
	}
}
$cat     = $f( 'category' );
$same    = array_values( array_filter( $services, function ( $s ) use ( $cat, $id ) { return $s['category'] === $cat && (int) $s['id'] !== (int) $id; } ) );
$related = $same;
for ( $k = 1; $k < count( $services ) && count( $related ) < 3; $k++ ) {
	$s = $services[ ( $idx + $k ) % count( $services ) ];
	if ( (int) $s['id'] !== (int) $id && ! in_array( $s['id'], wp_list_pluck( $related, 'id' ), true ) ) {
		$related[] = $s;
	}
}
$related = array_slice( $related, 0, 3 );

// фото адвоката для блоку «Про адвоката» — інше, ніж на першому екрані
$about_img = 'lawyer-standing' === $hero_preset ? 'lawyer-hero.webp' : 'lawyer-office.webp';
if ( $hero_img ) {
	$about_img = 'lawyer-standing.webp';
}

$months = 'ru' === mz_lang()
	? array( 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь' )
	: array( 'січень', 'лютий', 'березень', 'квітень', 'травень', 'червень', 'липень', 'серпень', 'вересень', 'жовтень', 'листопад', 'грудень' );
$updated = $months[ (int) get_the_modified_date( 'n', $id ) - 1 ] . ' ' . get_the_modified_date( 'Y', $id );
?>

<!-- ============ 01 HERO ============ -->
<section class="hero<?php echo $hero_img ? ' hero--photo' : ''; ?>" data-hero>
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container container--wide hero__grid">
    <div class="hero__content">
      <nav class="crumbs" aria-label="<?php echo mz_a( 'Хлібні крихти' ); ?>" data-hero-in>
        <a href="<?php echo esc_url( mz_url( 'home' ) ); ?>"><?php mz_e( 'Головна' ); ?></a><i class="ic ic-li-diamond ic-gold"></i>
        <a href="<?php echo esc_url( mz_url( 'home', '#services' ) ); ?>"><?php mz_e( 'Послуги' ); ?></a><i class="ic ic-li-diamond ic-gold"></i>
        <span><?php echo esc_html( $nav ); ?></span>
      </nav>

      <span class="eyebrow" data-hero-in><?php echo esc_html( $f( 'eyebrow' ) ?: $cat ); ?></span>
      <h1 class="hero__title" data-chars><?php echo esc_html( $f( 'h1' ) ); ?> <span class="gold-text"><?php echo esc_html( $f( 'h1_gold' ) ); ?></span></h1>
      <p class="hero__lead" data-hero-in><?php echo esc_html( $f( 'lead' ) ); ?></p>

      <ul class="hero__list" data-hero-in>
        <?php foreach ( mz_list_field( 'bullets', $id ) as $b ) : ?>
        <li><i class="ic ic-li-diamond ic-gold"></i><?php echo esc_html( $b ); ?></li>
        <?php endforeach; ?>
      </ul>

      <div class="hero__btns" data-hero-in>
        <?php if ( $hero_cta ) : ?>
        <button class="btn btn--gold btn--lg btn--shine" type="button" data-modal-open data-topic="<?php echo esc_attr( $first_topic ); ?>"><?php echo esc_html( $hero_cta ); ?></button>
        <a class="btn btn--ghost btn--lg" href="#contact"><i class="ic ic-gold ic-chat"></i><?php mz_e( 'Безкоштовна консультація' ); ?></a>
        <?php else : ?>
        <a class="btn btn--gold btn--lg btn--shine" href="#contact"><?php mz_e( 'Безкоштовна консультація' ); ?></a>
        <button class="btn btn--ghost btn--lg" type="button" data-modal-open data-topic="<?php echo mz_a( 'Інше питання' ); ?>"><i class="ic ic-gold ic-chat"></i><?php mz_e( 'Описати свою ситуацію' ); ?></button>
        <?php endif; ?>
      </div>

      <div class="hero__trust" data-hero-in>
        <span><i class="ic ic-gold ic-lock"></i><?php mz_e( 'Конфіденційно' ); ?></span>
        <span><i class="ic ic-gold ic-globe"></i><?php mz_e( 'Онлайн або в офісі' ); ?></span>
        <span><i class="ic ic-gold ic-user-focus"></i><?php mz_e( 'Справу веду особисто' ); ?></span>
      </div>
    </div>

    <div class="hero__media">
      <div class="hero__photo" data-parallax>
        <?php
        if ( $hero_img ) {
			echo wp_get_attachment_image( $hero_img, 'mz-hero', false, array( 'alt' => $f( 'hero_alt' ), 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '(max-width: 1100px) 100vw, 45vw' ) );
        } else {
			$dims = array( 'lawyer-standing' => array( 959, 1280 ), 'lawyer-hero' => array( 979, 1322 ), 'lawyer-desk' => array( 853, 1280 ), 'lawyer-office' => array( 944, 1025 ) );
			$wh   = isset( $dims[ $hero_preset ] ) ? $dims[ $hero_preset ] : array( 959, 1280 );
			printf( '<img src="%s" alt="%s" width="%d" height="%d" fetchpriority="high">', esc_url( mz_img( $hero_preset . '.webp' ) ), esc_attr( $f( 'hero_alt' ) ?: mz_t( 'Адвокат Сергій Мазур' ) ), $wh[0], $wh[1] );
        }
        ?>
      </div>

      <div class="free-seal" aria-hidden="true">
        <svg class="free-seal__ring" viewBox="0 0 200 200">
          <defs><path id="sealPath" d="M100,100 m-78,0 a78,78 0 1,1 156,0 a78,78 0 1,1 -156,0"/></defs>
          <text><textPath href="#sealPath"><?php mz_e( 'Безкоштовна консультація ◆ Безкоштовна консультація ◆' ); ?> </textPath></text>
        </svg>
        <span class="free-seal__core"><b>0 ₴</b><small><?php mz_e( 'перша' ); ?><br><?php mz_e( 'консультація' ); ?></small></span>
      </div>

      <?php if ( $lawyer_card ) : ?>
      <div class="hero__card hero__card--lawyer">
        <img class="hero__card-avatar" src="<?php echo esc_url( mz_img( 'lawyer-avatar.webp' ) ); ?>" alt="<?php echo mz_a( 'Адвокат Сергій Мазур' ); ?>" width="64" height="64">
        <span class="hero__card-text"><small><?php mz_e( 'Оформлення веде' ); ?></small><b><?php mz_e( 'адвокат Сергій Мазур' ); ?></b><?php echo esc_html( $site['years'] ); ?> <?php mz_e( 'років практики · Свідоцтво №' ); ?>&nbsp;<?php echo esc_html( $site['cert'] ); ?></span>
      </div>
      <?php else : ?>
      <div class="hero__card">
        <span class="hero__card-num"><span data-count="<?php echo esc_attr( $site['years'] ); ?>">0</span></span>
        <span class="hero__card-text"><?php mz_e( 'років практики' ); ?><br><?php echo esc_html( $f( 'years_text' ) ); ?></span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ( $show && mz_show( 'showcase', $id ) ) : ?>
<!-- ============ ФОТО-БЛОКИ (персоналізація) ============ -->
<section class="section showcase" id="how">
  <div class="container">
    <div class="sec-head sec-head--split">
      <div>
        <span class="eyebrow" data-reveal><?php mz_e( 'Як це працює' ); ?></span>
        <h2 class="h2" data-split><?php echo esc_html( $f( 'showcase_title' ) ); ?> <span class="gold-text"><?php echo esc_html( $f( 'showcase_gold' ) ); ?></span></h2>
      </div>
      <?php if ( $f( 'showcase_text' ) ) : ?><p class="sec-head__text" data-reveal><?php echo esc_html( $f( 'showcase_text' ) ); ?></p><?php endif; ?>
    </div>
    <div class="showcase__list">
      <?php foreach ( $show as $i => $b ) : ?>
      <article class="showcase__item<?php echo $i % 2 ? ' showcase__item--rev' : ''; ?>">
        <div class="showcase__media" data-reveal>
          <div class="showcase__img" data-parallax>
            <?php echo wp_get_attachment_image( (int) $b['image'], 'mz-block', false, array( 'alt' => $b['title'], 'loading' => 'lazy', 'sizes' => '(max-width: 1100px) 100vw, 50vw' ) ); ?>
          </div>
          <span class="showcase__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
        </div>
        <div class="showcase__body" data-reveal>
          <?php if ( ! empty( $b['eyebrow'] ) ) : ?><span class="eyebrow"><?php echo esc_html( $b['eyebrow'] ); ?></span><?php endif; ?>
          <h3 class="h3"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php echo mz_html( $b['p'] ); // phpcs:ignore ?>
          <?php if ( mz_list( $b['list'] ) ) : ?>
          <ul class="check">
            <?php foreach ( mz_list( $b['list'] ) as $li ) : ?>
            <li><?php echo esc_html( $li ); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
          <?php if ( ! empty( $b['cta'] ) ) : ?>
          <button class="btn btn--gold btn--md btn--shine" type="button" data-modal-open data-topic="<?php echo esc_attr( $b['topic'] ?: $first_topic ); ?>"><?php echo esc_html( $b['cta'] ); ?></button>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ( $sits && mz_show( 'situations', $id ) ) : ?>
<!-- ============ 02 SITUATIONS ============ -->
<section class="section<?php echo $show ? ' section--alt' : ''; ?>" id="situations">
  <div class="container">
    <div class="sec-head sec-head--split">
      <div>
        <span class="eyebrow" data-reveal><b><?php echo esc_html( $n() ); ?></b><?php mz_e( 'Ситуації' ); ?></span>
        <h2 class="h2" data-split><?php mz_e( 'З яким питанням вам' ); ?> <span class="gold-text"><?php mz_e( 'потрібна допомога?' ); ?></span></h2>
      </div>
      <p class="sec-head__text" data-reveal><?php echo esc_html( $f( 'situations_text' ) ?: mz_t( 'Не обов\'язково самостійно визначати, які норми закону порушено. Оберіть ситуацію — і почнемо з її безкоштовної правової оцінки.' ) ); ?></p>
    </div>

    <div class="sit-grid" data-reveal-group>
      <?php foreach ( $sits as $i => $it ) : $last = ( count( $sits ) - 1 === $i ); ?>
      <article class="sit<?php echo $last ? ' sit--accent' : ''; ?>">
        <span class="tile"><i class="ic ic-gold <?php echo esc_attr( $it['icon'] ); ?>"></i></span>
        <h3 class="sit__title"><?php echo esc_html( $it['title'] ); ?></h3>
        <p class="sit__text"><?php echo esc_html( $it['text'] ); ?></p>
        <button class="sit__more" type="button" data-modal-open data-topic="<?php echo esc_attr( $it['topic'] ); ?>"><?php echo esc_html( $last ? mz_t( 'Описати ситуацію' ) : mz_t( 'Оцінити безкоштовно' ) ); ?> <i class="ic ic-arrow-right"></i></button>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php endif; ?>

<?php if ( $tabs && mz_show( 'help', $id ) ) : ?>
<!-- ============ 03 HELP (tabs) ============ -->
<section class="section<?php echo $show ? '' : ' section--alt'; ?>" id="help">
  <div class="container">
    <div class="sec-head sec-head--center">
      <span class="ghost-word" aria-hidden="true"><?php mz_e( 'Допомога' ); ?></span>
      <span class="eyebrow" data-reveal><b><?php echo esc_html( $n() ); ?></b><?php mz_e( 'Чим допоможу' ); ?></span>
      <h2 class="h2" data-split><?php mz_e( 'Яку допомогу надаю' ); ?> <span class="gold-text"><?php echo esc_html( $f( 'help_gold' ) ); ?></span></h2>
      <p class="sec-head__text" data-reveal><?php echo esc_html( $f( 'help_text' ) ); ?></p>
    </div>

    <div class="tabs" data-tabs>
      <div class="tabs__nav" role="tablist" data-reveal-group>
        <span class="tabs__indicator" aria-hidden="true"></span>
        <?php foreach ( $tabs as $i => $t ) : ?>
        <button class="tabs__btn<?php echo 0 === $i ? ' is-active' : ''; ?>" type="button" role="tab" data-tab="<?php echo (int) $i; ?>"><span class="tabs__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><span class="tabs__label"><?php echo esc_html( $t['label'] ); ?></span><i class="ic ic-arrow-right"></i></button>
        <?php endforeach; ?>
      </div>

      <div class="tabs__panels" data-reveal>
        <?php foreach ( $tabs as $i => $t ) : ?>
        <div class="tabs__panel<?php echo 0 === $i ? ' is-active' : ''; ?>" role="tabpanel">
          <button class="tabs__acc" type="button"><span class="tabs__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php echo esc_html( $t['label'] ); ?><i class="ic ic-plus"></i></button>
          <div class="tabs__body"><div class="tabs__inner">
            <span class="tile tile--lg"><i class="ic ic-gold <?php echo esc_attr( $t['icon'] ); ?>"></i></span>
            <h3 class="h3"><?php echo esc_html( $t['h3'] ); ?></h3>
            <?php echo mz_html( $t['p'] ); // phpcs:ignore ?>
            <div class="tabs__cols">
              <div>
                <span class="mini-label"><?php mz_e( 'У межах допомоги' ); ?></span>
                <ul class="check">
                  <?php foreach ( mz_list( $t['list'] ) as $li ) : ?>
                  <li><?php echo esc_html( $li ); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <aside class="tabs__note">
                <i class="ic ic-gold <?php echo esc_attr( $t['note_icon'] ); ?>"></i>
                <p><?php echo esc_html( $t['note'] ); ?></p>
                <button class="link-gold" type="button" data-modal-open data-topic="<?php echo esc_attr( $t['topic'] ); ?>"><?php echo esc_html( $t['cta'] ); ?> <i class="ic ic-arrow-right"></i></button>
              </aside>
            </div>
          </div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php endif; ?>

<?php if ( mz_show( 'band', $id ) ) : ?>
<!-- ============ 04 FREE CONSULTATION BAND ============ -->
<section class="band">
  <div class="marquee" aria-hidden="true">
    <div class="marquee__track">
      <?php for ( $r = 0; $r < 2; $r++ ) : ?>
      <span><?php mz_e( 'Безкоштовна консультація' ); ?></span><i class="ic ic-li-diamond"></i><span><?php mz_e( 'Конфіденційно' ); ?></span><i class="ic ic-li-diamond"></i><span><?php mz_e( 'Онлайн або в офісі' ); ?></span><i class="ic ic-li-diamond"></i><span><?php mz_e( 'Без зобов\'язань' ); ?></span><i class="ic ic-li-diamond"></i>
      <?php endfor; ?>
    </div>
  </div>
  <div class="container">
    <div class="band__box" data-wow-band>
      <div class="band__text">
        <span class="band__price shimmer-dark">0 ₴</span>
        <div>
          <h2 class="band__title"><?php mz_e( 'Перша консультація — безкоштовно' ); ?></h2>
          <p><?php mz_e( 'Опишіть ситуацію та надішліть документи. Я оціню перспективи й скажу, які кроки варто зробити. Договір — лише якщо ви вирішите продовжити.' ); ?></p>
        </div>
      </div>
      <div class="band__actions">
        <button class="btn btn--dark btn--lg" type="button" data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'Записатися безкоштовно' ); ?></button>
        <div class="band__msg">
          <?php get_template_part( 'template-parts/messengers' ); ?>
          <a class="band__phone" href="tel:<?php echo esc_attr( $site['phone_href'] ); ?>" data-track="band"><?php echo esc_html( $site['phone'] ); ?></a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php endif; ?>

<?php if ( $steps && mz_show( 'steps', $id ) ) : ?>
<!-- ============ 05 STEPS ============ -->
<section class="section" id="steps">
  <div class="container">
    <div class="sec-head sec-head--center">
      <span class="ghost-word" aria-hidden="true"><?php mz_e( 'Етапи' ); ?></span>
      <span class="eyebrow" data-reveal><b><?php echo esc_html( $n() ); ?></b><?php mz_e( 'Етапи' ); ?></span>
      <h2 class="h2 h2--white" data-split><?php mz_e( 'Як відбувається правовий супровід' ); ?></h2>
      <p class="sec-head__text" data-reveal><?php mz_e( 'Від першого звернення до реалізації погодженої стратегії захисту.' ); ?></p>
    </div>

    <ol class="steps" data-wow-steps>
      <li class="steps__line" aria-hidden="true"><span class="steps__fill"></span></li>
      <?php foreach ( $steps as $i => $st ) : ?>
      <li class="step">
        <span class="step__badge"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
        <span class="tile tile--xl"><i class="ic ic-gold <?php echo esc_attr( $st['icon'] ); ?>"></i></span>
        <h3 class="step__title"><?php echo esc_html( $st['title'] ); ?></h3>
        <p class="step__text"><?php echo esc_html( $st['text'] ); ?></p>
        <?php if ( 0 === $i ) : ?>
        <span class="step__tag">0 ₴</span>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<?php endif; ?>

<?php if ( mz_show( 'price', $id ) ) : ?>
<!-- ============ 06 PRICE ============ -->
<section class="section section--tight" id="price">
  <div class="container">
    <div class="sec-head sec-head--split">
      <div>
        <span class="eyebrow" data-reveal><b><?php echo esc_html( $n() ); ?></b><?php mz_e( 'Вартість' ); ?></span>
        <h2 class="h2" data-split><?php mz_e( 'Скільки коштують' ); ?> <span class="gold-text"><?php mz_e( 'послуги адвоката' ); ?></span></h2>
      </div>
      <p class="sec-head__text" data-reveal><?php echo esc_html( $f( 'price_text' ) ?: mz_t( 'Вартість залежить від складності питання, обсягу документів і формату представництва. Ціна фіксується в договорі — без доплат.' ) ); ?></p>
    </div>

    <div class="price-grid" data-wow-fan>
      <article class="price price--free">
        <span class="price__ribbon"><?php mz_e( 'Безкоштовно' ); ?></span>
        <span class="price__label"><?php mz_e( 'Крок 1' ); ?></span>
        <h3 class="price__title"><?php mz_e( 'Первинна консультація' ); ?></h3>
        <span class="price__value"><b>0</b> ₴</span>
        <ul class="check">
          <li><?php mz_e( 'Аналіз вашого питання' ); ?></li>
          <li><?php mz_e( 'Оцінка перспектив і ризиків' ); ?></li>
          <li><?php mz_e( 'Рекомендації щодо подальших дій' ); ?></li>
        </ul>
        <button class="btn btn--gold btn--md btn--block btn--shine" type="button" data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'Записатися безкоштовно' ); ?></button>
      </article>
      <?php foreach ( array( 'docs' => 2, 'full' => 3 ) as $pk => $step ) : ?>
      <article class="price">
        <span class="price__label"><?php mz_e( 'Крок ' . $step ); ?></span>
        <h3 class="price__title"><?php echo esc_html( $f( 'price_' . $pk . '_title' ) ?: mz_t( 'Комплексний супровід' ) ); ?></h3>
        <?php if ( $f( 'price_' . $pk . '_value' ) ) : ?>
        <span class="price__value"><?php echo wp_kses_post( $f( 'price_' . $pk . '_value' ) ); ?></span>
        <?php if ( $f( 'price_' . $pk . '_hint' ) ) : ?><span class="price__hint"><?php echo esc_html( $f( 'price_' . $pk . '_hint' ) ); ?></span><?php endif; ?>
        <?php else : ?>
        <span class="price__value price__value--sm"><?php mz_e( 'docs' === $pk ? 'Фіксована ціна' : 'За договором' ); ?></span>
        <?php endif; ?>
        <ul class="check">
          <?php foreach ( mz_list_field( 'price_' . $pk, $id ) as $li ) : ?>
          <li><?php echo esc_html( $li ); ?></li>
          <?php endforeach; ?>
        </ul>
        <button class="btn btn--ghost btn--md btn--block" type="button" data-modal-open data-topic="<?php echo mz_a( 'Безкоштовна консультація' ); ?>"><?php mz_e( 'docs' === $pk ? 'Дізнатися вартість' : 'Обговорити справу' ); ?></button>
      </article>
      <?php endforeach; ?>
    </div>
    <p class="price-note" data-reveal><i class="ic ic-gold ic-li-diamond"></i><?php mz_e( 'Обсяг робіт і вартість погоджуються до початку надання правової допомоги.' ); ?></p>
  </div>
</section>

<?php endif; ?>

<?php if ( mz_show( 'about', $id ) ) : ?>
<!-- ============ 07 ABOUT + ADVANTAGES ============ -->
<section class="section" id="about">
  <div class="container about">
    <div class="about__media" data-reveal>
      <div class="about__photo" data-parallax data-wow-reveal>
        <img src="<?php echo esc_url( mz_img( $about_img ) ); ?>" alt="<?php echo mz_a( 'Адвокат Сергій Мазур' ); ?>" loading="lazy">
      </div>
      <div class="about__stats">
        <?php if ( $stat_cases ) : ?>
        <div><b><?php echo esc_html( $stat_cases ); ?></b><span><?php mz_e( 'справ і проваджень' ); ?></span></div>
        <?php else : ?>
        <div><b><span data-count="<?php echo esc_attr( $site['years'] ); ?>">0</span></b><span><?php mz_e( 'років практики' ); ?></span></div>
        <?php endif; ?>
        <?php if ( $g_rating ) : ?>
        <div><b><?php echo esc_html( $g_rating ); ?></b><span><?php mz_e( 'рейтинг у Google' ); ?></span></div>
        <?php else : ?>
        <div><b>0 ₴</b><span><?php mz_e( 'перша консультація' ); ?></span></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="about__content">
      <span class="eyebrow" data-reveal><b><?php echo esc_html( $n() ); ?></b><?php mz_e( 'Про адвоката' ); ?></span>
      <h2 class="h2" data-split><?php mz_e( 'Ваші інтереси захищає' ); ?> <span class="gold-text"><?php mz_e( 'адвокат Мазур' ); ?></span></h2>
      <p class="about__lead" data-wow-fill><?php mz_e( 'Підхід до кожної справи починається з уважного вивчення обставин і документів.' ); ?></p>
      <p class="about__text" data-reveal><?php echo esc_html( $f( 'about_text' ) ); ?></p>

      <ul class="creds" data-reveal-group>
        <li><i class="ic ic-gold ic-id-card"></i><span><?php mz_e( 'Свідоцтво №' ); ?> <?php echo esc_html( $site['cert'] ); ?><small><?php mz_e( 'право на заняття адвокатською діяльністю' ); ?></small></span></li>
        <li><i class="ic ic-gold ic-scales"></i><span><?php mz_e( 'Член НААУ' ); ?><small><?php mz_e( 'Київ · вся Україна' ); ?></small></span></li>
        <li><i class="ic ic-gold ic-briefcase"></i><span><?php echo esc_html( $site['years'] ); ?> <?php mz_e( 'років' ); ?><small><?php mz_e( 'практики' ); ?></small></span></li>
      </ul>

      <div class="adv" data-reveal-group>
        <div class="adv__item"><i class="ic ic-gold ic-user-focus"></i><div><h3><?php mz_e( 'Особисто' ); ?></h3><p><?php mz_e( 'Не передаю справи помічникам — ви говорите з адвокатом, який веде захист.' ); ?></p></div></div>
        <div class="adv__item"><i class="ic ic-gold ic-eye"></i><div><h3><?php mz_e( 'Чесна оцінка' ); ?></h3><p><?php mz_e( 'Реальні перспективи та ризики — ще до підписання договору.' ); ?></p></div></div>
        <div class="adv__item"><i class="ic ic-gold ic-receipt"></i><div><h3><?php mz_e( 'Прозора ціна' ); ?></h3><p><?php mz_e( 'Вартість і етапи фіксуються в договорі. Без доплат.' ); ?></p></div></div>
        <div class="adv__item"><i class="ic ic-gold ic-lock"></i><div><h3><?php mz_e( 'Адвокатська таємниця' ); ?></h3><p><?php mz_e( 'Усе, що ви розповідаєте, захищено законом.' ); ?></p></div></div>
      </div>

      <div class="about__btns" data-reveal>
        <a class="btn btn--gold btn--md btn--shine" href="#contact"><?php mz_e( 'Безкоштовна консультація' ); ?></a>
        <a class="btn btn--ghost btn--md" href="<?php echo esc_url( mz_url( 'home', '#about' ) ); ?>"><?php mz_e( 'Детальніше про адвоката' ); ?> <i class="ic ic-gold ic-arrow-up-right"></i></a>
      </div>
    </div>
  </div>
</section>

<?php endif; ?>

<?php
$svc_cases = mz_show( 'cases', $id ) ? mz_cases( $id, 3 ) : array();
if ( $svc_cases ) :
	?>
<!-- ============ CASES ============ -->
<section class="section section--tight" id="cases">
  <div class="container">
    <div class="sec-head sec-head--split">
      <div>
        <span class="eyebrow" data-reveal><?php mz_e( 'Кейси' ); ?></span>
        <h2 class="h2" data-split><?php mz_e( 'Приклади справ' ); ?> <span class="gold-text"><?php mz_e( 'з практики' ); ?></span></h2>
      </div>
      <a class="btn btn--ghost btn--md" href="<?php echo esc_url( mz_url( 'keysy' ) ); ?>" data-reveal><?php mz_e( 'Усі кейси' ); ?> <i class="ic ic-gold ic-arrow-right"></i></a>
    </div>
    <div class="cases__list cases__list--3" data-reveal-group>
      <?php foreach ( $svc_cases as $c ) { get_template_part( 'template-parts/case-card', null, array( 'post' => $c ) ); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ( $revs && mz_show( 'reviews', $id ) ) : ?>
<!-- ============ 08 REVIEWS ============ -->
<section class="section section--alt reviews" id="reviews">
  <div class="container reviews__head">
    <div>
      <span class="eyebrow" data-reveal><b><?php echo esc_html( $n() ); ?></b><?php echo esc_html( $f( 'reviews_label' ) ?: mz_t( 'Відгуки Google' ) ); ?></span>
      <h2 class="h2" data-split><?php mz_e( 'Відгуки клієнтів' ); ?> <span class="gold-text"><?php echo esc_html( $f( 'reviews_gold' ) ); ?></span></h2>
      <?php if ( ! $f( 'reviews_label' ) && $g_rating ) : ?>
      <span class="rating" data-reveal><b><?php echo esc_html( $g_rating ); ?></b><?php get_template_part( 'template-parts/stars' ); ?><?php echo esc_html( $g_count ? str_replace( '48', $g_count, mz_t( '48 відгуків у Google' ) ) : '' ); ?></span>
      <?php endif; ?>
    </div>
    <div class="arrows" data-reveal>
      <button class="arrow" type="button" aria-label="<?php echo mz_a( 'Назад' ); ?>" data-slider-prev><i class="ic ic-gold ic-arrow-left"></i></button>
      <button class="arrow arrow--gold" type="button" aria-label="<?php echo mz_a( 'Далі' ); ?>" data-slider-next><i class="ic ic-arrow-right"></i></button>
    </div>
  </div>

  <div class="reviews__track" data-slider>
    <?php foreach ( $revs as $v ) { get_template_part( 'template-parts/review', null, $v ); } ?>
  </div>

  <?php if ( $g_url || $review_url ) : ?>
  <div class="container reviews__foot" data-reveal>
    <?php if ( $g_url ) : ?><a class="link-gold link-gold--underline" href="<?php echo esc_url( $g_url ); ?>" target="_blank" rel="noopener"><?php mz_e( 'Усі відгуки в Google' ); ?> <i class="ic ic-gold ic-arrow-up-right"></i></a><?php endif; ?>
    <?php if ( $review_url ) : ?><a class="reviews__leave" href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener"><?php mz_e( 'Залишити відгук' ); ?></a><?php endif; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ( mz_show( 'seo', $id ) && $f( 'seo_h2' ) ) : ?>
<!-- ============ 09 SEO TEXT (visible) ============ -->
<section class="section seo" id="seo">
  <div class="container seo__grid">
    <aside class="seo__aside">
      <span class="eyebrow" data-reveal><?php mz_e( 'Корисно знати' ); ?></span>
      <h2 class="h2 h2--sm" data-reveal><?php echo esc_html( $f( 'seo_h2' ) ); ?></h2>
      <nav class="seo__toc" aria-label="<?php echo mz_a( 'Зміст статті' ); ?>" data-reveal>
        <a href="#seo-when"><?php mz_e( 'Коли потрібен адвокат' ); ?></a>
        <?php foreach ( $secs as $sec ) : ?>
        <a href="#<?php echo esc_attr( $sec['sid'] ); ?>"><?php echo esc_html( $sec['toc'] ); ?></a>
        <?php endforeach; ?>
        <a href="#seo-tips"><?php mz_e( 'Що варто знати' ); ?></a>
      </nav>
      <div class="author" data-reveal>
        <span class="avatar avatar--lg"><?php mz_e( 'АМ' ); ?></span>
        <div><b><?php mz_e( 'Адвокат Сергій Мазур' ); ?></b><span><?php echo esc_html( mz_t( 'Автор матеріалу' ) . ' · ' . ( 'ru' === mz_lang() ? 'обновлено' : 'оновлено' ) . ' ' . $updated ); ?></span></div>
      </div>
    </aside>

    <article class="seo__article">
      <p class="seo__intro" data-wow-fill><?php echo mz_inline( $f( 'seo_intro' ) ); // phpcs:ignore ?></p>
      <?php if ( $f( 'seo_p' ) ) : ?><p><?php echo mz_inline( $f( 'seo_p' ) ); // phpcs:ignore ?></p><?php endif; ?>

      <h3 class="h3" id="seo-when"><?php echo esc_html( $f( 'seo_when_title' ) ); ?></h3>
      <ul class="dlist">
        <?php foreach ( mz_list_field( 'seo_when', $id ) as $li ) : ?>
        <li><?php echo esc_html( $li ); ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if ( $f( 'seo_quote' ) ) : ?><blockquote class="quote" data-wow-fill><?php echo esc_html( $f( 'seo_quote' ) ); ?></blockquote><?php endif; ?>
      <?php foreach ( $secs as $sec ) : ?>

      <h3 class="h3" id="<?php echo esc_attr( $sec['sid'] ); ?>"><?php echo esc_html( $sec['h3'] ); ?></h3>
      <?php echo mz_html( $sec['p'] ); // phpcs:ignore ?>
      <?php endforeach; ?>

      <h3 class="h3" id="seo-tips"><?php echo esc_html( $f( 'seo_tips_title' ) ); ?></h3>
      <div class="tips">
        <?php foreach ( $tips as $i => $tp ) : ?>
        <div class="tip"><span class="tip__n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><h4><?php echo esc_html( $tp['t'] ); ?></h4><p><?php echo esc_html( $tp['x'] ); ?></p></div>
        <?php endforeach; ?>
      </div>
    </article>
  </div>
</section>

<?php endif; ?>

<?php if ( $faq && mz_show( 'faq', $id ) ) : ?>
<!-- ============ 10 FAQ ============ -->
<section class="section section--alt" id="faq">
  <div class="container faq">
    <div class="faq__side">
      <span class="eyebrow" data-reveal><b><?php echo esc_html( $n() ); ?></b><?php mz_e( 'Питання та відповіді' ); ?></span>
      <h2 class="h2" data-split><?php mz_e( 'Поширені' ); ?> <span class="gold-text"><?php mz_e( 'запитання' ); ?></span></h2>
      <p class="sec-head__text" data-reveal><?php mz_e( 'Не знайшли відповідь? Поставте питання напряму — відповім особисто і безкоштовно.' ); ?></p>
      <button class="btn btn--ghost btn--md" type="button" data-modal-open data-topic="<?php echo mz_a( 'Інше питання' ); ?>" data-reveal><i class="ic ic-gold ic-chat"></i><?php mz_e( 'Поставити питання' ); ?></button>
    </div>

    <div class="faq__list" data-reveal-group>
      <?php foreach ( $faq as $i => $q ) { get_template_part( 'template-parts/faq-item', null, array( 'q' => $q['q'], 'a' => $q['a'], 'open' => 0 === $i ) ); } ?>
    </div>
  </div>
</section>

<?php endif; ?>

<!-- ============ 11 FINAL FORM ============ -->
<section class="section" id="contact">
  <div class="container">
    <div class="final">
      <div class="final__content">
        <span class="eyebrow" data-reveal><?php mz_e( 'Заявка' ); ?></span>
        <h2 class="h2" data-split><?php echo esc_html( $f( 'final_h2' ) ); ?> <span class="gold-text"><?php echo esc_html( $f( 'final_gold' ) ); ?></span></h2>
        <p class="final__text" data-reveal><?php mz_e( 'Опишіть ситуацію — я зв\'яжуся з вами, проведу безкоштовну консультацію та запропоную план дій.' ); ?></p>

        <div class="final__docs" data-reveal>
          <span class="mini-label"><?php mz_e( 'Що підготувати до консультації' ); ?></span>
          <ul class="check check--cols">
            <?php foreach ( mz_list_field( 'docs', $id ) as $d ) : ?>
            <li><?php echo esc_html( $d ); ?></li>
            <?php endforeach; ?>
          </ul>
          <p class="final__hint"><?php mz_e( 'Немає всіх документів? Це не перешкода для першого звернення.' ); ?></p>
        </div>

        <div class="final__direct" data-reveal>
          <span><?php mz_e( 'Зручніше напряму?' ); ?></span>
          <a class="final__phone" href="tel:<?php echo esc_attr( $site['phone_href'] ); ?>" data-track="contact"><i class="ic ic-gold ic-phone"></i><?php echo esc_html( $site['phone'] ); ?></a>
          <div class="socials"><?php get_template_part( 'template-parts/messengers', null, array( 'gold' => true ) ); ?></div>
        </div>
      </div>

      <div class="final__form" data-reveal>
        <div class="form-card" data-form-wrap>
          <div class="form-card__head">
            <span class="form-card__badge">0 ₴</span>
            <div><b><?php mz_e( 'Безкоштовна консультація' ); ?></b><span><?php mz_e( 'Відповім протягом робочого дня' ); ?></span></div>
          </div>
          <form class="form" data-form data-form-location="contact">
            <input type="hidden" name="topic" value="<?php echo esc_attr( $nav ); ?>">
            <label class="field"><i class="ic ic-user"></i><input type="text" name="name" placeholder="<?php echo mz_a( 'Ваше ім\'я' ); ?>" autocomplete="name" required></label>
            <label class="field"><i class="ic ic-phone"></i><input type="tel" name="phone" placeholder="<?php echo mz_a( 'Номер телефону' ); ?>" autocomplete="tel" required></label>
            <label class="field field--area"><i class="ic ic-chat"></i><textarea name="message" rows="4" placeholder="<?php echo mz_a( 'Коротко опишіть ситуацію' ); ?>"></textarea></label>
            <button class="btn btn--gold btn--block btn--lg btn--shine" type="submit"><?php mz_e( 'Отримати безкоштовну консультацію' ); ?></button>
            <p class="form__policy"><?php mz_e( 'Натискаючи кнопку, ви погоджуєтеся на обробку' ); ?> <?php $pu = mz_privacy_url(); echo $pu ? '<a href="' . esc_url( $pu ) . '">' . esc_html( mz_t( 'персональних даних' ) ) . '</a>' : esc_html( mz_t( 'персональних даних' ) ); ?><?php mz_e( '. Конфіденційність гарантована адвокатською таємницею.' ); ?></p>
          </form>
          <div class="form-success">
            <i class="ic ic-gold ic-check-circle"></i>
            <div><b><?php mz_e( 'Дякую.' ); ?></b> <?php mz_e( 'Заявку отримано. Я зв\'яжуся з вами найближчим часом.' ); ?></div>
          </div>
        </div>
      </div>
    </div>

    <?php if ( mz_show( 'related', $id ) ) : ?>
    <div class="related" data-reveal>
      <span class="related__title"><?php mz_e( 'Інші напрями допомоги' ); ?></span>
      <div class="related__list">
        <?php foreach ( $related as $r ) : ?>
        <a href="<?php echo esc_url( $r['url'] ); ?>"><i class="ic ic-gold <?php echo esc_attr( $r['icon'] ); ?>"></i><?php echo esc_html( $r['nav'] ); ?><i class="ic ic-arrow-up-right"></i></a>
        <?php endforeach; ?>
        <a href="<?php echo esc_url( mz_url( 'home', '#services' ) ); ?>"><i class="ic ic-gold ic-folder"></i><?php mz_e( 'Усі послуги' ); ?><i class="ic ic-arrow-up-right"></i></a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
get_footer();
