<?php

return [
    'name' => 'Searchbox',
    'module_version' => '1.6',

    /**
     * Desktop header search bar (overridden by DB: searchbox_header_enabled).
     */
    'header_search_enabled' => true,

    /**
     * Mobile floating FAB + overlay (overridden by DB: searchbox_mobile_fab_enabled).
     */
    'mobile_floating_search_enabled' => true,

    /** Minimum characters before searching */
    'min_query_length' => 2,

    /** Input debounce (milliseconds) — overridden client-side by window.searchboxConfig */
    'debounce_ms' => 320,

    /** Recent queries stored in browser localStorage */
    'enable_recent_searches' => true,
    'max_recent_searches' => 10,

    /** Arrow keys + Enter + Escape in results */
    'enable_keyboard_navigation' => true,

    /** Starred queries (localStorage), quick copy, clear input, category chips, CSV export */
    'enable_pinned_searches' => true,
    'max_pinned_searches' => 5,
    'enable_copy_query_button' => true,
    'enable_clear_input_button' => true,
    'enable_category_result_chips' => true,
    'enable_export_results_csv' => true,

    /**
     * Scope filter values (empty string = all). Keys are sent as the `only` query param.
     */
    'scope_keys' => [
        '',
        'products',
        'contacts',
        'users',
        'sales',
        'purchases',
        'payments',
        'expenses',
        'sales_orders',
        'purchase_orders',
        'stock_transfers',
        'stock_adjustments',
        'sale_returns',
        'purchase_returns',
        'repair',
    ],

    /**
     * AI defaults (overridden by Admin → Searchbox AI Config, stored in `system` table).
     */
    'ai' => [
        'enabled' => false,
        'ui_enabled' => true,
        'api_key' => '',
        'base_url' => 'https://api.openai.com/v1',
        'model' => 'gpt-4o-mini',
        'timeout' => 25,
    ],

    /** Min characters for AI search requests */
    'ai_min_query_length' => 2,
];
