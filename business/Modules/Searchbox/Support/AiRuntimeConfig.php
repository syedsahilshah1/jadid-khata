<?php

namespace Modules\Searchbox\Support;

use App\System;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

/**
 * Merges AI settings from the `system` table into config at runtime (no .env required).
 */
class AiRuntimeConfig
{
    public static function applyDatabaseOverrides(): void
    {
        if (! Schema::hasTable('system')) {
            return;
        }

        try {
            $header = System::getProperty('searchbox_header_enabled');
            if ($header !== null && $header !== '') {
                config([
                    'searchbox.header_search_enabled' => in_array(strtolower((string) $header), ['1', 'true', 'yes', 'on'], true),
                ]);
            }

            $mobileFab = System::getProperty('searchbox_mobile_fab_enabled');
            if ($mobileFab !== null && $mobileFab !== '') {
                config([
                    'searchbox.mobile_floating_search_enabled' => in_array(strtolower((string) $mobileFab), ['1', 'true', 'yes', 'on'], true),
                ]);
            }

            $ai = config('searchbox.ai', []);

            $enabled = System::getProperty('searchbox_ai_enabled');
            if ($enabled !== null && $enabled !== '') {
                $ai['enabled'] = in_array(strtolower((string) $enabled), ['1', 'true', 'yes', 'on'], true);
            }

            $ui = System::getProperty('searchbox_ai_ui_enabled');
            if ($ui !== null && $ui !== '') {
                $ai['ui_enabled'] = in_array(strtolower((string) $ui), ['1', 'true', 'yes', 'on'], true);
            }

            $enc = System::getProperty('searchbox_ai_api_key');
            if (! empty($enc)) {
                try {
                    $ai['api_key'] = Crypt::decryptString($enc);
                } catch (\Throwable $e) {
                    $ai['api_key'] = is_string($enc) ? $enc : '';
                }
            }

            $bu = System::getProperty('searchbox_ai_base_url');
            if ($bu !== null && trim((string) $bu) !== '') {
                $ai['base_url'] = rtrim(trim((string) $bu), '/');
            }

            $model = System::getProperty('searchbox_ai_model');
            if ($model !== null && trim((string) $model) !== '') {
                $ai['model'] = trim((string) $model);
            }

            $to = System::getProperty('searchbox_ai_timeout');
            if ($to !== null && $to !== '') {
                $ai['timeout'] = max(5, min(120, (int) $to));
            }

            $ml = System::getProperty('searchbox_ai_min_length');
            if ($ml !== null && $ml !== '') {
                config(['searchbox.ai_min_query_length' => max(1, min(10, (int) $ml))]);
            }

            config(['searchbox.ai' => $ai]);
        } catch (\Throwable $e) {
            // e.g. during early bootstrap
        }
    }
}
