<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Platform\Models\ApplicationSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationSettingController extends Controller
{
    /**
     * Known, admin-editable settings. Keeping this explicit (rather than a
     * free-form key manager) matches the brief's fixed CMS scope for Tahap 1
     * and avoids admins accidentally creating unused/garbage keys.
     */
    private const DEFINITIONS = [
        'contact' => [
            'contact_whatsapp' => ['label' => 'Nomor WhatsApp', 'type' => 'string'],
            'contact_email' => ['label' => 'Email Kontak', 'type' => 'string'],
            'contact_phone' => ['label' => 'Telepon Kantor', 'type' => 'string'],
            'contact_address' => ['label' => 'Alamat', 'type' => 'string'],
            'contact_map_embed_url' => ['label' => 'URL Embed Peta (Google Maps)', 'type' => 'string'],
        ],
        'social' => [
            'social_instagram' => ['label' => 'Instagram URL', 'type' => 'string'],
            'social_facebook' => ['label' => 'Facebook URL', 'type' => 'string'],
            'social_tiktok' => ['label' => 'TikTok URL', 'type' => 'string'],
        ],
        'seo' => [
            'seo_default_title' => ['label' => 'Meta Title Default', 'type' => 'string'],
            'seo_default_description' => ['label' => 'Meta Description Default', 'type' => 'string'],
        ],
    ];

    public function index(): Response
    {
        $this->authorize('viewAny', ApplicationSetting::class);

        $existing = ApplicationSetting::query()->pluck('value', 'key');

        $groups = collect(self::DEFINITIONS)->map(function (array $fields, string $group) use ($existing) {
            return collect($fields)->map(function (array $meta, string $key) use ($existing, $group) {
                return [
                    'key' => $key,
                    'group' => $group,
                    'label' => $meta['label'],
                    'type' => $meta['type'],
                    'value' => $existing->get($key, ''),
                ];
            })->values();
        });

        return Inertia::render('Dashboard/Admin/Content/Settings/Index', [
            'groups' => $groups,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', ApplicationSetting::class);

        $knownKeys = collect(self::DEFINITIONS)->flatMap(fn ($fields) => array_keys($fields))->all();

        $validated = $request->validate([
            'values' => ['required', 'array'],
            'values.*' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($validated['values'] as $key => $value) {
            if (! in_array($key, $knownKeys, true)) {
                continue;
            }

            $groupName = collect(self::DEFINITIONS)->search(fn ($fields) => array_key_exists($key, $fields));

            ApplicationSetting::set($key, $value, [
                'type' => 'string',
                'group' => $groupName ?: 'general',
                'label' => self::DEFINITIONS[$groupName][$key]['label'] ?? $key,
                'is_public' => true,
            ]);
        }

        Cache::forget('public_settings');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
