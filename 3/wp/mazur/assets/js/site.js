/* Адвокат Мазур — заявки (Contact Form 7 REST API) та події аналітики (dataLayer → GTM → GA4).
 *
 * Події:
 *   generate_lead   — заявку успішно відправлено (form_location: modal | contact | home, form_topic)
 *   click_phone     — клік по номеру телефону (link_location)
 *   click_messenger — клік по Telegram / Viber / WhatsApp (messenger, link_location)
 * Після generate_lead — перехід на сторінку подяки (/thanks/, RU — /ru/spasibo/).
 */
(function () {
  'use strict';
  var C = window.MZ || {};
  var T = C.i18n || {};
  window.dataLayer = window.dataLayer || [];

  /* ---------- dataLayer ---------- */
  function push(data, cb) {
    var done = false;
    var fin = function () { if (!done) { done = true; if (cb) cb(); } };
    if (cb) {
      data.eventCallback = fin;
      data.eventTimeout = 1500;
      setTimeout(fin, 1700); // якщо GTM заблоковано — все одно продовжуємо
    }
    window.dataLayer.push(data);
  }

  function where(el) {
    var t = el.closest('[data-track]');
    if (t) return t.getAttribute('data-track');
    if (el.closest('.hdr')) return 'header';
    if (el.closest('.drawer')) return 'menu';
    if (el.closest('.ftr')) return 'footer';
    if (el.closest('.mbar')) return 'mobile_bar';
    var s = el.closest('section[id]');
    return s ? s.id : (C.page || 'page');
  }

  function messenger(href) {
    if (/^(tg:|https?:\/\/(t\.me|telegram\.me|telegram\.dog)\/)/i.test(href)) return 'telegram';
    if (/^viber:/i.test(href)) return 'viber';
    if (/^(whatsapp:|https?:\/\/(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)\/)/i.test(href)) return 'whatsapp';
    return '';
  }

  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href') || '';
    if (/^tel:/i.test(href)) {
      push({ event: 'click_phone', link_location: where(a), phone_number: href.replace(/^tel:/i, ''), page_type: C.page });
      return;
    }
    var m = messenger(href);
    if (m) push({ event: 'click_messenger', messenger: m, link_location: where(a), page_type: C.page });
  }, true);

  /* ---------- UTM / gclid першого заходу (додаються до листа із заявкою) ---------- */
  var UTM_KEY = 'mz-utm';
  try {
    var q = new URLSearchParams(location.search), keep = [];
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'].forEach(function (k) {
      if (q.get(k)) keep.push(k + '=' + q.get(k));
    });
    if (keep.length) sessionStorage.setItem(UTM_KEY, keep.join('&'));
  } catch (err) { /* sessionStorage недоступний */ }
  function utm() { try { return sessionStorage.getItem(UTM_KEY) || ''; } catch (err) { return ''; } }

  /* ---------- форми ---------- */
  function showError(form, text) {
    var box = form.querySelector('.form__error');
    if (!box) {
      box = document.createElement('p');
      box.className = 'form__error';
      box.setAttribute('role', 'alert');
      form.appendChild(box);
    }
    box.textContent = text;
  }

  function done(form) {
    var wrap = form.closest('[data-form-wrap]');
    if (wrap) wrap.classList.add('is-sent');
    else { var modal = document.querySelector('[data-modal]'); if (modal) modal.classList.add('is-sent'); }
  }

  Array.prototype.forEach.call(document.querySelectorAll('form[data-form]'), function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (form.classList.contains('is-sending')) return;
      var name = form.querySelector('[name="name"]'), phone = form.querySelector('[name="phone"]');
      if (name && !name.value.trim()) { showError(form, T.name); name.focus(); return; }
      if (phone && phone.value.replace(/\D/g, '').length < 9) { showError(form, T.phone); phone.focus(); return; }

      var loc = form.getAttribute('data-form-location') || 'form';
      var topicEl = form.querySelector('[name="topic"]');
      var topic = (topicEl && topicEl.value) || C.service || T.topic || '';
      var msg = form.querySelector('[name="message"]');

      if (!C.endpoint) { showError(form, T.error); return; }

      var fd = new FormData();
      fd.append('your-name', name ? name.value.trim() : '');
      fd.append('your-phone', phone ? phone.value.trim() : '');
      fd.append('your-topic', topic);
      fd.append('your-message', msg ? msg.value.trim() : '');
      fd.append('form-location', loc);
      fd.append('page-url', location.href.split('#')[0]);
      fd.append('page-title', document.title);
      fd.append('lang', C.lang || 'uk');
      fd.append('utm', utm());
      fd.append('_wpcf7', C.formId);
      fd.append('_wpcf7_unit_tag', C.unitTag);
      fd.append('_wpcf7_container_post', C.postId || 0);
      fd.append('_wpcf7_locale', C.lang === 'ru' ? 'ru_RU' : 'uk');

      var btn = form.querySelector('[type="submit"]'), label = btn ? btn.innerHTML : '';
      form.classList.add('is-sending');
      if (btn) { btn.disabled = true; btn.textContent = T.sending; }
      var err = form.querySelector('.form__error'); if (err) err.textContent = '';

      var headers = {};
      if (C.nonce) headers['X-WP-Nonce'] = C.nonce;

      fetch(C.endpoint, { method: 'POST', body: fd, headers: headers, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.status === 'mail_sent') {
            push({ event: 'generate_lead', form_location: loc, form_topic: topic, page_type: C.page }, function () {
              if (C.thanks) location.href = C.thanks;
              else done(form);
            });
            return;
          }
          throw new Error(res && res.message ? res.message : 'cf7');
        })
        .catch(function () {
          form.classList.remove('is-sending');
          if (btn) { btn.disabled = false; btn.innerHTML = label; }
          showError(form, T.error);
        });
    });
  });
})();
