<?php

namespace Modules\Searchbox\Http\Controllers;

use App\System;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;

class AiConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorizeConfigAccess();

        $hasKey = ! empty(System::getProperty('searchbox_ai_api_key'));

        return view('searchbox::ai_config.index', [
            'header_search_enabled' => (bool) config('searchbox.header_search_enabled', true),
            'mobile_floating_search_enabled' => (bool) config('searchbox.mobile_floating_search_enabled', true),
            'ai_enabled' => (bool) config('searchbox.ai.enabled', false),
            'ai_ui_enabled' => (bool) config('searchbox.ai.ui_enabled', true),
            'has_stored_api_key' => $hasKey,
            'base_url' => (string) config('searchbox.ai.base_url', 'https://api.openai.com/v1'),
            'model' => (string) config('searchbox.ai.model', 'gpt-4o-mini'),
            'timeout' => (int) config('searchbox.ai.timeout', 25),
            'ai_min_length' => (int) config('searchbox.ai_min_query_length', 2),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorizeConfigAccess();

        $request->validate([
            'ai_api_key' => 'nullable|string|max:500',
            'clear_ai_api_key' => 'nullable|boolean',
            'base_url' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:120',
            'timeout' => 'nullable|integer|min:5|max:120',
            'ai_min_length' => 'nullable|integer|min:1|max:10',
        ]);

        System::addProperty('searchbox_header_enabled', $request->boolean('header_search_enabled') ? '1' : '0');
        System::addProperty('searchbox_mobile_fab_enabled', $request->boolean('mobile_floating_search_enabled') ? '1' : '0');
        System::addProperty('searchbox_ai_enabled', $request->boolean('ai_enabled') ? '1' : '0');
        System::addProperty('searchbox_ai_ui_enabled', $request->boolean('ai_ui_enabled') ? '1' : '0');

        if ($request->boolean('clear_ai_api_key')) {
            System::addProperty('searchbox_ai_api_key', '');
        } elseif ($request->filled('ai_api_key')) {
            System::addProperty('searchbox_ai_api_key', Crypt::encryptString($request->input('ai_api_key')));
        }

        $bu = trim((string) $request->input('base_url', ''));
        System::addProperty('searchbox_ai_base_url', $bu === '' ? '' : rtrim($bu, '/'));

        System::addProperty('searchbox_ai_model', trim((string) $request->input('model', '')));

        $timeout = $request->input('timeout');
        $timeout = $timeout !== null && $timeout !== '' ? (int) $timeout : (int) config('searchbox.ai.timeout', 25);
        System::addProperty('searchbox_ai_timeout', (string) max(5, min(120, $timeout)));

        $minLen = $request->input('ai_min_length');
        $minLen = $minLen !== null && $minLen !== '' ? (int) $minLen : (int) config('searchbox.ai_min_query_length', 2);
        System::addProperty('searchbox_ai_min_length', (string) max(1, min(10, $minLen)));

        \Illuminate\Support\Facades\Cache::forget('module_assets');

        return redirect()
            ->route('searchbox.ai-config')
            ->with('status', ['success' => true, 'msg' => __('searchbox::lang.ai_config_saved')]);
    }

    protected function authorizeConfigAccess(): void
    {
        if (! auth()->user()->can('manage_modules') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
    }
}
