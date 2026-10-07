// GTech Digital website behaviour (Blade). Plain JavaScript ports of the React components:
// Header, ContactModal, ContactForm/InquiryForm, CookieBanner, BackToTop, SiteMotion and the page components
// (TocBar, Toc, CaseCarousel, Testimonials, StatsBar, ServiceStack, HowWeWork, Results, videos, sharing, newsletter).
(function () {
  'use strict';
  var d = document;
  var reduced = function () { return window.matchMedia('(prefers-reduced-motion: reduce)').matches; };

  // ---- Analytics: first-party page views, no cookies ---------------------------------------------------
  // The visit id lives only in this tab (sessionStorage). Staff browsers (anyone who opened the admin panel) are not counted.
  var sid = '';
  (function () {
    var staff = false;
    try { staff = localStorage.getItem('gt_staff') === '1'; } catch (e) {}
    try { sid = sessionStorage.getItem('gt_sid') || ''; } catch (e) {}
    if (!/^[a-f0-9]{32}$/.test(sid)) {
      var b = new Uint8Array(16); (window.crypto || window.msCrypto).getRandomValues(b);
      sid = Array.prototype.map.call(b, function (x) { return ('0' + x.toString(16)).slice(-2); }).join('');
      try { sessionStorage.setItem('gt_sid', sid); } catch (e) {}
    }
    if (staff || !window.fetch) return;
    var pv = 0, shown = Date.now(), total = 0;
    fetch('/api/t', { method: 'POST', headers: { 'Content-Type': 'application/json' }, keepalive: true,
      body: JSON.stringify({ sid: sid, p: location.pathname, r: document.referrer, q: location.search }) })
      .then(function (r) { return r.status === 200 ? r.json() : null; }).then(function (j) { if (j && j.pv) pv = j.pv; }).catch(function () {});
    var send = function () {
      if (!pv) return;
      var sec = Math.round((total + (shown ? Date.now() - shown : 0)) / 1000);
      var body = JSON.stringify({ sid: sid, pv: pv, sec: sec });
      if (navigator.sendBeacon) navigator.sendBeacon('/api/t', body); else fetch('/api/t', { method: 'POST', body: body, keepalive: true });
    };
    d.addEventListener('visibilitychange', function () {
      if (d.visibilityState === 'hidden') { if (shown) { total += Date.now() - shown; shown = 0; } send(); } else shown = Date.now();
    });
    window.addEventListener('pagehide', send);
  })();

  // ---- Header: hover menus on desktop (with a short close delay), tap to toggle on mobile -------------
  var nav = d.querySelector('.nav'), burger = d.querySelector('.burger'), timer = null;
  var desktop = function () { return window.matchMedia('(min-width: 901px)').matches; };
  var dds = Array.prototype.slice.call(d.querySelectorAll('.dd'));
  function setOpen(dd) {
    dds.forEach(function (x) { var on = x === dd; x.classList.toggle('open', on); x.querySelector('.dd-t').setAttribute('aria-expanded', on ? 'true' : 'false'); });
  }
  function closeAll() { if (timer) clearTimeout(timer); setOpen(null); if (nav) nav.classList.remove('show'); if (burger) burger.setAttribute('aria-expanded', 'false'); }
  dds.forEach(function (dd) {
    dd.addEventListener('mouseenter', function () { if (!desktop()) return; if (timer) clearTimeout(timer); setOpen(dd); });
    dd.addEventListener('mouseleave', function () { if (!desktop()) return; if (timer) clearTimeout(timer); timer = setTimeout(function () { setOpen(null); }, 220); });
    dd.querySelector('.dd-t').addEventListener('click', function (e) { e.preventDefault(); setOpen(dd.classList.contains('open') ? null : dd); });
  });
  d.querySelectorAll('.mega-tabs a[data-tab]').forEach(function (a) {
    var pick = function () {
      d.querySelectorAll('.mega-tabs a[data-tab]').forEach(function (x) { x.className = x === a ? 'on' : ''; });
      d.querySelectorAll('.mega-pane').forEach(function (p) { p.classList.toggle('on', p.getAttribute('data-pane') === a.getAttribute('data-tab')); });
    };
    a.addEventListener('mouseenter', pick); a.addEventListener('focus', pick);
  });
  if (burger) burger.addEventListener('click', function () { var on = !nav.classList.contains('show'); nav.classList.toggle('show', on); burger.setAttribute('aria-expanded', on ? 'true' : 'false'); });
  if (nav) nav.addEventListener('click', function (e) { var a = e.target.closest('a'); if (a && !a.classList.contains('dd-t')) closeAll(); });
  window.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(null); });

  // ---- Forms (contact / proposal and inquiry) ----------------------------------------------------------
  function bindForm(form) {
    if (form.__bound) return; form.__bound = true;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('.iq-submit'), alert = form.querySelector('.alert');
      var data = {}; new FormData(form).forEach(function (v, k) { data[k] = v; });
      if (form.getAttribute('data-form') === 'inquiry') data.source = 'inquiry';
      if (sid) data.sid = sid; // links the lead to this visit in Analytics
      btn.disabled = true; btn.textContent = 'Sending...';
      var ok = form.getAttribute('data-form') === 'inquiry' ? 'Thank you. Our team will contact you shortly.' : 'Thanks. We will send your proposal within 24 hours.';
      var show = function (good, text) { alert.hidden = false; alert.className = 'alert ' + (good ? 'ok' : 'err'); alert.textContent = text; };
      fetch('/api/contact', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(data) })
        .then(function (r) { return r.json(); })
        .then(function (res) { if (res.ok) { show(true, ok); form.reset(); } else show(false, res.error || 'Something went wrong.'); })
        .catch(function () { show(false, form.getAttribute('data-form') === 'inquiry' ? 'Network error. Please try again.' : 'Network error. Try again.'); })
        .then(function () { btn.disabled = false; btn.textContent = btn.getAttribute('data-label'); });
    });
  }
  d.querySelectorAll('form[data-form="contact"],form[data-form="inquiry"]').forEach(bindForm);
  // On the /contact page the service comes from ?service= in the URL.
  var q = new URLSearchParams(window.location.search).get('service');
  if (q) d.querySelectorAll('main form[data-form="contact"] select[name="service"]').forEach(function (s) {
    if (Array.prototype.some.call(s.options, function (o) { return o.value === q; })) s.value = q;
  });

  // ---- Contact popup: any link to /contact opens the form instead of navigating --------------------------
  var tpl = d.getElementById('cm-tpl'), modal = null, lastFocus = null, prevOverflow = '';
  function closeModal() {
    if (!modal) return; modal.remove(); modal = null; d.body.style.overflow = prevOverflow;
    window.removeEventListener('keydown', escModal); if (lastFocus) lastFocus.focus();
  }
  function escModal(e) { if (e.key === 'Escape') closeModal(); }
  function openModal(service, from) {
    if (modal) closeModal();
    lastFocus = from;
    modal = tpl.content.firstElementChild.cloneNode(true);
    d.body.insertBefore(modal, tpl);
    var sel = modal.querySelector('select[name="service"]');
    if (service && Array.prototype.some.call(sel.options, function (o) { return o.value === service; })) sel.value = service;
    bindForm(modal.querySelector('form'));
    modal.addEventListener('mousedown', function (e) { if (e.target === modal) closeModal(); });
    modal.querySelector('.cm-close').addEventListener('click', closeModal);
    window.addEventListener('keydown', escModal);
    prevOverflow = d.body.style.overflow; d.body.style.overflow = 'hidden';
    modal.querySelector('.cm-close').focus();
  }
  d.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a || a.hasAttribute('data-page') || a.target === '_blank') return;
    var url = new URL(a.href, window.location.href);
    if (url.origin !== window.location.origin || url.pathname !== '/contact') return;
    e.preventDefault();
    openModal(url.searchParams.get('service') || '', a);
  }, true);

  // ---- Cookie consent ----------------------------------------------------------------------------------
  var KEY = 'gtech_consent', ck = d.querySelector('.ck');
  function apply(c) {
    var g = function (v) { return v ? 'granted' : 'denied'; };
    if (window.gtag) window.gtag('consent', 'update', { analytics_storage: g(c.analytics), ad_storage: g(c.marketing), ad_user_data: g(c.marketing), ad_personalization: g(c.marketing) });
    window.dispatchEvent(new CustomEvent('gtech-consent', { detail: c }));
  }
  function setManage(on) {
    ck.querySelector('.ck-prefs').hidden = !on;
    var primary = ck.querySelector('.ck-primary');
    primary.setAttribute('data-ck-act', on ? 'save' : 'all');
    primary.textContent = on ? 'Save preferences' : 'Accept all';
    ck.querySelector('[data-ck-act="manage"]').hidden = on;
  }
  function save(a, m) {
    var c = { analytics: a, marketing: m, date: new Date().toISOString() };
    try { localStorage.setItem(KEY, JSON.stringify(c)); } catch (err) { /* storage blocked */ }
    d.cookie = KEY + '=' + (a ? 1 : 0) + (m ? 1 : 0) + '; max-age=' + 60 * 60 * 24 * 365 + '; path=/; SameSite=Lax';
    ck.querySelector('[data-ck="analytics"]').checked = a; ck.querySelector('[data-ck="marketing"]').checked = m;
    apply(c); ck.hidden = true; setManage(false);
  }
  if (ck) {
    var saved = null;
    try { saved = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (err) { /* storage blocked */ }
    if (saved) { ck.querySelector('[data-ck="analytics"]').checked = !!saved.analytics; ck.querySelector('[data-ck="marketing"]').checked = !!saved.marketing; apply(saved); }
    else ck.hidden = false;
    ck.addEventListener('click', function (e) {
      var act = e.target.getAttribute && e.target.getAttribute('data-ck-act');
      if (act === 'all') save(true, true);
      else if (act === 'none') save(false, false);
      else if (act === 'manage') setManage(true);
      else if (act === 'save') save(ck.querySelector('[data-ck="analytics"]').checked, ck.querySelector('[data-ck="marketing"]').checked);
    });
    d.querySelectorAll('[data-cookie-settings]').forEach(function (b) { b.addEventListener('click', function () { setManage(true); ck.hidden = false; }); });
  }

  // ---- Back to top -------------------------------------------------------------------------------------
  var top = d.querySelector('.to-top');
  if (top) {
    var onTop = function () { var on = window.scrollY > 500; top.classList.toggle('on', on); top.tabIndex = on ? 0 : -1; };
    onTop(); window.addEventListener('scroll', onTop, { passive: true });
    top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduced() ? 'auto' : 'smooth' }); });
  }

  // ---- Scroll motion: below-the-fold headings and cards fade and rise in; progress bar -------------------
  var ITEMS = '.card,.rcard,.sz-card,.sz-in,.sh-why-card,.ih-card,.bl-card,.how-step,.sp-card,.sp-step,.sp-split,.sp-media,.pstrip-tile,.cs-metric,.who-body,.who-video,.iq-copy,.iq-form,.sp-faq details,.contact-aside,.ft-grid>div';
  var HEADS = 'main section:not(.hero-video):not(.sp-hero):not(.cs-hero):not(.page-hd) h2';
  if (!reduced() && 'IntersectionObserver' in window) {
    var vh = window.innerHeight, seen = new Map();
    var io = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('rv-in'); io.unobserve(e.target); } }); }, { threshold: 0.1, rootMargin: '0px 0px -6% 0px' });
    d.querySelectorAll(HEADS + ',' + ITEMS).forEach(function (el) {
      if (el.closest('.sstack,.case-track,.testi-stage,.brand-track') || el.getBoundingClientRect().top < vh * 0.92) return;
      var i = seen.get(el.parentElement) || 0; seen.set(el.parentElement, i + 1);
      el.setAttribute('data-rv', ''); el.style.setProperty('--rv-d', Math.min(i, 5) * 80 + 'ms'); io.observe(el);
    });
    var bar = d.getElementById('scroll-progress'), raf = 0;
    var onScroll = function () { cancelAnimationFrame(raf); raf = requestAnimationFrame(function () { var h = d.documentElement.scrollHeight - window.innerHeight; if (bar) bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, window.scrollY / h) : 0) + ')'; }); };
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  }
  // ---- Page components -----------------------------------------------------------------------------------
  var each = function (sel, fn) { Array.prototype.forEach.call(d.querySelectorAll(sel), fn); };
  var inView = function (el, threshold, fn) {
    if (!('IntersectionObserver' in window)) return fn();
    var o = new IntersectionObserver(function (es) { if (es[0].isIntersecting) { o.disconnect(); fn(); } }, { threshold: threshold });
    o.observe(el);
  };
  var swapIcon = function (btn) { var t = btn.querySelector('template'), svg = btn.querySelector('svg'); if (!t || !svg) return; var now = svg.outerHTML; svg.outerHTML = t.innerHTML; t.innerHTML = now; };

  // TocBar: sticky in-page tabs with scroll-spy; the active tab is kept centred inside the bar.
  each('[data-toc]', function (bar) {
    var links = Array.prototype.slice.call(bar.querySelectorAll('a')), wrap = bar.querySelector('.wrap'), lock = 0, active = '', raf = 0;
    var ids = links.map(function (a) { return a.getAttribute('href').slice(1); });
    var set = function (id) {
      if (id === active) return; active = id;
      links.forEach(function (a, i) { var on = ids[i] === id; if (on) { a.className = 'on'; a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('class'); a.removeAttribute('aria-current'); } });
      var a = links[ids.indexOf(id)];
      if (wrap && a) wrap.scrollTo({ left: a.offsetLeft - (wrap.clientWidth - a.offsetWidth) / 2, behavior: 'smooth' });
    };
    var update = function () {
      raf = 0; if (Date.now() < lock) return;
      var line = 70 + (bar.offsetHeight || 56) + 40, cur = ids[0];
      ids.forEach(function (id) { var el = d.getElementById(id); if (el && el.getBoundingClientRect().top <= line) cur = id; });
      if (window.innerHeight + window.scrollY >= d.documentElement.scrollHeight - 4) cur = ids[ids.length - 1];
      set(cur);
    };
    var on = function () { if (!raf) raf = requestAnimationFrame(update); };
    links.forEach(function (a, i) { a.addEventListener('click', function () { set(ids[i]); lock = Date.now() + 900; }); });
    update();
    window.addEventListener('scroll', on, { passive: true }); window.addEventListener('resize', on);
  });

  // Toc (blog posts and legal pages): highlights the section currently in view.
  each('[data-spy]', function (nav) {
    var lis = Array.prototype.slice.call(nav.querySelectorAll('li'));
    var els = lis.map(function (li) { return d.getElementById(li.querySelector('a').getAttribute('href').slice(1)); });
    if (!('IntersectionObserver' in window)) return;
    var o = new IntersectionObserver(function (es) {
      var vis = es.filter(function (e) { return e.isIntersecting; }).sort(function (a, b) { return a.boundingClientRect.top - b.boundingClientRect.top; });
      if (vis[0]) lis.forEach(function (li, i) { li.className = els[i] === vis[0].target ? 'on' : ''; });
    }, { rootMargin: '-100px 0px -65% 0px' });
    els.forEach(function (el) { if (el) o.observe(el); });
  });

  // CaseCarousel: progress bar and previous/next under the cards, only when they overflow.
  each('[data-carousel]', function (wrap) {
    var track = wrap.querySelector('.case-track'), tpl = wrap.querySelector('template'), ctrl = null;
    var update = function () {
      var pages = Math.max(1, Math.round(track.scrollWidth / track.clientWidth));
      if (pages <= 1) { if (ctrl) { ctrl.remove(); ctrl = null; } return; }
      if (!ctrl) {
        ctrl = tpl.content.firstElementChild.cloneNode(true); wrap.appendChild(ctrl);
        var b = ctrl.querySelectorAll('.case-arrow');
        b[0].addEventListener('click', function () { track.scrollBy({ left: -track.clientWidth, behavior: 'smooth' }); });
        b[1].addEventListener('click', function () { track.scrollBy({ left: track.clientWidth, behavior: 'smooth' }); });
      }
      var end = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
      var page = end ? pages : Math.min(pages, Math.round(track.scrollLeft / track.clientWidth) + 1);
      var btns = ctrl.querySelectorAll('.case-arrow');
      btns[0].disabled = track.scrollLeft < 4; btns[1].disabled = end;
      ctrl.querySelector('.case-bar i').style.width = (page / pages) * 100 + '%';
    };
    update(); track.addEventListener('scroll', update, { passive: true }); window.addEventListener('resize', update);
  });

  // Testimonials: rotate every 9 seconds, pause while hovered; the dots pick one.
  each('[data-testi]', function (sec) {
    var quotes = sec.querySelectorAll('.testi-stage blockquote'), dots = sec.querySelectorAll('.testi-dots button'), i = 0, t = null;
    var show = function (n) {
      i = n;
      Array.prototype.forEach.call(quotes, function (q, k) { q.className = k === n ? 'on' : ''; q.setAttribute('aria-hidden', k === n ? 'false' : 'true'); });
      Array.prototype.forEach.call(dots, function (b, k) { b.className = k === n ? 'on' : ''; b.setAttribute('aria-selected', k === n ? 'true' : 'false'); });
    };
    var play = function () { if (t) clearInterval(t); t = setInterval(function () { show((i + 1) % quotes.length); }, 9000); };
    Array.prototype.forEach.call(dots, function (b, k) { b.addEventListener('click', function () { show(k); play(); }); });
    sec.addEventListener('mouseenter', function () { if (t) clearInterval(t); t = null; });
    sec.addEventListener('mouseleave', play);
    if (quotes.length > 1) play();
  });

  // StatsBar: the numbers count up (2.8 s, ease-out) once the bar is in view.
  each('[data-stats]', function (sec) {
    inView(sec, 0.4, function () {
      Array.prototype.forEach.call(sec.querySelectorAll('[data-count]'), function (el) {
        var to = parseFloat(el.getAttribute('data-count')), dec = +el.getAttribute('data-dec') || 0, suf = el.getAttribute('data-suffix') || '', t0 = performance.now();
        var tick = function (t) { var p = Math.min(1, (t - t0) / 2800); el.textContent = (to * (1 - Math.pow(1 - p, 3))).toFixed(dec) + suf; if (p < 1) requestAnimationFrame(tick); };
        requestAnimationFrame(tick);
      });
    });
  });

  // HowWeWork and Results: the illustrations and charts animate in once the section is seen.
  each('[data-inview]', function (sec) { inView(sec, parseFloat(sec.getAttribute('data-inview')) || 0.25, function () { sec.classList.add('in'); }); });

  // ServiceStack: cards pin and stack (CSS); falls back to normal flow when a card is taller than the screen.
  // Each animated image restarts from its first frame the first time its card is on screen.
  each('[data-stack]', function (stack) {
    var cards = Array.prototype.slice.call(stack.querySelectorAll('.svc-card'));
    var measure = function () { var tallest = Math.max.apply(null, cards.map(function (c) { return c.offsetHeight; })); stack.classList.toggle('flow', tallest + 120 > window.innerHeight); };
    measure(); window.addEventListener('resize', measure);
    if (!('IntersectionObserver' in window)) return;
    var o = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        e.target.classList.add('seen'); o.unobserve(e.target);
        var img = e.target.querySelector('.svc-media img'); if (img) img.replaceWith(img.cloneNode(true));
      });
    }, { threshold: 0.55 });
    cards.forEach(function (c) { o.observe(c); });
  });

  // Home hero background video: added after load so it never blocks first paint; fades in once playing.
  each('[data-hero-video]', function (box) {
    var add = function () {
      var id = '4YKT1KJbzuQ', f = d.createElement('iframe');
      f.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&mute=1&loop=1&playlist=' + id + '&controls=0&disablekb=1&fs=0&iv_load_policy=3&modestbranding=1&playsinline=1&rel=0';
      f.title = 'Background video'; f.tabIndex = -1; f.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
      f.addEventListener('load', function () { setTimeout(function () { f.className = 'on'; }, 1800); });
      box.appendChild(f);
    };
    if (d.readyState === 'complete') add(); else window.addEventListener('load', add);
  });

  // Intro video: play/pause button. The icon and label follow the intended state, as in the React component.
  each('[data-video-toggle]', function (btn) {
    var v = btn.parentElement.querySelector('video'), playing = true;
    btn.addEventListener('click', function () {
      if (!v) return;
      var was = playing;
      if (v.paused) { var r = v.play(); if (r && r.catch) r.catch(function () {}); playing = true; } else { v.pause(); playing = false; }
      if (playing !== was) swapIcon(btn);
      btn.setAttribute('aria-label', playing ? 'Pause video' : 'Play video');
    });
  });

  // Share: copy the post link (a tick shows for two seconds).
  each('[data-copy]', function (btn) {
    btn.addEventListener('click', function () {
      if (!navigator.clipboard) return;
      navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(function () {
        if (btn.__copied) return; btn.__copied = true; swapIcon(btn);
        setTimeout(function () { swapIcon(btn); btn.__copied = false; }, 2000);
      }).catch(function () {});
    });
  });

  // Newsletter sign-up.
  each('form[data-form="subscribe"]', function (form) {
    var msg = form.parentElement.querySelector('.bl-msg');
    var show = function (ok, text) { msg.hidden = false; msg.className = 'bl-msg ' + (ok ? 'bl-ok' : 'bl-err'); msg.textContent = text; };
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = {}; new FormData(form).forEach(function (v, k) { data[k] = v; });
      fetch('/api/subscribe', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(data) })
        .then(function (r) { return r.json(); })
        .then(function (res) { if (res.ok) { show(true, 'Thanks for subscribing.'); form.reset(); } else show(false, res.error || 'Something went wrong.'); })
        .catch(function () { show(false, 'Network error. Try again.'); });
    });
  });
})();
