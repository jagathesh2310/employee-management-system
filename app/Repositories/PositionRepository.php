<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\PositionRepositoryInterface;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PositionRepository – Eloquent implementation of PositionRepositoryInterface.
 */
class PositionRepository implements PositionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Position::withCount('employees')
            ->byLevel(); // local scope: order by level asc

        if (! empty($filters['search'])) {
            $query->where('name', 'ilike', "%{$filters['search']}%");
        }

        if (isset($filters['level'])) {
            $query->ofLevel((int) $filters['level']);
        }

        return $query->paginate($perPage);
    }

    public function all(): Collection
    {
        return Position::byLevel()->get();
    }

    public function findById(string $id): Position
    {
        return Position::with(['employees' => fn ($q) => $q->limit(10)])
            ->withCount('employees')
            ->findOrFail($id);
    }

    public function create(array $data): Position
    {
        return DB::transaction(fn () => Position::create($data));
    }

    public function update(Position $position, array $data): Position
    {
        return DB::transaction(function () use ($position, $data) {
            $position->update($data);

            return $position->fresh();
        });
    }

    public function delete(Position $position): bool
    {
        return DB::transaction(fn () => $position->delete());
    }
}
