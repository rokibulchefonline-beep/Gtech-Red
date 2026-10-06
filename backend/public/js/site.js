// GTech Digital website behaviour (Blade). Plain JavaScript ports of the React components:
// Header, ContactModal, ContactForm/InquiryForm, CookieBanner, BackToTop and SiteMotion.
(function () {
  'use strict';
  var d = document;
  var reduced = function () { return window.matchMedia('(prefers-reduced-motion: reduce)').matches; };

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
  d.querySelectorAll('form[data-form]').forEach(bindForm);
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
})();
