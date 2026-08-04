<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = $request->user()->notifications()
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, int $notification): RedirectResponse
    {
        $model = $request->user()->notifications()->findOrFail($notification);
        $model->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->notifications()->unread()->update(['read_at' => now()]);

        return back();
    }
}
