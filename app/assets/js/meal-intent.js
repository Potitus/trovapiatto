/**
 * meal-intent.js — Guida ai piatti consigliati per pasto e citta (es. "pranzo Bari").
 *
 * Quando un utente cerca "pranzo Bari", "dove cenare a Bari", "colazione Bari"...
 * la ricerca testuale classica restituisce 0 risultati (nessun piatto si chiama
 * "pranzo"). Questa libreria rileva l'intento (pasto + citta) e restituisce
 * indicazioni sui piatti consigliati, con link diretti alle ricerche reali.
 *
 * Funzioni pubbliche:
 *   detectMealIntent(query)          -> { meal, city, explicitCity } | null
 *   getPiattiConsigliatiBari(query)  -> guida { title, subtitle, dishes, ... } | null
 *   renderMealGuideHtml(guida)       -> stringa HTML (stili inline, riusabile ovunque)
 *
 * Nessuna dipendenza. Funziona in /piatti/, /app/ e qualsiasi altra pagina.
 */
(function (global) {
  'use strict';

  var DEFAULT_CITY = 'Bari';

  // Parole che identificano il pasto (chiavi normalizzate senza accenti).
  var MEAL_KEYWORDS = {
    pranzo: ['pranzo', 'pranzi', 'pranzare', 'pranzetto', 'mezzogiorno', 'pausa pranzo', 'pausapranzo'],
    cena: ['cena', 'cene', 'cenare', 'cenetta', 'stasera', 'apericena'],
    colazione: ['colazione', 'colazioni', 'breakfast', 'buongiorno'],
    merenda: ['merenda', 'merende', 'spuntino', 'spuntini', 'pomeriggio'],
    aperitivo: ['aperitivo', 'aperitivi', 'aperihour', 'happy hour', 'happyhour', 'spritz'],
    brunch: ['brunch']
  };

  // Citta servite (normalizzate). Estendibile: basta aggiungere la chiave.
  var CITY_KEYWORDS = {
    Bari: ['bari', 'bari vecchia', 'barivecchia', 'murat', 'lungomare']
  };

  // Guida curata: piatti consigliati per ogni pasto a Bari.
  // "q" e il termine usato per la ricerca reale (/piatti/?q=... e API search-dishes).
  var MEAL_GUIDES = {
    pranzo: {
      meal: 'pranzo',
      icon: 'fas fa-sun',
      color: '#f59e0b',
      title: 'Pranzo a Bari',
      subtitle: 'I piatti che i baresi mangiano davvero a pranzo: dal simbolo orecchiette allo street food da passeggio.',
      guideLink: '/bari/',
      guideLabel: 'Leggi la guida completa ai piatti di Bari',
      dishes: [
        { q: 'orecchiette', label: 'Orecchiette con cime di rapa', desc: 'Il simbolo di Bari, fatte a mano ogni giorno', icon: 'fas fa-utensils', img: '/images/dishes/orecchiette-con-le-cime-di-rapa.webp' },
        { q: 'tiella', label: 'Tiella barese', desc: 'Riso, patate e cozze al forno: il piatto unico', icon: 'fas fa-bowl-food', img: 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=200&h=200&fit=crop' },
        { q: 'focaccia', label: 'Focaccia barese', desc: 'Alta, morbida, con pomodoro e olive', icon: 'fas fa-bread-slice', img: '/images/dishes/focaccia-barese.jpg' },
        { q: 'panzerotto', label: 'Panzerotti fritti', desc: 'Filanti dentro, croccanti fuori', icon: 'fas fa-fire', img: 'https://images.unsplash.com/photo-1536964549204-cce9eab227bd?w=200&h=200&fit=crop' },
        { q: 'burrata', label: 'Burrata pugliese', desc: 'Cremosa, perfetta come antipasto', icon: 'fas fa-cheese', img: 'https://images.unsplash.com/photo-1753791320863-bda47c67cd00?w=200&h=200&fit=crop' },
        { q: 'branzino', label: 'Branzino al forno', desc: 'Pescato la mattina stessa', icon: 'fas fa-fish', img: 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=200&h=200&fit=crop' }
      ]
    },
    cena: {
      meal: 'cena',
      icon: 'fas fa-moon',
      color: '#8b5cf6',
      title: 'Cena a Bari',
      subtitle: 'Dal crudo di mare al forno a legna: cosa mangiare la sera a Bari, tra pesce freschissimo e pizza.',
      guideLink: '/bari/',
      guideLabel: 'Leggi la guida completa ai piatti di Bari',
      dishes: [
        { q: 'branzino', label: 'Branzino al forno', desc: 'Il re della tavola barese', icon: 'fas fa-fish', img: 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=200&h=200&fit=crop' },
        { q: 'pizza', label: 'Pizza barese', desc: 'Sottile e croccante dal forno a legna', icon: 'fas fa-pizza-slice', img: 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?w=200&h=200&fit=crop' },
        { q: 'crudo di mare', label: 'Crudo di mare', desc: 'Ostriche, gamberi rossi, tartare', icon: 'fas fa-shrimp', img: 'https://images.unsplash.com/photo-1534604973900-c43e1f2cbcbf?w=200&h=200&fit=crop' },
        { q: 'polpo', label: 'Polpo alla griglia', desc: 'Classico della cucina marinara', icon: 'fas fa-fish', img: 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=200&h=200&fit=crop' },
        { q: 'frutta di mare', label: 'Frutta di mare', desc: 'Il pescato del giorno dall\u2019Adriatico', icon: 'fas fa-shrimp', img: 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=200&h=200&fit=crop' },
        { q: 'burrata', label: 'Burrata pugliese', desc: 'Per iniziare la cena con dolcezza', icon: 'fas fa-cheese', img: 'https://images.unsplash.com/photo-1753791320863-bda47c67cd00?w=200&h=200&fit=crop' }
      ]
    },
    colazione: {
      meal: 'colazione',
      icon: 'fas fa-mug-saucer',
      color: '#f97316',
      title: 'Colazione a Bari',
      subtitle: 'Dolce o salata? Ecco come iniziano la giornata i baresi, dal pasticciotto alla focaccia del forno.',
      guideLink: '/bari/#pasticciotto',
      guideLabel: 'Scopri i dolci tipici nella guida',
      dishes: [
        { q: 'pasticciotto', label: 'Pasticciotto leccese', desc: 'Frolla croccante e crema, il re del mattino', icon: 'fas fa-cake-candles', img: 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=200&h=200&fit=crop' },
        { q: 'caffe leccese', label: 'Caff\u00e8 leccese', desc: 'Espresso su ghiaccio e latte di mandorla', icon: 'fas fa-mug-hot', img: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=200&h=200&fit=crop' },
        { q: 'focaccia', label: 'Focaccia barese', desc: 'A Bari si mangia anche a colazione', icon: 'fas fa-bread-slice', img: '/images/dishes/focaccia-barese.jpg' },
        { q: 'cornetto', label: 'Cornetto artigianale', desc: 'Il classico da bar, sempre fresco', icon: 'fas fa-croissant', img: 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=200&h=200&fit=crop' }
      ]
    },
    merenda: {
      meal: 'merenda',
      icon: 'fas fa-cookie-bite',
      color: '#eab308',
      title: 'Merenda a Bari',
      subtitle: 'Lo spuntino pomeridiano barese: street food fritto e dolci da passeggio nel centro storico.',
      guideLink: '/bari/#sgagliozze',
      guideLabel: 'Scopri lo street food nella guida',
      dishes: [
        { q: 'focaccia', label: 'Focaccia barese', desc: 'La merenda ufficiale, calda dal forno', icon: 'fas fa-bread-slice', img: '/images/dishes/focaccia-barese.jpg' },
        { q: 'panzerotto', label: 'Panzerotti fritti', desc: 'Da mangiare camminando per Bari Vecchia', icon: 'fas fa-fire', img: 'https://images.unsplash.com/photo-1536964549204-cce9eab227bd?w=200&h=200&fit=crop' },
        { q: 'sgagliozze', label: 'Sgagliozze e popizze', desc: 'Polenta fritta e palline di impasto', icon: 'fas fa-fire', img: 'https://images.unsplash.com/photo-1536964549204-cce9eab227bd?w=200&h=200&fit=crop' },
        { q: 'pasticciotto', label: 'Pasticciotto leccese', desc: 'La pausa dolce perfetta', icon: 'fas fa-cake-candles', img: 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=200&h=200&fit=crop' }
      ]
    },
    aperitivo: {
      meal: 'aperitivo',
      icon: 'fas fa-martini-glass',
      color: '#06b6d4',
      title: 'Aperitivo a Bari',
      subtitle: 'Spritz sul lungomare con stuzzichini pugliesi: taralli, burrata e crudo di mare.',
      guideLink: '/bari/#crudo',
      guideLabel: 'Scopri il crudo di mare nella guida',
      dishes: [
        { q: 'taralli', label: 'Taralli pugliesi', desc: 'Alle olive, al finocchietto, al peperoncino', icon: 'fas fa-bread-slice', img: '/images/dishes/focaccia-barese.jpg' },
        { q: 'burrata', label: 'Burrata pugliese', desc: 'Con pomodorini e rucola', icon: 'fas fa-cheese', img: 'https://images.unsplash.com/photo-1753791320863-bda47c67cd00?w=200&h=200&fit=crop' },
        { q: 'focaccia', label: 'Focaccia barese', desc: 'A tranci, perfetta con lo spritz', icon: 'fas fa-bread-slice', img: '/images/dishes/focaccia-barese.jpg' },
        { q: 'crudo di mare', label: 'Crudo di mare', desc: 'Per un aperitivo vista mare', icon: 'fas fa-shrimp', img: 'https://images.unsplash.com/photo-1534604973900-c43e1f2cbcbf?w=200&h=200&fit=crop' }
      ]
    },
    brunch: {
      meal: 'brunch',
      icon: 'fas fa-egg',
      color: '#10b981',
      title: 'Brunch a Bari',
      subtitle: 'Dolce e salato insieme: il meglio del mattino barese in un unico pasto rilassato.',
      guideLink: '/bari/',
      guideLabel: 'Leggi la guida completa ai piatti di Bari',
      dishes: [
        { q: 'focaccia', label: 'Focaccia barese', desc: 'Il salato che non manca mai', icon: 'fas fa-bread-slice', img: '/images/dishes/focaccia-barese.jpg' },
        { q: 'burrata', label: 'Burrata pugliese', desc: 'Fresca e cremosa', icon: 'fas fa-cheese', img: 'https://images.unsplash.com/photo-1753791320863-bda47c67cd00?w=200&h=200&fit=crop' },
        { q: 'pasticciotto', label: 'Pasticciotto leccese', desc: 'Il dolce del brunch', icon: 'fas fa-cake-candles', img: 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=200&h=200&fit=crop' },
        { q: 'caffe leccese', label: 'Caff\u00e8 leccese', desc: 'Fresco con latte di mandorla', icon: 'fas fa-mug-hot', img: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=200&h=200&fit=crop' }
      ]
    }
  };

  function normalize(s) {
    return String(s || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, ' ')
      .replace(/[^a-z0-9 ]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function containsKeyword(normalized, keyword) {
    var re = new RegExp('(^| )' + keyword.replace(/ /g, ' +') + '( |$)');
    return re.test(normalized);
  }

  /**
   * Rileva l'intento "pasto + citta" in una query libera.
   * Es: "pranzo Bari" -> { meal: 'pranzo', city: 'Bari', explicitCity: true }
   *     "dove cenare" -> { meal: 'cena', city: 'Bari', explicitCity: false }
   * Ritorna null se la query non contiene alcun riferimento a un pasto.
   */
  function detectMealIntent(query) {
    var n = normalize(query);
    if (!n) return null;

    var meal = null;
    for (var m in MEAL_KEYWORDS) {
      var kws = MEAL_KEYWORDS[m];
      for (var i = 0; i < kws.length; i++) {
        if (containsKeyword(n, normalize(kws[i]))) { meal = m; break; }
      }
      if (meal) break;
    }
    if (!meal) return null;

    var city = DEFAULT_CITY;
    var explicitCity = false;
    for (var c in CITY_KEYWORDS) {
      var ckws = CITY_KEYWORDS[c];
      for (var j = 0; j < ckws.length; j++) {
        if (containsKeyword(n, normalize(ckws[j]))) { city = c; explicitCity = true; break; }
      }
      if (explicitCity) break;
    }

    return { meal: meal, city: city, explicitCity: explicitCity };
  }

  /**
   * Funzione principale: data una query utente (es. "pranzo Bari") o il nome
   * di un pasto (es. "pranzo"), restituisce la guida ai piatti consigliati.
   * Ritorna null se non c'e intento pasto (ricerca piatto normale).
   */
  function getPiattiConsigliatiBari(queryOrMeal) {
    var raw = String(queryOrMeal || '').trim();
    if (!raw) return null;

    var intent = detectMealIntent(raw);
    if (!intent) {
      // Accetta anche il solo nome del pasto ("pranzo", "cena", ...)
      var n = normalize(raw);
      if (MEAL_GUIDES[n]) intent = { meal: n, city: DEFAULT_CITY, explicitCity: false };
      else return null;
    }

    var guide = MEAL_GUIDES[intent.meal];
    if (!guide) return null;

    return {
      meal: guide.meal,
      city: intent.city,
      icon: guide.icon,
      color: guide.color,
      title: guide.title.replace(DEFAULT_CITY, intent.city),
      subtitle: guide.subtitle,
      dishes: guide.dishes,
      dishQueries: guide.dishes.map(function (d) { return d.q; }),
      guideLink: guide.guideLink,
      guideLabel: guide.guideLabel,
      query: raw
    };
  }

  function escHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
  }

  /**
   * Rendering HTML della guida (stili inline: funziona in qualsiasi pagina).
   * Ogni piatto linka alla ricerca reale /piatti/?q=termine.
   */
  function renderMealGuideHtml(guide) {
    if (!guide) return '';
    var color = guide.color || '#e73a3a';

    var chips = guide.dishes.map(function (d) {
      var href = '/piatti/?q=' + encodeURIComponent(d.q);
      var img = d.img
        ? '<img src="' + escHtml(d.img) + '" alt="' + escHtml(d.label) + '" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;">'
        : '<i class="' + escHtml(d.icon || 'fas fa-utensils') + '" style="color:' + color + ';font-size:1.1rem;"></i>';
      return '' +
        '<a href="' + href + '" title="Dove mangiare ' + escHtml(d.label) + ' a ' + escHtml(guide.city) + '" ' +
        'style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:rgba(255,255,255,0.05);' +
        'border:1px solid rgba(255,255,255,0.1);border-radius:12px;text-decoration:none;color:inherit;transition:all 0.25s;" ' +
        'onmouseover="this.style.borderColor=\'' + color + '\';this.style.transform=\'translateY(-2px)\'" ' +
        'onmouseout="this.style.borderColor=\'rgba(255,255,255,0.1)\';this.style.transform=\'none\'">' +
        '<span style="width:46px;height:46px;border-radius:10px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.06);border:2px solid ' + color + '55;">' + img + '</span>' +
        '<span style="flex:1;min-width:0;">' +
        '<span style="display:block;font-weight:700;font-size:0.88rem;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(d.label) + '</span>' +
        '<span style="display:block;font-size:0.72rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(d.desc) + '</span>' +
        '</span>' +
        '<i class="fas fa-arrow-right" style="color:' + color + ';font-size:0.8rem;flex-shrink:0;"></i>' +
        '</a>';
    }).join('');

    return '' +
      '<div class="meal-guide-banner" style="background:linear-gradient(135deg,' + color + '22,' + color + '08);' +
      'border:1px solid ' + color + '55;border-top:3px solid ' + color + ';border-radius:16px;padding:20px;margin-bottom:2rem;">' +
      '<div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">' +
      '<span style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,' + color + ',' + color + 'cc);' +
      'display:flex;align-items:center;justify-content:center;flex-shrink:0;">' +
      '<i class="' + escHtml(guide.icon) + '" style="color:#fff;font-size:1.2rem;"></i></span>' +
      '<div style="flex:1;min-width:0;">' +
      '<div style="font-weight:800;font-size:1.15rem;color:#fff;">' + escHtml(guide.title) + ': cosa ti consigliamo</div>' +
      '<div style="font-size:0.78rem;color:#9ca3af;">Hai cercato &ldquo;' + escHtml(guide.query) + '&rdquo; &mdash; ecco i piatti tipici da provare</div>' +
      '</div></div>' +
      '<p style="font-size:0.88rem;color:#e0e0e0;line-height:1.6;margin:8px 0 16px;">' + escHtml(guide.subtitle) + '</p>' +
      '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px;">' + chips + '</div>' +
      (guide.guideLink
        ? '<a href="' + escHtml(guide.guideLink) + '" style="display:inline-flex;align-items:center;gap:8px;margin-top:16px;' +
          'color:' + color + ';font-size:0.85rem;font-weight:700;text-decoration:none;">' +
          '<i class="fas fa-book-open"></i> ' + escHtml(guide.guideLabel) + ' <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i></a>'
        : '') +
      '</div>';
  }

  global.detectMealIntent = detectMealIntent;
  global.getPiattiConsigliatiBari = getPiattiConsigliatiBari;
  global.renderMealGuideHtml = renderMealGuideHtml;
  global.MEAL_GUIDES = MEAL_GUIDES;
})(typeof window !== 'undefined' ? window : globalThis);
