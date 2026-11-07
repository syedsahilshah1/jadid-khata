/**
 * Universal search: debounced fetch, scope filter, recent + pinned queries (localStorage),
 * loading/abort, keyboard navigation, category chips, CSV export, copy/clear, multi-word highlight.
 * Expects window.universalSearchRoute and optional window.searchboxConfig (from DataController).
 */
(function () {
    'use strict';

    var RECENT_KEY = 'searchbox_recent_v1';
    var PINNED_KEY = 'searchbox_pinned_v1';
    var AI_MODE_KEY = 'searchbox_ai_mode_v1';
    var scopeValue = '';

    var lastSearchSnapshot = {
        desktop: null,
        mobile: null,
    };

    function cfg() {
        var c = typeof window.searchboxConfig === 'object' && window.searchboxConfig !== null
            ? window.searchboxConfig
            : {};
        return {
            desktopSearchEnabled: c.desktopSearchEnabled !== false,
            mobileFloatingEnabled: c.mobileFloatingEnabled !== false,
            minQueryLength: typeof c.minQueryLength === 'number' ? c.minQueryLength : 2,
            aiMinQueryLength: typeof c.aiMinQueryLength === 'number' ? c.aiMinQueryLength : 2,
            debounceMs: typeof c.debounceMs === 'number' ? c.debounceMs : 320,
            maxRecent: typeof c.maxRecent === 'number' ? c.maxRecent : 10,
            enableRecent: c.enableRecent !== false,
            enableKeyboardNav: c.enableKeyboardNav !== false,
            enablePinned: c.enablePinned !== false,
            maxPinned: typeof c.maxPinned === 'number' ? c.maxPinned : 5,
            enableCopyQuery: c.enableCopyQuery !== false,
            enableClearButton: c.enableClearButton !== false,
            enableCategoryChips: c.enableCategoryChips !== false,
            enableExportCsv: c.enableExportCsv !== false,
            aiUiEnabled: c.aiUiEnabled !== false,
            aiBackendEnabled: c.aiBackendEnabled === true,
            scopes: Array.isArray(c.scopes) ? c.scopes : [],
        };
    }

    function getRoute() {
        return typeof window.universalSearchRoute === 'string' ? window.universalSearchRoute : '';
    }

    function getAiRoute() {
        return typeof window.universalSearchAiRoute === 'string' ? window.universalSearchAiRoute : '';
    }

    function isAiMode() {
        try {
            return localStorage.getItem(AI_MODE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function setAiMode(on) {
        try {
            if (on) {
                localStorage.setItem(AI_MODE_KEY, '1');
            } else {
                localStorage.removeItem(AI_MODE_KEY);
            }
        } catch (e) {}
        syncAiCheckboxes(!!on);
        updateInputPlaceholders();
    }

    function syncAiCheckboxes(checked) {
        ['searchbox_ai_d', 'searchbox_ai_m'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.checked = checked;
            }
        });
    }

    function updateInputPlaceholders() {
        var classic = t('placeholder_classic', 'Search…');
        var ai = t('placeholder_ai', 'Ask anything…');
        var ph = isAiMode() ? ai : classic;
        ['universal_search_input', 'mobile_search_input'].forEach(function (id) {
            var inp = document.getElementById(id);
            if (inp) {
                inp.setAttribute('placeholder', ph);
            }
        });
    }

    function minLenForMode() {
        return isAiMode() ? cfg().aiMinQueryLength : cfg().minQueryLength;
    }

    function getActiveSearchUrl() {
        if (isAiMode() && getAiRoute()) {
            return getAiRoute();
        }
        return getRoute();
    }

    function t(key, fallback) {
        var sc = typeof window.searchboxConfig === 'object' && window.searchboxConfig !== null
            ? window.searchboxConfig
            : {};
        if (sc.strings && sc.strings[key]) {
            return sc.strings[key];
        }
        try {
            if (typeof __ !== 'undefined' && __.searchbox && __.searchbox[key]) {
                return __.searchbox[key];
            }
        } catch (e) {}
        return fallback;
    }

    function debounce(fn, ms) {
        var timer;
        return function () {
            var ctx = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(ctx, args);
            }, ms);
        };
    }

    function escapeHtml(s) {
        if (s == null || s === '') {
            return '';
        }
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function escapeAttr(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function escapeRegExp(s) {
        return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Highlight every word from hlSource (e.g. user query or AI keywords).
     */
    function highlightQuery(text, hlSource) {
        var txt = String(text || '');
        var src = String(hlSource || '').trim();
        if (src.length === 0) {
            return escapeHtml(txt);
        }
        var escaped = escapeHtml(txt);
        var words = src.split(/\s+/).filter(function (w) {
            return w.length > 0;
        });
        if (!words.length) {
            return escaped;
        }
        var parts = words.map(escapeRegExp);
        var re = new RegExp('(' + parts.join('|') + ')', 'ig');
        return escaped.replace(re, '<mark class="tw-bg-yellow-200 tw-rounded-sm">$1</mark>');
    }

    function readRecent() {
        try {
            var raw = localStorage.getItem(RECENT_KEY);
            if (!raw) {
                return [];
            }
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function writeRecent(items) {
        try {
            localStorage.setItem(RECENT_KEY, JSON.stringify(items));
        } catch (e) {}
    }

    function pushRecent(q) {
        q = String(q || '').trim();
        if (q.length < minLenForMode()) {
            return;
        }
        var max = cfg().maxRecent;
        var list = readRecent().filter(function (x) {
            return x && x.q && x.q.toLowerCase() !== q.toLowerCase();
        });
        list.unshift({ q: q, t: Date.now(), scope: scopeValue || '' });
        if (list.length > max) {
            list = list.slice(0, max);
        }
        writeRecent(list);
    }

    function readPinned() {
        try {
            var raw = localStorage.getItem(PINNED_KEY);
            if (!raw) {
                return [];
            }
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function writePinned(items) {
        try {
            localStorage.setItem(PINNED_KEY, JSON.stringify(items));
        } catch (e) {}
    }

    function isPinnedQuery(q) {
        q = String(q || '').trim().toLowerCase();
        if (!q) {
            return false;
        }
        return readPinned().some(function (x) {
            return x && x.q && String(x.q).trim().toLowerCase() === q;
        });
    }

    function togglePinnedForQuery(q) {
        q = String(q || '').trim();
        if (q.length < minLenForMode()) {
            return;
        }
        var max = cfg().maxPinned;
        var raw = readPinned();
        var list = raw.filter(function (x) {
            return x && x.q && x.q.toLowerCase() !== q.toLowerCase();
        });
        var wasPinned = list.length < raw.length;
        if (!wasPinned) {
            list.unshift({ q: q, t: Date.now(), scope: scopeValue || '' });
            if (list.length > max) {
                list = list.slice(0, max);
            }
        }
        writePinned(list);
    }

    function scopeLabelForValue(val) {
        var scopes = cfg().scopes;
        for (var i = 0; i < scopes.length; i++) {
            if (scopes[i].value === val) {
                return scopes[i].label || val;
            }
        }
        return val || '';
    }

    function clearRecent() {
        try {
            localStorage.removeItem(RECENT_KEY);
        } catch (e) {}
    }

    var navState = {
        desktop: { idx: -1, abort: null },
        mobile: { idx: -1, abort: null },
    };

    function ctxKey(inputId) {
        return inputId === 'mobile_search_input' ? 'mobile' : 'desktop';
    }

    function getHits(container) {
        if (!container) {
            return [];
        }
        var raw = container.querySelectorAll('.universal-search-hit');
        var out = [];
        for (var i = 0; i < raw.length; i++) {
            if (raw[i].offsetParent !== null) {
                out.push(raw[i]);
            }
        }
        return out;
    }

    function setHitActive(container, idx) {
        var hits = getHits(container);
        for (var i = 0; i < hits.length; i++) {
            hits[i].classList.remove('tw-bg-primary-50', 'searchbox-hit-active');
        }
        if (idx < 0 || !hits.length) {
            return;
        }
        if (idx < hits.length) {
            hits[idx].classList.add('tw-bg-primary-50', 'searchbox-hit-active');
            hits[idx].scrollIntoView({ block: 'nearest' });
        }
    }

    function renderRecentPanel(container, inputEl, onPick) {
        if (!cfg().enableRecent && !cfg().enablePinned) {
            return;
        }
        var pinned = cfg().enablePinned ? readPinned() : [];
        var list = cfg().enableRecent ? readRecent() : [];
        if (!pinned.length && !list.length) {
            container.innerHTML =
                '<div class="tw-p-4 tw-text-sm tw-text-gray-500">' +
                escapeHtml(t('no_recent_searches', 'No recent searches yet')) +
                '</div>';
            container.classList.remove('tw-hidden');
            return;
        }
        var clearLbl = escapeHtml(t('clear_recent', 'Clear'));
        var html = '';

        if (pinned.length) {
            var ptitle = escapeHtml(t('pinned_searches', 'Pinned'));
            html +=
                '<div class="tw-flex tw-items-center tw-justify-between tw-px-3 tw-py-2 tw-bg-amber-50 tw-border-b tw-border-amber-100">' +
                '<span class="tw-text-xs tw-font-semibold tw-text-amber-800 tw-uppercase">' +
                ptitle +
                '</span></div><div class="tw-py-1 tw-border-b tw-border-gray-100">';
            pinned.forEach(function (item) {
                if (!item || !item.q) {
                    return;
                }
                var sc = item.scope ? '<span class="tw-ml-2 tw-shrink-0 tw-rounded tw-bg-gray-100 tw-px-1.5 tw-py-0.5 tw-text-[10px] tw-text-gray-600">' + escapeHtml(scopeLabelForValue(item.scope)) + '</span>' : '';
                html +=
                    '<button type="button" class="searchbox-recent-item tw-flex tw-w-full tw-items-center tw-justify-between tw-gap-2 tw-text-left tw-px-3 tw-py-2 tw-text-sm tw-text-gray-800 hover:tw-bg-amber-50/80"' +
                    ' data-q="' +
                    escapeAttr(item.q) +
                    '" data-scope="' +
                    escapeAttr(item.scope != null ? item.scope : '') +
                    '">' +
                    '<span class="tw-min-w-0 tw-truncate">' +
                    escapeHtml(item.q) +
                    '</span>' +
                    sc +
                    '<span class="tw-text-amber-500 tw-shrink-0" aria-hidden="true">&#9733;</span></button>';
            });
            html += '</div>';
        }

        if (list.length) {
            var title = escapeHtml(t('recent_searches', 'Recent searches'));
            html +=
                '<div class="tw-flex tw-items-center tw-justify-between tw-px-3 tw-py-2 tw-bg-gray-50 tw-border-b tw-border-gray-100">' +
                '<span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase">' +
                title +
                '</span>' +
                '<button type="button" class="searchbox-clear-recent tw-text-xs tw-text-primary-700 hover:tw-underline">' +
                clearLbl +
                '</button></div><div class="tw-py-1">';
            list.forEach(function (item) {
                if (!item || !item.q) {
                    return;
                }
                var sc2 = item.scope ? '<span class="tw-ml-2 tw-shrink-0 tw-rounded tw-bg-gray-100 tw-px-1.5 tw-py-0.5 tw-text-[10px] tw-text-gray-600">' + escapeHtml(scopeLabelForValue(item.scope)) + '</span>' : '';
                html +=
                    '<button type="button" class="searchbox-recent-item tw-flex tw-w-full tw-items-center tw-justify-start tw-gap-2 tw-text-left tw-px-3 tw-py-2 tw-text-sm tw-text-gray-800 hover:tw-bg-gray-50 tw-border-b tw-border-gray-50"' +
                    ' data-q="' +
                    escapeAttr(item.q) +
                    '" data-scope="' +
                    escapeAttr(item.scope != null ? item.scope : '') +
                    '">' +
                    '<span class="tw-min-w-0 tw-truncate">' +
                    escapeHtml(item.q) +
                    '</span>' +
                    sc2 +
                    '</button>';
            });
            html += '</div>';
        }

        container.innerHTML = html;
        container.classList.remove('tw-hidden');

        var clr = container.querySelector('.searchbox-clear-recent');
        if (clr) {
            clr.addEventListener('click', function (e) {
                e.preventDefault();
                clearRecent();
                renderRecentPanel(container, inputEl, onPick);
            });
        }
        container.querySelectorAll('.searchbox-recent-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var q = btn.getAttribute('data-q') || '';
                var scopeAttr = btn.getAttribute('data-scope');
                if (scopeAttr !== null) {
                    scopeValue = scopeAttr;
                    ['searchbox_scope_desktop', 'searchbox_scope_mobile'].forEach(function (id) {
                        var el = document.getElementById(id);
                        if (el) {
                            el.value = scopeValue;
                        }
                    });
                }
                inputEl.value = q;
                onPick(q);
            });
        });
    }

    function aiHintBanner(data) {
        if (!data.ai || !data.ai.hint) {
            return '';
        }
        return (
            '<div class="tw-mx-2 tw-mt-2 tw-rounded-lg tw-border tw-border-violet-200 tw-bg-violet-50 tw-px-3 tw-py-2 tw-text-xs tw-text-violet-950">' +
            '<span class="tw-mr-1 tw-inline-flex tw-rounded tw-bg-violet-600 tw-px-1.5 tw-py-0.5 tw-text-[10px] tw-font-bold tw-text-white">AI</span>' +
            escapeHtml(data.ai.hint) +
            '</div>'
        );
    }

    function snapKeyForContainer(container) {
        return container && container.id === 'mobile_search_results' ? 'mobile' : 'desktop';
    }

    function formatTimingLine(clientMs, serverMs) {
        var hasC = typeof clientMs === 'number' && !isNaN(clientMs);
        var hasS = typeof serverMs === 'number' && !isNaN(serverMs);
        if (!hasC && !hasS) {
            return '';
        }
        var tpl = t('timing_footer', ':client ms · server :server ms');
        var s = tpl;
        s = s.replace(':client', hasC ? String(Math.round(clientMs)) : '—');
        s = s.replace(':server', hasS ? String(serverMs) : '—');
        return s;
    }

    function csvEscapeCell(val) {
        var s = String(val == null ? '' : val);
        if (/[",\n\r]/.test(s)) {
            return '"' + s.replace(/"/g, '""') + '"';
        }
        return s;
    }

    function downloadSearchCsv(rows, query) {
        var headers = ['title', 'subtitle', 'category', 'type', 'url', 'status'];
        var lines = [headers.join(',')];
        rows.forEach(function (row) {
            lines.push(
                [
                    csvEscapeCell(row.title),
                    csvEscapeCell(row.subtitle),
                    csvEscapeCell(row.category),
                    csvEscapeCell(row.type),
                    csvEscapeCell(row.url),
                    csvEscapeCell(row.status_text),
                ].join(',')
            );
        });
        var blob = new Blob(['\ufeff' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'search-results-' + String(query || 'export').replace(/\s+/g, '-').slice(0, 40) + '.csv';
        a.click();
        URL.revokeObjectURL(a.href);
    }

    function applyCategoryFilter(container, filterCat) {
        var all = filterCat === '__all';
        container.querySelectorAll('.searchbox-cat-block').forEach(function (block) {
            var c = block.getAttribute('data-sb-cat');
            var show = all || c === filterCat;
            block.classList.toggle('tw-hidden', !show);
        });
        container.querySelectorAll('.searchbox-cat-chip').forEach(function (b) {
            var active = b.getAttribute('data-filter') === filterCat;
            b.classList.toggle('tw-ring-2', active);
            b.classList.toggle('tw-ring-primary-500', active);
            b.classList.toggle('tw-bg-primary-600', active);
            b.classList.toggle('tw-text-white', active);
            b.classList.toggle('tw-bg-gray-100', !active);
            b.classList.toggle('tw-text-gray-700', !active);
        });
        navState.desktop.idx = -1;
        navState.mobile.idx = -1;
    }

    function renderResults(container, data, query, clientMs) {
        navState.desktop.idx = -1;
        navState.mobile.idx = -1;

        var sk = snapKeyForContainer(container);
        lastSearchSnapshot[sk] = null;

        var hl = query;
        if (data.ai && data.ai.interpreted_keywords) {
            hl = data.ai.interpreted_keywords;
        }

        if (data.message && (!data.results || !data.results.length)) {
            container.innerHTML = aiHintBanner(data) +
                '<div class="tw-p-4 tw-text-sm tw-text-gray-600">' + escapeHtml(data.message) + '</div>';
            container.classList.remove('tw-hidden');
            return;
        }

        if (!data.results || !data.results.length) {
            var empty =
                typeof LANG !== 'undefined' && LANG.no_results_found
                    ? LANG.no_results_found
                    : 'No results';
            container.innerHTML = aiHintBanner(data) +
                '<div class="tw-p-4 tw-text-sm tw-text-gray-500">' + escapeHtml(empty) + '</div>';
            container.classList.remove('tw-hidden');
            return;
        }

        lastSearchSnapshot[sk] = {
            results: data.results,
            query: query,
        };

        var total = typeof data.total === 'number' ? data.total : data.results.length;
        var countTpl = t('results_count', ':count results');
        var countLine =
            '<div class="tw-px-3 tw-py-1.5 tw-text-xs tw-text-gray-500 tw-border-b tw-border-gray-100 tw-bg-white">' +
            escapeHtml(String(countTpl).replace(':count', String(total))) +
            '</div>';

        var byCat = {};
        data.results.forEach(function (row) {
            var c = row.category || '—';
            if (!byCat[c]) {
                byCat[c] = [];
            }
            byCat[c].push(row);
        });

        var catKeys = Object.keys(byCat);
        var showChips = cfg().enableCategoryChips && catKeys.length > 1;

        var chipsHtml = '';
        if (showChips) {
            var allLbl = escapeHtml(t('filter_all_categories', 'All'));
            chipsHtml =
                '<div class="searchbox-cat-chips tw-flex tw-flex-wrap tw-gap-1.5 tw-px-2 tw-py-2 tw-border-b tw-border-gray-100 tw-bg-gray-50">' +
                '<button type="button" class="searchbox-cat-chip tw-rounded-full tw-px-2.5 tw-py-1 tw-text-[11px] tw-font-medium tw-bg-primary-600 tw-text-white tw-ring-2 tw-ring-primary-500" data-filter="__all">' +
                allLbl +
                '</button>';
            catKeys.forEach(function (cat) {
                var enc = encodeURIComponent(cat);
                chipsHtml +=
                    '<button type="button" class="searchbox-cat-chip tw-rounded-full tw-px-2.5 tw-py-1 tw-text-[11px] tw-font-medium tw-bg-gray-100 tw-text-gray-700" data-filter="' +
                    escapeAttr(enc) +
                    '">' +
                    escapeHtml(cat) +
                    '</button>';
            });
            chipsHtml += '</div>';
        }

        var html = aiHintBanner(data) + countLine + chipsHtml + '<div class="tw-py-1">';
        catKeys.forEach(function (cat) {
            var encCat = encodeURIComponent(cat);
            html += '<div class="searchbox-cat-block" data-sb-cat="' + escapeAttr(encCat) + '">';
            html +=
                '<div class="tw-px-3 tw-py-2 tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide tw-bg-gray-50">' +
                escapeHtml(cat) +
                '</div>';
            byCat[cat].forEach(function (row) {
                var titleHtml = highlightQuery(row.title != null ? String(row.title) : '', hl);
                var sub = highlightQuery(row.subtitle || '', hl);
                var badge = '';
                if (row.status_text) {
                    var bc =
                        row.status_badge_class ||
                        row.badge_class ||
                        'tw-bg-gray-500 tw-text-white';
                    badge =
                        '<span class="tw-ml-2 tw-inline-flex tw-shrink-0 tw-rounded tw-px-1.5 tw-py-0.5 tw-text-[10px] ' +
                        bc +
                        '">' +
                        escapeHtml(row.status_text) +
                        '</span>';
                }
                var url = row.url || '#';
                var isModal = row.is_modal === true || row.is_modal === 1;
                var dContainer = row.data_container || '.view_modal';
                var extra = '';
                if (isModal) {
                    extra =
                        ' data-modal="1" data-href="' +
                        escapeAttr(url) +
                        '" data-container="' +
                        escapeAttr(dContainer) +
                        '"';
                }
                html +=
                    '<a href="' +
                    (isModal ? '#' : escapeAttr(url)) +
                    '" class="tw-flex tw-items-start tw-gap-3 tw-px-3 tw-py-2.5 tw-text-left hover:tw-bg-gray-50 tw-cursor-pointer tw-border-b tw-border-gray-100 last:tw-border-0 universal-search-hit"' +
                    extra +
                    '>';
                if (row.image) {
                    html +=
                        '<img src="' +
                        escapeAttr(row.image) +
                        '" alt="" class="tw-h-10 tw-w-10 tw-rounded tw-object-cover tw-shrink-0" />';
                } else {
                    html +=
                        '<span class="tw-mt-0.5 tw-inline-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-full tw-bg-primary-100 tw-text-primary-800"><svg class="tw-h-4 tw-w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>';
                }
                html +=
                    '<span class="tw-min-w-0 tw-flex-1"><span class="tw-flex tw-flex-wrap tw-items-center tw-gap-1"><span class="tw-text-sm tw-font-medium tw-text-gray-900">' +
                    titleHtml +
                    '</span>' +
                    badge +
                    '</span>';
                html +=
                    '<span class="tw-block tw-text-xs tw-text-gray-500 tw-mt-0.5">' + sub + '</span>';
                if (row.type) {
                    html +=
                        '<span class="tw-inline-block tw-mt-1 tw-text-[10px] tw-text-gray-400">' +
                        escapeHtml(row.type) +
                        '</span>';
                }
                html += '</span></a>';
            });
            html += '</div>';
        });
        html += '</div>';

        var timingTxt = formatTimingLine(clientMs, data.took_ms);
        var exportId = '';
        if (cfg().enableExportCsv && data.results.length) {
            exportId = 'searchbox-export-' + sk;
            html +=
                '<div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2 tw-px-3 tw-py-2 tw-border-t tw-border-gray-100 tw-bg-gray-50">' +
                '<span class="tw-text-[11px] tw-text-gray-500">' +
                escapeHtml(timingTxt) +
                '</span>' +
                '<button type="button" id="' +
                exportId +
                '" class="searchbox-export-csv tw-rounded-md tw-bg-white tw-px-2.5 tw-py-1 tw-text-[11px] tw-font-medium tw-text-primary-700 tw-ring-1 tw-ring-gray-200 hover:tw-bg-gray-50">' +
                escapeHtml(t('export_csv', 'Export CSV')) +
                '</button></div>';
        } else if (timingTxt) {
            html +=
                '<div class="tw-px-3 tw-py-2 tw-border-t tw-border-gray-100 tw-bg-gray-50 tw-text-[11px] tw-text-gray-500">' +
                escapeHtml(timingTxt) +
                '</div>';
        }

        var hint = t('kbd_hint', '');
        if (hint && cfg().enableKeyboardNav) {
            html +=
                '<div class="tw-px-3 tw-py-1.5 tw-text-[10px] tw-text-gray-400 tw-border-t tw-border-gray-100 tw-bg-gray-50">' +
                escapeHtml(hint) +
                '</div>';
        }
        container.innerHTML = html;
        container.classList.remove('tw-hidden');

        if (exportId) {
            var exBtn = document.getElementById(exportId);
            if (exBtn) {
                exBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var snap = lastSearchSnapshot[sk];
                    if (snap && snap.results && snap.results.length) {
                        downloadSearchCsv(snap.results, snap.query);
                    }
                });
            }
        }
    }

    function runSearch(query, resultEl, inputEl) {
        var route = getActiveSearchUrl();
        if (!route) {
            return;
        }
        query = (query || '').trim();
        var minLen = minLenForMode();
        if (query.length < minLen) {
            resultEl.innerHTML = '';
            resultEl.classList.add('tw-hidden');
            return;
        }

        var ck = ctxKey(inputEl.id);
        if (navState[ck].abort) {
            try {
                navState[ck].abort.abort();
            } catch (e) {}
        }
        navState[ck].abort = typeof AbortController !== 'undefined' ? new AbortController() : null;

        var t0 =
            typeof performance !== 'undefined' && typeof performance.now === 'function'
                ? performance.now()
                : Date.now();

        var loadMsg = escapeHtml(
            isAiMode() ? t('loading_ai', 'Understanding your request…') : t('loading', 'Searching…')
        );
        resultEl.innerHTML =
            '<div class="tw-flex tw-items-center tw-gap-2 tw-p-4 tw-text-sm tw-text-gray-600"><span class="tw-inline-block tw-h-4 tw-w-4 tw-animate-spin tw-rounded-full tw-border-2 tw-border-primary-600 tw-border-t-transparent"></span>' +
            loadMsg +
            '</div>';
        resultEl.classList.remove('tw-hidden');

        var url = route + (route.indexOf('?') === -1 ? '?' : '&') + 'q=' + encodeURIComponent(query);
        if (scopeValue) {
            url += '&only=' + encodeURIComponent(scopeValue);
        }

        var fetchOpts = {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        };
        if (navState[ck].abort) {
            fetchOpts.signal = navState[ck].abort.signal;
        }

        fetch(url, fetchOpts)
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function (data) {
                var t1 =
                    typeof performance !== 'undefined' && typeof performance.now === 'function'
                        ? performance.now()
                        : Date.now();
                var clientMs = t1 - t0;
                renderResults(resultEl, data, query, clientMs);
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') {
                    return;
                }
                resultEl.innerHTML =
                    '<div class="tw-p-4 tw-text-sm tw-text-red-600">Search request failed</div>';
                resultEl.classList.remove('tw-hidden');
            });
    }

    function openModalFromHit(hit) {
        var href = hit.getAttribute('data-href');
        var container = hit.getAttribute('data-container') || '.view_modal';
        if (!href || !window.jQuery) {
            return;
        }
        window.jQuery.ajax({
            url: href,
            dataType: 'html',
            success: function (result) {
                window.jQuery(container).html(result).modal('show');
            },
        });
    }

    function hideDesktopDropdown() {
        var r = document.getElementById('universal_search_results');
        if (r) {
            r.classList.add('tw-hidden');
        }
        navState.desktop.idx = -1;
    }

    /**
     * One compact row for scope + AI (avoids stacking tall blocks over the navbar).
     */
    function syncPinButtons() {
        ['searchbox-pin-d', 'searchbox-pin-m'].forEach(function (id) {
            var b = document.getElementById(id);
            if (!b) {
                return;
            }
            var inp =
                id === 'searchbox-pin-m'
                    ? document.getElementById('mobile_search_input')
                    : document.getElementById('universal_search_input');
            if (!inp) {
                return;
            }
            var q = inp.value.trim();
            var on = q.length >= minLenForMode() && isPinnedQuery(q);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
            b.classList.toggle('tw-text-amber-500', on);
            b.classList.toggle('tw-text-gray-400', !on);
        });
    }

    function injectClearButton(input, results) {
        if (!cfg().enableClearButton) {
            return;
        }
        var mobile = input.id === 'mobile_search_input';
        var cid = mobile ? 'searchbox-clear-m' : 'searchbox-clear-d';
        if (document.getElementById(cid)) {
            return;
        }
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = cid;
        btn.className =
            'tw-absolute tw-z-20 tw-inline-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-text-gray-500 hover:tw-bg-gray-100 hover:tw-text-gray-800 ' +
            (mobile ? 'tw-right-2 tw-top-1/2 -tw-translate-y-1/2' : 'tw-right-12 tw-top-1/2 -tw-translate-y-1/2');
        btn.setAttribute('aria-label', t('clear_input', 'Clear'));
        btn.innerHTML =
            '<svg class="tw-h-4 tw-w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            input.value = '';
            results.innerHTML = '';
            results.classList.add('tw-hidden');
            input.focus();
            syncPinButtons();
        });
        function syncVis() {
            btn.style.display = input.value.trim() ? 'inline-flex' : 'none';
        }
        input.addEventListener('input', syncVis);
        input.addEventListener('focus', syncVis);
        syncVis();
        input.parentNode.appendChild(btn);
    }

    function injectSearchToolbar(beforeEl, scopeSelectId) {
        var mobile = beforeEl.id === 'mobile_search_input';
        var barId = mobile ? 'searchbox-toolbar-mobile' : 'searchbox-toolbar-desktop';
        if (document.getElementById(barId)) {
            return;
        }

        var scopes = cfg().scopes;
        var showScope = scopes && scopes.length > 0;
        var showAi = cfg().aiUiEnabled;
        var showCopy = cfg().enableCopyQuery;
        var showPin = cfg().enablePinned;
        if (!showScope && !showAi && !showCopy && !showPin) {
            return;
        }

        var bar = document.createElement('div');
        bar.id = barId;
        bar.className =
            'searchbox-inline-toolbar tw-mb-2 tw-flex tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-1.5 tw-w-full tw-min-w-0';

        if (showScope) {
            var lab = document.createElement('span');
            lab.className = 'tw-text-xs tw-text-gray-600 tw-shrink-0';
            lab.textContent = t('scope_label', 'Search in');
            var sel = document.createElement('select');
            sel.id = scopeSelectId;
            sel.className =
                'searchbox-scope-select tw-min-w-0 tw-max-w-[11rem] sm:tw-max-w-[14rem] tw-flex-1 tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-py-1 tw-pl-2 tw-pr-6 tw-text-xs tw-text-gray-800 focus:tw-border-primary-500 focus:tw-outline-none';
            scopes.forEach(function (s) {
                var opt = document.createElement('option');
                opt.value = s.value != null ? s.value : '';
                opt.textContent = s.label || s.value || '—';
                sel.appendChild(opt);
            });
            sel.value = scopeValue;
            sel.addEventListener('change', function () {
                scopeValue = sel.value;
                var other = mobile ? 'searchbox_scope_desktop' : 'searchbox_scope_mobile';
                var o = document.getElementById(other);
                if (o) {
                    o.value = scopeValue;
                }
                var desk = document.getElementById('universal_search_input');
                var mob = document.getElementById('mobile_search_input');
                var active = document.activeElement;
                if (active === desk && desk && desk.value.trim().length >= minLenForMode()) {
                    runSearch(desk.value, document.getElementById('universal_search_results'), desk);
                } else if (active === mob && mob && mob.value.trim().length >= minLenForMode()) {
                    runSearch(mob.value, document.getElementById('mobile_search_results'), mob);
                }
            });
            var scopeWrap = document.createElement('div');
            scopeWrap.className = 'tw-flex tw-min-w-0 tw-flex-1 tw-items-center tw-gap-2 sm:tw-max-w-md';
            scopeWrap.appendChild(lab);
            scopeWrap.appendChild(sel);
            bar.appendChild(scopeWrap);
        }

        if (showAi) {
            var tid = mobile ? 'searchbox_ai_m' : 'searchbox_ai_d';
            var aiWrap = document.createElement('label');
            aiWrap.className =
                'tw-inline-flex tw-shrink-0 tw-cursor-pointer tw-items-center tw-gap-1.5 tw-whitespace-nowrap tw-text-xs tw-font-medium tw-text-violet-700';
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.id = tid;
            cb.checked = isAiMode();
            cb.addEventListener('change', function () {
                setAiMode(cb.checked);
                var resId = mobile ? 'mobile_search_results' : 'universal_search_results';
                if (beforeEl.value.trim().length >= minLenForMode()) {
                    runSearch(beforeEl.value, document.getElementById(resId), beforeEl);
                }
            });
            aiWrap.appendChild(cb);
            var aiSpan = document.createElement('span');
            aiSpan.innerHTML = '<span aria-hidden="true">&#10024;</span> ' + escapeHtml(t('ai_mode_label', 'AI search'));
            aiWrap.appendChild(aiSpan);
            bar.appendChild(aiWrap);
        }

        if (showCopy) {
            var copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className =
                'tw-inline-flex tw-shrink-0 tw-items-center tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-px-2 tw-py-1 tw-text-[11px] tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50';
            copyBtn.textContent = t('copy_query', 'Copy');
            copyBtn.setAttribute('title', t('copy_query', 'Copy query'));
            copyBtn.addEventListener('click', function () {
                var q = beforeEl.value.trim();
                if (!q || !navigator.clipboard || !navigator.clipboard.writeText) {
                    return;
                }
                var orig = copyBtn.textContent;
                navigator.clipboard.writeText(q).then(function () {
                    copyBtn.textContent = t('copied', 'Copied');
                    setTimeout(function () {
                        copyBtn.textContent = orig;
                    }, 1600);
                });
            });
            bar.appendChild(copyBtn);
        }

        if (showPin) {
            var pinBtn = document.createElement('button');
            pinBtn.type = 'button';
            pinBtn.id = mobile ? 'searchbox-pin-m' : 'searchbox-pin-d';
            pinBtn.className =
                'tw-inline-flex tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-amber-200 tw-bg-white tw-px-2 tw-py-1 tw-text-sm tw-leading-none tw-text-gray-400 hover:tw-bg-amber-50';
            pinBtn.innerHTML = '&#9733;';
            pinBtn.setAttribute('aria-label', t('pin_search', 'Pin this search'));
            pinBtn.setAttribute('title', t('pin_search', 'Pin this search'));
            pinBtn.addEventListener('click', function () {
                var q = beforeEl.value.trim();
                if (q.length < minLenForMode()) {
                    return;
                }
                togglePinnedForQuery(q);
                syncPinButtons();
            });
            bar.appendChild(pinBtn);
        }

        if (beforeEl.parentNode) {
            beforeEl.parentNode.insertBefore(bar, beforeEl.parentNode.firstChild);
        }
        syncPinButtons();
    }

    function attachInput(inputId, resultsId) {
        var input = document.getElementById(inputId);
        var results = document.getElementById(resultsId);
        if (!input || !results) {
            return;
        }

        var sid = inputId === 'mobile_search_input' ? 'searchbox_scope_mobile' : 'searchbox_scope_desktop';
        if (!document.getElementById(inputId === 'mobile_search_input' ? 'searchbox-toolbar-mobile' : 'searchbox-toolbar-desktop')) {
            injectSearchToolbar(input, sid);
        }
        injectClearButton(input, results);

        var debounced = debounce(function () {
            runSearch(input.value, results, input);
        }, cfg().debounceMs);

        input.addEventListener('input', debounced);
        input.addEventListener('input', syncPinButtons);
        input.addEventListener('focus', function () {
            var v = input.value.trim();
            if (v.length >= minLenForMode()) {
                debounced();
            } else if ((cfg().enableRecent || cfg().enablePinned) && v.length === 0 && !isAiMode()) {
                renderRecentPanel(results, input, function (q) {
                    debounced();
                });
            }
        });

        if (cfg().enableKeyboardNav) {
            input.addEventListener('keydown', function (e) {
                var key = e.key || e.keyCode;
                var down = key === 'ArrowDown' || key === 40;
                var up = key === 'ArrowUp' || key === 38;
                var ent = key === 'Enter' || key === 13;
                var esc = key === 'Escape' || key === 27;
                if (!down && !up && !ent && !esc) {
                    return;
                }
                var ck = ctxKey(inputId);
                var hits = getHits(results);
                if (results.classList.contains('tw-hidden') || !hits.length) {
                    if (esc) {
                        hideDesktopDropdown();
                        if (inputId === 'mobile_search_input' && window.jQuery) {
                            window.jQuery('#mobile_search_overlay').modal('hide');
                        }
                    }
                    return;
                }
                if (esc) {
                    e.preventDefault();
                    results.innerHTML = '';
                    results.classList.add('tw-hidden');
                    navState[ck].idx = -1;
                    if (inputId === 'mobile_search_input' && window.jQuery) {
                        window.jQuery('#mobile_search_overlay').modal('hide');
                    } else {
                        hideDesktopDropdown();
                    }
                    return;
                }
                if (ent) {
                    if (navState[ck].idx >= 0 && hits[navState[ck].idx]) {
                        e.preventDefault();
                        hits[navState[ck].idx].click();
                    }
                    return;
                }
                e.preventDefault();
                if (down) {
                    navState[ck].idx = Math.min(navState[ck].idx + 1, hits.length - 1);
                } else if (up) {
                    navState[ck].idx = navState[ck].idx <= 0 ? -1 : navState[ck].idx - 1;
                }
                setHitActive(results, navState[ck].idx);
            });
        }
    }

    document.addEventListener('click', function (e) {
        var chip = e.target.closest('.searchbox-cat-chip');
        if (chip) {
            var panel = chip.closest('#universal_search_results, #mobile_search_results');
            if (panel) {
                e.preventDefault();
                applyCategoryFilter(panel, chip.getAttribute('data-filter') || '__all');
                return;
            }
        }

        var hit = e.target.closest('.universal-search-hit');
        if (hit) {
            var qSave = '';
            if (hit.closest('#universal_search_results')) {
                var di = document.getElementById('universal_search_input');
                qSave = di ? di.value.trim() : '';
            } else if (hit.closest('#mobile_search_results')) {
                var mix = document.getElementById('mobile_search_input');
                qSave = mix ? mix.value.trim() : '';
            }
            if (qSave) {
                pushRecent(qSave);
            }
        }
        if (hit && hit.getAttribute('data-modal') === '1') {
            e.preventDefault();
            openModalFromHit(hit);
            hideDesktopDropdown();
            var mr = document.getElementById('mobile_search_results');
            if (mr) {
                mr.innerHTML = '';
            }
            var overlay = document.getElementById('mobile_search_overlay');
            if (overlay && window.jQuery) {
                window.jQuery(overlay).modal('hide');
            }
            var mobileInput = document.getElementById('mobile_search_input');
            if (mobileInput) {
                mobileInput.value = '';
            }
            return;
        }

        var wrap = document.getElementById('universal_search_input');
        if (wrap) {
            wrap = wrap.closest('.tw-relative');
        }
        if (wrap && !wrap.contains(e.target)) {
            hideDesktopDropdown();
        }
    });

    document.addEventListener('keydown', function (e) {
        var key = e.key || e.keyCode;
        var isK = key === 'k' || key === 'K' || key === 75;
        if (!(e.ctrlKey || e.metaKey) || !isK) {
            return;
        }
        var desk = document.getElementById('universal_search_input');
        if (desk && cfg().desktopSearchEnabled) {
            var floatBar = document.getElementById('searchbox-desktop-float');
            var desktopUsable = false;
            if (floatBar) {
                desktopUsable = window.getComputedStyle(floatBar).display !== 'none';
            } else {
                var w = desk.closest('.tw-relative');
                desktopUsable = !!(w && window.getComputedStyle(w).display !== 'none');
            }
            if (desktopUsable) {
                e.preventDefault();
                desk.focus();
                return;
            }
        }
        if (
            cfg().mobileFloatingEnabled &&
            window.jQuery &&
            document.getElementById('mobile_search_overlay')
        ) {
            e.preventDefault();
            window.jQuery('#mobile_search_overlay').modal('show');
            setTimeout(function () {
                var mi = document.getElementById('mobile_search_input');
                if (mi) {
                    mi.focus();
                }
            }, 350);
        }
    });

    /**
     * Move desktop search into .nextzen_header second row (below logo row).
     */
    function mountDesktopSearchInHeader() {
        if (!cfg().desktopSearchEnabled) {
            return;
        }
        var floatEl = document.getElementById('searchbox-desktop-float');
        if (!floatEl || floatEl.getAttribute('data-searchbox-header-mount') !== '1') {
            return;
        }
        var wrapOk = document.getElementById('searchbox-header-search-row');
        if (floatEl.getAttribute('data-sb-mounted') === '1' && wrapOk && wrapOk.contains(floatEl)) {
            return;
        }
        if (floatEl.getAttribute('data-sb-mounted') === '1') {
            floatEl.removeAttribute('data-sb-mounted');
        }

        var header = document.querySelector('.nextzen_header');
        if (!header) {
            return;
        }

        var legacy = document.getElementById('searchbox-header-center-slot');
        if (legacy) {
            legacy.remove();
        }

        var shell = null;
        var hc = header.children;
        for (var k = 0; k < hc.length; k++) {
            if (hc[k].classList && hc[k].classList.contains('tw-px-5')) {
                shell = hc[k];
                break;
            }
        }
        if (!shell) {
            return;
        }
        var row = null;
        var sk = shell.children;
        for (var i = 0; i < sk.length; i++) {
            var el = sk[i];
            if (
                el.classList &&
                el.classList.contains('tw-flex') &&
                el.classList.contains('tw-justify-between')
            ) {
                row = el;
                break;
            }
        }
        if (!row) {
            return;
        }

        var wrap = document.getElementById('searchbox-header-search-row');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = 'searchbox-header-search-row';
            wrap.className =
                'searchbox-header-search-row tw-w-full tw-max-w-full tw-overflow-visible tw-border-t tw-border-white/15 tw-pt-2.5 tw-pb-1.5';
            wrap.setAttribute('role', 'search');
            if (row.nextSibling) {
                shell.insertBefore(wrap, row.nextSibling);
            } else {
                shell.appendChild(wrap);
            }
        }
        wrap.appendChild(floatEl);

        header.classList.add('searchbox-header-has-search');

        floatEl.classList.remove('tw-hidden');
        floatEl.classList.add('md:tw-block');
        floatEl.setAttribute('data-sb-mounted', '1');
    }

    function initMobile() {
        if (!cfg().mobileFloatingEnabled) {
            return;
        }
        if (!window.jQuery) {
            return;
        }
        var $ = window.jQuery;
        var btn = document.getElementById('mobile_search_btn');
        if (btn) {
            btn.addEventListener('click', function () {
                $('#mobile_search_overlay').modal('show');
                setTimeout(function () {
                    var mi = document.getElementById('mobile_search_input');
                    if (mi) {
                        mi.focus();
                    }
                }, 300);
            });
        }
        var closeBtn = document.getElementById('mobile_search_close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                $('#mobile_search_overlay').modal('hide');
            });
        }
    }

    function init() {
        if (!getRoute()) {
            return;
        }
        mountDesktopSearchInHeader();
        syncAiCheckboxes(isAiMode());
        updateInputPlaceholders();
        attachInput('universal_search_input', 'universal_search_results');
        attachInput('mobile_search_input', 'mobile_search_results');
        updateInputPlaceholders();
        syncPinButtons();
        initMobile();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
