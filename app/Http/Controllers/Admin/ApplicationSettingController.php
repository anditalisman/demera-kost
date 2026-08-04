<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Platform\Models\ApplicationSetting;
use App\Domain\Platform\Services\ImageUploadService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
        'booking' => [
            'booking_hold_hours' => ['label' => 'Batas Waktu Pembayaran Booking (jam)', 'type' => 'number'],
        ],
        'payment' => [
            'booking_admin_fee' => ['label' => 'Biaya Admin Booking (Rp)', 'type' => 'number'],
            'payment_bank_name' => ['label' => 'Nama Bank', 'type' => 'string'],
            'payment_bank_account_number' => ['label' => 'Nomor Rekening', 'type' => 'string'],
            'payment_bank_account_holder' => ['label' => 'Nama Pemilik Rekening', 'type' => 'string'],
            'invoice_late_fee_type' => ['label' => 'Tipe Denda Keterlambatan (flat/percentage)', 'type' => 'string'],
            'invoice_late_fee_amount' => ['label' => 'Nilai Denda Keterlambatan', 'type' => 'number'],
        ],
        'notification' => [
            'invoice_reminder_offsets' => ['label' => 'Jadwal Pengingat Tagihan (hari, pisahkan koma, mis. -7,-3,-1,0,1,3,7)', 'type' => 'string'],
        ],
    ];

    public function __construct(private readonly ImageUploadService $imageUploadService) {}

    public function index(): Response
    {
        $this->authorize('viewAny', ApplicationSetting::class);

        $existing = ApplicationSetting::query()->pluck('value', 'key');
        $qrisPath = $existing->get('payment_qris_image');

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
            'qrisImageUrl' => $qrisPath ? Storage::disk('public_media')->url($qrisPath) : null,
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
                'type' => self::DEFINITIONS[$groupName][$key]['type'] ?? 'string',
                'group' => $groupName ?: 'general',
                'label' => self::DEFINITIONS[$groupName][$key]['label'] ?? $key,
                'is_public' => true,
            ]);
        }

        Cache::forget('public_settings');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function uploadQris(Request $request): RedirectResponse
    {
        $this->authorize('update', ApplicationSetting::class);

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $oldPath = ApplicationSetting::get('payment_qris_image');

        $uploaded = $this->imageUploadService->upload($request->file('image'), 'qris');

        ApplicationSetting::set('payment_qris_image', $uploaded['path'], [
            'type' => 'string',
            'group' => 'payment',
            'label' => 'Gambar QRIS',
            'is_public' => false,
        ]);

        if ($oldPath) {
            $this->imageUploadService->delete($oldPath);
        }

        return back()->with('success', 'Gambar QRIS berhasil diperbarui.');
    }
}
