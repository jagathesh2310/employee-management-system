<?php

namespace App\Providers;

use App\Contracts\DepartmentRepositoryInterface;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\LeaveRequestRepositoryInterface;
use App\Contracts\PositionRepositoryInterface;
use App\Events\EmployeeCreated;
use App\Events\EmployeeDeleted;
use App\Events\EmployeeUpdated;
use App\Events\LeaveApproved;
use App\Events\LeaveRejected;
use App\Events\LeaveRequested;
use App\Listeners\ClearDepartmentCache;
use App\Listeners\ClearEmployeeCache;
use App\Listeners\LogEmployeeActivity;
use App\Listeners\SendLeaveStatusNotification;
use App\Listeners\SendWelcomeNotification;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Observers\EmployeeObserver;
use App\Observers\LeaveRequestObserver;
use App\Repositories\DepartmentRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\LeaveRequestRepository;
use App\Repositories\PositionRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interfaces to concrete repositories
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, PositionRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(LeaveRequestRepositoryInterface::class, LeaveRequestRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ---------------------------------------------------------
        // Observers
        // ---------------------------------------------------------
        Employee::observe(EmployeeObserver::class);
        LeaveRequest::observe(LeaveRequestObserver::class);

        // ---------------------------------------------------------
        // Events & Listeners
        // ---------------------------------------------------------
        
        // Employee Events
        Event::listen(EmployeeCreated::class, [ClearEmployeeCache::class, 'handleEmployeeCreated']);
        Event::listen(EmployeeCreated::class, [ClearDepartmentCache::class, 'handleEmployeeCreated']);
        Event::listen(EmployeeCreated::class, [LogEmployeeActivity::class, 'handleEmployeeCreated']);
        Event::listen(EmployeeCreated::class, SendWelcomeNotification::class); // Queue listener

        Event::listen(EmployeeUpdated::class, [ClearEmployeeCache::class, 'handleEmployeeUpdated']);
        Event::listen(EmployeeUpdated::class, [ClearDepartmentCache::class, 'handleEmployeeUpdated']);
        Event::listen(EmployeeUpdated::class, [LogEmployeeActivity::class, 'handleEmployeeUpdated']);

        Event::listen(EmployeeDeleted::class, [ClearEmployeeCache::class, 'handleEmployeeDeleted']);
        Event::listen(EmployeeDeleted::class, [ClearDepartmentCache::class, 'handleEmployeeDeleted']);
        Event::listen(EmployeeDeleted::class, [LogEmployeeActivity::class, 'handleEmployeeDeleted']);

        // Leave Request Events
        Event::listen(LeaveApproved::class, [SendLeaveStatusNotification::class, 'handleLeaveApproved']);
        Event::listen(LeaveRejected::class, [SendLeaveStatusNotification::class, 'handleLeaveRejected']);
    }
}
