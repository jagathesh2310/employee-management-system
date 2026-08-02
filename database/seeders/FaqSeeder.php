<?php

namespace Database\Seeders;

use App\Jobs\GenerateFaqEmbeddingJob;
use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = require database_path('data/faqs.php');

        foreach ($faqs as $faqData) {
            $faq = Faq::create([
                'question' => $faqData['question'],
                'answer' => $faqData['answer'],
                'is_active' => true,
            ]);

            GenerateFaqEmbeddingJob::dispatch($faq);
        }
    }
}
