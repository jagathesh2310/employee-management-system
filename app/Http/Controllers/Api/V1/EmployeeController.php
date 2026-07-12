<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\EmployeeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeCollection;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class EmployeeController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly EmployeeService $employeeService,
    ) {}

    public static function middleware(): array
    {
        return [];
    }

    public function index(Request $request): EmployeeCollection
    {
        $this->authorize('viewAny', Employee::class);

        $filters = $request->only([
            'search', 'status', 'department_id', 'position_id',
            'salary_min', 'salary_max', 'joined_from', 'joined_to',
            'department_search', 'sort_by', 'sort_dir',
        ]);

        // Using cursor pagination for the employee list to demonstrate both types
        // In a real app, you'd choose one or make it configurable
        if ($request->query('paginate_type') === 'cursor') {
            $employees = $this->employeeService->getCursorPaginated($filters);
        } else {
            $employees = $this->employeeService->getPaginated($filters);
        }

        return new EmployeeCollection($employees);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $data     = EmployeeData::fromArray($request->validated());
        $employee = $this->employeeService->create($data);

        return response()->json(new EmployeeResource($employee), 201);
    }

    public function show(Employee $employee): EmployeeResource
    {
        $this->authorize('view', $employee);

        $employee = $this->employeeService->getById($employee->id);

        return new EmployeeResource($employee);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): EmployeeResource
    {
        // $request->validated() only contains fields that were present in the request
        $updated = $this->employeeService->update($employee, $request->validated());

        return new EmployeeResource($updated);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);
        
        $this->employeeService->delete($employee);

        return response()->json(null, 204);
    }

    /**
     * Trigger a background job to export employees.
     */
    public function export(): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $this->employeeService->queueReportGeneration();

        return response()->json([
            'message' => 'Employee export has been queued. You will be notified when it is ready.',
        ], 202);
    }
}
