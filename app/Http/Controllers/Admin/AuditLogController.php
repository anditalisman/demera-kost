<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Platform\Models\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when($request->string('action')->trim()->isNotEmpty(), fn ($q) => $q->where('action', $request->string('action')))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only('action'),
            'availableActions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
