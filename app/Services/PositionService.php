<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PositionRepositoryInterface;
use App\DTO\PositionData;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PositionService
{
    public function __construct(
        private readonly PositionRepositoryInterface $positionRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = []): LengthAwarePaginator
    {
        return $this->positionRepository->paginate($filters);
    }

    /**
     * @return Collection<int, Position>
     */
    public function getAll(): Collection
    {
        return $this->positionRepository->all();
    }

    public function getById(string $id): Position
    {
        return $this->positionRepository->findById($id);
    }

    public function create(PositionData $data): Position
    {
        return $this->positionRepository->create($data->toArray());
    }

    public function update(Position $position, PositionData $data): Position
    {
        return $this->positionRepository->update($position, $data->toArray());
    }

    public function delete(Position $position): bool
    {
        if ($position->employees()->count() > 0) {
            throw new \DomainException('Cannot delete a position that is currently assigned to employees.');
        }

        return $this->positionRepository->delete($position);
    }
}
