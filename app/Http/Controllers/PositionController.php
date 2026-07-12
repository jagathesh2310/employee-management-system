<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\PositionData;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function __construct(
        private readonly PositionService $positionService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Position::class);

        $filters = $request->only(['search', 'level']);
        $positions = $this->positionService->getPaginated($filters);

        return view('positions.index', compact('positions', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Position::class);

        return view('positions.create');
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        $data = PositionData::fromArray($request->validated());
        $this->positionService->create($data);

        return redirect()->route('positions.index')->with('success', 'Position created successfully.');
    }

    public function show(Position $position): View
    {
        $this->authorize('view', $position);
        $position = $this->positionService->getById($position->id);

        return view('positions.show', compact('position'));
    }

    public function edit(Position $position): View
    {
        $this->authorize('update', $position);
        $position = $this->positionService->getById($position->id);

        return view('positions.edit', compact('position'));
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $data = PositionData::fromArray(array_merge($position->toArray(), $request->validated()));
        $this->positionService->update($position, $data);

        return redirect()->route('positions.index')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $this->authorize('delete', $position);
        $this->positionService->delete($position);

        return redirect()->route('positions.index')->with('success', 'Position deleted successfully.');
    }
}
