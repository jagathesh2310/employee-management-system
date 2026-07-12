<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FaqRepositoryInterface
{
    /**
     * Return paginated faqs with optional search.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Return all faqs.
     *
     * @return Collection<int, Faq>
     */
    public function all(): Collection;

    public function findById(string $id): Faq;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Faq;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Faq $faq, array $data): Faq;

    public function delete(Faq $faq): bool;
}
