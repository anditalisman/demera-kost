<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Facility;
use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomImage;
use App\Domain\Living\Models\RoomType;
use App\Domain\Platform\Services\ImageUploadService;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function __construct(private readonly ImageUploadService $imageUploadService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::query()
            ->with(['property', 'building', 'floor', 'roomType', 'primaryImage'])
            ->when($request->string('search')->toString(), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('room_number', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%");
            }))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->integer('property_id'), fn ($q, $id) => $q->where('property_id', $id))
            ->when($request->integer('room_type_id'), fn ($q, $id) => $q->where('room_type_id', $id))
            ->orderBy('room_number')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Living/Rooms/Index', [
            'rooms' => $rooms,
            'properties' => Property::query()->orderBy('name')->get(['id', 'name']),
            'roomTypes' => RoomType::query()->orderBy('name')->get(['id', 'name', 'property_id']),
            'statuses' => collect(RoomStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'filters' => $request->only(['search', 'status', 'property_id', 'room_type_id']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Room::class);

        return Inertia::render('Dashboard/Admin/Living/Rooms/Form', [
            'room' => null,
            'properties' => Property::query()->with(['buildings.floors'])->orderBy('name')->get(),
            'roomTypes' => RoomType::query()->orderBy('name')->get(),
            'facilities' => Facility::query()->orderBy('type')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Room::class);

        $validated = $this->validated($request);
        $validated['slug'] = $this->uniqueSlug($validated['room_number']);

        $room = Room::create($validated);

        return redirect()->route('admin.rooms.edit', $room)->with('success', 'Kamar berhasil ditambahkan. Silakan unggah foto dan atur fasilitas.');
    }

    public function edit(Room $room): Response
    {
        $this->authorize('update', $room);

        $room->load(['images', 'facilities', 'statusHistories' => fn ($q) => $q->latest('created_at')->with('changedBy'), 'property.buildings.floors']);

        return Inertia::render('Dashboard/Admin/Living/Rooms/Form', [
            'room' => $room,
            'properties' => Property::query()->with(['buildings.floors'])->orderBy('name')->get(),
            'roomTypes' => RoomType::query()->orderBy('name')->get(),
            'facilities' => Facility::query()->orderBy('type')->orderBy('sort_order')->get(),
            'statuses' => collect(RoomStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $room->update($this->validated($request));

        return back()->with('success', 'Kamar berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        foreach ($room->images as $image) {
            $this->imageUploadService->delete($image->path, $image->thumbnail_path);
        }

        $room->delete();

        return redirect()->route('admin.rooms.index')->with('success', 'Kamar berhasil dihapus.');
    }

    public function updateFacilities(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'facility_ids' => ['array'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],
        ]);

        $room->facilities()->sync($validated['facility_ids'] ?? []);

        return back()->with('success', 'Fasilitas kamar berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_map(fn ($s) => $s->value, RoomStatus::cases()))],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $room->recordStatusChange(RoomStatus::from($validated['status']), $validated['reason'] ?? null, $request->user()->id);

        return back()->with('success', 'Status kamar berhasil diperbarui.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $this->authorize('update', Room::class);

        $validated = $request->validate([
            'room_ids' => ['required', 'array', 'min:1'],
            'room_ids.*' => ['integer', 'exists:rooms,id'],
            'status' => ['required', 'string', 'in:'.implode(',', array_map(fn ($s) => $s->value, RoomStatus::cases()))],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $rooms = Room::query()->whereIn('id', $validated['room_ids'])->get();
        $status = RoomStatus::from($validated['status']);

        foreach ($rooms as $room) {
            $room->recordStatusChange($status, $validated['reason'] ?? null, $request->user()->id);
        }

        return back()->with('success', count($rooms).' kamar berhasil diperbarui statusnya.');
    }

    public function storePhoto(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:8192'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $uploaded = $this->imageUploadService->upload($request->file('image'), 'rooms');

        $maxSort = $room->images()->max('sort_order') ?? 0;
        $isFirst = $room->images()->count() === 0;

        $room->images()->create([
            'path' => $uploaded['path'],
            'thumbnail_path' => $uploaded['thumbnail_path'],
            'caption' => $validated['caption'] ?? null,
            'is_primary' => $isFirst,
            'sort_order' => $maxSort + 1,
        ]);

        return back()->with('success', 'Foto berhasil ditambahkan.');
    }

    public function reorderPhotos(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:room_images,id'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            $room->images()->whereKey($id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Urutan foto berhasil disimpan.');
    }

    public function setPrimaryPhoto(Room $room, RoomImage $roomImage): RedirectResponse
    {
        $this->authorize('update', $room);

        $room->images()->update(['is_primary' => false]);
        $roomImage->update(['is_primary' => true]);

        return back()->with('success', 'Foto utama berhasil diatur.');
    }

    public function destroyPhoto(Room $room, RoomImage $roomImage): RedirectResponse
    {
        $this->authorize('update', $room);

        $this->imageUploadService->delete($roomImage->path, $roomImage->thumbnail_path);
        $wasPrimary = $roomImage->is_primary;
        $roomImage->delete();

        if ($wasPrimary) {
            $room->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'building_id' => ['required', 'exists:buildings,id'],
            'floor_id' => ['required', 'exists:floors,id'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'room_number' => ['required', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'size_sqm' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'available_from' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $roomNumber): string
    {
        $base = Str::slug($roomNumber);
        $slug = $base;
        $i = 1;

        while (Room::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
