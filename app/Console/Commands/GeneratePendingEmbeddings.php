<?php

namespace App\Console\Commands;

use App\Jobs\GenerateFaqEmbeddingJob;
use App\Models\Faq;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-pending-embeddings')]
#[Description('Dispatch jobs to generate missing embeddings for FAQs')]
class GeneratePendingEmbeddings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $faqs = Faq::query()->whereNull('embedding')->get();

        if ($faqs->isEmpty()) {
            $this->info('No pending embeddings to generate.');

            return;
        }

        $this->withProgressBar($faqs, function (Faq $faq) {
            GenerateFaqEmbeddingJob::dispatch($faq);
        });

        $this->newLine();
        $this->info("Dispatched {$faqs->count()} jobs to generate embeddings.");
    }
}
