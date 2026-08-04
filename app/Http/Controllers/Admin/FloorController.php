<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Building;
use App\Domain\Living\Models\Floor;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function store(Request $request, Building $building): RedirectResponse
    {
        $this->authorize('create', Floor::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
        ]);

        $building->floors()->create($validated);

        return back()->with('success', 'Lantai berhasil ditambahkan.');
    }

    public function update(Request $request, Floor $floor): RedirectResponse
    {
        $this->authorize('update', $floor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
        ]);

        $floor->update($validated);

        return back()->with('success', 'Lantai berhasil diperbarui.');
    }

    public function destroy(Floor $floor): RedirectResponse
    {
        $this->authorize('delete', $floor);

        $floor->delete();

        return back()->with('success', 'Lantai berhasil dihapus.');
    }
}
