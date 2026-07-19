<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\GenerateFaqEmbeddingJob;
use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Embeddings;

/**
 * FaqService – all FAQ business logic lives here.
 *
 * Controllers delegate to this class; no business logic exists in controllers.
 * Eloquent is used directly (no repository layer for FAQs).
 */
class FaqService
{
    /**
     * Return a paginated list of FAQs, optionally filtered and sorted.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Faq::query();

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('question', 'ilike', $search)
                    ->orWhere('answer', 'ilike', $search);
            });
        }

        if (isset($filters['status'])) {
            $query->where('is_active', (bool) $filters['status']);
        }

        $allowedSorts = ['question', 'created_at', 'updated_at'];
        $sortBy = in_array($filters['sort_by'] ?? 'created_at', $allowedSorts, true)
            ? ($filters['sort_by'] ?? 'created_at')
            : 'created_at';

        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();
    }

    public function getById(int $id): Faq
    {
        return Faq::findOrFail($id);
    }

    /**
     * Create a new FAQ and dispatch an embedding job.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Faq
    {
        $faq = DB::transaction(fn () => Faq::create($data));

        GenerateFaqEmbeddingJob::dispatch($faq);

        return $faq;
    }

    /**
     * Update an existing FAQ.
     * Re-dispatches embedding job if question or answer changed.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Faq $faq, array $data): Faq
    {
        $contentChanged = isset($data['question'], $data['answer'])
            && ($data['question'] !== $faq->question || $data['answer'] !== $faq->answer);

        $faq = DB::transaction(function () use ($faq, $data) {
            $faq->update($data);

            return $faq->fresh();
        });

        if ($contentChanged) {
            GenerateFaqEmbeddingJob::dispatch($faq);
        }

        return $faq;
    }

    public function delete(Faq $faq): bool
    {
        return (bool) $faq->delete();
    }

    /**
     * Perform a semantic similarity search using pgvector cosine distance.
     *
     * Generates a query embedding via Gemini, then finds the closest FAQs
     * using the <=> (cosine distance) operator. Lower distance = more similar.
     *
     * @return Collection<int, Faq>
     */
    public function semanticSearch(string $query, int $limit = 5): Collection
    {
        $response = Embeddings::for([$query])
            ->dimensions(768)
            ->generate();

        /** @var array<float> $vector */
        $vector = $response->first();

        /** @var Collection<int, Faq> $results */
        $results = Faq::active()
            ->withEmbedding()
            ->orderByVectorDistance('embedding', $vector)
            ->limit($limit)
            ->get();

        return $results;
    }
}
