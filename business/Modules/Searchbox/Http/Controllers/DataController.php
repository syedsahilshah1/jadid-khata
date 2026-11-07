<?php

namespace Modules\Searchbox\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Menu;
use Nwidart\Modules\Facades\Module;

class DataController extends Controller
{
    public function user_permissions(): array
    {
        return [
            [
                'value' => 'universal_search.access',
                'label' => __('searchbox::lang.universal_search_access'),
                'default' => true,
            ],
        ];
    }

    public function modifyAdminMenu(): void
    {
        $moduleUtil = new ModuleUtil();
        if (! $moduleUtil->isModuleInstalled('Searchbox')) {
            return;
        }

        $menuParent = Menu::instance('admin-sidebar-menu');
        $menuParent->url(
            route('searchbox.ai-config'),
            __('searchbox::lang.menu_ai_config'),
            [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>',
                'active' => request()->segment(1) === 'searchbox' && request()->segment(2) === 'ai-config',
            ]
        )->order(84);
    }

    public function get_additional_script(): array
    {
        if (! auth()->check()) {
            return [
                'additional_js' => '',
                'additional_css' => '',
                'additional_html' => '',
                'additional_views' => [],
            ];
        }

        $desktopOn = (bool) config('searchbox.header_search_enabled', true);
        $mobileOn = (bool) config('searchbox.mobile_floating_search_enabled', true);
        if (! $desktopOn && ! $mobileOn) {
            return [
                'additional_js' => '',
                'additional_css' => '',
                'additional_html' => '',
                'additional_views' => [],
            ];
        }

        $v = (string) config('constants.asset_version', 1);
        $routeJs = json_encode((string) route('searchbox.universal-search'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $aiRouteJs = json_encode((string) route('searchbox.universal-search-ai'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $jsUrl = e(Module::asset('searchbox:js/universal-search.js').'?v='.$v);

        $scopeKeys = config('searchbox.scope_keys', ['']);
        $scopes = [];
        foreach ($scopeKeys as $key) {
            $langKey = $key === '' ? 'scope_all' : 'scope_'.$key;
            $scopes[] = [
                'value' => $key,
                'label' => __('searchbox::lang.'.$langKey),
            ];
        }

        $searchboxConfig = json_encode([
            'desktopSearchEnabled' => $desktopOn,
            'mobileFloatingEnabled' => $mobileOn,
            'minQueryLength' => (int) config('searchbox.min_query_length', 2),
            'aiMinQueryLength' => (int) config('searchbox.ai_min_query_length', 2),
            'debounceMs' => (int) config('searchbox.debounce_ms', 320),
            'maxRecent' => (int) config('searchbox.max_recent_searches', 10),
            'enableRecent' => (bool) config('searchbox.enable_recent_searches', true),
            'enableKeyboardNav' => (bool) config('searchbox.enable_keyboard_navigation', true),
            'enablePinned' => (bool) config('searchbox.enable_pinned_searches', true),
            'maxPinned' => (int) config('searchbox.max_pinned_searches', 5),
            'enableCopyQuery' => (bool) config('searchbox.enable_copy_query_button', true),
            'enableClearButton' => (bool) config('searchbox.enable_clear_input_button', true),
            'enableCategoryChips' => (bool) config('searchbox.enable_category_result_chips', true),
            'enableExportCsv' => (bool) config('searchbox.enable_export_results_csv', true),
            'aiUiEnabled' => (bool) config('searchbox.ai.ui_enabled', true),
            'aiBackendEnabled' => (bool) config('searchbox.ai.enabled', false),
            'scopes' => $scopes,
            'strings' => [
                'recent_searches' => __('searchbox::lang.recent_searches'),
                'no_recent_searches' => __('searchbox::lang.no_recent_searches'),
                'clear_recent' => __('searchbox::lang.clear_recent'),
                'pinned_searches' => __('searchbox::lang.pinned_searches'),
                'pin_search' => __('searchbox::lang.pin_search'),
                'unpin_search' => __('searchbox::lang.unpin_search'),
                'copy_query' => __('searchbox::lang.copy_query'),
                'copied' => __('searchbox::lang.copied'),
                'clear_input' => __('searchbox::lang.clear_input'),
                'filter_all_categories' => __('searchbox::lang.filter_all_categories'),
                'export_csv' => __('searchbox::lang.export_csv'),
                'timing_footer' => __('searchbox::lang.timing_footer'),
                'loading' => __('searchbox::lang.loading'),
                'loading_ai' => __('searchbox::lang.loading_ai'),
                'results_count' => __('searchbox::lang.results_count'),
                'scope_label' => __('searchbox::lang.scope_label'),
                'kbd_hint' => __('searchbox::lang.kbd_hint'),
                'ai_mode_label' => __('searchbox::lang.ai_mode_label'),
                'placeholder_classic' => __('searchbox::lang.search_placeholder'),
                'placeholder_ai' => __('searchbox::lang.search_placeholder_ai'),
            ],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $boot = '<script type="text/javascript">window.universalSearchRoute='.$routeJs.';window.universalSearchAiRoute='.$aiRouteJs.';window.searchboxConfig='.$searchboxConfig.';</script>'
            .'<script src="'.$jsUrl.'"></script>';

        $additionalHtml = '';
        if ($desktopOn) {
            $additionalHtml .= view('searchbox::layouts.partials.floating_desktop')->render();
        }
        if ($mobileOn) {
            $additionalHtml .= view('searchbox::layouts.partials.universal_search_mobile')->render();
        }

        return [
            'additional_js' => $boot,
            'additional_css' => '',
            'additional_html' => $additionalHtml,
            'additional_views' => [],
        ];
    }
}
