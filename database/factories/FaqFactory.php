<?php

namespace Database\Factories;

use App\Jobs\GenerateFaqEmbeddingJob;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence(),
            'answer' => $this->faker->paragraph(),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Faq $faq) {
            GenerateFaqEmbeddingJob::dispatch($faq);
        });
    }
}
