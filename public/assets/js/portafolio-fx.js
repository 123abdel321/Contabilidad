/* =====================================================================
   PORTAFOLIO ERP · FX global con anime.js v4
   Cargar DESPUÉS de anime.iife.min.js y de sistema.js
   Anima TODAS las tablas DataTables, modales, cards y toasts del sistema.
   ===================================================================== */
(function () {
  'use strict';

  var A = window.anime;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!A || !A.animate || reduce) return;

  var animate = A.animate, stagger = A.stagger;

  /* 1) Todas las DataTables: filas entran en cascada en cada draw */
  if (window.jQuery) {
    window.jQuery(document).on('draw.dt', function (e, settings) {
      var rows = settings.nTable.querySelectorAll('tbody tr');
      if (!rows.length) return;
      // Solo opacidad: no rompe las columnas fijas (fixedColumns)
      animate(rows, {
        opacity: [0, 1],
        duration: 450,
        delay: stagger(25, { start: 40 }),
        ease: 'outQuad'
      });
    });
  }

  /* 2) Modales: los campos entran en cascada.
     Bootstrap 5 se carga antes que jQuery, por eso usamos listener NATIVO,
     $('#modal').on('shown.bs.modal') no se dispararía. */
  document.addEventListener('shown.bs.modal', function (e) {
    var modal = e.target;
    var items = modal.querySelectorAll('.modal-body .form-group, .modal-body .form-check, .modal-body .row > div');
    if (!items.length) return;
    animate(items, {
      opacity: [0, 1],
      translateY: [14, 0],
      duration: 500,
      delay: stagger(28, { start: 80 }),
      ease: 'outExpo'
    });
  });

  /* 3) Cards de las vistas: aparecen al cargar cada pestaña/vista */
  function revealCards(root) {
    var cards = (root || document).querySelectorAll('.card:not([data-pf-done])');
    if (!cards.length) return;
    cards.forEach(function (c) { c.setAttribute('data-pf-done', '1'); });
    animate(cards, {
      opacity: [0, 1],
      translateY: [18, 0],
      duration: 650,
      delay: stagger(90),
      ease: 'outExpo'
    });
  }

  // Tu sistema inyecta vistas por AJAX en #contenerdores-views: observamos cambios.
  var host = document.getElementById('contenerdores-views');
  if (host && 'MutationObserver' in window) {
    var t = null;
    new MutationObserver(function () {
      clearTimeout(t);
      t = setTimeout(function () { revealCards(host); }, 60);
    }).observe(host, { childList: true, subtree: true });
  }

  /* 4) Toasts: pequeño rebote al aparecer */
  var toastBox = document.getElementById('contenedor-toast');
  if (toastBox && 'MutationObserver' in window) {
    new MutationObserver(function (muts) {
      muts.forEach(function (m) {
        m.addedNodes.forEach(function (n) {
          if (n.nodeType === 1 && n.classList.contains('toast')) {
            animate(n, { scale: [0.92, 1], duration: 500, ease: 'outBack' });
          }
        });
      });
    }).observe(toastBox, { childList: true });
  }

  /* 5) Contadores: cualquier elemento con data-pf-count="1234" */
  window.pfCountUp = function (el, target, ms) {
    var o = { v: 0 };
    animate(o, {
      v: [0, target],
      duration: ms || 1400,
      ease: 'outExpo',
      onUpdate: function () { el.textContent = Math.round(o.v).toLocaleString('es-CO'); }
    });
  };
  document.querySelectorAll('[data-pf-count]').forEach(function (el) {
    window.pfCountUp(el, parseFloat(el.getAttribute('data-pf-count')) || 0);
  });
})();