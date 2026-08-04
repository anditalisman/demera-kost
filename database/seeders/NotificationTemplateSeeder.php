<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\NotificationTemplate;
use App\Enums\NotificationChannel;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code' => 'booking_confirmed',
                'name' => 'Pemesanan Dikonfirmasi',
                'channel' => NotificationChannel::Email,
                'subject' => 'Pemesanan Anda telah dikonfirmasi',
                'body_template' => 'Halo {{name}}, pemesanan kamar {{room_name}} dengan kode {{booking_code}} telah dikonfirmasi.',
            ],
            [
                'code' => 'booking_confirmed_wa',
                'name' => 'Pemesanan Dikonfirmasi (WhatsApp)',
                'channel' => NotificationChannel::Whatsapp,
                'subject' => null,
                'body_template' => 'Halo {{name}}, pemesanan kamar {{room_name}} Anda (kode: {{booking_code}}) sudah dikonfirmasi. Terima kasih!',
            ],
            [
                'code' => 'invoice_due_reminder_h7',
                'name' => 'Pengingat Tagihan (H-7)',
                'channel' => NotificationChannel::Email,
                'subject' => 'Tagihan Anda jatuh tempo dalam 7 hari',
                'body_template' => 'Halo {{name}}, tagihan {{invoice_number}} sebesar {{amount}} akan jatuh tempo pada {{due_date}}.',
            ],
            [
                'code' => 'invoice_due_reminder_h3',
                'name' => 'Pengingat Tagihan (H-3)',
                'channel' => NotificationChannel::Email,
                'subject' => 'Tagihan Anda jatuh tempo dalam 3 hari',
                'body_template' => 'Halo {{name}}, tagihan {{invoice_number}} sebesar {{amount}} akan jatuh tempo pada {{due_date}}.',
            ],
            [
                'code' => 'invoice_due_reminder_h1',
                'name' => 'Pengingat Tagihan (H-1)',
                'channel' => NotificationChannel::Whatsapp,
                'subject' => null,
                'body_template' => 'Halo {{name}}, tagihan {{invoice_number}} Anda jatuh tempo besok ({{due_date}}). Mohon segera lakukan pembayaran.',
            ],
            [
                'code' => 'invoice_due_reminder_h0',
                'name' => 'Pengingat Tagihan (Hari-H)',
                'channel' => NotificationChannel::Whatsapp,
                'subject' => null,
                'body_template' => 'Halo {{name}}, tagihan {{invoice_number}} Anda jatuh tempo hari ini. Mohon segera lakukan pembayaran.',
            ],
            [
                'code' => 'invoice_overdue_h1',
                'name' => 'Tagihan Terlambat (H+1)',
                'channel' => NotificationChannel::Whatsapp,
                'subject' => null,
                'body_template' => 'Halo {{name}}, tagihan {{invoice_number}} Anda telah melewati jatuh tempo 1 hari. Mohon segera lakukan pembayaran untuk menghindari denda.',
            ],
            [
                'code' => 'invoice_overdue_h3',
                'name' => 'Tagihan Terlambat (H+3)',
                'channel' => NotificationChannel::Email,
                'subject' => 'Tagihan Anda telah terlambat 3 hari',
                'body_template' => 'Halo {{name}}, tagihan {{invoice_number}} Anda telah terlambat 3 hari. Denda keterlambatan mungkin berlaku.',
            ],
            [
                'code' => 'invoice_overdue_h7',
                'name' => 'Tagihan Terlambat (H+7)',
                'channel' => NotificationChannel::Email,
                'subject' => 'Tagihan Anda telah terlambat 7 hari',
                'body_template' => 'Halo {{name}}, tagihan {{invoice_number}} Anda telah terlambat 7 hari. Mohon segera hubungi kami.',
            ],
            [
                'code' => 'lease_expiring_soon',
                'name' => 'Kontrak Akan Berakhir',
                'channel' => NotificationChannel::Email,
                'subject' => 'Kontrak sewa Anda akan segera berakhir',
                'body_template' => 'Halo {{name}}, kontrak sewa {{lease_number}} Anda akan berakhir pada {{end_date}}. Hubungi kami untuk perpanjangan.',
            ],
            [
                'code' => 'payment_verified',
                'name' => 'Pembayaran Terverifikasi',
                'channel' => NotificationChannel::InApp,
                'subject' => null,
                'body_template' => 'Pembayaran {{payment_code}} sebesar {{amount}} telah diverifikasi.',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::query()->updateOrCreate(['code' => $template['code']], $template);
        }
    }
}
