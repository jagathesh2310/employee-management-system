<?php

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
        \App\Contracts\DepartmentRepositoryInterface::class => \App\Repositories\DepartmentRepository::class,
        \App\Contracts\PositionRepositoryInterface::class   => \App\Repositories\PositionRepository::class,
        \App\Contracts\EmployeeRepositoryInterface::class   => \App\Repositories\EmployeeRepository::class,
        \App\Contracts\LeaveRequestRepositoryInterface::class => \App\Repositories\LeaveRequestRepository::class,
    ],
];
