<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\DepartmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentCollection;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * DepartmentController – API endpoint for departments.
 *
 * WHY THIS STRUCTURE:
 *   - Follows standard Resource Controller pattern (index, store, show, update, destroy)
 *   - Thin controller: delegates all logic to DepartmentService
 *   - Request validation handled by FormRequests (StoreDepartmentRequest)
 *   - Response formatting handled by API Resources (DepartmentResource)
 *
 * LARAVEL 11+ FEATURE:
 *   Implements HasMiddleware interface instead of calling $this->middleware()
 *   in the constructor. This is the modern way to attach middleware to controllers.
 */
class DepartmentController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly DepartmentService $departmentService,
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     *
     * @return array<int, \Illuminate\Routing\Controllers\Middleware|string>
     */
    public static function middleware(): array
    {
        return [
            // auth:sanctum applied globally in routes, but can add specific ones here
            // e.g., new Middleware('role:admin', only: ['destroy']),
        ];
    }

    public function index(Request $request): DepartmentCollection
    {
        // Authorize via policy (viewAny)
        $this->authorize('viewAny', Department::class);

        // Extract allowed filters from query string
        $filters = $request->only(['search', 'sort_by', 'sort_dir']);
        $perPage = (int) $request->query('per_page', 15);

        $departments = $this->departmentService->getPaginated($filters);

        // ResourceCollection wraps pagination and adds meta tags automatically
        return new DepartmentCollection($departments);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        // Authorization is handled inside StoreDepartmentRequest::authorize()

        // Map validated request array to strongly-typed DTO
        $data = DepartmentData::fromArray($request->validated());

        $department = $this->departmentService->create($data);

        // Return 201 Created with the serialized resource
        return response()->json(new DepartmentResource($department), 201);
    }

    public function show(Department $department): DepartmentResource
    {
        $this->authorize('view', $department);

        // Ensure we return the model with relations loaded (Service fetches fresh)
        $department = $this->departmentService->getById($department->id);

        return new DepartmentResource($department);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): DepartmentResource
    {
        // Authorization handled in UpdateDepartmentRequest

        // DTOs for partial updates might differ, but for simplicity we reuse the array mapping
        // A dedicated UpdateDepartmentData DTO with nullable fields is better for strict PATCH.
        $data = DepartmentData::fromArray(array_merge(
            $department->toArray(), // merge existing data to satisfy DTO constructor
            $request->validated()
        ));

        $updated = $this->departmentService->update($department, $data);

        return new DepartmentResource($updated);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('delete', $department);

        $this->departmentService->delete($department);

        return response()->json(null, 204);
    }
}
