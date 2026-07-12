<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\DepartmentData;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $departmentService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $filters = $request->only(['search', 'sort_by', 'sort_dir']);
        $departments = $this->departmentService->getPaginated($filters);

        return view('departments.index', compact('departments', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('departments.create');
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $data = DepartmentData::fromArray($request->validated());
        $this->departmentService->create($data);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function show(Department $department): View
    {
        $this->authorize('view', $department);
        $department = $this->departmentService->getById($department->id);

        return view('departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);
        $department = $this->departmentService->getById($department->id);

        return view('departments.edit', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $data = DepartmentData::fromArray(array_merge(
            $department->toArray(),
            $request->validated()
        ));

        $this->departmentService->update($department, $data);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);
        $this->departmentService->delete($department);

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
