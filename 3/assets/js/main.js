/* Адвокат Мазур — сторінка послуги */
(function () {
  'use strict';
  var doc = document.documentElement;
  doc.classList.add('js');
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var isDesktop = function () { return window.matchMedia('(min-width:1101px)').matches; };
  var RU = (doc.getAttribute('lang') || '').indexOf('ru') === 0;
  var L = RU
    ? { free: 'Бесплатная консультация', collapse: 'Свернуть', more: 'Читать полностью' }
    : { free: 'Безкоштовна консультація', collapse: 'Згорнути', more: 'Читати повністю' };
  var reduce = window.matchMedia('(prefers-reduced-motion:reduce)').matches && doc.getAttribute('data-motion') !== 'force';

  /* ---------- перемикач мов ---------- */
  $$('[data-lang]').forEach(function (box) {
    var btn = $('.lang__btn', box);
    var set = function (open) { box.classList.toggle('is-open', open); btn.setAttribute('aria-expanded', open); };
    btn.addEventListener('click', function (e) { e.stopPropagation(); set(!box.classList.contains('is-open')); });
    box.addEventListener('mouseleave', function () { set(false); });
    document.addEventListener('click', function (e) { if (!box.contains(e.target)) set(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
  });

  /* ---------- mega menu ---------- */
  var megaBtn = $('[data-mega-toggle]'), mega = $('[data-mega]'), hdr = $('.hdr');
  if (megaBtn && mega) {
    var setMega = function (open) { mega.classList.toggle('is-open', open); megaBtn.setAttribute('aria-expanded', open); };
    megaBtn.addEventListener('mouseenter', function () { setMega(true); });
    megaBtn.addEventListener('click', function () { setMega(!mega.classList.contains('is-open')); });
    hdr.addEventListener('mouseleave', function () { setMega(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMega(false); });
  }

  /* ---------- drawer ---------- */
  var drawer = $('[data-drawer]');
  var setDrawer = function (open) { drawer.classList.toggle('is-open', open); document.body.classList.toggle('is-locked', open); };
  $$('[data-burger]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(true); }); });
  $$('[data-drawer-close]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(false); }); });
  drawer.addEventListener('click', function (e) { if (e.target === drawer) setDrawer(false); });

  /* ---------- custom select ----------
     Нативний <select> лишається в DOM (працює з формою / CF7),
     поверх нього — кнопка + список. Синхронізація через подію change. */
  var openSelect = null;
  var closeSelect = function () {
    if (!openSelect) return;
    openSelect.field.classList.remove('is-open', 'is-up');
    openSelect.btn.setAttribute('aria-expanded', 'false');
    openSelect = null;
  };
  $$('select[data-select]').forEach(function (sel, n) {
    var field = sel.closest('.field') || sel.parentNode;
    var wrap = document.createElement('div'); wrap.className = 'cselect';
    sel.parentNode.insertBefore(wrap, sel); wrap.appendChild(sel);
    sel.tabIndex = -1;

    var listId = 'cselect-list-' + n;
    var btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'cselect__btn';
    btn.setAttribute('aria-haspopup', 'listbox'); btn.setAttribute('aria-expanded', 'false'); btn.setAttribute('aria-controls', listId);
    if (sel.getAttribute('aria-label')) btn.setAttribute('aria-label', sel.getAttribute('aria-label'));
    btn.innerHTML = '<span class="cselect__value"></span><i class="ic ic-chevron-down"></i>';
    wrap.appendChild(btn);

    var list = document.createElement('div');
    list.className = 'cselect__list'; list.id = listId; list.setAttribute('role', 'listbox');
    var opts = $$('option', sel).map(function (o, i) {
      var el = document.createElement('div');
      el.className = 'cselect__opt'; el.setAttribute('role', 'option'); el.dataset.i = i;
      el.innerHTML = '<span></span><i class="ic ic-gold ic-check"></i>';
      el.firstChild.textContent = o.textContent;
      list.appendChild(el);
      return el;
    });
    field.appendChild(list);

    var active = 0;
    var setActive = function (i) {
      active = (i + opts.length) % opts.length;
      opts.forEach(function (o, j) { o.classList.toggle('is-active', j === active); });
      var o = opts[active], top = o.offsetTop, bottom = top + o.offsetHeight;
      if (top < list.scrollTop) list.scrollTop = top - 8;
      else if (bottom > list.scrollTop + list.clientHeight) list.scrollTop = bottom - list.clientHeight + 8;
    };
    var sync = function () {
      $('.cselect__value', btn).textContent = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].textContent : '';
      opts.forEach(function (o, j) { o.setAttribute('aria-selected', j === sel.selectedIndex); });
    };
    var choose = function (i) {
      if (sel.selectedIndex !== i) { sel.selectedIndex = i; sel.dispatchEvent(new Event('change', { bubbles: true })); }
      sync(); closeSelect(); btn.focus();
    };
    var open = function () {
      if (openSelect && openSelect.field !== field) closeSelect();
      // відкриваємо вгору, якщо знизу не вистачає місця
      var r = field.getBoundingClientRect();
      field.classList.toggle('is-up', window.innerHeight - r.bottom < 320 && r.top > window.innerHeight - r.bottom);
      field.classList.add('is-open'); btn.setAttribute('aria-expanded', 'true');
      openSelect = { field: field, btn: btn };
      setActive(Math.max(sel.selectedIndex, 0));
    };

    field.addEventListener('click', function (e) {
      if (e.target.closest('.cselect__list')) return;
      field.classList.contains('is-open') ? closeSelect() : open();
    });
    list.addEventListener('click', function (e) {
      var o = e.target.closest('.cselect__opt'); if (o) choose(+o.dataset.i);
    });
    list.addEventListener('mousemove', function (e) {
      var o = e.target.closest('.cselect__opt'); if (o && +o.dataset.i !== active) setActive(+o.dataset.i);
    });
    btn.addEventListener('keydown', function (e) {
      var isOpen = field.classList.contains('is-open');
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        if (!isOpen) open(); else setActive(active + (e.key === 'ArrowDown' ? 1 : -1));
      } else if (e.key === 'Home' && isOpen) { e.preventDefault(); setActive(0); }
      else if (e.key === 'End' && isOpen) { e.preventDefault(); setActive(opts.length - 1); }
      else if ((e.key === 'Enter' || e.key === ' ') && isOpen) { e.preventDefault(); choose(active); }
      else if (e.key === 'Escape' && isOpen) { e.preventDefault(); e.stopPropagation(); closeSelect(); }
      else if (e.key === 'Tab') closeSelect();
    });
    sel.addEventListener('change', sync);
    sync();
  });
  document.addEventListener('click', function (e) {
    if (openSelect && !openSelect.field.contains(e.target)) closeSelect();
  });

  /* ---------- modal ---------- */
  var modal = $('[data-modal]'), topicSelect = $('[data-topic-select]');
  var openModal = function (topic) {
    modal.classList.remove('is-sent');
    closeSelect();
    if (topic && topicSelect) {
      var opts = $$('option', topicSelect);
      var find = function (t) { return opts.filter(function (o) { return o.textContent === t; })[0]; };
      // тема з кнопки → якщо такої немає у списку, «Безкоштовна консультація»
      var opt = find(topic) || find(L.free);
      if (opt) { topicSelect.value = opt.value; topicSelect.dispatchEvent(new Event('change', { bubbles: true })); }
    }
    modal.classList.add('is-open');
    document.body.classList.add('is-locked');
    setTimeout(function () { var i = $('input[name="name"]', modal); if (i && isDesktop()) i.focus(); }, 300);
  };
  var closeModal = function () { modal.classList.remove('is-open'); document.body.classList.remove('is-locked'); };
  $$('[data-modal-open]').forEach(function (b) {
    b.addEventListener('click', function () { openModal(b.getAttribute('data-topic')); });
  });
  $$('[data-modal-close]').forEach(function (b) { b.addEventListener('click', closeModal); });
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeModal(); setDrawer(false); } });

  /* ---------- forms (демо) ---------- */
  $$('[data-form]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      var wrap = f.closest('[data-form-wrap]');
      if (wrap) wrap.classList.add('is-sent'); else modal.classList.add('is-sent');
    });
  });

  /* ---------- tabs / accordion (mobile) ---------- */
  var tabs = $('[data-tabs]');
  if (tabs) {
    var btns = $$('.tabs__btn', tabs), panels = $$('.tabs__panel', tabs);
    btns.forEach(function (b, i) {
      b.addEventListener('click', function () {
        btns.forEach(function (x) { x.classList.toggle('is-active', x === b); x.setAttribute('aria-selected', x === b); });
        panels.forEach(function (p, j) { p.classList.toggle('is-active', j === i); });
      });
    });
    panels.forEach(function (p) {
      $('.tabs__acc', p).addEventListener('click', function () { p.classList.toggle('is-open'); });
    });
  }

  /* ---------- «Читати повністю» (SEO-блоки головної) ---------- */
  $$('[data-more]').forEach(function (m) {
    var b = $('[data-more-toggle]', m);
    if (!b) return;
    b.addEventListener('click', function () {
      var open = m.classList.toggle('is-open');
      b.setAttribute('aria-expanded', open);
      $('span', b).textContent = open ? L.collapse : L.more;
    });
  });

  /* ---------- кейси: фільтр за напрямом ---------- */
  var casesBox = $('[data-cases]');
  if (casesBox) {
    var chips = $$('.chip', casesBox), cards = $$('.case-card', casesBox);
    chips.forEach(function (ch) {
      ch.addEventListener('click', function () {
        var f = ch.getAttribute('data-filter');
        chips.forEach(function (x) { x.classList.toggle('is-active', x === ch); });
        cards.forEach(function (c) { c.classList.toggle('is-hidden', f !== 'all' && c.getAttribute('data-cat') !== f); });
      });
    });
  }

  /* ---------- faq ---------- */
  $$('.faq__item').forEach(function (it) {
    var q = $('.faq__q', it);
    q.addEventListener('click', function () {
      var open = !it.classList.contains('is-open');
      $$('.faq__item.is-open').forEach(function (o) { o.classList.remove('is-open'); $('.faq__q', o).setAttribute('aria-expanded', 'false'); });
      it.classList.toggle('is-open', open);
      q.setAttribute('aria-expanded', open);
    });
  });

  /* ---------- reviews slider ---------- */
  var track = $('[data-slider]');
  if (track) {
    var AUTOPLAY = 5000;            // інтервал автопрокрутки, мс
    var hovered = false, touchedAt = 0, inView = false, lastManual = 0;
    var behavior = reduce ? 'auto' : 'smooth';
    var step = function (d) {
      var card = $('.review', track); var w = card ? card.getBoundingClientRect().width + 20 : 440;
      if (d > 0 && track.scrollLeft + track.clientWidth >= track.scrollWidth - 8) track.scrollTo({ left: 0, behavior: behavior });
      else if (d < 0 && track.scrollLeft <= 4) track.scrollTo({ left: track.scrollWidth, behavior: behavior });
      else track.scrollBy({ left: d * w, behavior: behavior });
    };
    var manual = function (d) { lastManual = Date.now(); step(d); };
    $('[data-slider-prev]').addEventListener('click', function () { manual(-1); });
    $('[data-slider-next]').addEventListener('click', function () { manual(1); });
    // пауза лише поки курсор над слайдером
    track.addEventListener('mouseenter', function () { hovered = true; });
    track.addEventListener('mouseleave', function () { hovered = false; });
    // на тачі — пауза після свайпу, далі автопрокрутка продовжується
    track.addEventListener('touchstart', function () { touchedAt = Date.now(); }, { passive: true });
    // крутимо лише коли блок у полі зору
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (en) { inView = en[0].isIntersecting; }, { threshold: 0.3 }).observe(track);
    } else { inView = true; }
    setInterval(function () {
      var now = Date.now();
      if (hovered || document.hidden || !inView) return;
      if (now - touchedAt < AUTOPLAY * 2 || now - lastManual < AUTOPLAY) return;
      step(1);
    }, AUTOPLAY);
  }

  /* ---------- situation cards: spotlight ---------- */
  $$('.sit, .spot').forEach(function (c) {
    c.addEventListener('mousemove', function (e) {
      var r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });

  /* ---------- заголовки з довгими словами ----------
     Слово не переноситься → якщо не вміщається в ширину блоку,
     поступово зменшуємо шрифт (не менше 55% від CSS-розміру). */
  var fitEls = $$('h1, h2, h3');
  var fitTitles = function () {
    fitEls.forEach(function (el) {
      el.style.fontSize = '';
      if (!el.clientWidth || el.scrollWidth <= el.clientWidth + 1) return;
      var base = parseFloat(getComputedStyle(el).fontSize), size = base;
      while (el.scrollWidth > el.clientWidth + 1 && size > base * 0.55) {
        size *= 0.95;
        el.style.fontSize = size.toFixed(1) + 'px';
      }
    });
  };
  fitTitles();
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitTitles);
  var fitTimer;
  window.addEventListener('resize', function () { clearTimeout(fitTimer); fitTimer = setTimeout(fitTitles, 150); });
  $$('.tabs__btn, .tabs__acc').forEach(function (b) { b.addEventListener('click', function () { setTimeout(fitTitles, 0); }); });

  /* ---------- split headings into words ---------- */
  var splitNode = function (node, state) {
    Array.prototype.slice.call(node.childNodes).forEach(function (ch) {
      if (ch.nodeType === 3) {
        var frag = document.createDocumentFragment();
        ch.textContent.split(/(\s+)/).forEach(function (part) {
          if (!part) return;
          if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
          var w = document.createElement('span'); w.className = 'w';
          var inner = document.createElement('span'); inner.textContent = part;
          inner.style.setProperty('--d', (state.i++ * 0.06) + 's');
          w.appendChild(inner); frag.appendChild(w);
        });
        node.replaceChild(frag, ch);
      } else if (ch.nodeType === 1) {
        splitNode(ch, state);
      }
    });
  };
  $$('[data-split]').forEach(function (h) { splitNode(h, { i: 0 }); });

  /* ---------- reveal on scroll ---------- */
  $$('[data-reveal-group]').forEach(function (g) {
    Array.prototype.slice.call(g.children).forEach(function (c, i) { c.style.setProperty('--d', (i * 0.08) + 's'); });
  });
  var targets = $$('[data-reveal],[data-reveal-group],[data-split]');
  if ('IntersectionObserver' in window && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    targets.forEach(function (t) { io.observe(t); });
  } else {
    targets.forEach(function (t) { t.classList.add('is-in'); });
  }

  /* ---------- counters ---------- */
  var counters = $$('[data-count]');
  var runCounter = function (el) {
    var to = parseFloat(el.getAttribute('data-count')), t0 = null, dur = 1600;
    var tick = function (t) {
      if (!t0) t0 = t;
      var p = Math.min((t - t0) / dur, 1), e = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(to * e);
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };
  if ('IntersectionObserver' in window && !reduce) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { runCounter(en.target); cio.unobserve(en.target); } });
    }, { threshold: 0.6 });
    counters.forEach(function (c) { cio.observe(c); });
  } else {
    counters.forEach(function (c) { c.textContent = c.getAttribute('data-count'); });
  }

  /* ---------- toc: active section ---------- */
  var tocLinks = $$('.toc__link');
  var seoLinks = $$('.seo__toc a');
  var watch = function (links, offset) {
    var items = links.map(function (a) { return { a: a, el: $(a.getAttribute('href')) }; }).filter(function (x) { return x.el; });
    return function () {
      var y = window.scrollY + offset, cur = null;
      items.forEach(function (x) { if (x.el.getBoundingClientRect().top + window.scrollY <= y) cur = x; });
      items.forEach(function (x) { x.a.classList.toggle('is-active', x === cur); });
      if (cur && links === tocLinks) {
        var list = cur.a.parentNode;
        var l = cur.a.offsetLeft - 20;
        if (l < list.scrollLeft || l + cur.a.offsetWidth > list.scrollLeft + list.clientWidth) list.scrollTo({ left: l, behavior: 'smooth' });
      }
    };
  };
  var updToc = watch(tocLinks, 140), updSeo = watch(seoLinks, 200);

  /* ---------- scroll-driven: steps line, parallax, mobile bar ---------- */
  var stepsEl = $('[data-steps]'), parallax = $$('[data-parallax] img'), mbar = $('.mbar'), hero = $('.hero, .h-hero'), contact = $('#contact');
  var totop = $('[data-totop]');
  if (totop) totop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    totop.blur();
  });
  var ticking = false;
  var onScroll = function () {
    ticking = false;
    var vh = window.innerHeight;
    if (stepsEl) {
      var r = stepsEl.getBoundingClientRect();
      var p = Math.min(Math.max((vh * 0.85 - r.top) / (r.height + vh * 0.3), 0), 1);
      stepsEl.style.setProperty('--p', p.toFixed(3));
    }
    if (!reduce) parallax.forEach(function (img) {
      var b = img.parentNode.getBoundingClientRect();
      if (b.bottom < 0 || b.top > vh) return;
      var k = (b.top + b.height / 2 - vh / 2) / vh;
      img.style.transform = 'translate3d(0,' + (k * -60 - 30).toFixed(1) + 'px,0)';
    });
    if (mbar) {
      var heroEnd = hero ? hero.getBoundingClientRect().bottom < 0 : window.scrollY > vh;
      var c = contact ? contact.getBoundingClientRect() : null, inContact = !!c && c.top < vh && c.bottom > 0;
      mbar.classList.toggle('is-visible', heroEnd && !inContact);
      document.body.classList.toggle('has-mbar', heroEnd && !inContact);
    }
    if (totop) {
      var max = document.documentElement.scrollHeight - vh;
      totop.classList.toggle('is-visible', window.scrollY > vh * 0.8);
      totop.style.setProperty('--p', max > 0 ? Math.min(window.scrollY / max, 1).toFixed(4) : 0);
    }
    updToc(); updSeo();
  };
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
  window.addEventListener('resize', onScroll);
  // hero image: drop the intro zoom animation once done so parallax can take over
  parallax.forEach(function (img) { img.addEventListener('animationend', function () { img.style.animation = 'none'; onScroll(); }); });
  onScroll();
})();
