<?php
/**
 * Головна (дизайн 3). Відгуки та FAQ — поля ACF «Головна сторінка», решта текстів — у шаблоні (UA/RU).
 */
defined( 'ABSPATH' ) || exit;
get_header();

$site       = mz_site();
$services   = mz_services();
$reviews    = mz_rows( mz_field( 'home_reviews' ), 'text' );
$faq        = mz_rows( mz_field( 'home_faq' ), 'q' );
$g_rating   = mz_opt( 'google_rating', '' );
$g_count    = mz_opt( 'google_count', '' );
$g_url      = mz_opt( 'google_url', '' );
$review_url = mz_opt( 'review_url', '' );
$stat_cases = mz_opt( 'stat_cases', '' );
$u          = function ( $key ) {
	return esc_url( mz_url( $key ) );
};
?>

<!-- ============ 01 HERO ============ -->
<section class="h-hero" data-hero>
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container container--wide h-hero__grid">
    <div class="h-hero__content">
      <span class="eyebrow" data-hero-in><?php mz_e( 'Індивідуальна адвокатська практика' ); ?></span>
      <h1 class="h-hero__title" data-chars><?php mz_e( 'Адвокат Мазур —' ); ?> <span class="gold-text"><?php mz_e( 'захист прав' ); ?></span> <?php mz_e( 'фізичних осіб і бізнесу' ); ?></h1>
      <p class="h-hero__lead" data-hero-in><?php mz_e( 'У кримінальних, адміністративних і транспортних справах — особисто, від першої консультації до рішення суду.' ); ?></p>
      <div class="h-hero__btns" data-hero-in>
        <a class="btn btn--gold btn--xl btn--shine" href="#contact"><?php mz_e( 'Отримати консультацію' ); ?></a>
        <button class="btn btn--ghost btn--call" type="button" data-modal-open data-topic="<?php echo mz_a( 'Зворотний дзвінок' ); ?>"><i class="ic ic-gold ic-phone"></i><?php mz_e( 'Передзвоніть мені' ); ?></button>
      </div>
      <div class="h-hero__trust" data-hero-in>
        <span><?php mz_e( 'Свідоцтво №' ); ?> <?php echo esc_html( $site['cert'] ); ?></span><i class="ic ic-li-diamond ic-gold"></i><span><?php mz_e( 'Член НААУ' ); ?></span><i class="ic ic-li-diamond ic-gold"></i><span><?php mz_e( 'Київ · вся Україна' ); ?></span>
      </div>
    </div>
    <div class="h-hero__media">
      <div class="h-hero__photo" data-parallax>
        <img src="<?php echo esc_url( mz_img( 'lawyer-hero.webp' ) ); ?>" alt="<?php echo mz_a( 'Адвокат Мазур' ); ?>" width="979" height="1322" fetchpriority="high">
      </div>
    </div>
  </div>
</section>

<!-- ============ 02a STATS ============ -->
<div class="h-stats">
  <div class="h-stats__box">
    <div class="h-stats__grid">
      <div class="h-stats__item"><span class="h-stats__num"><span data-count="<?php echo esc_attr( $site['years'] ); ?>">0</span> <span class="h-stats__unit"><?php mz_e( 'років' ); ?></span></span><span class="h-stats__label"><?php mz_e( 'практики' ); ?></span></div>
      <?php if ( $stat_cases ) : ?>
      <div class="h-stats__item"><span class="h-stats__num"><?php echo esc_html( $stat_cases ); ?></span><span class="h-stats__label"><?php mz_e( 'справ і проваджень' ); ?></span></div>
      <?php else : ?>
      <div class="h-stats__item"><span class="h-stats__num">0 <span class="h-stats__unit">₴</span></span><span class="h-stats__label"><?php mz_e( 'перша консультація' ); ?></span></div>
      <?php endif; ?>
      <div class="h-stats__item"><span class="h-stats__num"><span data-count="<?php echo esc_attr( count( $services ) ); ?>">0</span></span><span class="h-stats__label"><?php mz_e( 'напрямів практики' ); ?></span></div>
      <?php if ( $g_rating ) : ?>
      <div class="h-stats__item"><span class="h-stats__num"><?php echo esc_html( $g_rating ); ?></span><span class="h-stats__label"><?php mz_e( 'рейтинг у Google' ); ?></span></div>
      <?php else : ?>
      <div class="h-stats__item"><span class="h-stats__num">№<?php echo esc_html( $site['cert'] ); ?></span><span class="h-stats__label"><?php mz_e( 'Свідоцтво про право на адвокатську діяльність' ); ?></span></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============ 02b ABOUT ============ -->
<section class="h-about" id="about">
  <div class="h-about__in">
    <span class="eyebrow" data-reveal><?php mz_e( 'Про адвоката' ); ?></span>
    <h2 class="h-about__title" data-split><?php mz_e( 'Я захищаю людей і бізнес там, де помилка коштує' ); ?> <span class="gold-text"><?php mz_e( 'свободи, ліцензії або репутації' ); ?></span></h2>
    <p class="h-about__text" data-wow-fill><?php mz_e( 'Індивідуальна практика без посередників: кожну справу веду особисто і відповідаю за результат — від аналізу документів до виступу в суді.' ); ?></p>
  </div>
</section>

<!-- ============ 03 SERVICES ============ -->
<section class="h-services" id="services">
  <div class="h-head">
    <span class="h-head__ghost" aria-hidden="true"><?php mz_e( 'Послуги' ); ?></span>
    <h2 class="h-head__title h-head__title--gold" data-split><?php mz_e( 'Надаю послуги' ); ?></h2>
    <p class="h-head__text" data-reveal><?php echo esc_html( count( $services ) ); ?> <?php mz_e( 'напрямів практики — від трудових спорів і кримінального захисту до документів для перевізників.' ); ?></p>
  </div>
  <div class="container h-services__grid" data-reveal-group>
    <?php foreach ( $services as $s ) : ?>
    <article class="svc spot">
      <span class="svc__tile"><span><i class="ic ic-gold <?php echo esc_attr( $s['icon'] ); ?>"></i></span></span>
      <span class="svc__label"><?php echo esc_html( $s['category'] ); ?></span>
      <h3 class="svc__title"><a href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['nav'] ); ?></a></h3>
      <i class="ic ic-li-diamond ic-gold svc__diamond"></i>
      <ul class="svc__list">
        <?php foreach ( $s['home_items'] as $it ) : ?>
        <li><i class="ic ic-li-diamond ic-gold"></i><?php echo esc_html( $it ); ?></li>
        <?php endforeach; ?>
      </ul>
      <a class="svc__more" href="<?php echo esc_url( $s['url'] ); ?>"><?php mz_e( 'Детальніше' ); ?></a>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ 04 SEO TEXT 1 ============ -->
<section class="h-seo">
  <div class="container">
    <div class="h-seo__in">
      <h2 class="h-seo__title" data-reveal><?php mz_e( 'Адвокат Мазур: юридичний захист у кримінальних, адміністративних і транспортних справах' ); ?></h2>
      <article class="h-seo__article" data-more>
        <p data-reveal><?php mz_e( 'Адвокат Мазур надає правову допомогу фізичним особам, підприємцям і перевізникам у справах, де від швидкості та точності дій залежить результат. Кожна справа починається з аналізу документів і обставин: ви отримуєте зрозумілу оцінку ризиків, план дій і фіксовану вартість роботи ще до підписання договору.' ); ?></p>
        <div class="h-seo__more" data-more-body><div>
          <h3><?php mz_e( 'Трудові спори' ); ?></h3>
          <p><?php mz_e( 'Основна практика —' ); ?> <a href="<?php echo $u( 'trudovi-spory' ); ?>"><?php mz_e( 'спори про незаконне звільнення' ); ?></a> <?php mz_e( 'з державної служби та з будь-яких підприємств, поновлення на роботі, дисциплінарні стягнення, службові розслідування, захист від мобінгу.' ); ?></p>
          <h3><?php mz_e( 'Кримінальний захист' ); ?></h3>
          <p><?php mz_e( 'Захист у справах про' ); ?> <a href="<?php echo $u( 'korupciyni-spravy' ); ?>"><?php mz_e( 'корупційні' ); ?></a>, <a href="<?php echo $u( 'ekonomichni-zlochyny' ); ?>"><?php mz_e( 'економічні та фінансові злочини' ); ?></a><?php mz_e( '; супровід на допитах; оскарження запобіжних заходів і арешту майна; представництво у ВАКС та судах усіх інстанцій.' ); ?></p>
          <h3><?php mz_e( 'Військові та адміністративні справи' ); ?></h3>
          <p><?php mz_e( 'Допомога у справах' ); ?> <a href="<?php echo $u( 'advokat-po-tck' ); ?>"><?php mz_e( 'з ТЦК та СП' ); ?></a><?php mz_e( ': відстрочка, неправомірні дії, адвокатські запити. Для бізнесу —' ); ?> <a href="<?php echo $u( 'krytychnist-pidpryiemstv' ); ?>"><?php mz_e( 'критичність підприємства' ); ?></a> <?php mz_e( 'та бронювання працівників.' ); ?> <a href="<?php echo $u( 'administratyvni-pravoporushennya' ); ?>"><?php mz_e( 'Адміністративна практика' ); ?></a><?php mz_e( ': оскарження постанов, рішень органів влади, представництво в ДВС і НАЗК.' ); ?></p>
          <h3><?php mz_e( 'Транспортне право' ); ?></h3>
          <p><?php mz_e( 'Видача, розширення та звуження' ); ?> <a href="<?php echo $u( 'licenziya-na-perevezennya' ); ?>"><?php mz_e( 'ліцензій на перевезення пасажирів та вантажів' ); ?></a><?php mz_e( ', відкриття міжнародних автобусних маршрутів і готові фірми з ліцензіями. Дистанційне оформлення' ); ?> <a href="<?php echo $u( 'chip-karta-vodiya' ); ?>"><?php mz_e( 'чіп-карти водія' ); ?></a>, <a href="<?php echo $u( 'kod-95' ); ?>"><?php mz_e( 'кваліфікаційної картки (Код 95)' ); ?></a> <?php mz_e( 'та' ); ?> <a href="<?php echo $u( 'kod-eori' ); ?>"><?php mz_e( 'коду EORI' ); ?></a> <?php mz_e( 'для роботи з митницями ЄС. Оскарження' ); ?> <a href="<?php echo $u( 'shtrafy-za-perevantazhennya' ); ?>"><?php mz_e( 'штрафів Укртрансбезпеки за перевантаження' ); ?></a> (WIM).</p>
        </div></div>
        <button class="more-btn" type="button" data-more-toggle aria-expanded="false"><span><?php mz_e( 'Читати повністю' ); ?></span> <i class="ic ic-gold ic-chevron-down"></i></button>
      </article>
    </div>
  </div>
</section>

<!-- ============ 05 STEPS ============ -->
<section class="h-steps" id="steps">
  <div class="h-head h-head--steps">
    <span class="h-head__ghost" aria-hidden="true"><?php mz_e( 'Етапи' ); ?></span>
    <h2 class="h-head__title h-head__title--white" data-split><?php mz_e( 'Як проходить робота' ); ?></h2>
  </div>
  <div class="container">
    <ol class="h-steps__grid" data-steps>
      <?php
      $steps = array(
		  array( 'ic-search', 'Аналіз ситуації, перспектив справи та можливих ризиків' ),
		  array( 'ic-consult', 'Досудове врегулювання: претензії, переговори, запити' ),
		  array( 'ic-folder', 'Збір документів і доказів для захисту позиції' ),
		  array( 'ic-quill', 'Розробка правової позиції та підготовка процесуальних документів' ),
		  array( 'ic-court', 'Представництво в суді та державних органах' ),
		  array( 'ic-appeal', 'Оскарження рішень і контроль їх виконання' ),
      );
      foreach ( $steps as $i => $st ) :
		  ?>
      <li class="h-step" data-reveal>
        <?php if ( $i < count( $steps ) - 1 ) : ?>
        <span class="h-step__line" aria-hidden="true"><span></span></span>
        <?php endif; ?>
        <div class="h-step__icon">
          <span class="h-step__badge"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
          <span class="h-step__tile"><span><i class="ic ic-gold <?php echo esc_attr( $st[0] ); ?>"></i></span></span>
        </div>
        <p class="h-step__text"><?php mz_e( $st[1] ); ?></p>
      </li>
      <?php endforeach; ?>
    </ol>
    <div class="h-steps__cta" data-reveal>
      <a class="btn btn--gold btn--xl btn--shine" href="#contact"><?php mz_e( 'Отримати консультацію' ); ?></a>
    </div>
  </div>
</section>

<!-- ============ 06 WHY ============ -->
<section class="h-why" id="why">
  <div class="container h-why__in">
    <div class="h-why__photo" data-wow-reveal>
      <div data-parallax><img src="<?php echo esc_url( mz_img( 'lawyer-desk.webp' ) ); ?>" alt="<?php echo mz_a( 'Адвокат Мазур у робочому кабінеті' ); ?>" width="853" height="1280" loading="lazy"></div>
    </div>
    <div class="h-why__content">
      <span class="eyebrow" data-reveal><?php mz_e( 'Чому я' ); ?></span>
      <h2 class="h-why__title" data-split><?php mz_e( 'Чому клієнти' ); ?> <span class="gold-text"><?php mz_e( 'обирають мене' ); ?></span></h2>
      <div class="h-why__grid" data-reveal-group>
        <?php
        $reasons = array(
			array( 'ic-user-focus', 'Особисто', 'Не передаю справи помічникам. Ви говорите з адвокатом, який веде ваш захист.' ),
			array( 'ic-target', 'Спеціалізація', 'Кримінальні, адміністративні та транспортні справи — щоденна практика.' ),
			array( 'ic-eye', 'Чесно', 'Реальна оцінка перспектив ще до підписання договору.' ),
			array( 'ic-receipt', 'Прозора ціна', 'Вартість і етапи фіксуються в договорі. Без доплат.' ),
			array( 'ic-chat', 'На зв’язку', 'Прямий номер адвоката, месенджери, консультації онлайн.' ),
			array( 'ic-lock', 'Таємниця', 'Усе, що ви розповідаєте, захищено адвокатською таємницею.' ),
        );
        foreach ( $reasons as $r ) :
			?>
        <div class="reason">
          <span class="reason__head"><i class="ic ic-gold <?php echo esc_attr( $r[0] ); ?>"></i><span><?php mz_e( $r[1] ); ?></span></span>
          <p><?php mz_e( $r[2] ); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="h-why__btns" data-reveal>
        <a class="btn btn--gold btn--why btn--shine" href="#contact"><?php mz_e( 'Записатися на консультацію' ); ?></a>
        <button class="btn btn--ghost btn--call btn--call-sm" type="button" data-modal-open data-topic="<?php echo mz_a( 'Зворотний дзвінок' ); ?>"><i class="ic ic-gold ic-phone"></i><?php mz_e( 'Передзвоніть мені' ); ?></button>
      </div>
    </div>
  </div>
</section>

<!-- ============ 07 CONTACT ============ -->
<section class="h-contact" id="contact">
  <div class="container">
    <div class="h-contact__box" data-reveal>
      <div class="h-contact__media" data-wow-reveal>
        <div data-parallax><img src="<?php echo esc_url( mz_img( 'themis.webp' ) ); ?>" alt="<?php echo mz_a( 'Адвокат Мазур' ); ?>" width="941" height="941" loading="lazy"></div>
      </div>
      <div class="h-contact__body" data-form-wrap>
        <span class="eyebrow"><?php mz_e( 'Заявка' ); ?></span>
        <h2 class="h-contact__title" data-split><?php mz_e( 'Хочете отримати консультацію та оцінку перспектив справи?' ); ?></h2>
        <p class="h-contact__text"><?php mz_e( 'Заповніть форму — я зв\'яжуся з вами та запропоную план дій.' ); ?></p>
        <form class="form h-form" data-form data-form-location="home">
          <label class="field field--h"><i class="ic ic-user"></i><input type="text" name="name" placeholder="<?php echo mz_a( 'Ваше ім\'я' ); ?>" autocomplete="name" required></label>
          <label class="field field--h"><i class="ic ic-phone"></i><input type="tel" name="phone" placeholder="<?php echo mz_a( 'Номер телефону' ); ?>" autocomplete="tel" required></label>
          <div class="field field--h field--select h-form__full"><i class="ic ic-briefcase"></i>
            <select name="topic" data-select aria-label="<?php echo mz_a( 'Напрям' ); ?>">
              <option value=""><?php mz_e( 'Оберіть напрям' ); ?></option>
              <?php foreach ( $services as $s ) : ?>
              <option><?php echo esc_html( $s['nav'] ); ?></option>
              <?php endforeach; ?>
              <option><?php mz_e( 'Інше' ); ?></option>
            </select>
          </div>
          <button class="btn btn--gold btn--block h-form__full h-form__submit" type="submit"><?php mz_e( 'Надіслати заявку' ); ?></button>
          <p class="form__policy h-form__full"><?php mz_e( 'Натискаючи кнопку, ви погоджуєтеся на обробку' ); ?> <?php $pu = mz_privacy_url(); echo $pu ? '<a href="' . esc_url( $pu ) . '">' . esc_html( mz_t( 'персональних даних' ) ) . '</a>' : esc_html( mz_t( 'персональних даних' ) ); ?>.</p>
        </form>
        <div class="form-success">
          <i class="ic ic-gold ic-check-circle"></i>
          <div><b><?php mz_e( 'Дякую.' ); ?></b> <?php mz_e( 'Заявку отримано. Я зв\'яжуся з вами найближчим часом.' ); ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ( $reviews ) : ?>
<!-- ============ 08 REVIEWS ============ -->
<section class="reviews h-reviews" id="reviews">
  <div class="container reviews__head">
    <div>
      <span class="eyebrow" data-reveal><?php echo esc_html( $g_rating ? mz_t( 'Відгуки Google' ) : mz_t( 'Відгуки клієнтів' ) ); ?></span>
      <h2 class="h-head__title h-head__title--gold" data-split><?php mz_e( 'Відгуки клієнтів' ); ?></h2>
      <?php if ( $g_rating ) : ?>
      <span class="rating" data-reveal><b><?php echo esc_html( $g_rating ); ?></b><?php get_template_part( 'template-parts/stars' ); ?><?php echo esc_html( $g_count ? str_replace( '48', $g_count, mz_t( '48 відгуків у Google' ) ) : '' ); ?></span>
      <?php endif; ?>
    </div>
    <div class="arrows" data-reveal>
      <button class="arrow" type="button" aria-label="<?php echo mz_a( 'Назад' ); ?>" data-slider-prev><i class="ic ic-gold ic-arrow-left"></i></button>
      <button class="arrow arrow--gold" type="button" aria-label="<?php echo mz_a( 'Далі' ); ?>" data-slider-next><i class="ic ic-arrow-right"></i></button>
    </div>
  </div>

  <div class="reviews__track h-reviews__track" data-slider>
    <?php foreach ( $reviews as $v ) { get_template_part( 'template-parts/review', null, $v ); } ?>
  </div>

  <?php if ( $g_url || $review_url ) : ?>
  <div class="container reviews__foot" data-reveal>
    <?php if ( $g_url ) : ?><a class="link-gold link-gold--underline" href="<?php echo esc_url( $g_url ); ?>" target="_blank" rel="noopener"><?php mz_e( 'Усі відгуки в Google' ); ?> <i class="ic ic-gold ic-arrow-up-right"></i></a><?php endif; ?>
    <?php if ( $review_url ) : ?><a class="reviews__leave" href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener"><?php mz_e( 'Залишити відгук' ); ?></a><?php endif; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ( $faq ) : ?>
<!-- ============ 09 FAQ ============ -->
<section class="h-faq" id="faq">
  <div class="container faq">
    <div class="faq__side">
      <span class="eyebrow" data-reveal><?php mz_e( 'Питання та відповіді' ); ?></span>
      <h2 class="h2 h-faq__title" data-split><?php mz_e( 'Часті' ); ?> <span class="gold-text"><?php mz_e( 'питання' ); ?></span></h2>
      <p class="sec-head__text" data-reveal><?php mz_e( 'Не знайшли відповідь? Поставте питання напряму — відповім особисто.' ); ?></p>
      <button class="btn btn--ghost btn--call btn--call-sm" type="button" data-modal-open data-topic="<?php echo mz_a( 'Інше' ); ?>" data-reveal><i class="ic ic-gold ic-chat"></i><?php mz_e( 'Поставити питання' ); ?></button>
    </div>
    <div class="faq__list" data-reveal-group>
      <?php foreach ( $faq as $i => $q ) { get_template_part( 'template-parts/faq-item', null, array( 'q' => $q['q'], 'a' => $q['a'], 'open' => 0 === $i ) ); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ 10 SEO TEXT 2 ============ -->
<section class="h-seo h-seo--last">
  <div class="container">
    <div class="h-seo__in">
      <h2 class="h-seo__title" data-reveal><?php mz_e( 'Як обрати адвоката для своєї справи' ); ?></h2>
      <article class="h-seo__article" data-more>
        <p data-reveal><?php mz_e( 'Звертайте увагу на три речі: чи має адвокат практику саме у вашій категорії справ, чи веде він справу особисто і чи готовий письмово зафіксувати вартість та обсяг робіт. Індивідуальна практика дає саме це: один відповідальний адвокат, прямий зв\'язок і прозорі умови.' ); ?></p>
        <div class="h-seo__more" data-more-body><div>
          <h3><?php mz_e( 'Що підготувати до консультації' ); ?></h3>
          <p><?php mz_e( 'Документи, які у вас є, хронологію подій і перелік питань. Якщо документів немає — це не перешкода: їх можна отримати в процесі роботи.' ); ?></p>
          <h3><?php mz_e( 'Консультація онлайн' ); ?></h3>
          <p><?php mz_e( 'Консультація доступна в Telegram, Viber, WhatsApp або відеозв\'язком. Це зручно, якщо ви в іншому місті чи за кордоном.' ); ?></p>
        </div></div>
        <button class="more-btn" type="button" data-more-toggle aria-expanded="false"><span><?php mz_e( 'Читати повністю' ); ?></span> <i class="ic ic-gold ic-chevron-down"></i></button>
      </article>
    </div>
  </div>
</section>

<?php
get_footer();
