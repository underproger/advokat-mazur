/* =========================================================
   WOW-шар для service-wow.html
   Потрібні: gsap, ScrollTrigger, Lenis (CDN) + service.js
   ========================================================= */
(function () {
  'use strict';
  var html = document.documentElement;
  var force = html.getAttribute('data-motion') === 'force';
  var reduce = window.matchMedia('(prefers-reduced-motion:reduce)').matches && !force;
  var pre = document.querySelector('[data-preloader]');

  // без бібліотек або при reduced-motion — звичайна сторінка
  if (!window.gsap || !window.ScrollTrigger || reduce) {
    if (pre) pre.remove();
    return;
  }

  html.classList.add('wow-on');
  gsap.registerPlugin(ScrollTrigger);

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var fine = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  var mm = gsap.matchMedia();
  var tocH = function () { var t = $('.toc'); return t ? t.offsetHeight : 64; };
  var clamp = gsap.utils.clamp;

  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  window.scrollTo(0, 0);

  /* =======================================================
     1. Smooth scroll (Lenis)
     ======================================================= */
  var lenis = null;
  if (window.Lenis) {
    lenis = new window.Lenis({ lerp: 0.09, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
    gsap.ticker.lagSmoothing(0);
    $$('.modal, .drawer__panel, .cselect__list').forEach(function (e) { e.setAttribute('data-lenis-prevent', ''); });
    // модалка / меню блокують body → зупиняємо Lenis
    new MutationObserver(function () {
      document.body.classList.contains('is-locked') ? lenis.stop() : lenis.start();
    }).observe(document.body, { attributes: true, attributeFilter: ['class'] });
  }
  var scrollTo = function (target, opts) {
    if (lenis) setTimeout(function () { lenis.scrollTo(target, opts || {}); }, 30);
    else window.scrollTo({ top: typeof target === 'number' ? target : target.getBoundingClientRect().top + scrollY, behavior: 'smooth' });
  };

  // якорі, «нагору» та перехід між сторінками
  var curtain = $('[data-curtain]');
  document.addEventListener('click', function (e) {
    var top = e.target.closest('[data-totop]');
    if (top) { e.preventDefault(); e.stopPropagation(); scrollTo(0, { duration: 1.6 }); return; }

    var a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented) return;
    var href = a.getAttribute('href');
    if (href.charAt(0) === '#') {
      if (href.length < 2) return;
      var t = $(href);
      if (t) { e.preventDefault(); scrollTo(t, { offset: -(tocH() + 16), duration: 1.4 }); }
      return;
    }
    // перехід на іншу сторінку — шторка
    if (/^(tel:|mailto:|javascript:)/.test(href) || a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey) return;
    if (curtain) {
      e.preventDefault();
      gsap.fromTo(curtain, { yPercent: 100 }, { yPercent: 0, duration: 0.7, ease: 'expo.inOut', onComplete: function () { location.href = a.href; } });
    }
  }, true);
  window.addEventListener('pageshow', function (e) { if (e.persisted && curtain) gsap.set(curtain, { yPercent: 100 }); });

  /* =======================================================
     2. Hero: розбивка H1 на літери
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

  /* =======================================================
     3. Scramble для eyebrow
     ======================================================= */
  var GLYPHS = 'АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЮЯ0123456789◆';
  var scramble = function (el) {
    var node = null;
    Array.prototype.slice.call(el.childNodes).forEach(function (n) { if (n.nodeType === 3 && n.textContent.trim()) node = n; });
    if (!node) return;
    var final = node.textContent, len = final.length, frame = 0, total = 26;
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
     4. Прелоадер → інтро hero
     ======================================================= */
  var intro = gsap.timeline({ paused: true, defaults: { ease: 'expo.out' } });
  intro
    .from('.hdr__in > *', { y: -24, opacity: 0, duration: 1, stagger: 0.08 }, 0)
    .fromTo('.hero__photo', { clipPath: 'inset(100% 0% 0% 0% round 28px)', scale: 1.18 },
      { clipPath: 'inset(0% 0% 0% 0% round 28px)', scale: 1, duration: 1.7, ease: 'expo.inOut' }, 0)
    .from(chars, { yPercent: 120, rotateX: -95, opacity: 0, duration: 1.2, stagger: 0.035 }, 0.25)
    .fromTo('[data-hero-in]', { y: 34, opacity: 0 }, { y: 0, opacity: 1, duration: 1, stagger: 0.09, ease: 'power3.out' }, 0.45)
    .add(function () { $$('[data-hero] .eyebrow').forEach(scramble); }, 0.5)
    .from('.free-seal', { scale: 0, rotation: -200, duration: 1.4, ease: 'back.out(1.5)' }, 0.9)
    .from('.hero__card', { x: -70, opacity: 0, duration: 1.1 }, 1.05)
    .from('.toc', { y: 30, opacity: 0, duration: 0.9 }, 1.1);

  var heroCount = $('.hero__card [data-count]');
  if (heroCount) {
    var hc = { v: 0 };
    intro.to(hc, { v: +heroCount.getAttribute('data-count'), duration: 1.6, ease: 'power3.out', onUpdate: function () { heroCount.textContent = Math.round(hc.v); } }, 1.1);
  }

  var startPage = function () {
    if (lenis) lenis.start();
    intro.play();
    ScrollTrigger.refresh();
  };

  if (pre) {
    if (lenis) lenis.stop();
    var count = $('[data-preloader-count]'), cnt = { v: 0 };
    gsap.timeline({ onComplete: function () { pre.remove(); } })
      .to('.preloader__mono text', { strokeDashoffset: 0, duration: 1.2, ease: 'power2.inOut' }, 0)
      .to('.preloader__line i', { scaleX: 1, duration: 1.35, ease: 'power3.inOut' }, 0)
      .to(cnt, { v: 100, duration: 1.35, ease: 'power3.inOut', onUpdate: function () { count.textContent = Math.round(cnt.v); } }, 0)
      .to('.preloader__mono text', { fillOpacity: 1, strokeOpacity: 0, duration: 0.5 }, 1.0)
      .to('.preloader__inner', { y: -24, opacity: 0, duration: 0.45, ease: 'power2.in' }, 1.55)
      .add(function () { pre.classList.add('is-done'); }, 1.8)
      .to('.preloader__panel--top', { yPercent: -100, duration: 1.1, ease: 'expo.inOut' }, 1.8)
      .to('.preloader__panel--bottom', { yPercent: 100, duration: 1.1, ease: 'expo.inOut' }, 1.8)
      .add(startPage, 2.0);
  } else startPage();

  /* =======================================================
     5. Hero: світло за мишкою, 3D-нахил, золотий пил
     ======================================================= */
  var hero = $('[data-hero]');
  if (hero && fine) {
    var media = $('.hero__media'), seal = $('.free-seal'), card = $('.hero__card');
    var q = function (el, p, d) { return gsap.quickTo(el, p, { duration: d || 0.9, ease: 'power3' }); };
    var mRY = q(media, 'rotationY'), mRX = q(media, 'rotationX');
    var sX = q(seal, 'x', 1.1), sY = q(seal, 'y', 1.1), cX = q(card, 'x'), cY = q(card, 'y');
    hero.addEventListener('mousemove', function (e) {
      var r = hero.getBoundingClientRect();
      var nx = (e.clientX - r.left) / r.width - 0.5, ny = (e.clientY - r.top) / r.height - 0.5;
      mRY(nx * 9); mRX(-ny * 7);
      sX(nx * 46); sY(ny * 46); cX(nx * -28); cY(ny * -28);
      hero.style.setProperty('--hx', ((e.clientX - r.left) / r.width * 100).toFixed(1) + '%');
      hero.style.setProperty('--hy', ((e.clientY - r.top) / r.height * 100).toFixed(1) + '%');
    });
    hero.addEventListener('mouseleave', function () { mRY(0); mRX(0); sX(0); sY(0); cX(0); cY(0); });
  }

  var canvas = $('[data-dust]');
  if (canvas) (function () {
    var ctx = canvas.getContext('2d'), dpr = Math.min(window.devicePixelRatio || 1, 2);
    var W = 0, H = 0, parts = [], mouse = { x: -999, y: -999 }, running = false;
    var make = function () {
      var n = Math.min(120, Math.round(W * H / 12000));
      parts = [];
      for (var i = 0; i < n; i++) parts.push({
        x: Math.random() * W, y: Math.random() * H,
        r: 0.4 + Math.random() * 1.6, vx: (Math.random() - 0.5) * 0.18, vy: -(0.08 + Math.random() * 0.35),
        a: 0.25 + Math.random() * 0.6, t: Math.random() * 6.28
      });
    };
    var resize = function () {
      W = canvas.offsetWidth; H = canvas.offsetHeight;
      canvas.width = W * dpr; canvas.height = H * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      make();
    };
    var draw = function () {
      if (!running) return;
      ctx.clearRect(0, 0, W, H);
      for (var i = 0; i < parts.length; i++) {
        var p = parts[i];
        var dx = p.x - mouse.x, dy = p.y - mouse.y, d2 = dx * dx + dy * dy;
        if (d2 < 16000) { var f = (16000 - d2) / 16000 * 1.6; var d = Math.sqrt(d2) || 1; p.x += dx / d * f; p.y += dy / d * f; }
        p.x += p.vx; p.y += p.vy; p.t += 0.03;
        if (p.y < -10) { p.y = H + 10; p.x = Math.random() * W; }
        if (p.x < -10) p.x = W + 10; else if (p.x > W + 10) p.x = -10;
        var al = p.a * (0.55 + 0.45 * Math.sin(p.t));
        ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 6.283);
        ctx.fillStyle = 'rgba(240,207,127,' + al.toFixed(3) + ')'; ctx.fill();
        if (p.r > 1.4) {
          ctx.beginPath(); ctx.arc(p.x, p.y, p.r * 4, 0, 6.283);
          ctx.fillStyle = 'rgba(226,184,94,' + (al * 0.08).toFixed(3) + ')'; ctx.fill();
        }
      }
      requestAnimationFrame(draw);
    };
    resize();
    window.addEventListener('resize', resize);
    hero.addEventListener('mousemove', function (e) { var r = canvas.getBoundingClientRect(); mouse.x = e.clientX - r.left; mouse.y = e.clientY - r.top; });
    hero.addEventListener('mouseleave', function () { mouse.x = mouse.y = -999; });
    new IntersectionObserver(function (en) {
      var v = en[0].isIntersecting;
      if (v && !running) { running = true; draw(); } else if (!v) running = false;
    }).observe(canvas);
  })();

  /* =======================================================
     6. Кастомний курсор
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
    var LABELS = [['.sit', 'Обрати'], ['.reviews__track', 'Тягни'], ['.tabs__btn', 'Дивитись']];

    window.addEventListener('mousemove', function (e) {
      cur.classList.remove('is-hidden');
      dX(e.clientX); dY(e.clientY); rX(e.clientX); rY(e.clientY);
    });
    document.addEventListener('mouseover', function (e) {
      var t = e.target, lab = '';
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
     7. Магнітні кнопки
     ======================================================= */
  if (fine) $$('.btn--gold:not(.mbar__btn), .btn--dark, .btn--ghost, .arrow, .fab, .socials a, .hdr__phone, .band__msg a').forEach(function (el) {
    el.setAttribute('data-magnetic', '');
    var xTo = gsap.quickTo(el, 'x', { duration: 0.6, ease: 'power3' }), yTo = gsap.quickTo(el, 'y', { duration: 0.6, ease: 'power3' });
    el.addEventListener('mousemove', function (e) {
      var r = el.getBoundingClientRect();
      xTo(clamp(-16, 16, (e.clientX - r.left - r.width / 2) * 0.3));
      yTo(clamp(-12, 12, (e.clientY - r.top - r.height / 2) * 0.4));
    });
    el.addEventListener('mouseleave', function () { xTo(0); yTo(0); });
  });

  /* =======================================================
     8. 3D-нахил карток
     ======================================================= */
  if (fine) $$('.sit, .price').forEach(function (card) {
    if (card.classList.contains('price')) { var g = document.createElement('span'); g.className = 'glare'; card.prepend(g); }
    gsap.set(card, { transformPerspective: 900 });
    var rx = gsap.quickTo(card, 'rotationX', { duration: 0.6, ease: 'power3' }), ry = gsap.quickTo(card, 'rotationY', { duration: 0.6, ease: 'power3' });
    card.addEventListener('mousemove', function (e) {
      var r = card.getBoundingClientRect(), nx = (e.clientX - r.left) / r.width, ny = (e.clientY - r.top) / r.height;
      ry((nx - 0.5) * 12); rx((0.5 - ny) * 12);
      card.style.setProperty('--gx', (nx * 100) + '%'); card.style.setProperty('--gy', (ny * 100) + '%');
    });
    card.addEventListener('mouseleave', function () { rx(0); ry(0); });
  });

  /* =======================================================
     9. Скрол-ефекти
     ======================================================= */
  // прогрес-бар
  var bar = $('[data-progress]');
  if (bar) ScrollTrigger.create({ start: 0, end: 'max', onUpdate: function (s) { gsap.set(bar, { scaleX: s.progress }); } });

  // eyebrow — scramble при появі
  $$('.eyebrow').forEach(function (eb) {
    if (eb.closest('[data-hero]') || eb.closest('.modal')) return;
    ScrollTrigger.create({ trigger: eb, start: 'top 92%', once: true, onEnter: function () { scramble(eb); } });
  });

  // фонові слова їдуть горизонтально
  $$('.ghost-word').forEach(function (g) {
    gsap.set(g, { xPercent: -50, yPercent: -50, x: 0, y: 0 });
    gsap.fromTo(g, { x: function () { return innerWidth * 0.14; } }, {
      x: function () { return -innerWidth * 0.14; }, ease: 'none',
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
    gsap.to(words, { opacity: 1, stagger: 0.1, ease: 'none', scrollTrigger: { trigger: el, start: 'top 88%', end: 'bottom 50%', scrub: true } });
  });

  // marquee: швидкість і напрям від швидкості скролу
  var mq = $('.marquee__track');
  if (mq) (function () {
    var half = 0, xPos = 0, dir = 1;
    var measure = function () { half = mq.scrollWidth / 2; };
    measure(); window.addEventListener('resize', measure);
    var skewTo = gsap.quickTo(mq, 'skewX', { duration: 0.5, ease: 'power3' });
    gsap.ticker.add(function (t, dt) {
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
  if (band) gsap.fromTo(band, { scale: 0.84, y: 60, borderRadius: 120 }, {
    scale: 1, y: 0, borderRadius: 28, ease: 'none',
    scrollTrigger: { trigger: band, start: 'top 98%', end: 'top 45%', scrub: true }
  });

  // етапи: пін + послідовне підсвічування (десктоп)
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
      gsap.from(steps, { y: 70, opacity: 0, duration: 1, stagger: 0.12, ease: 'expo.out', scrollTrigger: { trigger: stepsWrap, start: 'top 80%' }, onComplete: function () { lit(1); } });
    });
  }

  // ціни: віяло карток
  var fan = $('[data-wow-fan]');
  if (fan) {
    var pc = $$('.price', fan);
    gsap.from(pc, {
      y: 200, opacity: 0, duration: 1.4, ease: 'expo.out', stagger: 0.12,
      x: function (i) { return innerWidth > 767 ? [180, 0, -180][i] || 0 : 0; },
      rotation: function (i) { return innerWidth > 767 ? [-14, 0, 14][i] || 0 : 0; },
      scrollTrigger: { trigger: fan, start: 'top 88%' }
    });
  }

  // фото адвоката: розкриття маски + статистика
  if ($('.about__photo')) {
    gsap.fromTo('.about__photo', { clipPath: 'inset(16% 14% 16% 14% round 24px)' }, {
      clipPath: 'inset(0% 0% 0% 0% round 24px)', ease: 'none',
      scrollTrigger: { trigger: '.about__media', start: 'top 92%', end: 'center 55%', scrub: true }
    });
    gsap.from('.about__stats > div', { x: 90, opacity: 0, duration: 1.1, stagger: 0.15, ease: 'expo.out', scrollTrigger: { trigger: '.about__media', start: 'top 60%' } });
  }

  // поради та список ситуацій — каскад
  gsap.utils.toArray('.tips').forEach(function (t) {
    gsap.from($$('.tip', t), { y: 60, rotationX: -40, transformPerspective: 800, opacity: 0, duration: 1, stagger: 0.1, ease: 'expo.out', scrollTrigger: { trigger: t, start: 'top 85%' } });
  });
  gsap.utils.toArray('.dlist').forEach(function (l) {
    gsap.from($$('li', l), { x: -40, opacity: 0, duration: 0.9, stagger: 0.06, ease: 'power3.out', scrollTrigger: { trigger: l, start: 'top 85%' } });
  });

  /* =======================================================
     10. Таби: золотий індикатор + каскад контенту
     ======================================================= */
  var ind = $('.tabs__indicator');
  if (ind) {
    var moveInd = function (btn, instant) {
      gsap.to(ind, { top: btn.offsetTop, height: btn.offsetHeight, duration: instant ? 0 : 0.7, ease: 'expo.out' });
    };
    var act = $('.tabs__btn.is-active');
    if (act) moveInd(act, true);
    window.addEventListener('resize', function () { var a = $('.tabs__btn.is-active'); if (a) moveInd(a, true); });
    $$('.tabs__btn').forEach(function (b) {
      b.addEventListener('click', function () {
        moveInd(b);
        var panel = $('.tabs__panel.is-active');
        if (panel) gsap.from($$('.tabs__inner > *', panel), { y: 26, opacity: 0, duration: 0.7, stagger: 0.05, ease: 'power3.out', overwrite: true });
      });
    });
  }

  /* =======================================================
     11. Відгуки: drag мишкою + skew від швидкості
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
      var k = clamp(-10, 10, vel * 0.5);
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
     12. Оновлення розмірів
     ======================================================= */
  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { ScrollTrigger.refresh(); });
})();
