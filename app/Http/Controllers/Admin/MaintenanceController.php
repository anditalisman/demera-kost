<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\MaintenanceRequest;
use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MaintenanceRequest::class);

        $requests = MaintenanceRequest::query()
            ->with(['tenant.user', 'room.property'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('priority')->toString(), fn ($q, $priority) => $q->where('priority', $priority))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Living/Maintenance/Index', [
            'requests' => $requests,
            'filters' => $request->only(['status', 'priority']),
        ]);
    }

    public function show(MaintenanceRequest $maintenanceRequest): Response
    {
        $this->authorize('view', $maintenanceRequest);

        $maintenanceRequest->load(['tenant.user', 'room.property', 'attachments', 'comments.user', 'assignedTo']);

        return Inertia::render('Dashboard/Admin/Living/Maintenance/Show', [
            'maintenanceRequest' => $maintenanceRequest,
            'statuses' => collect(MaintenanceStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $this->authorize('manage', MaintenanceRequest::class);

        $validated = $request->validate([
            'status' => ['required', 'in:new,in_progress,waiting,completed,closed'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $maintenanceRequest->update([
            'status' => $validated['status'],
            'assigned_to' => $maintenanceRequest->assigned_to ?? $request->user()->id,
            'resolution_notes' => $validated['resolution_notes'] ?? $maintenanceRequest->resolution_notes,
            'resolved_at' => in_array($validated['status'], ['completed', 'closed'], true) ? now() : null,
        ]);

        return back()->with('success', 'Status keluhan berhasil diperbarui.');
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
