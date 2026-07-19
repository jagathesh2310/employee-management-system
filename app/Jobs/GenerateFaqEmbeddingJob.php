<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Faq;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;

/**
 * GenerateFaqEmbeddingJob – generates a pgvector embedding for a FAQ.
 *
 * Runs on the "embeddings" queue (managed by Supervisor).
 * Dispatched by FaqService after create/update when question or answer changes.
 *
 * Retry strategy:
 *   - 3 attempts total
 *   - Exponential backoff: 60s, 120s, 240s
 *   - Fails silently with a log entry so it never crashes the queue worker
 */
class GenerateFaqEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     *
     * @var array<int, int>
     */
    public array $backoff = [60, 120, 240];

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    public function __construct(public readonly Faq $faq)
    {
        $this->onQueue('embeddings');
    }

    /**
     * Execute the job.
     *
     * Embeds the concatenation of question + answer for maximum context,
     * then persists the vector and timestamp to the database.
     */
    public function handle(): void
    {
        // Re-fetch to ensure we have fresh data (model might have changed since dispatch).
        $faq = Faq::find($this->faq->id);

        if ($faq === null) {
            // FAQ was deleted before the job ran – nothing to do.
            return;
        }

        $text = $faq->question.' '.$faq->answer;

        $response = Embeddings::for([$text])
            ->dimensions(768)
            ->generate();

        /** @var array<float> $vector */
        $vector = $response->first();

        $faq->update([
            'embedding' => '['.implode(',', $vector).']',
            'embedding_generated_at' => now(),
        ]);

        Log::info('FAQ embedding generated', [
            'faq_id' => $faq->id,
            'tokens' => $response->tokens,
            'model' => $response->meta->model,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('FAQ embedding generation failed', [
            'faq_id' => $this->faq->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
