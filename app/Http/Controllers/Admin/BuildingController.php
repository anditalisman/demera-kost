<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Property;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function store(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('create', Building::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $property->buildings()->create($validated);

        return back()->with('success', 'Gedung berhasil ditambahkan.');
    }

    public function update(Request $request, Building $building): RedirectResponse
    {
        $this->authorize('update', $building);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $building->update($validated);

        return back()->with('success', 'Gedung berhasil diperbarui.');
    }

    public function destroy(Building $building): RedirectResponse
    {
        $this->authorize('delete', $building);

        $building->delete();

        return back()->with('success', 'Gedung berhasil dihapus.');
    }
}
