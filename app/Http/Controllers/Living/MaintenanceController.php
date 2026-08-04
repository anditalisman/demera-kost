<?php

namespace App\Http\Controllers\Living;

use App\Domain\Living\Models\MaintenanceRequest;
use App\Domain\Platform\Services\ImageUploadService;
use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function __construct(private readonly ImageUploadService $imageUploadService) {}

    public function index(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        $requests = $tenant
            ? MaintenanceRequest::query()->where('tenant_id', $tenant->id)->orderByDesc('created_at')->paginate(10)
            : MaintenanceRequest::query()->whereRaw('1 = 0')->paginate(10);

        return Inertia::render('Living/Maintenance/Index', [
            'requests' => $requests,
            'canCreate' => (bool) $tenant,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', MaintenanceRequest::class);

        return Inertia::render('Living/Maintenance/Create', [
            'room' => $request->user()->tenant->room,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceRequest::class);

        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:8192'],
        ]);

        $maintenanceRequest = MaintenanceRequest::create([
            'tenant_id' => $tenant->id,
            'room_id' => $tenant->room_id,
            'reported_by' => $request->user()->id,
            'category' => $validated['category'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => MaintenanceStatus::New,
        ]);

        foreach ($request->file('photos', []) as $photo) {
            $uploaded = $this->imageUploadService->upload($photo, 'maintenance');

            $maintenanceRequest->attachments()->create([
                'file_path' => $uploaded['path'],
                'uploaded_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('maintenance-requests.show', $maintenanceRequest)->with('success', 'Keluhan berhasil dikirim. Admin akan segera menindaklanjuti.');
    }

    public function show(MaintenanceRequest $maintenanceRequest): Response
    {
        $this->authorize('view', $maintenanceRequest);

        $maintenanceRequest->load(['attachments', 'comments.user', 'room', 'assignedTo']);

        return Inertia::render('Living/Maintenance/Show', [
            'maintenanceRequest' => $maintenanceRequest,
        ]);
    }

    public function storeComment(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $this->authorize('view', $maintenanceRequest);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $maintenanceRequest->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Komentar berhasil dikirim.');
    }
}
