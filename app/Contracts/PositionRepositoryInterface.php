<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * PositionRepositoryInterface – contract for position data access.
 */
interface PositionRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Position>
     */
    public function all(): Collection;

    public function findById(string $id): Position;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Position;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Position $position, array $data): Position;

    public function delete(Position $position): bool;
}
