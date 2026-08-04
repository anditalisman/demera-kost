<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Tenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = Tenant::query()
            ->with(['user', 'room.property'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('search')->toString(), fn ($q, $search) => $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Living/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Tenant $tenant): Response
    {
        $this->authorize('view', $tenant);

        $tenant->load([
            'user.profile',
            'room.property',
            'booking.documents',
            'leases' => fn ($q) => $q->orderByDesc('start_date'),
            'leases.room',
            'leases.extensions',
            'leases.deposits',
            'invoices' => fn ($q) => $q->orderByDesc('created_at'),
            'invoices.payments',
            'maintenanceRequests' => fn ($q) => $q->orderByDesc('created_at'),
        ]);

        return Inertia::render('Dashboard/Admin/Living/Tenants/Show', [
            'tenant' => $tenant,
        ]);
    }
}
