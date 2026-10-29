/**
 * Portafolio ERP — Landing v5
 * Anime.js v4 (global `anime`)
 * Órbitas con satélites girando alrededor del icono central
 */
(function () {
  'use strict';

  const A = window.anime;
  const hasAnime = !!(A && A.animate);
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const FX = hasAnime && !reduce;
  const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  if (FX) document.documentElement.classList.add('has-anime');

  const animate = FX ? A.animate : null;
  const stagger = FX ? A.stagger : null;

  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
  const clamp = (n, a, b) => Math.min(b, Math.max(a, n));

  function onView(el, cb, threshold) {
    if (!el) return;
    if (!('IntersectionObserver' in window)) { cb(el); return; }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) { io.disconnect(); cb(e.target); }
      });
    }, { threshold: threshold == null ? 0.2 : threshold });
    io.observe(el);
  }

  /* =========================================
     TEMA
     ========================================= */
  const themeToggle = $('#themeToggle');
  const html = document.documentElement;

  function applyTheme(theme) {
    html.setAttribute('data-theme', theme);
    try { localStorage.setItem('theme', theme); } catch (e) {}
    const meta = $('meta[name="theme-color"]');
    if (meta) meta.content = theme === 'dark' ? '#0b1120' : '#4f46e5';
  }

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
      themeToggle.style.transform = 'scale(0.85) rotate(180deg)';
      setTimeout(() => { themeToggle.style.transform = ''; }, 260);
    });
  }

  /* =========================================
     HEADER, PROGRESO, SCROLL TOP, MENÚ MÓVIL
     ========================================= */
  const header = $('#header');
  const scrollTopBtn = $('#scrollTopBtn');
  const progressBar = $('#progressBar');
  const mobileToggle = $('#mobileToggle');
  const nav = $('#nav');
  const navLinks = $$('.nav-link');

  function closeMenu() {
    if (!nav || !mobileToggle) return;
    nav.classList.remove('active');
    mobileToggle.classList.remove('active');
    mobileToggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
  }

  if (mobileToggle && nav) {
    mobileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = nav.classList.toggle('active');
      mobileToggle.classList.toggle('active', isOpen);
      mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      document.body.classList.toggle('menu-open', isOpen);
    });
    document.addEventListener('click', (e) => {
      if (nav.classList.contains('active') && !nav.contains(e.target) && !mobileToggle.contains(e.target)) closeMenu();
    });
    nav.querySelectorAll('a').forEach((a) => a.addEventListener('click', closeMenu));
  }

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  function updateActiveNav() {
    if (!navLinks.length) return;
    const sections = navLinks
      .map((l) => l.getAttribute('href'))
      .filter((h) => h && h.startsWith('#') && h.length > 1)
      .map((id) => document.querySelector(id))
      .filter(Boolean);
    if (!sections.length) return;

    const headerOffset = (header ? header.offsetHeight : 0) + 60;
    const viewportCenter = window.innerHeight / 2;
    let activeId = sections[0].id;
    let minDistance = Infinity;

    sections.forEach((sec) => {
      const rect = sec.getBoundingClientRect();
      const center = rect.top + rect.height / 2 + headerOffset * 0.4;
      const dist = Math.abs(center - viewportCenter);
      const isVisible = rect.bottom > headerOffset && rect.top < window.innerHeight;
      if (isVisible && dist < minDistance) { minDistance = dist; activeId = sec.id; }
    });

    if (window.scrollY < 100) activeId = sections[0].id;
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 80) activeId = sections[sections.length - 1].id;

    navLinks.forEach((link) => link.classList.toggle('active', link.getAttribute('href') === '#' + activeId));
  }

  function handleScroll() {
    const y = window.scrollY;
    if (header) header.classList.toggle('scrolled', y > 30);
    if (scrollTopBtn) scrollTopBtn.classList.toggle('visible', y > 400);
    if (progressBar) {
      const max = document.documentElement.scrollHeight - window.innerHeight;
      progressBar.style.transform = 'scaleX(' + (max > 0 ? clamp(y / max, 0, 1) : 0) + ')';
    }
    updateActiveNav();
  }

  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#' || href.length < 2) return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      const offset = (header ? header.offsetHeight : 0) + 16;
      const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
      window.scrollTo({ top, behavior: 'smooth' });
      closeMenu();
    });
  });

  let ticking = false;
  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(() => { handleScroll(); ticking = false; });
      ticking = true;
    }
  }, { passive: true });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 1023) closeMenu();
    handleScroll();
  });

  /* =========================================
     REVEAL
     ========================================= */
  function initReveal() {
    const els = $$('[data-reveal]');
    if (!FX || !els.length) return;

    const variants = {
      up: { translateY: [44, 0] },
      left: { translateX: [-48, 0] },
      right: { translateX: [48, 0] },
      scale: { scale: [0.9, 1], translateY: [24, 0] },
      fade: {}
    };

    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        io.unobserve(e.target);
        const el = e.target;
        const v = variants[el.dataset.reveal] || variants.up;
        animate(el, Object.assign({
          opacity: [0, 1],
          duration: 1000,
          delay: parseInt(el.dataset.delay || '0', 10),
          ease: 'outExpo'
        }, v));
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

    els.forEach((el) => io.observe(el));
  }

  /* =========================================
     HERO: BLOBS
     ========================================= */
  function initHeroBlobs() {
    if (!FX) return;
    $$('.blob').forEach((b, i) => {
      animate(b, {
        translateX: [0, 70 * (i % 2 ? -1 : 1), -40 * (i % 2 ? -1 : 1), 0],
        translateY: [0, -50, 40, 0],
        scale: [1, 1.12, 0.94, 1],
        duration: 14000 + i * 3500,
        loop: true,
        ease: 'inOutSine'
      });
    });
    $$('.cta-orb').forEach((b, i) => {
      animate(b, {
        translateX: [0, 60, -30, 0],
        translateY: [0, 40, -40, 0],
        duration: 16000 + i * 3000,
        loop: true,
        ease: 'inOutSine'
      });
    });
  }

  /* =========================================
     ÓRBITAS
     Los satélites giran alrededor del centro.
     Cada satélite tiene un radio y una fase distinta.
     ========================================= */
  function initOrbit(orbitEl) {
    if (!orbitEl || !FX) return;

    const sats = $$('[data-orbit-sat], [data-orbit-satellite]', orbitEl);
    if (!sats.length) return;

    const rect = orbitEl.getBoundingClientRect();
    const size = Math.min(rect.width, rect.height) || 380;
    const center = size / 2;

    // Radios en % del tamaño total
    const radiusOuter = size * 0.40;
    const radiusInner = size * 0.28;

    sats.forEach((sat, i) => {
      const total = sats.length;
      const isOuter = i % 2 === 0;
      const radius = isOuter ? radiusOuter : radiusInner;
      const phase = (Math.PI * 2 / total) * i;
      const speed = isOuter ? 22000 : 16000;

      // Estado inicial
      sat.style.left = '50%';
      sat.style.top = '50%';
      sat.style.transform = 'translate(-50%, -50%)';

      // Animar rotación alrededor del centro con una timeline loop
      // Usamos translateX/Y para orbitar de forma manual
      const startX = Math.cos(phase) * radius;
      const startY = Math.sin(phase) * radius;

      sat.style.transform = `translate(calc(-50% + ${startX}px), calc(-50% + ${startY}px))`;

      // Anime.js: rotación continua 0 -> 360 -> 0 con easing lineal y loop
      // Pero como anime no anima ángulos directamente, usamos keyframes de posición
      const steps = 60;
      const keyframes = [];
      for (let k = 0; k <= steps; k++) {
        const angle = phase + (k / steps) * Math.PI * 2;
        const x = Math.cos(angle) * radius;
        const y = Math.sin(angle) * radius;
        keyframes.push({ translateX: x, translateY: y });
      }

      animate(sat, {
        translateX: keyframes.map((kf) => kf.translateX),
        translateY: keyframes.map((kf) => kf.translateY),
        duration: speed,
        loop: true,
        ease: 'linear',
        // Mantener el offset -50% con un wrapper: aplicamos translateX/Y sobre el transform ya existente
      });
    });
  }

  /* =========================================
     ÓRBITA HERO (sección sistema)
     ========================================= */
  function initHeroOrbit() {
    const orbit = $('.teaser-orbit-visual');
    if (!orbit) return;
    onView(orbit, () => initOrbit(orbit), 0.25);
  }

  /* =========================================
     ÓRBITAS DE MÓDULOS
     ========================================= */
  function initModuleOrbits() {
    $$('[data-module-orbit]').forEach((orbit) => {
      onView(orbit, () => initOrbit(orbit), 0.25);
    });
  }

  /* =========================================
     STATS: CONTADORES
     ========================================= */
  function initStatsCounters() {
    const stats = $$('.stat-number[data-count]');
    if (!stats.length) return;
    stats.forEach((el) => {
      const target = parseInt(el.dataset.count, 10) || 0;
      if (!FX) { el.textContent = target.toLocaleString('es-CO'); return; }
      el.textContent = '0';
      onView(el, () => {
        const o = { v: 0 };
        animate(o, {
          v: target, duration: 2000, ease: 'outQuart',
          onUpdate: () => { el.textContent = Math.round(o.v).toLocaleString('es-CO'); }
        });
      }, 0.5);
    });
  }

  /* =========================================
     INTERACCIONES: magnéticos
     ========================================= */
  function initInteractions() {
    if (!finePointer || reduce) return;
    $$('.magnetic').forEach((b) => {
      b.addEventListener('pointermove', (e) => {
        const r = b.getBoundingClientRect();
        const x = e.clientX - (r.left + r.width / 2);
        const y = e.clientY - (r.top + r.height / 2);
        b.style.translate = (x * 0.22) + 'px ' + (y * 0.35) + 'px';
      });
      b.addEventListener('pointerleave', () => { b.style.translate = ''; });
    });
  }

  /* =========================================
     INIT
     ========================================= */
  let booted = false;
  function boot() {
    if (booted) return;
    booted = true;
    handleScroll();
    initReveal();
    initHeroBlobs();
    initHeroOrbit();
    initModuleOrbits();
    initStatsCounters();
    initInteractions();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();