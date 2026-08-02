<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AiFaqSearchController – side-by-side keyword vs. semantic search comparison.
 *
 * Teaching point: Semantic search matches meaning, not just keywords.
 */
class AiFaqSearchController extends Controller
{
    public function __construct(private readonly FaqService $faqService) {}

    public function show(): View
    {
        return view('demo.ai.faq-search');
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:500'],
        ]);

        $query = $validated['query'];

        // Keyword (ILIKE) search
        try {
            $keywordResults = $this->faqService->getPaginated(
                ['search' => $query, 'status' => '1'],
                perPage: 5
            )->items();
        } catch (\Throwable) {
            $keywordResults = [];
        }

        // Semantic search
        try {
            $semanticResults = $this->faqService->semanticSearch($query, 5)->all();
        } catch (\Throwable $e) {
            $semanticResults = [];
        }

        $format = fn ($faq) => [
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => $faq->answer,
        ];

        return response()->json([
            'query' => $query,
            'keyword' => array_map($format, $keywordResults),
            'semantic' => array_map($format, $semanticResults),
        ]);
    }
}
