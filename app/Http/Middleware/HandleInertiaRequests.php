<?php

namespace App\Http\Middleware;

use App\Domain\Platform\Models\ApplicationSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'roles' => $user?->getRoleNames() ?? [],
                'permissions' => $user?->getAllPermissions()->pluck('name') ?? [],
                'unreadNotificationsCount' => $user?->unreadNotifications()->count() ?? 0,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'settings' => [
                'whatsapp' => ApplicationSetting::get('contact_whatsapp', '+6281200000000'),
                'email' => ApplicationSetting::get('contact_email', 'halo@demera.my.id'),
                'phone' => ApplicationSetting::get('contact_phone'),
                'address' => ApplicationSetting::get('contact_address', 'Jakarta, Indonesia'),
                'mapEmbedUrl' => ApplicationSetting::get('contact_map_embed_url'),
                'instagram' => ApplicationSetting::get('social_instagram'),
                'facebook' => ApplicationSetting::get('social_facebook'),
                'tiktok' => ApplicationSetting::get('social_tiktok'),
            ],
        ];
    }
}
