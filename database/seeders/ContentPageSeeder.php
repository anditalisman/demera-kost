<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\ContentPage;
use Illuminate\Database\Seeder;

class ContentPageSeeder extends Seeder
{
    public function run(): void
    {
        ContentPage::query()->updateOrCreate(
            ['group' => 'hero_slide', 'key' => null],
            [
                'title' => 'Gaya Hidup Modern, Hunian yang Terasa Seperti Rumah',
                'subtitle' => 'Demera Fashion & Demera Living',
                'body' => 'Demera menghadirkan dua dunia dalam satu platform: fashion editorial yang akan segera hadir, dan kost nyaman terpercaya yang siap Anda huni hari ini.',
                'is_published' => true,
                'sort_order' => 0,
            ],
        );

        ContentPage::query()->updateOrCreate(
            ['group' => 'business_info', 'key' => 'living'],
            [
                'title' => 'Demera Living',
                'body' => 'Kost yang hangat, nyaman, dan terpercaya — dilengkapi fasilitas lengkap dan proses sewa yang transparan dari pemesanan hingga pembayaran.',
                'is_published' => true,
            ],
        );

        ContentPage::query()->updateOrCreate(
            ['group' => 'business_info', 'key' => 'fashion'],
            [
                'title' => 'Demera Fashion',
                'body' => 'Lini fashion editorial dan minimalis Demera sedang dipersiapkan. Daftarkan diri Anda untuk menjadi yang pertama tahu saat kami meluncur.',
                'is_published' => true,
            ],
        );

        ContentPage::query()->updateOrCreate(
            ['group' => 'policy', 'key' => 'syarat-penggunaan'],
            [
                'title' => 'Syarat Penggunaan',
                'subtitle' => 'Berlaku untuk seluruh pengguna platform Demera',
                'body' => $this->termsBody(),
                'is_published' => true,
            ],
        );

        ContentPage::query()->updateOrCreate(
            ['group' => 'policy', 'key' => 'kebijakan-privasi'],
            [
                'title' => 'Kebijakan Privasi',
                'subtitle' => 'Bagaimana Demera mengelola data Anda',
                'body' => $this->privacyBody(),
                'is_published' => true,
            ],
        );

        ContentPage::query()->updateOrCreate(
            ['group' => 'policy', 'key' => 'kebijakan-pembayaran'],
            [
                'title' => 'Kebijakan Pembayaran & Pembatalan',
                'subtitle' => 'Ketentuan deposit, tagihan, dan pembatalan sewa',
                'body' => $this->paymentPolicyBody(),
                'is_published' => true,
            ],
        );
    }

    private function termsBody(): string
    {
        return <<<'HTML'
            <p>Dengan mendaftar dan menggunakan layanan Demera, Anda menyetujui ketentuan berikut:</p>
            <ul>
                <li>Anda bertanggung jawab atas kebenaran data yang diberikan saat registrasi dan pemesanan.</li>
                <li>Pemesanan kamar hanya dapat dilakukan setelah akun Anda terverifikasi.</li>
                <li>Demera berhak membatalkan pemesanan apabila pembayaran tidak diselesaikan dalam batas waktu yang ditentukan.</li>
                <li>Penyalahgunaan akun atau data pengguna lain dapat berakibat pemblokiran akun secara permanen.</li>
            </ul>
            HTML;
    }

    private function privacyBody(): string
    {
        return <<<'HTML'
            <p>Demera mengumpulkan data pribadi (nama, email, nomor WhatsApp, dokumen identitas) semata untuk keperluan verifikasi, pemesanan kamar, dan komunikasi terkait layanan.</p>
            <ul>
                <li>Dokumen identitas disimpan pada penyimpanan privat dan tidak dapat diakses publik.</li>
                <li>Data tidak dibagikan ke pihak ketiga kecuali diwajibkan oleh hukum.</li>
                <li>Anda dapat meminta penghapusan akun dan data pribadi Anda melalui tim dukungan kami.</li>
            </ul>
            HTML;
    }

    private function paymentPolicyBody(): string
    {
        return <<<'HTML'
            <p>Setiap pemesanan kamar dikenakan deposit yang akan dikembalikan penuh saat masa sewa berakhir, dikurangi biaya kerusakan apabila ada.</p>
            <ul>
                <li>Tagihan sewa bulanan wajib dibayar sebelum tanggal jatuh tempo yang tertera pada invoice.</li>
                <li>Pembatalan pemesanan sebelum pembayaran tidak dikenakan biaya.</li>
                <li>Pembatalan setelah pembayaran mengikuti kebijakan pengembalian dana yang berlaku sesuai kontrak sewa.</li>
            </ul>
            HTML;
    }
}
