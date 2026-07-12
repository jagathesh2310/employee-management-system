<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\EmployeeData;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $filters = $request->only([
            'search', 'status', 'department_id', 'position_id',
            'salary_min', 'salary_max', 'joined_from', 'joined_to',
            'department_search', 'sort_by', 'sort_dir',
        ]);

        $employees = $this->employeeService->getPaginated($filters);

        $departments = Department::all();
        $positions = Position::all();

        return view('employees.index', compact('employees', 'filters', 'departments', 'positions'));
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);
        $departments = Department::all();
        $positions = Position::all();

        return view('employees.create', compact('departments', 'positions'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = EmployeeData::fromArray($request->validated());
        $this->employeeService->create($data);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);
        $employee = $this->employeeService->getById($employee->id);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);
        $employee = $this->employeeService->getById($employee->id);
        $departments = Department::all();
        $positions = Position::all();

        return view('employees.edit', compact('employee', 'departments', 'positions'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->update($employee, $request->validated());

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);
        $this->employeeService->delete($employee);

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $this->authorize('viewAny', Employee::class);
        $this->employeeService->queueReportGeneration();

        return redirect()->route('employees.index')->with('success', 'Employee export has been queued. You will be notified when it is ready.');
    }
}
