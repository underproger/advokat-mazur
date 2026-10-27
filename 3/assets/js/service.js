/* Адвокат Мазур — сторінка послуги */
(function () {
  'use strict';
  var doc = document.documentElement;
  doc.classList.add('js');
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var isDesktop = function () { return window.matchMedia('(min-width:1101px)').matches; };
  var reduce = window.matchMedia('(prefers-reduced-motion:reduce)').matches;

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

  /* ---------- modal ---------- */
  var modal = $('[data-modal]'), topicSelect = $('[data-topic-select]');
  var openModal = function (topic) {
    modal.classList.remove('is-sent');
    if (topic && topicSelect) {
      var opt = $$('option', topicSelect).filter(function (o) { return o.textContent === topic; })[0];
      if (opt) topicSelect.value = opt.value;
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
    var paused = false;
    var step = function (d) {
      var card = $('.review', track); var w = card ? card.getBoundingClientRect().width + 20 : 440;
      if (d > 0 && track.scrollLeft + track.clientWidth >= track.scrollWidth - 8) track.scrollTo({ left: 0, behavior: 'smooth' });
      else if (d < 0 && track.scrollLeft <= 4) track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });
      else track.scrollBy({ left: d * w, behavior: 'smooth' });
    };
    $('[data-slider-prev]').addEventListener('click', function () { step(-1); });
    $('[data-slider-next]').addEventListener('click', function () { step(1); });
    track.addEventListener('mouseenter', function () { paused = true; });
    track.addEventListener('mouseleave', function () { paused = false; });
    track.addEventListener('touchstart', function () { paused = true; }, { passive: true });
    if (!reduce) setInterval(function () { if (!paused && !document.hidden) step(1); }, 5500);
  }

  /* ---------- situation cards: spotlight ---------- */
  $$('.sit').forEach(function (c) {
    c.addEventListener('mousemove', function (e) {
      var r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });

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
  var stepsEl = $('[data-steps]'), parallax = $$('[data-parallax] img'), mbar = $('.mbar'), hero = $('.hero'), contact = $('#contact');
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
      var heroEnd = hero.getBoundingClientRect().bottom < 0;
      var c = contact.getBoundingClientRect(), inContact = c.top < vh && c.bottom > 0;
      mbar.classList.toggle('is-visible', heroEnd && !inContact);
    }
    updToc(); updSeo();
  };
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
  window.addEventListener('resize', onScroll);
  // hero image: drop the intro zoom animation once done so parallax can take over
  parallax.forEach(function (img) { img.addEventListener('animationend', function () { img.style.animation = 'none'; onScroll(); }); });
  onScroll();
})();
