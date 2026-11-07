/**
 * POS Offline Mode — Sale, Customer queuing, and Product caching
 */

var POS_OFFLINE_DB_NAME = 'jadid_pos_offline_db';
var POS_OFFLINE_DB_VERSION = 2;
var posOfflineDbInstance = null;

var OFFLINE_CACHE_MAX_AGE_MS = 10 * 60 * 1000; // 10 minutes
var OFFLINE_CACHE_LAST_SYNC_KEY = 'jadid_pos_offline_last_sync';

function isCacheStale() {
    var lastSync = parseInt(localStorage.getItem(OFFLINE_CACHE_LAST_SYNC_KEY) || '0', 10);
    return (Date.now() - lastSync) > OFFLINE_CACHE_MAX_AGE_MS;
}

// ─── IndexedDB ───────────────────────────────────────────────────────────────

function openPosOfflineDb() {
    if (posOfflineDbInstance) {
        return Promise.resolve(posOfflineDbInstance);
    }

    return new Promise(function(resolve, reject) {
        var request = indexedDB.open(POS_OFFLINE_DB_NAME, POS_OFFLINE_DB_VERSION);

        request.onupgradeneeded = function(event) {
            var db = event.target.result;

            if (!db.objectStoreNames.contains('products')) {
                db.createObjectStore('products', { keyPath: 'variation_id' });
            }

            if (!db.objectStoreNames.contains('customers')) {
                db.createObjectStore('customers', { keyPath: 'id' });
            }

            if (!db.objectStoreNames.contains('offline_sales_queue')) {
                var salesStore = db.createObjectStore('offline_sales_queue', { keyPath: 'local_id' });
                salesStore.createIndex('is_synced', 'is_synced', { unique: false });
                salesStore.createIndex('sync_status', 'sync_status', { unique: false });
            }

            if (!db.objectStoreNames.contains('offline_contacts_queue')) {
                var contactStore = db.createObjectStore('offline_contacts_queue', { keyPath: 'offline_id' });
                contactStore.createIndex('is_synced', 'is_synced', { unique: false });
                contactStore.createIndex('sync_status', 'sync_status', { unique: false });
            }
        };

        request.onsuccess = function(event) {
            posOfflineDbInstance = event.target.result;
            posOfflineDbInstance.onversionchange = function() {
                posOfflineDbInstance.close();
                posOfflineDbInstance = null;
            };
            posOfflineDbInstance.onclose = function() {
                posOfflineDbInstance = null;
            };
            resolve(posOfflineDbInstance);
        };

        request.onerror = function(event) {
            reject(event.target.error);
        };

        request.onblocked = function() {
            toastr.warning('Please close other POS tabs and refresh to complete database upgrade.');
        };
    });
}

// ─── Init ─────────────────────────────────────────────────────────────────────

window._isOnlineReal = window.navigator.onLine;

function updateNetworkUiState(isOnline) {
    var wasOnline = window._isOnlineReal;
    window._isOnlineReal = isOnline;
    var badge = $('#pos-network-status-badge');
    var banner = $('#offline-mode-banner');

    if (isOnline) {
        if (badge.length) {
            badge.removeClass('tw-bg-amber-600').addClass('tw-bg-[#059669]');
            badge.find('.status-text').text('Online Mode Active');
            badge.find('.status-dot').css('background', '#ffffff');
        }
        if (banner.length) {
            banner.addClass('hide').slideUp(200);
        }
        $('#pos_offline_save_btn').hide();
    } else {
        if (badge.length) {
            badge.removeClass('tw-bg-[#059669]').addClass('tw-bg-amber-600');
            badge.find('.status-text').text('Offline Mode Active');
            badge.find('.status-dot').css('background', '#fef3c7');
        }
        if (banner.length) {
            banner.removeClass('hide').slideDown(200);
        }
        $('#pos_offline_save_btn').show();
    }
}

function checkRealConnectivity() {
    if (!window.navigator.onLine) {
        updateNetworkUiState(false);
        return Promise.resolve(false);
    }

    var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
    var signal = controller ? controller.signal : undefined;
    var timeoutId = setTimeout(function() {
        if (controller) controller.abort();
    }, 2500);

    return fetch('https://business.jadidkhata.com/api/sync/ping?t=' + Date.now(), {
        method: 'GET',
        mode: 'cors',
        cache: 'no-store',
        signal: signal
    }).then(function(res) {
        clearTimeout(timeoutId);
        var online = res.status >= 200 && res.status < 400;
        updateNetworkUiState(online);
        return online;
    }).catch(function() {
        clearTimeout(timeoutId);
        updateNetworkUiState(false);
        return false;
    });
}

function initializePosOfflineMode() {
    if (!('indexedDB' in window)) {
        return;
    }

    checkRealConnectivity();
    setInterval(checkRealConnectivity, 5000);

    $(document).on('click', '#pos-network-status-badge', function() {
        if (typeof toastr !== 'undefined') toastr.info('Checking cloud connection...');
        checkRealConnectivity().then(function(isOnline) {
            if (isOnline) {
                if (typeof toastr !== 'undefined') toastr.success('Connected to cloud server (Online Mode).');
            } else {
                if (typeof toastr !== 'undefined') toastr.warning('Cloud server unreachable (Offline Mode Active).');
            }
        });
    });

    openPosOfflineDb()
        .then(function() {
            ensureSyncButtonElement();
            refreshOfflineUiState();

            if (window._isOnlineReal) {
                setTimeout(function() {
                    if (isCacheStale()) {
                        syncOfflineMasterData();
                    }
                }, 5000);
                scheduleOfflineCacheAutoRefresh();
            } else {
                getOfflineProductsFromIndexedDb().then(function(products) {
                    if (products && products.length) {
                        window.cached_product_list = products;
                    }
                }).catch(function() {});
            }
        })
        .catch(function() {});

    var _onlineDebounceTimer = null;
    window.addEventListener('online', function() {
        clearTimeout(_onlineDebounceTimer);
        _onlineDebounceTimer = setTimeout(function() {
            checkRealConnectivity().then(function(isOnline) {
                if (isOnline) {
                    if (typeof toastr !== 'undefined') toastr.info('Internet connection restored. Online mode active.');
                    syncOfflineMasterData();
                    scheduleOfflineCacheAutoRefresh();
                    refreshOfflineUiState();
                }
            });
        }, 1000);
    });

    window.addEventListener('offline', function() {
        updateNetworkUiState(false);
        stopOfflineCacheAutoRefresh();
        refreshOfflineUiState();
        if (typeof toastr !== 'undefined') toastr.warning('Offline mode active. Sales will be saved to IndexedDB cache.');
    });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function force_zero_pos_totals_ui() {
    var zero = __currency_trans_from_en(0, true);
    $('span#total_quantity_span, span.subtotal_text, span#total_subtotal_text, span#total_discount_text, ' +
      'span#total_tax_text, span#final_total_text').text(zero);
}

function serializeFormToObject(form) {
    var data = {};
    $(form).serializeArray().forEach(function(item) {
        if (Object.prototype.hasOwnProperty.call(data, item.name)) {
            if (!Array.isArray(data[item.name])) {
                data[item.name] = [data[item.name]];
            }
            data[item.name].push(item.value);
        } else {
            data[item.name] = item.value;
        }
    });
    return data;
}

function formatOfflineSaleTime(timestamp) {
    if (!timestamp) return 'Unknown time';
    var date = new Date(timestamp);
    var now = new Date();
    var diffMs = now - date;
    var diffMins = Math.floor(diffMs / 60000);
    var diffHours = Math.floor(diffMs / 3600000);
    var diffDays = Math.floor(diffMs / 86400000);
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return diffMins + ' min' + (diffMins > 1 ? 's' : '') + ' ago';
    if (diffHours < 24) return diffHours + ' hour' + (diffHours > 1 ? 's' : '') + ' ago';
    if (diffDays < 7) return diffDays + ' day' + (diffDays > 1 ? 's' : '') + ' ago';
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function isOfflineCustomerId(customerId) {
    return typeof customerId === 'string' && customerId.indexOf('offline_contact_') === 0;
}

function replaceSerializedFieldValue(serializedData, fieldName, value) {
    if (typeof serializedData !== 'string') {
        return serializedData;
    }
    var escapedFieldName = fieldName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var replacement = fieldName + '=' + encodeURIComponent(value);
    var pattern = new RegExp('(^|&)' + escapedFieldName + '=[^&]*');
    if (pattern.test(serializedData)) {
        return serializedData.replace(pattern, function(match, prefix) {
            return prefix + replacement;
        });
    }
    return serializedData + (serializedData ? '&' : '') + replacement;
}

// ─── Product Cache ────────────────────────────────────────────────────────────

var _syncInFlight = false;

function syncOfflineMasterData() {
    if (!window.navigator.onLine) return Promise.resolve();
    if (_syncInFlight) return Promise.resolve();

    var locationId = $('input#location_id').val() || $('#select_location_id').val();
    if (!locationId) return Promise.resolve();

    _syncInFlight = true;

    return new Promise(function(resolve) {
        $.ajax({
            method: 'GET',
            url: '/api/pos/offline-bootstrap',
            dataType: 'json',
            data: { location_id: locationId },
            success: function(result) {
                if (result && result.success) {
                    var products = result.products || [];
                    var customers = result.customers || [];
                    window.cached_product_list = products;
                    window.cached_customer_list = customers;
                    localStorage.setItem(OFFLINE_CACHE_LAST_SYNC_KEY, Date.now().toString());
                    Promise.all([
                        storeProductsOffline(products),
                        storeCustomersOffline(customers)
                    ]).then(function() { _syncInFlight = false; resolve(); })
                      .catch(function() { _syncInFlight = false; resolve(); });
                } else {
                    _syncInFlight = false;
                    resolve();
                }
            },
            error: function() { _syncInFlight = false; resolve(); }
        });
    });
}

var _offlineCacheRefreshTimer = null;
var OFFLINE_CACHE_REFRESH_INTERVAL_MS = 5 * 60 * 1000; // 5 minutes

function scheduleOfflineCacheAutoRefresh() {
    stopOfflineCacheAutoRefresh();
    _offlineCacheRefreshTimer = setInterval(function() {
        if (window.navigator.onLine && isCacheStale()) {
            syncOfflineMasterData();
        }
    }, OFFLINE_CACHE_REFRESH_INTERVAL_MS);
}

function stopOfflineCacheAutoRefresh() {
    if (_offlineCacheRefreshTimer !== null) {
        clearInterval(_offlineCacheRefreshTimer);
        _offlineCacheRefreshTimer = null;
    }
}

// Called after every successful online sale so stock quantities stay current
function refreshCacheAfterSale() {
    if (window.navigator.onLine) {
        syncOfflineMasterData();
    }
}

function storeProductsOffline(products) {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['products'], 'readwrite');
            var store = tx.objectStore('products');
            store.clear();
            (products || []).forEach(function(p) {
                if (p && p.variation_id != null) store.put(p);
            });
            tx.oncomplete = function() { resolve(); };
            tx.onerror = function(e) { reject(e.target.error); };
        });
    });
}

function getOfflineProductsFromIndexedDb() {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['products'], 'readonly');
            var req = tx.objectStore('products').getAll();
            req.onsuccess = function() { resolve(req.result || []); };
            req.onerror = function(e) { reject(e.target.error); };
        });
    });
}

function getOfflineProductByVariationId(variationId) {
    // Try in-memory cache first (fastest)
    if (window.cached_product_list && window.cached_product_list.length) {
        var found = window.cached_product_list.find(function(p) {
            return String(p.variation_id) === String(variationId);
        });
        if (found) return Promise.resolve(found);
    }
    // Fall back to IndexedDB
    return getOfflineProductsFromIndexedDb().then(function(products) {
        return (products || []).find(function(p) {
            return String(p.variation_id) === String(variationId);
        }) || null;
    });
}

function searchOfflineProducts(term) {
    var list = window.cached_product_list || [];
    if (!list.length) {
        return getOfflineProductsFromIndexedDb().then(function(products) {
            window.cached_product_list = products || [];
            return filterOfflineProducts(window.cached_product_list, term);
        });
    }
    return Promise.resolve(filterOfflineProducts(list, term));
}

function storeCustomersOffline(customers) {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['customers'], 'readwrite');
            var store = tx.objectStore('customers');
            store.clear();
            (customers || []).forEach(function(c) {
                if (c && c.id != null) store.put(c);
            });
            tx.oncomplete = function() { resolve(); };
            tx.onerror = function(e) { reject(e.target.error); };
        });
    });
}

function getOfflineCustomersFromIndexedDb() {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['customers'], 'readonly');
            var req = tx.objectStore('customers').getAll();
            req.onsuccess = function() { resolve(req.result || []); };
            req.onerror = function(e) { reject(e.target.error); };
        });
    });
}

function searchOfflineCustomers(term) {
    var list = window.cached_customer_list || [];
    if (!list.length) {
        return getOfflineCustomersFromIndexedDb().then(function(customers) {
            window.cached_customer_list = customers || [];
            return filterOfflineCustomers(window.cached_customer_list, term);
        });
    }
    return Promise.resolve(filterOfflineCustomers(list, term));
}

function filterOfflineCustomers(customers, term) {
    var t = (term || '').toLowerCase().trim();
    if (!t) return customers.slice(0, 50);
    return customers.filter(function(c) {
        var hay = ((c.text || c.name || '') + ' ' + (c.mobile || '') + ' ' + (c.supplier_business_name || '') + ' ' + (c.email || '')).toLowerCase();
        return hay.indexOf(t) !== -1;
    }).slice(0, 50);
}

function filterOfflineProducts(products, term) {
    var t = (term || '').toLowerCase().trim();
    if (!t) return products.slice(0, 50);
    return products.filter(function(p) {
        var haystack = ((p.name || '') + ' ' + (p.sku || '') + ' ' + (p.sub_sku || '')).toLowerCase();
        return haystack.indexOf(t) !== -1;
    }).slice(0, 50);
}

function addOfflineProductRow(variationId, quantity) {
    if (!variationId) {
        toastr.error('Product cannot be added offline.');
        return;
    }

    var rowIndex = parseInt($('input#product_row_count').val() || 0);
    var qty = parseFloat(quantity) || 1;
    var existingRow = $("table#pos_table tbody tr").filter(function() { return $(this).find("input.row_variation_id, input[name*='variation_id']").val() == variationId || $(this).data("variation_id") == variationId; });
    if (existingRow.length > 0) {
        var qtyInput = existingRow.find("input.pos_quantity");
        var currentQty = parseFloat(qtyInput.val()) || 0;
        qtyInput.val(currentQty + qty).trigger("change");
        if (typeof pos_total_row === "function") pos_total_row();
        return;
    }

    var appendRow = function(product) {
        if (!product) {
            toastr.error('Product not found in offline cache. Please go online once to refresh product data.');
            return;
        }

        var unitPrice = parseFloat(
            product.variation_group_price || product.selling_price ||
            product.sell_price_inc_tax || product.default_sell_price ||
            product.sell_price || 0
        );
        if (!isFinite(unitPrice)) unitPrice = 0;

        var purchasePrice = parseFloat(product.default_purchase_price || product.purchase_price || 0);
        if (!isFinite(purchasePrice)) purchasePrice = 0;

        var lineTotal = unitPrice * qty;
        var lineProfit = (unitPrice - purchasePrice) * qty;
        var displayName = (product.name || 'Product') + (product.sub_sku ? '<br><small>' + escapeHtml(product.sub_sku) + '</small>' : '');
        var productType = product.product_type || 'single';
        var qtyAvailable = parseFloat(product.qty_available || 0);
        if (!isFinite(qtyAvailable)) qtyAvailable = 0;
        var isStockManaged = Number(product.enable_stock) === 1;
        var isOversellingAllowed = $('input#is_overselling_allowed').length > 0;
        var isSalesOrder = $('#sale_type').length && $('#sale_type').val() === 'sales_order';
        var isDraft = $('#status').length && ($('#status').val() === 'quotation' || $('#status').val() === 'draft');

        if (isStockManaged && !isOversellingAllowed && !isSalesOrder && !isDraft) {
            if (qtyAvailable <= 0) {
                toastr.error(LANG.out_of_stock || 'Product out of stock');
                return;
            }
            if (qty > qtyAvailable) {
                toastr.error('Max available: ' + qtyAvailable);
                return;
            }
        }

        var maxAttr = isStockManaged
            ? ' data-qty_available="' + qtyAvailable + '" data-rule-max-value="' + qtyAvailable + '" data-msg-max-value="Max available: ' + qtyAvailable + '"'
            : '';

        var rowHtml =
            '<tr class="product_row" data-row_index="' + rowIndex + '">' +
            '<td class="text-center" style="width:30px;">' + (rowIndex + 1) + '</td>' +
            '<td style="min-width:220px;max-width:320px;word-break:break-word;">' + displayName +
                '<input type="hidden" class="enable_sr_no" value="0">' +
                '<input type="hidden" class="product_type" name="products[' + rowIndex + '][product_type]" value="' + productType + '">' +
            '</td>' +
            '<td>' +
                '<input type="hidden" name="products[' + rowIndex + '][product_id]" class="form-control product_id" value="' + (product.product_id || '') + '">' +
                '<input type="hidden" name="products[' + rowIndex + '][variation_id]" class="row_variation_id" value="' + product.variation_id + '">' +
                '<input type="hidden" name="products[' + rowIndex + '][enable_stock]" value="' + (isStockManaged ? 1 : 0) + '">' +
                '<input type="hidden" name="products[' + rowIndex + '][product_unit_id]" value="' + (product.unit_id || '') + '">' +
                '<input type="hidden" class="hidden_base_unit_sell_price" value="' + unitPrice + '">' +
                '<input type="hidden" name="products[' + rowIndex + '][base_unit_multiplier]" class="base_unit_multiplier" value="1">' +
                '<div class="input-group input-number">' +
                    '<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>' +
                    '<input type="text" class="form-control pos_quantity input_number mousetrap input_quantity" value="' + qty + '" name="products[' + rowIndex + '][quantity]" data-rule-required="true"' + maxAttr + '>' +
                    '<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>' +
                '</div>' +
            '</td>' +
            '<td>' +
                '<input type="text" name="products[' + rowIndex + '][unit_price]" class="form-control pos_unit_price input_number hide" value="' + unitPrice + '">' +
                '<input type="text" name="products[' + rowIndex + '][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number hide" value="' + unitPrice + '">' +
                '<select name="products[' + rowIndex + '][tax_id]" class="form-control tax_id input-sm hide"><option data-rate="0" value="">Tax: 0%</option></select>' +
                '<select name="products[' + rowIndex + '][line_discount_type]" class="form-control row_discount_type input-sm hide"><option value="fixed">Fixed</option><option value="percentage">%</option></select>' +
                '<input type="text" name="products[' + rowIndex + '][line_discount_amount]" class="form-control row_discount_amount input_number hide" value="0">' +
                '<input type="hidden" name="products[' + rowIndex + '][item_tax]" class="item_tax" value="0">' +
                '<input type="hidden" class="pos_purchase_price" value="' + purchasePrice + '">' +
                '<input type="hidden" class="pos_line_profit" value="' + lineProfit + '">' +
                '<input type="text" class="form-control pos_line_total input_number hide" value="' + lineTotal + '">' +
                '<span class="pos_line_total_text">' + __currency_trans_from_en(lineTotal, true) + '</span>' +
            '</td>' +
            '<td>' +
                '<span class="pos_line_profit_text">' + __currency_trans_from_en(lineProfit, true) + '</span>' +
            '</td>' +
            '<td class="text-center">' +
                '<i class="fa fa-times-circle text-danger pos_remove_row cursor-pointer" aria-hidden="true"></i>' +
            '</td>' +
            '</tr>';

        $('table#pos_table tbody').append(rowHtml);
        $('input#product_row_count').val(rowIndex + 1);

        // Sync empty-state class so the table becomes visible after offline add.
        if (typeof pos_sync_empty_state === 'function') { try { pos_sync_empty_state(); } catch (e) {} }

        var thisRow = $('table#pos_table tbody').find('tr').last();
        if (typeof pos_each_row === 'function') pos_each_row(thisRow);
        if (typeof pos_total_row === 'function') pos_total_row();
        if (typeof round_row_to_iraqi_dinnar === 'function') round_row_to_iraqi_dinnar(thisRow);
        if (typeof __currency_convert_recursively === 'function') __currency_convert_recursively(thisRow);

        if (!$('#__is_mobile').length) {
            $('input#search_product').val('').focus().select();
        }
    };

    getOfflineProductByVariationId(variationId).then(appendRow).catch(function() {
        toastr.error('Could not load product from offline cache. Please refresh the page.');
    });
}

// ─── Sale Queue ───────────────────────────────────────────────────────────────

function getCurrentSaleItemsForOffline() {
    var items = [];
    $('table#pos_table tbody').find('.product_row').each(function() {
        // Grab the visible product name from the row (works regardless of cache).
        var $row = $(this);
        var productName = $.trim($row.find('.pos_product_name').first().text()) || $.trim($row.find('td').eq(1).contents().filter(function(){ return this.nodeType === 3; }).text()) || '';
        items.push({
            product_id: $row.find('.product_id').val() || null,
            variation_id: $row.find('.row_variation_id').val() || null,
            product_name: productName,
            quantity: __read_number($row.find('input.pos_quantity')) || 0,
            unit_price: __read_number($row.find('input.pos_unit_price_inc_tax')) || 0,
            base_unit_multiplier: __read_number($row.find('input.base_unit_multiplier')) || 1
        });
    });
    return items;
}

function collectCurrentSaleVariationQuantities() {
    var map = {};
    $('table#pos_table tbody').find('tr.product_row').each(function() {
        var variationId = String($(this).find('.row_variation_id').val() || '');
        if (!variationId) return;
        var qty = __read_number($(this).find('input.pos_quantity')) || 0;
        map[variationId] = (map[variationId] || 0) + qty;
    });
    return map;
}

function applyLocalSaleStockDeduction(variationQtyMap) {
    if (!variationQtyMap) return;
    Object.keys(variationQtyMap).forEach(function(variationId) {
        var soldQty = parseFloat(variationQtyMap[variationId]) || 0;
        if (soldQty <= 0) return;

        if (window.cached_product_list && window.cached_product_list.length) {
            window.cached_product_list.forEach(function(item) {
                if (String(item.variation_id) === variationId && Number(item.enable_stock) === 1) {
                    item.qty_available = Math.max(0, parseFloat(item.qty_available || 0) - soldQty);
                }
            });
        }

        $('.product_box[data-variation_id="' + variationId + '"]').each(function() {
            var $badge = $(this).find('span.badge[title]').first();
            if (!$badge.length) return;
            var numberText = ($badge.text() || '').replace(/[^0-9.\-]/g, '');
            var currentQty = parseFloat(numberText);
            if (!isFinite(currentQty)) return;
            $badge.text('Qty ' + __number_f(Math.max(0, currentQty - soldQty), false));
        });
    });
}

function queueSaleForOfflineSync(form, serializedData) {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var localId = 'offline_' + Date.now() + '_' + Math.floor(Math.random() * 100000);
            var total = __read_number($('input#final_total_input')) || 0;
            var isCreditSale = $('#is_credit_sale').length ? $('#is_credit_sale').val() == '1' : false;
            var customerId = $('select#customer_id').val() || null;
            var customerText = ($('select#customer_id option:selected').text() || '').trim() || 'Walk-In Customer';

            var saleObject = {
                local_id: localId,
                payload: serializedData,
                items: getCurrentSaleItemsForOffline(),
                customer_id: customerId,
                customer_name: customerText,
                total: total,
                payment_status: isCreditSale ? 'Due' : 'Paid',
                created_at: new Date().toISOString(),
                is_synced: false,
                sync_status: 'pending',
                sync_error: null
            };

            var tx;
            try {
                tx = db.transaction(['offline_sales_queue'], 'readwrite');
            } catch (e) { reject(e); return; }

            tx.oncomplete = function() { resolve(saleObject); };
            tx.onerror = function(event) { reject((event && event.target && event.target.error) || tx.error); };
            tx.onabort = function() { reject(tx.error || new Error('Offline save aborted')); };

            try {
                tx.objectStore('offline_sales_queue').put(saleObject);
            } catch (e) {
                try { tx.abort(); } catch (_) {}
                reject(e);
            }
        });
    });
}

function getAllOfflineSales() {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['offline_sales_queue'], 'readonly');
            var request = tx.objectStore('offline_sales_queue').getAll();
            request.onsuccess = function() { resolve(request.result || []); };
            request.onerror = function(event) { reject(event.target.error); };
        });
    });
}

function getUnsyncedOfflineSales() {
    return getAllOfflineSales().then(function(all) {
        return all.filter(function(s) { return !s.is_synced; });
    });
}

function updateOfflineSaleRecord(localId, updates) {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['offline_sales_queue'], 'readwrite');
            var store = tx.objectStore('offline_sales_queue');
            var req = store.get(localId);
            req.onsuccess = function() {
                var existing = req.result;
                if (!existing) { resolve(); return; }
                Object.keys(updates).forEach(function(k) { existing[k] = updates[k]; });
                store.put(existing);
            };
            tx.oncomplete = function() { resolve(); };
            tx.onerror = function(event) { reject(event.target.error); };
        });
    });
}

function markOfflineSalesSynced(successIds) {
    return Promise.all((successIds || []).map(function(id) {
        return updateOfflineSaleRecord(id, {
            is_synced: true,
            sync_status: 'synced',
            sync_error: null,
            synced_at: new Date().toISOString()
        });
    }));
}

function markOfflineSalesFailed(failedList) {
    return Promise.all((failedList || []).map(function(item) {
        return updateOfflineSaleRecord(item.id, {
            is_synced: false,
            sync_status: 'failed',
            sync_error: item.message || 'Sync failed'
        });
    }));
}

// ─── Receipt ──────────────────────────────────────────────────────────────────

function shouldPrintOfflineCashReceipt(serializedData) {
    try {
        var params = new URLSearchParams(serializedData || '');
        var method = params.get('payment[0][method]');
        var isSuspend = params.get('is_suspend');
        return method === 'cash' && isSuspend !== '1';
    } catch (e) {
        return false;
    }
}

function getOfflinePaperSize() {
    var size = (window.localStorage && window.localStorage.getItem('pos_offline_paper_size')) || '80mm';
    return size === '58mm' ? '58mm' : '80mm';
}

function setOfflinePaperSize(size) {
    if (window.localStorage) {
        window.localStorage.setItem('pos_offline_paper_size', size === '58mm' ? '58mm' : '80mm');
    }
}

function buildOfflineReceiptHtml() {
    var rows = [];
    $('table#pos_table tbody').find('tr.product_row').each(function() {
        var name = $(this).find('td').eq(0).clone().children().remove().end().text().trim();
        var qty = __read_number($(this).find('input.pos_quantity'));
        var unit = __read_number($(this).find('input.pos_unit_price_inc_tax'));
        var subtotal = __read_number($(this).find('input.pos_line_total'));
        rows.push({ name: name, qty: qty, unit: unit, subtotal: subtotal });
    });

    if (!rows.length) return '';

    var paperSize = getOfflinePaperSize();
    var is58mm = paperSize === '58mm';

    var businessName = ($('select#select_location_id option:selected').text() || '').trim() || 'Offline POS';
    var customer = ($('select#customer_id option:selected').text() || '').trim();
    var total = __read_number($('input#final_total_input'));
    var paid = __read_number($('input#total_paying_input'));
    if (paid === 0) paid = total;
    var invoiceNo = 'OFF-' + Date.now().toString().slice(-6);
    var dateText = new Date().toLocaleString();

    var bodyRows = rows.map(function(row) {
        if (is58mm) {
            return '<tr>' +
                '<td style="padding:2px 0;word-break:break-word;">' + escapeHtml(row.name) + '</td>' +
                '<td style="padding:2px 0;text-align:center;">' + row.qty + '</td>' +
                '<td style="padding:2px 0;text-align:right;">' + __currency_trans_from_en(row.subtotal, false) + '</td>' +
                '</tr>';
        }
        return '<tr>' +
            '<td style="padding:3px 0;word-break:break-word;">' + escapeHtml(row.name) + '</td>' +
            '<td style="padding:3px 0;text-align:center;">' + row.qty + '</td>' +
            '<td style="padding:3px 0;text-align:right;">' + __currency_trans_from_en(row.unit, false) + '</td>' +
            '<td style="padding:3px 0;text-align:right;">' + __currency_trans_from_en(row.subtotal, false) + '</td>' +
            '</tr>';
    }).join('');

    var containerWidth = is58mm ? '48mm' : '72mm';
    var fontSize = is58mm ? '10px' : '12px';
    var titleSize = is58mm ? '13px' : '16px';

    var tableHeader = is58mm
        ? '<thead><tr style="border-bottom:1px dashed #000;"><th style="text-align:left;padding-bottom:3px;width:55%;">Item</th><th style="text-align:center;padding-bottom:3px;width:15%;">Qty</th><th style="text-align:right;padding-bottom:3px;width:30%;">Total</th></tr></thead>'
        : '<thead><tr style="border-bottom:1px dashed #000;"><th style="text-align:left;padding-bottom:4px;width:45%;">Item</th><th style="text-align:center;padding-bottom:4px;width:15%;">Qty</th><th style="text-align:right;padding-bottom:4px;width:20%;">Price</th><th style="text-align:right;padding-bottom:4px;width:20%;">Total</th></tr></thead>';

    return '<div class="thermal-receipt" style="width:' + containerWidth + ';max-width:' + containerWidth + ';margin:0 auto;font-family:\'Courier New\', Courier, monospace;font-size:' + fontSize + ';color:#000;line-height:1.2;">' +
        '<div style="text-align:center;margin-bottom:6px;">' +
            '<div style="font-size:' + titleSize + ';font-weight:bold;text-transform:uppercase;">' + escapeHtml(businessName) + '</div>' +
            '<div style="margin-top:2px;">' + (is58mm ? '58mm (2-Inch)' : '80mm (3-Inch)') + ' Thermal Receipt</div>' +
        '</div>' +
        '<div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:3px 0;margin-bottom:4px;font-size:0.9em;">' +
            '<div>Inv #: ' + invoiceNo + '</div>' +
            '<div>Date : ' + dateText + '</div>' +
            '<div>Cust : ' + escapeHtml(customer || 'Walk-In') + '</div>' +
        '</div>' +
        '<table style="width:100%;border-collapse:collapse;table-layout:fixed;margin-bottom:4px;">' +
            tableHeader +
            '<tbody>' + bodyRows + '</tbody>' +
        '</table>' +
        '<div style="border-top:1px dashed #000;padding-top:4px;margin-top:4px;">' +
            '<div style="display:flex;justify-content:space-between;font-weight:bold;"><span>TOTAL:</span><span>' + __currency_trans_from_en(total, false) + '</span></div>' +
            '<div style="display:flex;justify-content:space-between;"><span>PAID:</span><span>' + __currency_trans_from_en(paid, false) + '</span></div>' +
        '</div>' +
        '<div style="text-align:center;margin-top:10px;font-size:0.85em;">' +
            '<div>*** THANK YOU ***</div>' +
            '<div style="font-size:0.8em;color:#444;">(Offline Synchronized)</div>' +
        '</div>' +
    '</div>';
}

// ─── Sync Sales ───────────────────────────────────────────────────────────────

function syncPendingSalesQueue() {
    if (!window.navigator.onLine) {
        toastr.warning('Internet is required to sync pending sales.');
        return Promise.resolve();
    }

    return getUnsyncedOfflineSales().then(function(pendingSales) {
        if (!pendingSales.length) {
            refreshOfflineUiState();
            return;
        }

        var $syncBtn = $('#sync-pending-sales-btn');
        $syncBtn.prop('disabled', true);

        return new Promise(function(resolve) {
            $.ajax({
                method: 'POST',
                url: '/api/pos/sales/bulk-sync',
                contentType: 'application/json',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: JSON.stringify({
                    sales: pendingSales.map(function(sale) {
                        return { id: sale.local_id, payload: sale.payload, created_at: sale.created_at };
                    })
                }),
                success: function(result) {
                    var successIds = result.success_ids || [];
                    var failed = result.failed || [];
                    markOfflineSalesSynced(successIds)
                        .then(function() { return markOfflineSalesFailed(failed); })
                        .then(function() {
                            if (successIds.length) {
                                toastr.success(successIds.length + ' sale(s) synced successfully.');
                            }
                            if (failed.length) {
                                toastr.warning(failed.length + ' sale(s) failed to sync.');
                            }
                            refreshOfflineUiState();
                            resolve();
                        });
                },
                error: function() {
                    toastr.error('Failed to sync pending sales.');
                    resolve();
                },
                complete: function() {
                    $syncBtn.prop('disabled', false);
                }
            });
        });
    });
}

// ─── Contact Queue ────────────────────────────────────────────────────────────

function buildCustomerDisplayName(contact) {
    var nameParts = [
        $.trim(contact.prefix || ''),
        $.trim(contact.first_name || ''),
        $.trim(contact.middle_name || ''),
        $.trim(contact.last_name || '')
    ].filter(Boolean);
    var displayName = $.trim(contact.name || nameParts.join(' '));
    if (!displayName) displayName = $.trim(contact.supplier_business_name || '');
    return displayName;
}

function normalizeOfflineCustomerForPos(contact) {
    if (!contact) return null;
    var normalized = $.extend({}, contact);
    var customerId = normalized.id || normalized.offline_id || null;
    var displayName = buildCustomerDisplayName(normalized);
    normalized.id = customerId;
    normalized.offline_id = normalized.offline_id || (isOfflineCustomerId(customerId) ? customerId : null);
    normalized.name = displayName;
    normalized.text = normalized.text || displayName;
    normalized.balance = parseFloat(normalized.balance || 0) || 0;
    normalized.type = normalized.type || 'customer';
    return normalized;
}

function buildOfflineContactFromForm(form) {
    var formData = serializeFormToObject(form);
    var offlineId = 'offline_contact_' + Date.now() + '_' + Math.floor(Math.random() * 100000);
    var normalizedCustomer = normalizeOfflineCustomerForPos($.extend({}, formData, {
        id: offlineId,
        offline_id: offlineId,
        type: formData.type || 'customer',
        is_offline: true,
        is_synced: false,
        sync_status: 'pending',
        sync_error: null,
        created_at: new Date().toISOString()
    }));

    if (normalizedCustomer && !normalizedCustomer.name && $.trim(formData.first_name || '')) {
        normalizedCustomer.name = $.trim(formData.first_name);
        normalizedCustomer.text = normalizedCustomer.name;
    }
    if (normalizedCustomer) {
        normalizedCustomer.id = normalizedCustomer.id || offlineId;
        normalizedCustomer.offline_id = normalizedCustomer.offline_id || offlineId;
    }
    return normalizedCustomer;
}

function queueCustomerForOfflineSync(customer) {
    var normalizedCustomer = normalizeOfflineCustomerForPos(customer);
    if (!normalizedCustomer || !normalizedCustomer.id) {
        return Promise.reject(new Error('Invalid customer data.'));
    }

    normalizedCustomer.id = normalizedCustomer.id || normalizedCustomer.offline_id;
    normalizedCustomer.offline_id = normalizedCustomer.offline_id || normalizedCustomer.id;

    var contactRecord = $.extend({}, normalizedCustomer, {
        offline_id: normalizedCustomer.offline_id || normalizedCustomer.id,
        is_synced: false,
        sync_status: 'pending',
        sync_error: null,
        created_at: normalizedCustomer.created_at || new Date().toISOString()
    });

    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['offline_contacts_queue'], 'readwrite');
            try {
                tx.objectStore('offline_contacts_queue').put(contactRecord);
            } catch (error) {
                reject(error);
                return;
            }
            tx.oncomplete = function() { resolve(normalizedCustomer); };
            tx.onerror = function(event) { reject(event.target.error); };
        });
    });
}

function getAllOfflineContacts() {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['offline_contacts_queue'], 'readonly');
            var request = tx.objectStore('offline_contacts_queue').getAll();
            request.onsuccess = function() { resolve(request.result || []); };
            request.onerror = function(event) { reject(event.target.error); };
        });
    });
}

function getUnsyncedOfflineContacts() {
    return getAllOfflineContacts().then(function(all) {
        return all.filter(function(c) { return !c.is_synced; });
    });
}

function updateOfflineContactRecord(offlineId, updates) {
    return openPosOfflineDb().then(function(db) {
        return new Promise(function(resolve, reject) {
            var tx = db.transaction(['offline_contacts_queue'], 'readwrite');
            var store = tx.objectStore('offline_contacts_queue');
            var req = store.get(offlineId);
            req.onsuccess = function() {
                var existing = req.result;
                if (!existing) { resolve(); return; }
                Object.keys(updates).forEach(function(k) { existing[k] = updates[k]; });
                store.put(existing);
            };
            tx.oncomplete = function() { resolve(); };
            tx.onerror = function(event) { reject(event.target.error); };
        });
    });
}

function syncPendingOfflineContacts() {
    if (!window.navigator.onLine) return Promise.resolve({});

    return getUnsyncedOfflineContacts().then(function(pendingContacts) {
        if (!pendingContacts.length) return {};

        return new Promise(function(resolve) {
            $.ajax({
                method: 'POST',
                url: '/api/pos/contacts/sync-offline',
                contentType: 'application/json',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: JSON.stringify({ contacts: pendingContacts }),
                success: function(result) {
                    var successList = result.success_ids || [];
                    var failedList = result.failed || [];
                    var mappings = {};

                    successList.forEach(function(item) {
                        mappings[item.offline_id] = item.server_id;
                    });

                    var successTasks = successList.map(function(item) {
                        return updateOfflineContactRecord(item.offline_id, {
                            is_synced: true,
                            sync_status: 'synced',
                            sync_error: null,
                            synced_at: new Date().toISOString(),
                            server_id: item.server_id
                        });
                    });
                    var failedTasks = failedList.map(function(item) {
                        return updateOfflineContactRecord(item.offline_id, {
                            is_synced: false,
                            sync_status: 'failed',
                            sync_error: item.msg || 'Sync failed'
                        });
                    });

                    Promise.all(successTasks.concat(failedTasks))
                        .then(function() {
                            // Update sale payloads: replace offline customer IDs with server IDs
                            return updateOfflineSaleCustomerReferences(mappings);
                        })
                        .then(function() { resolve(mappings); })
                        .catch(function() { resolve(mappings); });
                },
                error: function() {
                    toastr.error('Failed to sync offline customers.');
                    resolve({});
                }
            });
        });
    });
}

function updateOfflineSaleCustomerReferences(mappings) {
    if (!mappings || !Object.keys(mappings).length) return Promise.resolve();

    return getUnsyncedOfflineSales().then(function(pendingSales) {
        var tasks = [];
        pendingSales.forEach(function(sale) {
            var mappedId = mappings[sale.customer_id];
            if (!mappedId) return;
            var updatedPayload = sale.payload;
            if (typeof updatedPayload === 'string') {
                updatedPayload = replaceSerializedFieldValue(updatedPayload, 'contact_id', mappedId);
            } else if (updatedPayload && typeof updatedPayload === 'object') {
                updatedPayload.contact_id = mappedId;
            }
            tasks.push(updateOfflineSaleRecord(sale.local_id, { customer_id: mappedId, payload: updatedPayload }));
        });
        return Promise.all(tasks);
    });
}

// ─── UI ───────────────────────────────────────────────────────────────────────

function ensureSyncButtonElement() {
    if ($('#sync-pending-sales-btn').length > 0) return;

    var btnHtml = '<button type="button" id="sync-pending-sales-btn" class="hide" ' +
        'style="position:fixed;right:20px;bottom:80px;z-index:9999;background:#0d9488;color:#fff;' +
        'border:none;border-radius:9999px;padding:10px 16px;font-weight:700;cursor:pointer;">' +
        '<i class="fas fa-sync"></i> Sync Pending Sales (<span id="offline-unsynced-count">0</span>)' +
        '</button>';
    $('body').append(btnHtml);

    $(document).on('click', '#sync-pending-sales-btn', function() {
        showOfflineSalesSyncModal();
    });
}

function refreshOfflineUiState() {
    ensureSyncButtonElement();
    getUnsyncedOfflineSales()
        .then(function(unsyncedSales) {
            var count = unsyncedSales.length;
            window.__has_pending_offline_sales = count > 0;
            $('#offline-unsynced-count').text(count);
            $('.offline-unsynced-count').text(count);

            var isOnline = (typeof window._isOnlineReal !== 'undefined') ? window._isOnlineReal : window.navigator.onLine;

            // Show the sync button whenever there are queued sales, even offline,
            // so cashier always sees how many are waiting. Online lets them click to sync.
            if (count > 0) {
                $('#sync-pending-sales-btn').removeClass('hide').show().css('opacity', isOnline ? '1' : '0.85');
                var label = isOnline ? 'Sync Pending Sales' : 'Pending Sales (offline)';
                $('#sync-pending-sales-btn').find('span#offline-unsynced-count').parent().contents().filter(function(){ return this.nodeType === 3; }).first().replaceWith(' ' + label + ' (');
            } else {
                $('#sync-pending-sales-btn').addClass('hide');
            }

            if (isOnline) {
                $('#offline-mode-banner').addClass('hide').slideUp(150);
            } else {
                $('#offline-mode-banner').removeClass('hide').slideDown(150);
            }
        })
        .catch(function() {});
}

// ─── Sync Modal ───────────────────────────────────────────────────────────────

function showOfflineSalesSyncModal() {
    $('#offline_sales_sync_modal').modal('show');
    loadOfflineSalesForModal();
}

function loadOfflineSalesForModal() {
    $('#offline-sales-container').html(
        '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading offline sales...</p></div>'
    );

    getAllOfflineSales().then(function(allSales) {
        var pendingSales = allSales.filter(function(s) { return !s.is_synced; });
        if (!pendingSales.length) {
            $('#offline-sales-container').addClass('hide');
            $('#no-offline-sales').removeClass('hide');
            return;
        }
        $('#offline-sales-container').removeClass('hide');
        $('#no-offline-sales').addClass('hide');
        renderOfflineSalesList(pendingSales);
        setupOfflineModalEventHandlers();
    }).catch(function() {
        $('#offline-sales-container').html(
            '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Failed to load offline sales. Please try again.</div>'
        );
    });
}

function renderOfflineSalesList(sales) {
    var html = '';
    sales.forEach(function(sale) {
        var statusClass = sale.sync_status === 'failed' ? 'status-failed' : 'status-pending';
        var statusText = sale.sync_status === 'failed' ? 'Failed' : 'Pending';
        var paymentMethod = sale.payment_status === 'Due' ? 'Credit Sale' : 'Paid';

        var itemsHtml = '';
        if (sale.items && sale.items.length > 0) {
            itemsHtml = '<div class="offline-sale-items">';
            sale.items.forEach(function(item) {
                var itemTotal = (item.quantity || 0) * (item.unit_price || 0);
                itemsHtml += '<div class="item-row">' +
                    '<span class="item-name">' + escapeHtml(item.product_name || ('Product #' + (item.product_id || '?'))) + '</span>' +
                    '<span class="item-quantity">' + (item.quantity || 0) + 'x</span>' +
                    '<span class="item-price">' + __currency_trans_from_en(itemTotal, true) + '</span>' +
                    '</div>';
            });
            itemsHtml += '</div>';
        }

        var errorHtml = sale.sync_error
            ? '<div class="sync-error-message"><i class="fas fa-exclamation-triangle"></i> ' + escapeHtml(sale.sync_error) + '</div>'
            : '';

        html += '<div class="offline-sale-item selected" data-sale-id="' + escapeHtml(sale.local_id) + '">' +
            '<div class="offline-sale-header">' +
            '<div class="sale-checkbox">' +
            '<input type="checkbox" class="sale-select-checkbox" data-sale-id="' + escapeHtml(sale.local_id) + '" checked> ' +
            '<span class="offline-sale-id">' + escapeHtml(sale.local_id) + '</span>' +
            '</div>' +
            '<div>' +
            '<span class="offline-sale-status ' + statusClass + '">' + statusText + '</span> ' +
            '<span class="offline-sale-time">' + formatOfflineSaleTime(sale.created_at) + '</span>' +
            '</div></div>' +
            '<div class="customer-info"><i class="fas fa-user"></i> ' + escapeHtml(sale.customer_name || 'Walk-In Customer') +
            ' <span class="payment-method">' + paymentMethod + '</span></div>' +
            itemsHtml +
            '<div class="offline-sale-total">' +
            '<span class="total-label">Total:</span>' +
            '<span class="total-amount">' + __currency_trans_from_en(sale.total || 0, true) + '</span>' +
            '</div>' + errorHtml +
            '</div>';
    });

    $('#offline-sales-container').html(html);
    updateOfflineSelectedSalesCount();
}

function setupOfflineModalEventHandlers() {
    $('#select-all-offline-sales').off('change').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.sale-select-checkbox').prop('checked', isChecked);
        $('.offline-sale-item').toggleClass('selected', isChecked);
        updateOfflineSelectedSalesCount();
    });

    $(document).off('change.offlineSale', '.sale-select-checkbox').on('change.offlineSale', '.sale-select-checkbox', function() {
        var saleId = $(this).data('sale-id');
        $('#offline-sales-container').find('.offline-sale-item[data-sale-id="' + saleId + '"]').toggleClass('selected', $(this).is(':checked'));
        updateOfflineSelectedSalesCount();
    });

    $('#sync-selected-sales-btn').off('click').on('click', function() {
        syncSelectedOfflineSales();
    });

    $(document).off('click.offlineSaleItem', '.offline-sale-item').on('click.offlineSaleItem', '.offline-sale-item', function(e) {
        if (e.target.type === 'checkbox') return;
        var $cb = $(this).find('.sale-select-checkbox');
        if ($cb.length) $cb.prop('checked', !$cb.prop('checked')).trigger('change');
    });
}

function updateOfflineSelectedSalesCount() {
    var count = $('.sale-select-checkbox:checked').length;
    $('#selected-sales-count').text(count);
    $('#sync-selected-sales-btn').prop('disabled', count === 0);
    var total = $('.sale-select-checkbox').length;
    $('#select-all-offline-sales').prop('checked', total > 0 && count === total);
}

function syncSelectedOfflineSales() {
    if (!window.navigator.onLine) {
        toastr.warning('Internet connection required to sync sales.');
        return;
    }

    var selectedIds = [];
    $('.sale-select-checkbox:checked').each(function() {
        selectedIds.push($(this).data('sale-id'));
    });

    if (!selectedIds.length) {
        toastr.warning('No sales selected for sync.');
        return;
    }

    getAllOfflineSales().then(function(allSales) {
        var salesToSync = allSales.filter(function(s) {
            return selectedIds.indexOf(s.local_id) !== -1 && !s.is_synced;
        });

        if (!salesToSync.length) {
            toastr.info('No pending sales to sync.');
            return;
        }

        $('#sync-selected-sales-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Syncing...');

        $.ajax({
            method: 'POST',
            url: '/api/pos/sales/bulk-sync',
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: JSON.stringify({
                sales: salesToSync.map(function(s) {
                    return { id: s.local_id, payload: s.payload, created_at: s.created_at };
                })
            }),
            success: function(result) {
                var successIds = result.success_ids || [];
                var failed = result.failed || [];
                markOfflineSalesSynced(successIds)
                    .then(function() { return markOfflineSalesFailed(failed); })
                    .then(function() {
                        if (successIds.length) toastr.success(successIds.length + ' sale(s) synced successfully.');
                        if (failed.length) toastr.warning(failed.length + ' sale(s) failed to sync.');
                        setTimeout(function() {
                            loadOfflineSalesForModal();
                            refreshOfflineUiState();
                        }, 800);
                    });
            },
            error: function() {
                toastr.error('Failed to sync sales. Please try again.');
            },
            complete: function() {
                $('#sync-selected-sales-btn').prop('disabled', false)
                    .html('<i class="fas fa-sync"></i> Sync Selected (<span id="selected-sales-count">0</span>)');
            }
        });
    });
}

// ─── Boot ─────────────────────────────────────────────────────────────────────

$(document).ready(function() {
    initializePosOfflineMode();
});

// ??? OFFLINE: monkey-patch pos_product_row for offline product add ???????????
// When navigator is offline, route clicks on product tiles to the IndexedDB
// cache instead of AJAX. Keep the online path unchanged.
(function () {
    if (typeof window === 'undefined') return;
    if (window.__pos_offline_product_row_patched) return;
    window.__pos_offline_product_row_patched = true;

    function waitForPosProductRow(cb) {
        if (typeof window.pos_product_row === 'function') { cb(); return; }
        var tries = 0;
        var interval = setInterval(function () {
            if (typeof window.pos_product_row === 'function' || tries > 100) {
                clearInterval(interval);
                if (typeof window.pos_product_row === 'function') cb();
            }
            tries++;
        }, 50);
    }

    waitForPosProductRow(function () {
        var originalFn = window.pos_product_row;
        window.pos_product_row = function (variation_id, purchase_line_id, weighing_scale_barcode, quantity) {
            if (!window.navigator.onLine) {
                // Route to offline path. addOfflineProductRow accepts (variationId, quantity).
                var qty = quantity || 1;
                try {
                    addOfflineProductRow(variation_id, qty);
                } catch (e) {
                    console.error('[POS Offline] addOfflineProductRow failed:', e);
                    toastr.error('Could not add product offline. Please check internet.');
                }
                return;
            }
            // Online: original behaviour
            return originalFn.apply(this, arguments);
        };
    });
})();

// ??? OFFLINE: separate button for offline sale save ??????????????????????????
// Instead of hijacking the regular Cash/Card submit, we add a dedicated
// "Offline Save" button that only appears when the browser is offline.
// This keeps the online + offline flows isolated and easy to reason about.
(function () {
    if (typeof window === 'undefined') return;
    if (window.__pos_offline_button_installed) return;
    window.__pos_offline_button_installed = true;

    function installOfflineButton() {
        if (typeof jQuery === 'undefined') { setTimeout(installOfflineButton, 150); return; }
        var $ = jQuery;
        if ($('#pos_offline_save_btn').length) return;
        // Floating button at the bottom-right, visible on both desktop and mobile.
        // We don't rely on finding a Cash/finalize button to attach to, because
        // the DOM structure differs between desktop and mobile POS layouts.
        var btn = $('<button>', {
            type: 'button',
            id: 'pos_offline_save_btn',
            html: '<i class="fas fa-cloud-upload-alt"></i> &nbsp;Offline Save',
            style: 'display:none; position:fixed; right:16px; bottom:20px; z-index:9998; background:#f59e0b; color:#fff; padding:14px 22px; border:none; border-radius:9999px; font-weight:700; cursor:pointer; box-shadow:0 6px 14px rgba(0,0,0,0.25); font-size:15px;',
            title: 'Save this sale locally. It will sync when internet is back.'
        });
        $('body').append(btn);
        btn.on('click', handleOfflineSaveClick);
        toggleOfflineButton();
    }

    function toggleOfflineButton() {
        if (typeof jQuery === 'undefined') return;
        var isOffline = (typeof window._isOnlineReal !== 'undefined') ? !window._isOnlineReal : !window.navigator.onLine;
        if (!$('#pos_offline_paper_selector').length) {
            var currentP = (window.localStorage && window.localStorage.getItem('pos_offline_paper_size')) || '80mm';
            var $selector = $('<select id="pos_offline_paper_selector" class="form-control input-sm" style="display:inline-block;width:auto;margin-left:5px;height:30px;padding:2px 6px;vertical-align:middle;font-weight:bold;">' +
                '<option value="80mm"' + (currentP === '80mm' ? ' selected' : '') + '>Receipt: 80mm (3")</option>' +
                '<option value="58mm"' + (currentP === '58mm' ? ' selected' : '') + '>Receipt: 58mm (2")</option>' +
                '</select>');
            $selector.on('change', function() {
                var chosen = $(this).val();
                if (window.localStorage) {
                    window.localStorage.setItem('pos_offline_paper_size', chosen);
                }
                if (typeof toastr !== 'undefined') {
                    toastr.info('Offline Receipt Paper set to: ' + chosen);
                }
            });
            $('#pos_offline_save_btn').after($selector);
        }
        jQuery('#pos_offline_save_btn').toggle(isOffline);
        // Keep buttons enabled; if offline, clicking them saves locally to IndexedDB
        jQuery('button.pos-express-finalize, button#pos-finalize').prop('disabled', false);
    }

    jQuery(document).on('click', 'button.pos-express-finalize, button#pos-finalize', function(e) {
        var isOffline = (typeof window._isOnlineReal !== 'undefined') ? !window._isOnlineReal : !window.navigator.onLine;
        if (isOffline) {
            e.preventDefault();
            e.stopImmediatePropagation();
            handleOfflineSaveClick();
            return false;
        }
    });

    var _offlineSaveBusy = false;

    function unlockOfflineSaveUi() {
        _offlineSaveBusy = false;
        try { jQuery('#pos_offline_save_btn').prop('disabled', false).css('opacity', ''); } catch (e) {}
        try { jQuery('div.pos-processing').hide(); } catch (e) {}
        try {
            jQuery('#pos-save').removeAttr('disabled').prop('disabled', false);
            jQuery('div.pos-form-actions').find('button').removeAttr('disabled').prop('disabled', false);
            jQuery('button.pos-express-finalize, button#pos-finalize, .pos_form_totals button').removeAttr('disabled').prop('disabled', false).css('opacity', '');
        } catch (e) {}
    }

    function handleOfflineSaveClick() {
        var $ = jQuery;
        if (window.navigator.onLine) {
            toastr.info('Internet is back. Please use the normal Cash button.');
            toggleOfflineButton();
            return;
        }
        if (_offlineSaveBusy) { return; }
        var form = $('form#add_pos_sell_form, form#edit_pos_sell_form').get(0);
        if (!form) { toastr.error('POS form not found.'); return; }
        if ($('table#pos_table tbody .product_row').length === 0) {
            toastr.warning('Add at least one product first.');
            return;
        }

        _offlineSaveBusy = true;
        $('#pos_offline_save_btn').prop('disabled', true).css('opacity', '0.6');

        // Watchdog: agar DB write 8 sec me settle na ho to UI unlock ho jaye.
        var watchdog = setTimeout(function () {
            console.error('[POS Offline] save 8s me complete nahi hui - UI unlock kar raha hoon');
            try { toastr.error('Offline save slow hai. Dobara save karne se pehle offline sales list check karo.'); } catch (e) {}
            unlockOfflineSaveUi();
            try { toggleOfflineButton(); } catch (e) {}
        }, 8000);

        // Ensure a method+amount is set so backend accepts it when synced
        try {
            if ($('input[name="payment[0][amount]"]').length && !$('input[name="payment[0][amount]"]').val()) {
                $('input[name="payment[0][amount]"]').val($('input#final_total_input').val() || '0');
            }
            if ($('select[name="payment[0][method]"]').length && !$('select[name="payment[0][method]"]').val()) {
                $('select[name="payment[0][method]"]').val('cash');
            }
        } catch (e) {}

        var queued;
        try {
            queued = queueSaleForOfflineSync(form, $(form).serialize());
        } catch (err) {
            clearTimeout(watchdog);
            console.error('[POS Offline] queueSaleForOfflineSync threw:', err);
            try { toastr.error('Could not save sale offline.'); } catch (e) {}
            unlockOfflineSaveUi();
            try { toggleOfflineButton(); } catch (e) {}
            return;
        }

        function step(name, fn) {
            try { fn(); } catch (e) { console.error('[POS Offline] ' + name + ' failed:', e); }
        }

        function postSave() {
            step('toast', function () { toastr.success('Sale saved offline. It will sync automatically when internet is back.'); });
            step('refreshOfflineUiState', function () { refreshOfflineUiState(); });
            step('stockDeduction', function () { applyLocalSaleStockDeduction(collectCurrentSaleVariationQuantities()); });
            step('receipt', function () {
                if (window.localStorage && window.localStorage.getItem('pos_offline_no_print') === '1') { return; }
                var html = typeof buildOfflineReceiptHtml === 'function' ? buildOfflineReceiptHtml() : '';
                if (!html) { return; }
                var $iframe = jQuery('#pos_offline_print_iframe');
                if (!$iframe.length) {
                    $iframe = jQuery('<iframe id="pos_offline_print_iframe" style="display:none;position:absolute;width:0;height:0;border:0;"></iframe>').appendTo('body');
                }
                var doc = $iframe[0].contentWindow || $iframe[0].contentDocument;
                if (doc.document) doc = doc.document;
                doc.open();
                var currentPaper = (typeof getOfflinePaperSize === 'function' ? getOfflinePaperSize() : '80mm');
                var pageCss = currentPaper === '58mm'
                    ? '@page { size: 58mm auto; margin: 0; } body { margin: 0; padding: 2mm 1mm; width: 56mm; }'
                    : '@page { size: 80mm auto; margin: 0; } body { margin: 0; padding: 3mm 2mm; width: 78mm; }';
                doc.write('<html><head><title>Offline Receipt</title><style>' +
                    pageCss +
                    ' * { box-sizing: border-box; }' +
                    ' body { font-family: monospace, sans-serif; -webkit-print-color-adjust: exact; }' +
                    ' table { width: 100%; }' +
                    '</style></head><body>' + html + '</body></html>');
                doc.close();
                setTimeout(function() {
                    try {
                        var frameWindow = $iframe[0].contentWindow;
                        frameWindow.focus();
                        frameWindow.print();
                    } catch(e) {
                        console.error('Offline print error:', e);
                    }
                }, 500);
            });
            step('reset_pos_form', function () { if (typeof reset_pos_form === 'function') reset_pos_form(); });
            step('hideModal', function () { $('#modal_payment').modal('hide'); });
        }

        Promise.resolve(queued).then(function (sale) {
            clearTimeout(watchdog);
            console.log('[POS Offline] sale queued:', sale && sale.local_id);
            postSave();
        }, function (err) {
            clearTimeout(watchdog);
            console.error('[POS Offline] queueSaleForOfflineSync failed:', err);
            try { toastr.error('Could not save sale offline.'); } catch (e) {}
        }).then(function () {
            unlockOfflineSaveUi();
            try { toggleOfflineButton(); } catch (e) {}
        });
    }

    window.addEventListener('online', toggleOfflineButton);
    window.addEventListener('offline', toggleOfflineButton);
    installOfflineButton();
})();



// SAFE ENGINE FIX: Real-time Ping & Offline Barcode/Search Support
(function() {
    if (typeof window === "undefined") return;

    var _networkState = window.navigator.onLine;
    var _pingTracker = null;

    function runNetworkHeartbeat() {
        if (!window.navigator.onLine) {
            markAsOffline();
            return;
        }
        var img = new Image();
        img.onload = function() {
            if (!_networkState) {
                _networkState = true;
                window.dispatchEvent(new Event("online"));
            }
        };
        img.onerror = function() {
            markAsOffline();
        };
        img.src = "/favicon.ico?t=" + Date.now();
    }

    function markAsOffline() {
        if (_networkState) {
            _networkState = false;
            window.dispatchEvent(new Event("offline"));
            setHeartbeatInterval(5000);
        }
    }

    function setHeartbeatInterval(delay) {
        clearInterval(_pingTracker);
        _pingTracker = setInterval(runNetworkHeartbeat, delay);
    }

    setHeartbeatInterval(window.navigator.onLine ? 30000 : 5000);
    window.addEventListener("online", function() { setHeartbeatInterval(30000); });
    window.addEventListener("offline", function() { setHeartbeatInterval(5000); });

    function attachOfflineProductSearch() {
        var $searchField = $("input#search_product");
        if (!$searchField.length || !$searchField.data("ui-autocomplete")) {
            setTimeout(attachOfflineProductSearch, 300);
            return;
        }

        var acInstance = $searchField.data("ui-autocomplete");
        var originalSourceHandler = acInstance.options.source;

        acInstance.options.source = function(req, res) {
            var isReallyOnline = window.navigator.onLine && (typeof _networkState === "undefined" || _networkState);
            if (isReallyOnline) {
                return originalSourceHandler.call(this, req, res);
            }

            var query = $.trim(req.term).toLowerCase();
            var cachedProducts = window.cached_product_list || [];

            if (!cachedProducts.length && typeof getOfflineProductsFromIndexedDb === "function") {
                getOfflineProductsFromIndexedDb().then(function(items) {
                    window.cached_product_list = items || [];
                    executeLocalSearch(window.cached_product_list, query, res);
                });
                return;
            }
            executeLocalSearch(cachedProducts, query, res);
        };

        function executeLocalSearch(products, query, res) {
            query = $.trim(query).toLowerCase();
            if (!query) {
                res([]);
                return;
            }

            // 1. Exact SKU/Barcode match first (scanner or exact typed SKU)
            var exactMatch = products.find(function(p) {
                var pSku = $.trim(String(p.sub_sku || p.sku || p.item_code || "")).toLowerCase();
                return pSku === query;
            });

            if (exactMatch) {
                $searchField.val("");
                if (typeof addOfflineProductRow === "function") {
                    addOfflineProductRow(exactMatch.variation_id, 1);
                }
                res([]);
                return;
            }

            // 2. Partial search
            var filtered = products.filter(function(p) {
                var name = String(p.product_name || p.name || "").toLowerCase();
                var sku = String(p.sub_sku || p.sku || p.item_code || "").toLowerCase();
                return name.indexOf(query) > -1 || sku.indexOf(query) > -1;
            });

// Single match auto-add disabled to allow typing

            res(filtered.slice(0, 25).map(function(p) {
                var pSku = p.sub_sku || p.sku || p.item_code || "";
                var pName = p.product_name || p.name || "";
                var pPrice = p.selling_price || p.unit_price || p.default_sell_price || 0;
                var obj = Object.assign({}, p);
                obj.label = pName + (pSku ? " (" + pSku + ")" : "");
                obj.value = pName;
                obj.name = pName;
                obj.product_name = pName;
                obj.sub_sku = pSku;
                obj.selling_price = pPrice;
                obj.qty_available = p.qty_available != null ? p.qty_available : 9999;
                obj.enable_stock = p.enable_stock != null ? p.enable_stock : 0;
                obj.variation_id = p.variation_id;
                return obj;
            }));
        }

        var originalSelectHandler = acInstance.options.select;
        acInstance.options.select = function(evt, ui) {
            if (!window.navigator.onLine && ui.item && ui.item.variation_id) {
                if (typeof addOfflineProductRow === "function") {
                    addOfflineProductRow(ui.item.variation_id, 1);
                }
                $searchField.val("");
                return false;
            }
            if (typeof originalSelectHandler === "function") {
                return originalSelectHandler.call(this, evt, ui);
            }
        };
    }

    $(document).ready(attachOfflineProductSearch);
})();

// Handle Enter key directly in offline mode for instant SKU scan/type



// Direct robust search & barcode listener
$(document).off('keydown.offlineSearch').on('keydown.offlineSearch', 'input#search_product', function(e) {
    if (e.which === 13) {
        var query = $.trim($(this).val()).toLowerCase();
        if (!query) return;

        // Check if offline
        var isOffline = !window.navigator.onLine || (typeof _networkState !== "undefined" && !_networkState);
        if (isOffline) {
            e.preventDefault();
            e.stopPropagation();
            var $input = $(this);

            var doAdd = function(list) {
                if (!list || !list.length) return false;
                var found = list.find(function(p) {
                    var sku = $.trim(String(p.sub_sku || p.sku || p.item_code || "")).toLowerCase();
                    return sku === query;
                }) || list.find(function(p) {
                    var name = $.trim(String(p.product_name || p.name || "")).toLowerCase();
                    var sku = $.trim(String(p.sub_sku || p.sku || p.item_code || "")).toLowerCase();
                    return sku.indexOf(query) > -1 || name.indexOf(query) > -1;
                });

                if (found) {
                    $input.val("");
                    if (typeof addOfflineProductRow === "function") {
                        addOfflineProductRow(found.variation_id, 1);
                    }
                    var ac = $input.data("ui-autocomplete");
                    if (ac) { try { ac.close(); } catch(err){} }
                    return true;
                }
                return false;
            };

            if (window.cached_product_list && window.cached_product_list.length) {
                if (doAdd(window.cached_product_list)) return false;
            }

            if (typeof getOfflineProductsFromIndexedDb === "function") {
                getOfflineProductsFromIndexedDb().then(function(items) {
                    window.cached_product_list = items || [];
                    doAdd(window.cached_product_list);
                });
            }
            return false;
        }
    }
});


// --- BULLETPROOF OFFLINE SEARCH & SCAN (CAPTURE PHASE) ---
(function() {
    function handleOfflineProductLookup(query, clearInputCb) {
        query = (query || "").trim().toLowerCase();
        if (!query) return;

        var runLookup = function(products) {
            if (!products || !products.length) return false;

            // 1. Exact match (Barcode / SKU)
            var match = products.find(function(p) {
                var sku = String(p.sub_sku || p.sku || p.item_code || "").trim().toLowerCase();
                return sku === query;
            });

            // 2. Partial match if exact not found
            if (!match) {
                match = products.find(function(p) {
                    var sku = String(p.sub_sku || p.sku || p.item_code || "").trim().toLowerCase();
                    var name = String(p.product_name || p.name || "").trim().toLowerCase();
                    return sku.indexOf(query) > -1 || name.indexOf(query) > -1;
                });
            }

            if (match) {
                if (typeof clearInputCb === "function") clearInputCb();
                if (typeof addOfflineProductRow === "function") {
                    addOfflineProductRow(match.variation_id, 1);
                }
                return true;
            }
            return false;
        };

        if (window.cached_product_list && window.cached_product_list.length) {
            if (runLookup(window.cached_product_list)) return;
        }

        if (typeof getOfflineProductsFromIndexedDb === "function") {
            getOfflineProductsFromIndexedDb().then(function(items) {
                window.cached_product_list = items || [];
                runLookup(window.cached_product_list);
            });
        }
    }

    // Capture enter key before jQuery UI Autocomplete or Form submits
    document.addEventListener("keydown", function(e) {
        var target = e.target;
        if (!target) return;
        var isSearch = target.id === "search_product" || target.matches("input#search_product") || (target.placeholder && target.placeholder.indexOf("Scan bar code") > -1);
        
        if (isSearch && e.keyCode === 13) {
            var isOffline = !window.navigator.onLine || (typeof _networkState !== "undefined" && !_networkState);
            if (isOffline) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var q = target.value;
                handleOfflineProductLookup(q, function() {
                    target.value = "";
                    if ($(target).data("ui-autocomplete")) {
                        try { $(target).autocomplete("close"); } catch(err){}
                    }
                });
                return false;
            }
        }
    }, true);
})();

// FORCE OFFLINE SEARCH OVERRIDE
(function() {
    $(document).on('input keyup paste', 'input#search_product', function(e) {
        if (window.navigator.onLine && (typeof _networkState === 'undefined' || _networkState)) {
            return;
        }
        var $el = $(this);
        var q = $.trim($el.val()).toLowerCase();
        if (q.length < 2) return;

        var runSearch = function(list) {
            if (!list || !list.length) return;
            var exact = list.find(function(p) {
                var sku = $.trim(String(p.sub_sku || p.sku || p.item_code || "")).toLowerCase();
                return sku === q;
            });
            if (exact && e.which === 13) {
                $el.val('');
                if (typeof addOfflineProductRow === 'function') {
                    addOfflineProductRow(exact.variation_id, 1);
                }
                var ac = $el.data('ui-autocomplete');
                if (ac) { try { ac.close(); } catch(err){} }
            }
        };

        if (window.cached_product_list && window.cached_product_list.length) {
            runSearch(window.cached_product_list);
        } else if (typeof getOfflineProductsFromIndexedDb === 'function') {
            getOfflineProductsFromIndexedDb().then(function(items) {
                window.cached_product_list = items || [];
                runSearch(window.cached_product_list);
            });
        }
    });
})();
