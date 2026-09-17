/**
 * app-upgrade.js — Nuove funzioni della web-app trovapiatto:
 *   1) "Aperti ora" (stima da fasce orarie tipiche, sempre marcata come indicativa)
 *   2) "Confronta prezzi" (stesso piatto in più locali, ordinati per prezzo)
 *   3) "Da provare" (wishlist piatti + ristoranti in localStorage)
 *
 * Nessuna dipendenza. Le funzioni pure sono testabili in Node (module.exports).
 */
(function (global) {
  'use strict';

  // ==================== 1) APERTI ORA ====================
  // Se il ristorante ha orari reali (available_hours dal DB) li usa;
  // altrimenti ripiega su fasce tipiche, sempre marcate come indicative.
  // Formato reale: {mon:{lunch:"12:00-14:30",dinner:"19:00-23:00"},...} (giorno assente = chiuso).
  var LUNCH = [12 * 60, 15 * 60];        // 12:00-15:00
  var DINNER = [19 * 60, 23 * 60 + 30];  // 19:00-23:30
  var DAY_KEYS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
  var DAY_NAMES = ['dom', 'lun', 'mar', 'mer', 'gio', 'ven', 'sab'];

  function slotsForCuisine(cuisine) {
    var c = String(cuisine || '').toLowerCase();
    if (/pizzeria|pizza/.test(c)) return [[11 * 60 + 30, 15 * 60], [18 * 60 + 30, 24 * 60]];
    if (/panificio|forno|pasticceria|pasticceria|bar\b|caffetteria|street|friggitoria|rosticceria|panino|kebab|fast/.test(c)) {
      return [[7 * 60, 20 * 60 + 30]];
    }
    return [LUNCH, DINNER];
  }

  function fmtMins(m) {
    var h = Math.floor(m / 60) % 24;
    var mm = m % 60;
    return (h < 10 ? '0' : '') + h + ':' + (mm < 10 ? '0' : '') + mm;
  }

  function toMins(t) {
    var m = /^([01]\d|2[0-3]):([0-5]\d)$/.exec(String(t || '').trim());
    return m ? (parseInt(m[1], 10) * 60 + parseInt(m[2], 10)) : null;
  }

  function realRangesForDay(hours, dayKey) {
    var out = [];
    var day = hours && hours[dayKey];
    if (!day || typeof day !== 'object') return out;
    ['lunch', 'dinner'].forEach(function (meal) {
      var v = String(day[meal] || '').trim();
      if (!v || v.indexOf('-') === -1) return;
      var parts = v.split('-');
      var a = toMins(parts[0]), b = toMins(parts[1]);
      if (a === null || b === null || a === b) return;
      out.push([a, b]);
    });
    return out;
  }

  function hasAnyRealRange(hours) {
    for (var i = 0; i < DAY_KEYS.length; i++) {
      if (realRangesForDay(hours, DAY_KEYS[i]).length) return true;
    }
    return false;
  }

  function stateFromRanges(mins, ranges, dayIdx, hours) {
    // Intervalli di oggi + code overnight di ieri
    var ivals = ranges.map(function (r) { return [r[0], r[1] <= r[0] ? r[1] + 1440 : r[1]]; });
    var yKey = DAY_KEYS[(dayIdx + 6) % 7];
    realRangesForDay(hours, yKey).forEach(function (r) {
      if (r[1] <= r[0]) ivals.push([0, r[1]]); // coda dopo mezzanotte
    });
    for (var i = 0; i < ivals.length; i++) {
      if (mins >= ivals[i][0] && mins < ivals[i][1]) {
        return { open: true, label: 'Aperto ora', until: fmtMins(ivals[i][1]), indicative: false, source: 'hours' };
      }
    }
    var next = null;
    ivals.forEach(function (iv) { if (mins < iv[0] && (next === null || iv[0] < next)) next = iv[0]; });
    if (next !== null) {
      return { open: false, label: 'Apre alle ' + fmtMins(next), indicative: false, source: 'hours' };
    }
    for (var d = 1; d <= 6; d++) {
      var k = DAY_KEYS[(dayIdx + d) % 7];
      var rr = realRangesForDay(hours, k);
      if (rr.length) {
        var first = Math.min.apply(null, rr.map(function (r) { return r[0]; }));
        return { open: false, label: 'Apre ' + DAY_NAMES[(dayIdx + d) % 7] + ' alle ' + fmtMins(first), indicative: false, source: 'hours' };
      }
    }
    return { open: false, label: 'Chiuso', indicative: false, source: 'hours' };
  }

  /**
   * Stato di apertura. `hours` = orari reali (oggetto o stringa JSON) o null.
   * Ritorna { open, label, until?, indicative, source: 'hours'|'estimate' }.
   */
  function getOpenState(when, cuisine, hours) {
    var d = when instanceof Date ? when : new Date();
    var mins = d.getHours() * 60 + d.getMinutes();
    var h = hours;
    if (typeof h === 'string') {
      try { h = JSON.parse(h); } catch (e) { h = null; }
    }
    if (h && typeof h === 'object' && hasAnyRealRange(h)) {
      return stateFromRanges(mins, realRangesForDay(h, DAY_KEYS[d.getDay()]), d.getDay(), h);
    }
    // Fallback: stima da fasce tipiche
    var slots = slotsForCuisine(cuisine);
    for (var i = 0; i < slots.length; i++) {
      if (mins >= slots[i][0] && mins < slots[i][1]) {
        return { open: true, label: 'Aperto ora', until: fmtMins(slots[i][1]), indicative: true, source: 'estimate' };
      }
    }
    var next = null;
    for (var j = 0; j < slots.length; j++) {
      if (mins < slots[j][0]) { next = slots[j][0]; break; }
    }
    return {
      open: false,
      label: next !== null ? 'Apre alle ' + fmtMins(next) : 'Apre domani',
      indicative: true, source: 'estimate'
    };
  }

  // ==================== 2) CONFRONTA PREZZI ====================
  function normalizeDishName(name) {
    return String(name || '')
      .toLowerCase()
      .normalize('NFD').replace(/[̀-ͯ]/g, '')
      .replace(/[^a-z0-9 ]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function parsePrice(p) {
    if (p === null || p === undefined || p === '') return null;
    var n = parseFloat(String(p).replace(/[^0-9.,]/g, '').replace(',', '.'));
    return isFinite(n) ? n : null;
  }

  /**
   * Raggruppa risultati-prezzo per piatto. Ritorna solo i gruppi presenti
   * in >= 2 ristoranti diversi, ordinati per n. locali (poi prezzo min).
   * Ogni gruppo: { key, name, min, max, items: [{...r, _price}] ordinati per prezzo }.
   */
  function groupDishesByPrice(results) {
    var map = {};
    (results || []).forEach(function (r) {
      var key = normalizeDishName(r.name);
      if (!key) return;
      if (!map[key]) map[key] = { key: key, name: r.name, items: [], restIds: {} };
      var price = parsePrice(r.price);
      map[key].items.push(Object.assign({}, r, { _price: price }));
      map[key].restIds[r.restaurant_id || r.restaurant_name] = true;
    });
    var groups = Object.keys(map).map(function (k) { return map[k]; }).filter(function (g) {
      return Object.keys(g.restIds).length >= 2;
    });
    groups.forEach(function (g) {
      g.items.sort(function (a, b) {
        var pa = a._price === null ? Infinity : a._price;
        var pb = b._price === null ? Infinity : b._price;
        return pa - pb;
      });
      var priced = g.items.filter(function (it) { return it._price !== null; }).map(function (it) { return it._price; });
      g.min = priced.length ? priced[0] : null;
      g.max = priced.length ? priced[priced.length - 1] : null;
      g.count = Object.keys(g.restIds).length;
    });
    groups.sort(function (a, b) { return (b.count - a.count) || ((a.min === null ? Infinity : a.min) - (b.min === null ? Infinity : b.min)); });
    return groups;
  }

  // ==================== 3) DA PROVARE (wishlist) ====================
  var LS_DISHES = 'tp_toprova_dishes';
  var LS_REST_EXTRA = 'tp_toprova_rests'; // id ristoranti salvati dal drawer (oltre ai cuori tp_favorites)

  function lsGet(key) {
    try { return JSON.parse(localStorage.getItem(key) || '[]'); }
    catch (e) { return []; }
  }
  function lsSet(key, val) {
    try { localStorage.setItem(key, JSON.stringify(val)); } catch (e) {}
  }

  function dishKey(d) {
    return normalizeDishName(d.name) + '::' + String(d.restaurant_id || d.restaurant_name || '');
  }

  var Wishlist = {
    getDishes: function () { return lsGet(LS_DISHES); },
    isDishSaved: function (d) {
      var k = dishKey(d);
      return lsGet(LS_DISHES).some(function (x) { return x.key === k; });
    },
    toggleDish: function (d) {
      var list = lsGet(LS_DISHES);
      var k = dishKey(d);
      var idx = -1;
      for (var i = 0; i < list.length; i++) { if (list[i].key === k) { idx = i; break; } }
      var saved;
      if (idx > -1) { list.splice(idx, 1); saved = false; }
      else {
        list.unshift({
          key: k,
          name: d.name, price: d.price != null ? d.price : null,
          restaurant_id: d.restaurant_id || null, restaurant_name: d.restaurant_name || '',
          restaurant_slug: d.restaurant_slug || '', ts: Date.now()
        });
        saved = true;
      }
      lsSet(LS_DISHES, list.slice(0, 200));
      return saved;
    },
    removeDish: function (key) {
      lsSet(LS_DISHES, lsGet(LS_DISHES).filter(function (x) { return x.key !== key; }));
    },
    // Ristoranti: riusa i cuori esistenti (tp_favorites) + extra dal drawer
    getRestIds: function () {
      var favs = lsGet('tp_favorites');
      var extra = lsGet(LS_REST_EXTRA);
      var seen = {}, out = [];
      favs.concat(extra).forEach(function (id) {
        if (id && !seen[id]) { seen[id] = true; out.push(id); }
      });
      return out;
    },
    toggleRest: function (id) {
      var favs = lsGet('tp_favorites');
      var idx = favs.indexOf(id);
      var saved;
      if (idx > -1) {
        favs.splice(idx, 1);
        lsSet(LS_REST_EXTRA, lsGet(LS_REST_EXTRA).filter(function (x) { return x !== id; }));
        saved = false;
      } else { favs.push(id); saved = true; }
      try { localStorage.setItem('tp_favorites', JSON.stringify(favs)); } catch (e) {}
      return saved;
    },
    removeRest: function (id) {
      try { localStorage.setItem('tp_favorites', JSON.stringify(lsGet('tp_favorites').filter(function (x) { return x !== id; }))); } catch (e) {}
      lsSet(LS_REST_EXTRA, lsGet(LS_REST_EXTRA).filter(function (x) { return x !== id; }));
    },
    clearAll: function () {
      lsSet(LS_DISHES, []);
      try { localStorage.setItem('tp_favorites', JSON.stringify([])); } catch (e) {}
      lsSet(LS_REST_EXTRA, []);
    },
    count: function () { return lsGet(LS_DISHES).length + Wishlist.getRestIds().length; }
  };

  // Export browser + Node
  global.TPOpen = { getOpenState: getOpenState, slotsForCuisine: slotsForCuisine };
  global.TPCompare = { normalizeDishName: normalizeDishName, parsePrice: parsePrice, groupDishesByPrice: groupDishesByPrice };
  global.TPWishlist = Wishlist;
  global.tpOpenNowOnly = false;
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { getOpenState: getOpenState, normalizeDishName: normalizeDishName, parsePrice: parsePrice, groupDishesByPrice: groupDishesByPrice, dishKey: dishKey };
  }
})(typeof window !== 'undefined' ? window : globalThis);
