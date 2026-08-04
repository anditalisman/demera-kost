<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::query()->exists()) {
            return;
        }

        $faqs = [
            ['question' => 'Bagaimana cara memesan kamar di Demera Living?', 'answer' => 'Daftar akun terlebih dahulu, pilih kamar yang tersedia, lalu ikuti proses pemesanan. Fitur pemesanan online akan segera aktif sepenuhnya.', 'category' => 'booking'],
            ['question' => 'Apakah deposit bisa dikembalikan?', 'answer' => 'Ya, deposit dikembalikan penuh saat masa sewa berakhir sesuai kebijakan pembatalan, dikurangi biaya kerusakan bila ada.', 'category' => 'payment'],
            ['question' => 'Metode pembayaran apa saja yang tersedia?', 'answer' => 'Saat ini tersedia transfer bank manual. Virtual Account, QRIS, dan e-wallet akan menyusul pada tahap pengembangan berikutnya.', 'category' => 'payment'],
            ['question' => 'Apakah saya wajib login untuk melihat kamar?', 'answer' => 'Tidak. Anda dapat melihat katalog kamar tanpa login, namun wajib login untuk melakukan pemesanan.', 'category' => 'general'],
            ['question' => 'Bagaimana jika saya ingin memperpanjang sewa?', 'answer' => 'Anda dapat mengajukan perpanjangan melalui dashboard akun Anda. Fitur ini akan tersedia pada tahap pengembangan berikutnya.', 'category' => 'general'],
            ['question' => 'Kapan Demera Fashion resmi diluncurkan?', 'answer' => 'Kami belum mengumumkan tanggal pasti. Daftarkan email atau WhatsApp Anda di halaman Demera Fashion untuk mendapat kabar terbaru.', 'category' => 'fashion'],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::create([
                ...$faq,
                'is_published' => true,
                'sort_order' => $index,
            ]);
        }
    }
}
