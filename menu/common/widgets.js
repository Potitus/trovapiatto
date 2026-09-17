/* trovapiatto menu widgets condivisi.
 * Modifica QUESTO file per aggiornare tutte le pagine menu in un colpo solo:
 * le pagine contengono solo <div id="tp-bariaround"></div> + questo script. */
(function () {
  'use strict';

  // --- Notizie BariAround (modifica qui: si aggiornano su tutti i menu) ---
  var BARIAROUND_NEWS = [
    {
      url: 'https://www.bariaround.it/bari/blog/basilica-san-nicola-guida-visita/',
      img: 'https://images.unsplash.com/photo-1511138895359-86d2a1f62e96?w=200&h=200&fit=crop',
      alt: 'Basilica di San Nicola, Bari',
      title: 'Basilica di San Nicola: guida alla visita',
      desc: 'Il cuore spirituale di Bari tra romanico, reliquie e pellegrinaggi.'
    },
    {
      url: 'https://www.bariaround.it/bari/blog/bari-vecchia-guida-centro-storico/',
      img: 'https://images.unsplash.com/photo-1578604490032-89cfeb9f0e08?w=200&h=200&fit=crop',
      alt: 'Bari Vecchia, centro storico',
      title: 'Bari Vecchia: guida al centro storico',
      desc: 'Vicoli, orecchiette e tradizioni con itinerario di mezza giornata.'
    },
    {
      url: 'https://www.bariaround.it/bari/blog/spiagge-nascoste-bari/',
      img: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=200&h=200&fit=crop',
      alt: 'Spiagge nascoste di Bari',
      title: 'Le spiagge nascoste di Bari',
      desc: 'Cove segrete e calette lontano dal turismo di massa.'
    }
  ];

  var FOOTER_URL = 'https://www.trovapiatto.it/aggiungi-menu.html';

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
  }

  function card(n) {
    return (
      '<a href="' + esc(n.url) + '" target="_blank" rel="noopener" style="display:flex;gap:12px;align-items:center;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:10px;text-decoration:none;margin-bottom:10px;">' +
        '<img src="' + esc(n.img) + '" alt="' + esc(n.alt) + '" loading="lazy" decoding="async" style="width:72px;height:72px;object-fit:cover;border-radius:10px;flex-shrink:0;" onerror="this.style.display=\'none\'">' +
        '<span style="flex:1;min-width:0;">' +
          '<span style="display:block;color:#fff;font-size:0.85em;font-weight:700;line-height:1.35;margin-bottom:3px;">' + esc(n.title) + '</span>' +
          '<span style="display:block;color:rgba(255,255,255,0.55);font-size:0.72em;line-height:1.4;">' + esc(n.desc) + '</span>' +
        '</span>' +
        '<i class="fas fa-chevron-right" style="color:#e73a3a;font-size:0.8rem;flex-shrink:0;"></i>' +
      '</a>'
    );
  }

  function renderBariAround() {
    var slot = document.getElementById('tp-bariaround');
    if (!slot || slot.dataset.done) return;
    slot.dataset.done = '1';
    slot.innerHTML =
      '<div style="margin: 15px; padding: 20px; background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(231,58,58,0.04)); border: 1px solid rgba(245,158,11,0.2); border-radius: 16px;">' +
        '<div style="display: flex; align-items:center; gap: 10px; margin-bottom: 4px;">' +
          '<i class="fas fa-newspaper" style="color: #f59e0b; font-size: 1.1rem;"></i>' +
          '<h3 style="margin: 0; color: #fff; font-size: 1em; font-weight: 700;">Da BariAround</h3>' +
        '</div>' +
        '<p style="margin: 0 0 14px; color: rgba(255,255,255,0.6); font-size: 0.8em;">Storie, guide e luoghi da scoprire a Bari e dintorni</p>' +
        BARIAROUND_NEWS.map(card).join('') +
      '</div>';
  }

  function fixFooter() {
    var a = document.querySelector('.footer-copyright a');
    if (a) a.setAttribute('href', FOOTER_URL);
  }

  function init() {
    renderBariAround();
    fixFooter();
  }

  if (document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);
})();
