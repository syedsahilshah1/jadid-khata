<?php

namespace Modules\Searchbox\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Searchbox\Services\AiQueryInterpreter;

class AiSearchController extends Controller
{
    /** @var UniversalSearchController */
    protected $universalSearchController;

    /** @var AiQueryInterpreter */
    protected $interpreter;

    public function __construct(UniversalSearchController $universalSearchController, AiQueryInterpreter $interpreter)
    {
        $this->middleware('auth');
        $this->universalSearchController = $universalSearchController;
        $this->interpreter = $interpreter;
    }

    /**
     * Natural-language search: interpret with AI, then reuse universal search results.
     */
    public function search(Request $request)
    {
        if (! config('searchbox.ai.ui_enabled', true)) {
            return response()->json(['results' => [], 'message' => __('searchbox::lang.ai_disabled')], 404);
        }

        $original = trim((string) $request->input('q', ''));
        $minLen = (int) config('searchbox.ai_min_query_length', 2);

        if ($original === '' || mb_strlen($original) < $minLen) {
            return response()->json([
                'results' => [],
                'message' => __('lang_v1.minimum_search_length'),
                'min_length' => $minLen,
            ]);
        }

        $manualOnly = trim((string) $request->input('only', ''));

        $parsed = $this->interpreter->interpret($original);

        if (($parsed['error'] ?? null) === 'missing_api_key') {
            $subRequest = $request->duplicate(array_merge($request->query->all(), [
                'q' => $original,
                'only' => $manualOnly,
            ]));
            $inner = $this->universalSearchController->search($subRequest);
            $data = json_decode($inner->getContent(), true);
            if (! is_array($data)) {
                return response()->json(['results' => [], 'message' => __('lang_v1.search_error')], 500);
            }
            $data['original_query'] = $original;
            $data['ai'] = [
                'enabled' => false,
                'hint' => __('searchbox::lang.ai_missing_key'),
                'interpreted_keywords' => $original,
                'scope_suggested' => '',
                'scope_applied' => $manualOnly,
                'manual_scope' => $manualOnly !== '',
                'fallback' => true,
                'error' => 'missing_api_key',
            ];

            return response()->json($data, $inner->getStatusCode());
        }

        $keywords = $parsed['keywords'] ?: $original;
        $aiOnly = $parsed['only'] ?? '';

        $onlyParam = $manualOnly !== '' ? $manualOnly : $aiOnly;

        $query = array_merge($request->query->all(), [
            'q' => $keywords,
            'only' => $onlyParam,
        ]);

        $subRequest = $request->duplicate($query);

        $inner = $this->universalSearchController->search($subRequest);
        $data = json_decode($inner->getContent(), true);
        if (! is_array($data)) {
            return response()->json(['results' => [], 'message' => __('lang_v1.search_error')], 500);
        }

        $data['original_query'] = $original;
        $data['ai'] = [
            'enabled' => true,
            'hint' => $parsed['hint'] ?? '',
            'interpreted_keywords' => $keywords,
            'scope_suggested' => $aiOnly,
            'scope_applied' => $onlyParam,
            'manual_scope' => $manualOnly !== '',
            'fallback' => ! empty($parsed['fallback']),
            'error' => $parsed['error'] ?? null,
        ];

        return response()->json($data, $inner->getStatusCode());
    }
}
