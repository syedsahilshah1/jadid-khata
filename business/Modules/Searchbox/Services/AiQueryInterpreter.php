<?php

namespace Modules\Searchbox\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiQueryInterpreter
{
    /**
     * @return array{keywords: string, only: string, hint: string, fallback: bool, error: ?string}
     */
    public function interpret(string $userQuery): array
    {
        $defaults = [
            'keywords' => $this->sanitizeKeywords($userQuery),
            'only' => '',
            'hint' => '',
            'fallback' => true,
            'error' => null,
        ];

        if (! config('searchbox.ai.enabled', false)) {
            return $defaults;
        }

        $apiKey = (string) (config('searchbox.ai.api_key') ?: '');
        if ($apiKey === '') {
            return array_merge($defaults, [
                'error' => 'missing_api_key',
            ]);
        }

        $baseUrl = rtrim((string) config('searchbox.ai.base_url', 'https://api.openai.com/v1'), '/');
        $model = (string) config('searchbox.ai.model', 'gpt-4o-mini');
        $timeout = (int) config('searchbox.ai.timeout', 25);

        $allowedOnly = implode('|', array_filter(config('searchbox.scope_keys', [])));

        $system = <<<PROMPT
You assist an ERP / POS universal search. The user may type natural language.
Reply with ONLY valid JSON (no markdown fences), one object:
{"keywords":"string","only":"","hint":"string"}

Rules:
- "keywords": Short search text for SQL LIKE (names, SKUs, invoice numbers, phone fragments). Max 120 characters. Strip filler words.
- "only": One scope or empty string. Allowed values only: {$allowedOnly} (empty string means search all categories). Use "products" for items/stock/SKU, "contacts" for customers/suppliers, "sales" for invoices/sells, "purchases" for purchase bills, "payments" for payment refs, "expenses" for expenses, "users" for staff accounts, "repair" for job sheets when relevant.
- "hint": One short sentence explaining what you matched (user's language).

If unsure, set "only" to "" and put best keywords in "keywords".
PROMPT;

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->acceptJson()
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $userQuery],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 400,
                ]);

            if (! $response->successful()) {
                Log::warning('Searchbox AI HTTP error', ['status' => $response->status(), 'body' => $response->body()]);

                return array_merge($defaults, ['error' => 'http_'.$response->status()]);
            }

            $content = data_get($response->json(), 'choices.0.message.content');
            if (! is_string($content) || $content === '') {
                return array_merge($defaults, ['error' => 'empty_response']);
            }

            $parsed = $this->parseJsonFromContent($content);
            if ($parsed === null) {
                return array_merge($defaults, ['error' => 'parse_json']);
            }

            $keywords = $this->sanitizeKeywords($parsed['keywords'] ?? $userQuery);
            if ($keywords === '') {
                $keywords = $defaults['keywords'];
            }

            $only = $this->sanitizeOnly($parsed['only'] ?? '');
            $hint = is_string($parsed['hint'] ?? null) ? trim(mb_substr($parsed['hint'], 0, 300)) : '';

            return [
                'keywords' => $keywords,
                'only' => $only,
                'hint' => $hint,
                'fallback' => false,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Searchbox AI exception: '.$e->getMessage());

            return array_merge($defaults, ['error' => 'exception']);
        }
    }

    protected function sanitizeKeywords(string $q): string
    {
        $q = preg_replace('/[\x00-\x1F\x7F]/u', '', $q);
        $q = trim($q);

        return mb_substr($q, 0, 200);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function parseJsonFromContent(string $content): ?array
    {
        $content = trim($content);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/m', $content, $m)) {
            $content = trim($m[1]);
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function sanitizeOnly(?string $only): string
    {
        if ($only === null || $only === '') {
            return '';
        }
        $only = trim(strtolower($only));
        $allowed = array_values(array_filter(config('searchbox.scope_keys', []), function ($k) {
            return $k !== '';
        }));

        return in_array($only, $allowed, true) ? $only : '';
    }
}
