<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\ApplicationSetting;
use Illuminate\Database\Seeder;

class ApplicationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'contact_whatsapp' => ['value' => '+6281200000000', 'group' => 'contact', 'label' => 'Nomor WhatsApp'],
            'contact_email' => ['value' => 'halo@demera.my.id', 'group' => 'contact', 'label' => 'Email Kontak'],
            'contact_phone' => ['value' => '021-5550100', 'group' => 'contact', 'label' => 'Telepon Kantor'],
            'contact_address' => ['value' => 'Jl. Demera Raya No. 1, Jakarta Selatan, DKI Jakarta', 'group' => 'contact', 'label' => 'Alamat'],
            'contact_map_embed_url' => ['value' => 'https://www.google.com/maps?q=Jakarta&output=embed', 'group' => 'contact', 'label' => 'URL Embed Peta'],
            'social_instagram' => ['value' => 'https://instagram.com/demera.id', 'group' => 'social', 'label' => 'Instagram URL'],
            'social_facebook' => ['value' => 'https://facebook.com/demera.id', 'group' => 'social', 'label' => 'Facebook URL'],
            'social_tiktok' => ['value' => 'https://tiktok.com/@demera.id', 'group' => 'social', 'label' => 'TikTok URL'],
            'seo_default_title' => ['value' => 'Demera — Fashion & Living', 'group' => 'seo', 'label' => 'Meta Title Default'],
            'seo_default_description' => ['value' => 'Demera menghadirkan kost nyaman dan terpercaya melalui Demera Living, serta fashion editorial melalui Demera Fashion.', 'group' => 'seo', 'label' => 'Meta Description Default'],
            'booking_hold_hours' => ['value' => '24', 'group' => 'booking', 'label' => 'Batas Waktu Pembayaran Booking (jam)', 'type' => 'number', 'is_public' => false],
            'invoice_reminder_offsets' => ['value' => '-7,-3,-1,0,1,3,7', 'group' => 'notification', 'label' => 'Jadwal Pengingat Tagihan (hari, koma pisah)', 'is_public' => false],
        ];

        foreach ($settings as $key => $attrs) {
            ApplicationSetting::set($key, $attrs['value'], [
                'type' => $attrs['type'] ?? 'string',
                'group' => $attrs['group'],
                'label' => $attrs['label'],
                'is_public' => $attrs['is_public'] ?? true,
            ]);
        }
    }
}
