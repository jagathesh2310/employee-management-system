<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\FaqRepositoryInterface;
use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FaqRepository implements FaqRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Faq::query();

        if (! empty($filters['search'])) {
            $query->where('question', 'like', '%'.$filters['search'].'%');
        }

        $sortBy = in_array($filters['sort_by'] ?? 'created_at', ['question', 'created_at']) ? ($filters['sort_by'] ?? 'created_at') : 'created_at';
        $sortDirection = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    public function all(): Collection
    {
        return Faq::orderBy('question')->get();
    }

    public function findById(string $id): Faq
    {
        return Faq::findOrFail($id);
    }

    public function create(array $data): Faq
    {
        return DB::transaction(fn () => Faq::create($data));
    }

    public function update(Faq $faq, array $data): Faq
    {
        return DB::transaction(function () use ($faq, $data) {
            $faq->update($data);

            return $faq->fresh();
        });
    }

    public function delete(Faq $faq): bool
    {
        return DB::transaction(fn () => $faq->delete());
    }
}
