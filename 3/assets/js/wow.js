/* =========================================================
   Адвокат Мазур — анімаційний шар для всього сайту
   Потрібні: gsap, ScrollTrigger, Lenis (CDN) + main.js
   Без бібліотек сайт працює на базових анімаціях main.js.
   ========================================================= */
(function () {
  'use strict';
  var html = document.documentElement;
  var force = html.getAttribute('data-motion') === 'force';
  var reduce = window.matchMedia('(prefers-reduced-motion:reduce)').matches && !force;
  var pre = document.querySelector('[data-preloader]');

  if (!window.gsap || !window.ScrollTrigger || reduce) {
    if (pre) pre.remove();
    return;
  }

  html.classList.add('wow-on');
  gsap.registerPlugin(ScrollTrigger);
  gsap.config({ nullTargetWarn: false });

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var fine = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  var mm = gsap.matchMedia();
  var tocH = function () { var t = $('.toc'); return t && t.offsetHeight ? t.offsetHeight : 0; };
  var clamp = gsap.utils.clamp;

  // одноразова поява елементів при скролі: fromTo + clearProps, щоб нічого не «застрягало»
  var reveal = function (targets, from, opts) {
    targets = gsap.utils.toArray(targets);
    if (!targets.length) return;
    opts = opts || {};
    var to = { opacity: 1, x: 0, y: 0, rotation: 0, duration: opts.duration || 1, ease: opts.ease || 'expo.out', stagger: opts.stagger || 0, clearProps: 'transform,opacity' };
    gsap.set(targets, from);
    ScrollTrigger.create({
      trigger: opts.trigger || targets[0], start: opts.start || 'top 88%', once: true,
      onEnter: function () { gsap.to(targets, to); }
    });
  };

  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  if (!location.hash) window.scrollTo(0, 0);

  /* =======================================================
     1. Плавний скрол (Lenis)
     ======================================================= */
  var lenis = null;
  if (window.Lenis) {
    lenis = new window.Lenis({ lerp: 0.09, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
    gsap.ticker.lagSmoothing(0);
    $$('.modal, .drawer__panel, .cselect__list').forEach(function (e) { e.setAttribute('data-lenis-prevent', ''); });
    new MutationObserver(function () {
      document.body.classList.contains('is-locked') ? lenis.stop() : lenis.start();
    }).observe(document.body, { attributes: true, attributeFilter: ['class'] });
  }
  var scrollTo = function (target, opts) {
    if (lenis) setTimeout(function () { lenis.scrollTo(target, opts || {}); }, 30);
    else window.scrollTo({ top: typeof target === 'number' ? target : target.getBoundingClientRect().top + scrollY - 16, behavior: 'smooth' });
  };

  // якорі, «нагору» та перехід між сторінками
  var curtain = $('[data-curtain]');
  document.addEventListener('click', function (e) {
    var top = e.target.closest('[data-totop]');
    if (top) { e.preventDefault(); e.stopPropagation(); scrollTo(0, { duration: 1.6 }); return; }

    var a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented || e.button !== 0) return;
    var href = a.getAttribute('href');
    if (href.charAt(0) === '#') {
      if (href.length < 2) return;
      var t = $(href);
      if (t) { e.preventDefault(); scrollTo(t, { offset: -(tocH() + 16), duration: 1.4 }); }
      return;
    }
    if (/^(tel:|mailto:|viber:|javascript:|https?:)/.test(href) || a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey) return;
    // якір на поточній сторінці у вигляді «index.html#about»
    var url = new URL(a.href, location.href);
    if (url.pathname === location.pathname && url.hash) {
      var t2 = $(url.hash);
      if (t2) { e.preventDefault(); scrollTo(t2, { offset: -(tocH() + 16), duration: 1.4 }); }
      return;
    }
    if (curtain) {
      e.preventDefault();
      gsap.fromTo(curtain, { yPercent: 100 }, { yPercent: 0, duration: 0.6, ease: 'expo.inOut', onComplete: function () { location.href = a.href; } });
    }
  }, true);
  window.addEventListener('pageshow', function (e) { if (e.persisted && curtain) gsap.set(curtain, { yPercent: 100 }); });

  /* =======================================================
     2. H1 по літерах + scramble для «назв блоків»
     ======================================================= */
  var h1 = $('[data-chars]');
  var chars = [];
  var splitChars = function (node) {
    Array.prototype.slice.call(node.childNodes).forEach(function (ch) {
      if (ch.nodeType === 3) {
        var frag = document.createDocumentFragment();
        ch.textContent.split(/(\s+)/).forEach(function (part) {
          if (!part) return;
          if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
          var w = document.createElement('span'); w.className = 'word';
          part.split('').forEach(function (l) {
            var c = document.createElement('span'); c.className = 'char'; c.textContent = l;
            w.appendChild(c); chars.push(c);
          });
          frag.appendChild(w);
        });
        node.replaceChild(frag, ch);
      } else if (ch.nodeType === 1) splitChars(ch);
    });
  };
  if (h1) splitChars(h1);

  var GLYPHS = 'АБВГДЕЖЗИКЛМНОПРСТУФХЦЧШЮЯ0123456789◆';
  var scramble = function (el) {
    var node = null;
    Array.prototype.slice.call(el.childNodes).forEach(function (n) { if (n.nodeType === 3 && n.textContent.trim()) node = n; });
    if (!node) return;
    var final = node.textContent, len = final.length, frame = 0, total = 24;
    var tick = function () {
      var p = frame / total, out = '';
      for (var i = 0; i < len; i++) {
        var c = final[i];
        out += (c === ' ' || i < p * len) ? c : GLYPHS[(Math.random() * GLYPHS.length) | 0];
      }
      node.textContent = out;
      if (frame++ < total) requestAnimationFrame(tick); else node.textContent = final;
    };
    tick();
  };

  /* =======================================================
     3. Прелоадер → інтро першого екрана
     ======================================================= */
  var heroPhoto = $('.hero__photo, .h-hero__photo');
  var intro = gsap.timeline({ paused: true, defaults: { ease: 'expo.out' } });
  intro.fromTo('.hdr__in > *', { y: -24, opacity: 0 }, { y: 0, opacity: 1, duration: 1, stagger: 0.08, clearProps: 'transform,opacity' }, 0);
  if (heroPhoto) intro.fromTo(heroPhoto, { clipPath: 'inset(100% 0% 0% 0%)', scale: 1.15 },
    { clipPath: 'inset(0% 0% 0% 0%)', scale: 1, duration: 1.6, ease: 'expo.inOut', clearProps: 'clipPath,transform' }, 0);
  if (chars.length) intro.fromTo(chars, { yPercent: 120, rotateX: -95, opacity: 0 }, { yPercent: 0, rotateX: 0, opacity: 1, duration: 1.2, stagger: Math.min(0.03, 0.9 / chars.length), clearProps: 'transform,opacity' }, 0.25);
  intro.fromTo('[data-hero-in]', { y: 34, opacity: 0 }, { y: 0, opacity: 1, duration: 1, stagger: 0.09, ease: 'power3.out', clearProps: 'transform' }, 0.45)
    .add(function () { $$('[data-hero] .eyebrow').forEach(scramble); }, 0.5);
  if ($('.free-seal')) intro.fromTo('.free-seal', { scale: 0, rotation: -200 }, { scale: 1, rotation: 0, duration: 1.4, ease: 'back.out(1.5)' }, 0.9);
  if ($('.hero__card')) intro.fromTo('.hero__card', { x: -70, opacity: 0 }, { x: 0, opacity: 1, duration: 1.1 }, 1.05);
  if ($('.h-stats__box')) intro.fromTo('.h-stats__box', { y: 60, opacity: 0 }, { y: 0, opacity: 1, duration: 1.2, clearProps: 'transform,opacity' }, 1.0);
  if ($('.toc')) intro.fromTo('.toc', { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: 0.9, clearProps: 'transform,opacity' }, 1.1);

  var startPage = function () {
    if (lenis) lenis.start();
    intro.play();
    ScrollTrigger.refresh();
    if (location.hash) { var t = $(location.hash); if (t) scrollTo(t, { offset: -(tocH() + 16), immediate: true }); }
  };

  var quick = html.classList.contains('pl-quick');
  try { sessionStorage.setItem('mz-v', '1'); } catch (e) { /* noop */ }

  if (pre) {
    if (lenis) lenis.stop();
    var done = function () { pre.remove(); };
    if (quick) {
      // повторний перехід — лише розкриття шторок
      gsap.timeline({ onComplete: done })
        .add(function () { pre.classList.add('is-done'); }, 0)
        .to('.preloader__panel--top', { yPercent: -100, duration: 0.9, ease: 'expo.inOut' }, 0.05)
        .to('.preloader__panel--bottom', { yPercent: 100, duration: 0.9, ease: 'expo.inOut' }, 0.05)
        .add(startPage, 0.2);
    } else {
      var count = $('[data-preloader-count]'), cnt = { v: 0 };
      gsap.timeline({ onComplete: done })
        .to('.preloader__mono text', { strokeDashoffset: 0, duration: 1.2, ease: 'power2.inOut' }, 0)
        .to('.preloader__line i', { scaleX: 1, duration: 1.35, ease: 'power3.inOut' }, 0)
        .to(cnt, { v: 100, duration: 1.35, ease: 'power3.inOut', onUpdate: function () { count.textContent = Math.round(cnt.v); } }, 0)
        .to('.preloader__mono text', { fillOpacity: 1, strokeOpacity: 0, duration: 0.5 }, 1.0)
        .to('.preloader__inner', { y: -24, opacity: 0, duration: 0.45, ease: 'power2.in' }, 1.55)
        .add(function () { pre.classList.add('is-done'); }, 1.8)
        .to('.preloader__panel--top', { yPercent: -100, duration: 1.1, ease: 'expo.inOut' }, 1.8)
        .to('.preloader__panel--bottom', { yPercent: 100, duration: 1.1, ease: 'expo.inOut' }, 1.8)
        .add(startPage, 2.0);
    }
  } else startPage();

  /* =======================================================
     4. Перший екран: світло за мишкою + легкий 3D-нахил
     ======================================================= */
  var hero = $('[data-hero]');
  if (hero && fine) {
    var media = $('.hero__media, .h-hero__media'), seal = $('.free-seal'), card = $('.hero__card');
    var q = function (el, p, d) { return el ? gsap.quickTo(el, p, { duration: d || 0.9, ease: 'power3' }) : function () {}; };
    var mRY = q(media, 'rotationY'), mRX = q(media, 'rotationX');
    var sX = q(seal, 'x', 1.1), sY = q(seal, 'y', 1.1), cX = q(card, 'x'), cY = q(card, 'y');
    var raf = 0, last = null;
    var apply = function () {
      raf = 0;
      var r = hero.getBoundingClientRect();
      var nx = (last.clientX - r.left) / r.width - 0.5, ny = (last.clientY - r.top) / r.height - 0.5;
      mRY(nx * 6); mRX(-ny * 5);
      sX(nx * 36); sY(ny * 36); cX(nx * -22); cY(ny * -22);
      hero.style.setProperty('--hx', ((nx + 0.5) * 100).toFixed(1) + '%');
      hero.style.setProperty('--hy', ((ny + 0.5) * 100).toFixed(1) + '%');
    };
    hero.addEventListener('mousemove', function (e) { last = e; if (!raf) raf = requestAnimationFrame(apply); });
    hero.addEventListener('mouseleave', function () { mRY(0); mRX(0); sX(0); sY(0); cX(0); cY(0); });
  }

  /* =======================================================
     5. Кастомний курсор
     ======================================================= */
  if (fine) (function () {
    var cur = document.createElement('div');
    cur.className = 'cursor is-hidden';
    cur.innerHTML = '<span class="cursor__ring"><span class="cursor__label"></span></span><span class="cursor__dot"></span>';
    document.body.appendChild(cur);
    html.classList.add('has-cursor');
    var dot = $('.cursor__dot', cur), ring = $('.cursor__ring', cur), label = $('.cursor__label', cur);
    var dX = gsap.quickTo(dot, 'x', { duration: 0.12, ease: 'power3' }), dY = gsap.quickTo(dot, 'y', { duration: 0.12, ease: 'power3' });
    var rX = gsap.quickTo(ring, 'x', { duration: 0.45, ease: 'power3' }), rY = gsap.quickTo(ring, 'y', { duration: 0.45, ease: 'power3' });
    var ru = (html.getAttribute('lang') || '').indexOf('ru') === 0;
    var LABELS = ru
      ? [['.sit', 'Выбрать'], ['.svc', 'Открыть'], ['.case-card', 'Читать'], ['.reviews__track', 'Тяни'], ['.tabs__btn', 'Смотреть']]
      : [['.sit', 'Обрати'], ['.svc', 'Відкрити'], ['.case-card', 'Читати'], ['.reviews__track', 'Тягни'], ['.tabs__btn', 'Дивитись']];

    window.addEventListener('mousemove', function (e) {
      cur.classList.remove('is-hidden');
      dX(e.clientX); dY(e.clientY); rX(e.clientX); rY(e.clientY);
    });
    document.addEventListener('mouseover', function (e) {
      var t = e.target, lab = '';
      if (!t.closest) return;
      for (var i = 0; i < LABELS.length; i++) if (t.closest(LABELS[i][0])) { lab = LABELS[i][1]; break; }
      var isText = !!t.closest('input, textarea');
      var isLink = !lab && !isText && !!t.closest('a, button, .field--select, .cselect__opt, label');
      label.textContent = lab;
      cur.classList.toggle('is-label', !!lab);
      cur.classList.toggle('is-text', isText);
      cur.classList.toggle('is-link', isLink);
    });
    document.addEventListener('mousedown', function () { cur.classList.add('is-down'); });
    document.addEventListener('mouseup', function () { cur.classList.remove('is-down'); });
    document.documentElement.addEventListener('mouseleave', function () { cur.classList.add('is-hidden'); });
  })();

  /* =======================================================
     6. Магнітні кнопки
     ======================================================= */
  if (fine) $$('.btn--gold:not(.mbar__btn), .btn--dark, .btn--ghost, .arrow, .fab, .totop, .socials a, .hdr__phone, .band__msg a, .more-btn, .svc__more').forEach(function (el) {
    el.setAttribute('data-magnetic', '');
    var xTo = gsap.quickTo(el, 'x', { duration: 0.6, ease: 'power3' }), yTo = gsap.quickTo(el, 'y', { duration: 0.6, ease: 'power3' });
    el.addEventListener('mousemove', function (e) {
      var r = el.getBoundingClientRect();
      xTo(clamp(-14, 14, (e.clientX - r.left - r.width / 2) * 0.3));
      yTo(clamp(-10, 10, (e.clientY - r.top - r.height / 2) * 0.4));
    });
    el.addEventListener('mouseleave', function () { xTo(0); yTo(0); });
  });

  /* =======================================================
     7. 3D-нахил карток
     ======================================================= */
  if (fine) $$('.sit, .price, .svc').forEach(function (card) {
    if (!card.querySelector('.glare')) { var g = document.createElement('span'); g.className = 'glare'; card.prepend(g); }
    gsap.set(card, { transformPerspective: 900 });
    var rx = gsap.quickTo(card, 'rotationX', { duration: 0.6, ease: 'power3' }), ry = gsap.quickTo(card, 'rotationY', { duration: 0.6, ease: 'power3' });
    card.addEventListener('mouseenter', function () { card.classList.add('is-tilt'); });
    card.addEventListener('mousemove', function (e) {
      var r = card.getBoundingClientRect(), nx = (e.clientX - r.left) / r.width, ny = (e.clientY - r.top) / r.height;
      ry((nx - 0.5) * 10); rx((0.5 - ny) * 10);
      card.style.setProperty('--gx', (nx * 100) + '%'); card.style.setProperty('--gy', (ny * 100) + '%');
    });
    card.addEventListener('mouseleave', function () { rx(0); ry(0); });
  });

  /* =======================================================
     8. Скрол-ефекти
     ======================================================= */
  // «назви блоків» — scramble при появі
  $$('.eyebrow').forEach(function (eb) {
    if (eb.closest('[data-hero]') || eb.closest('.modal')) return;
    ScrollTrigger.create({ trigger: eb, start: 'top 92%', once: true, onEnter: function () { scramble(eb); } });
  });

  // фонові слова їдуть горизонтально
  $$('.ghost-word, .h-head__ghost').forEach(function (g) {
    gsap.set(g, { xPercent: -50, yPercent: -50, x: 0, y: 0 });
    gsap.fromTo(g, { x: function () { return innerWidth * 0.12; } }, {
      x: function () { return -innerWidth * 0.12; }, ease: 'none',
      scrollTrigger: { trigger: g.closest('section'), start: 'top bottom', end: 'bottom top', scrub: true, invalidateOnRefresh: true }
    });
  });

  // текст «заливається» словами під час скролу
  $$('[data-wow-fill]').forEach(function (el) {
    var words = [];
    var walk = function (node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (ch) {
        if (ch.nodeType === 3) {
          var frag = document.createDocumentFragment();
          ch.textContent.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
            var s = document.createElement('span'); s.className = 'fw'; s.textContent = part;
            frag.appendChild(s); words.push(s);
          });
          node.replaceChild(frag, ch);
        } else if (ch.nodeType === 1) walk(ch);
      });
    };
    walk(el);
    gsap.fromTo(words, { opacity: 0.16 }, { opacity: 1, stagger: 0.1, ease: 'none', scrollTrigger: { trigger: el, start: 'top 88%', end: 'bottom 55%', scrub: true } });
  });

  // marquee: швидкість і напрям від швидкості скролу
  var mq = $('.marquee__track');
  if (mq) (function () {
    var half = 0, xPos = 0, dir = 1, visible = true;
    var measure = function () { half = mq.scrollWidth / 2; };
    measure(); window.addEventListener('resize', measure);
    new IntersectionObserver(function (en) { visible = en[0].isIntersecting; }).observe(mq);
    var skewTo = gsap.quickTo(mq, 'skewX', { duration: 0.5, ease: 'power3' });
    gsap.ticker.add(function (t, dt) {
      if (!visible) return;
      var v = lenis ? lenis.velocity : 0;
      if (Math.abs(v) > 0.5) dir = v > 0 ? 1 : -1;
      xPos -= (1.1 + Math.min(Math.abs(v), 50) * 0.55) * dir * (dt / 16.7);
      if (xPos <= -half) xPos += half; else if (xPos > 0) xPos -= half;
      gsap.set(mq, { x: xPos });
      skewTo(clamp(-14, 14, -v * 0.5));
    });
  })();

  // CTA-плашка розкривається
  var band = $('[data-wow-band]');
  if (band) gsap.fromTo(band, { scale: 0.86, y: 60, borderRadius: 120 }, {
    scale: 1, y: 0, borderRadius: 28, ease: 'none',
    scrollTrigger: { trigger: band, start: 'top 98%', end: 'top 50%', scrub: true }
  });

  // етапи на сторінках послуг: пін + послідовне підсвічування (десктоп)
  var stepsWrap = $('[data-wow-steps]');
  if (stepsWrap) {
    var steps = $$('.step', stepsWrap);
    var lit = function (p) { steps.forEach(function (s, i) { s.classList.toggle('is-lit', p >= i / (steps.length - 1) - 0.03); }); };
    mm.add('(min-width:1101px)', function () {
      gsap.set(steps, { opacity: 0.15, y: 40 });
      var tl = gsap.timeline({
        scrollTrigger: {
          trigger: '#steps', pin: true, scrub: 0.6, anticipatePin: 1,
          start: function () { return 'top top+=' + tocH(); }, end: '+=110%',
          onUpdate: function (self) { stepsWrap.style.setProperty('--p', self.progress.toFixed(3)); lit(self.progress); }
        }
      });
      steps.forEach(function (s, i) { tl.to(s, { opacity: 1, y: 0, duration: 1, ease: 'power2.out' }, i * 0.9); });
      return function () { gsap.set(steps, { clearProps: 'opacity,transform' }); };
    });
    mm.add('(max-width:1100px)', function () {
      stepsWrap.style.setProperty('--p', 1);
      lit(1);
      reveal(steps, { y: 60, opacity: 0 }, { trigger: stepsWrap, stagger: 0.12, start: 'top 82%' });
    });
  }

  // ціни: віяло карток
  var fan = $('[data-wow-fan]');
  if (fan) {
    var pc = $$('.price', fan);
    var wide = innerWidth > 767;
    pc.forEach(function (c, i) {
      reveal(c, { opacity: 0, y: 160, x: wide ? [160, 0, -160][i] || 0 : 0, rotation: wide ? [-12, 0, 12][i] || 0 : 0 },
        { trigger: fan, duration: 1.3, start: 'top 85%' });
    });
  }

  // фото: розкриття маски під час скролу
  $$('[data-wow-reveal]').forEach(function (el) {
    gsap.fromTo(el, { clipPath: 'inset(14% 12% 14% 12%)' }, {
      clipPath: 'inset(0% 0% 0% 0%)', ease: 'none',
      scrollTrigger: { trigger: el, start: 'top 95%', end: 'center 60%', scrub: true }
    });
  });
  if ($('.about__stats')) reveal('.about__stats > div', { x: 80, opacity: 0 }, { trigger: '.about__media', start: 'top 60%', stagger: 0.15 });

  // поради та список ситуацій — простий каскад (без 3D, без «застрягань»)
  $$('.tips').forEach(function (t) { reveal($$('.tip', t), { y: 40, opacity: 0 }, { trigger: t, stagger: 0.08, duration: 0.9 }); });
  $$('.dlist').forEach(function (l) { reveal($$('li', l), { x: -30, opacity: 0 }, { trigger: l, stagger: 0.05, duration: 0.8, ease: 'power3.out' }); });

  /* =======================================================
     9. Таби: золотий індикатор + поява контенту
     ======================================================= */
  var ind = $('.tabs__indicator');
  if (ind) {
    var moveInd = function (btn, instant) {
      gsap.to(ind, { top: btn.offsetTop, height: btn.offsetHeight, duration: instant ? 0 : 0.6, ease: 'expo.out' });
    };
    var act = $('.tabs__btn.is-active');
    if (act) moveInd(act, true);
    window.addEventListener('resize', function () { var a = $('.tabs__btn.is-active'); if (a) moveInd(a, true); });
    $$('.tabs__btn').forEach(function (b) {
      b.addEventListener('click', function () {
        moveInd(b);
        var panel = $('.tabs__panel.is-active');
        if (panel) gsap.fromTo($$('.tabs__inner > *', panel), { y: 22, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.6, stagger: 0.04, ease: 'power3.out', overwrite: true, clearProps: 'transform,opacity' });
      });
    });
  }

  /* =======================================================
     10. Відгуки: drag мишкою + нахил від швидкості
     ======================================================= */
  var track = $('.reviews__track');
  if (track && fine) (function () {
    var cards = $$('.review', track), down = false, sx = 0, sl = 0, lx = 0, vel = 0;
    var skewTo = cards.map(function (c) { return gsap.quickTo(c, 'skewX', { duration: 0.4, ease: 'power3' }); });
    track.addEventListener('pointerdown', function (e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      down = true; sx = lx = e.clientX; sl = track.scrollLeft; vel = 0;
      track.classList.add('is-dragging'); track.setPointerCapture(e.pointerId);
    });
    track.addEventListener('pointermove', function (e) {
      if (!down) return;
      track.scrollLeft = sl - (e.clientX - sx);
      vel = e.clientX - lx; lx = e.clientX;
      var k = clamp(-8, 8, vel * 0.5);
      skewTo.forEach(function (f) { f(k); });
    });
    var up = function () {
      if (!down) return;
      down = false;
      track.classList.remove('is-dragging');
      skewTo.forEach(function (f) { f(0); });
      if (Math.abs(vel) > 2) track.scrollBy({ left: -vel * 12, behavior: 'smooth' });
    };
    track.addEventListener('pointerup', up);
    track.addEventListener('pointercancel', up);
    track.addEventListener('dragstart', function (e) { e.preventDefault(); });
  })();

  /* =======================================================
     11. Перерахунок розмірів
     ======================================================= */
  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { ScrollTrigger.refresh(); });
})();
