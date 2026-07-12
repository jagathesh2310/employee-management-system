<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\PositionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Resources\PositionCollection;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class PositionController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly PositionService $positionService,
    ) {}

    public static function middleware(): array
    {
        return [];
    }

    public function index(Request $request): PositionCollection
    {
        $this->authorize('viewAny', Position::class);

        $filters  = $request->only(['search', 'level']);
        $perPage  = (int) $request->query('per_page', 15);
        $positions = $this->positionService->getPaginated($filters);

        return new PositionCollection($positions);
    }

    public function store(StorePositionRequest $request): JsonResponse
    {
        $data     = PositionData::fromArray($request->validated());
        $position = $this->positionService->create($data);

        return response()->json(new PositionResource($position), 201);
    }

    public function show(Position $position): PositionResource
    {
        $this->authorize('view', $position);

        $position = $this->positionService->getById($position->id);

        return new PositionResource($position);
    }

    public function update(UpdatePositionRequest $request, Position $position): PositionResource
    {
        $data = PositionData::fromArray(array_merge($position->toArray(), $request->validated()));
        $updated = $this->positionService->update($position, $data);

        return new PositionResource($updated);
    }

    public function destroy(Position $position): JsonResponse
    {
        $this->authorize('delete', $position);
        $this->positionService->delete($position);

        return response()->json(null, 204);
    }
}
