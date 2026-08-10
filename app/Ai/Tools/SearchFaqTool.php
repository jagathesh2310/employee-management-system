<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Services\FaqService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * SearchFaqTool – wraps FaqService::semanticSearch for agent use.
 *
 * Demonstrates: Wrapping existing app services as AI tools.
 * Used in: /demo/ai/tools, /demo/ai/approval.
 */
class SearchFaqTool implements Tool
{
    private readonly FaqService $faqService;

    public function __construct(?FaqService $faqService = null)
    {
        $this->faqService = $faqService ?? app(FaqService::class);
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the HR knowledge base (FAQ) using semantic similarity. Returns the most relevant FAQ entries for a given question or topic. Use this to look up HR policies, procedures, and guidelines.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = (string) $request['query'];
        $limit = min((int) ($request['limit'] ?? 3), 5);

        try {
            $results = $this->faqService->semanticSearch($query, $limit);
        } catch (\Throwable) {
            return 'FAQ search is unavailable. Please ensure embeddings are configured.';
        }

        if ($results->isEmpty()) {
            return "No FAQ entries found for: {$query}";
        }

        $lines = $results->map(function ($faq) {
            return "Q: {$faq->question}\nA: {$faq->answer}";
        });

        return "Relevant HR policies found:\n\n".$lines->implode("\n\n---\n\n");
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('The question or topic to search for in the HR FAQ knowledge base.')
                ->required(),
            'limit' => $schema->integer()
                ->min(1)
                ->max(5)
                ->default(3)
                ->description('Number of FAQ results to return.'),
        ];
    }
}
