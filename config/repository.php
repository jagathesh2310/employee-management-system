<?php

use App\Contracts\DepartmentRepositoryInterface;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\LeaveRequestRepositoryInterface;
use App\Contracts\PositionRepositoryInterface;
use App\Repositories\DepartmentRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\LeaveRequestRepository;
use App\Repositories\PositionRepository;

return [
    /*
    |--------------------------------------------------------------------------
    | Repository Bindings
    |--------------------------------------------------------------------------
    |
    | Define your repository interfaces and their corresponding implementations
    | here. You can then write a loop in your AppServiceProvider to bind
    | them automatically.
    |
    */

    'bindings' => [
        DepartmentRepositoryInterface::class => DepartmentRepository::class,
        PositionRepositoryInterface::class => PositionRepository::class,
        EmployeeRepositoryInterface::class => EmployeeRepository::class,
        LeaveRequestRepositoryInterface::class => LeaveRequestRepository::class,
    ],
];
