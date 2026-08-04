<?php

namespace Tests\Feature\Public;

use App\Domain\Platform\Models\ContentPage;
use App\Domain\Platform\Models\Faq;
use App\Domain\Platform\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_for_guests(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_landing_page_shows_published_content(): void
    {
        ContentPage::create([
            'group' => 'hero_slide',
            'title' => 'Selamat Datang di Demera',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Faq::create([
            'question' => 'Bagaimana cara memesan kamar?',
            'answer' => 'Daftar akun lalu pilih kamar yang tersedia.',
            'category' => 'booking',
            'is_published' => true,
        ]);

        Testimonial::create([
            'author_name' => 'Siti Rahma',
            'content' => 'Nyaman sekali tinggal di sini.',
            'source' => 'living',
            'is_published' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Landing')
            ->has('heroSlides', 1)
            ->has('faqs', 1)
            ->has('testimonials', 1)
        );
    }

    public function test_unpublished_content_is_not_shown(): void
    {
        ContentPage::create([
            'group' => 'hero_slide',
            'title' => 'Draft Banner',
            'is_published' => false,
        ]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->has('heroSlides', 0));
    }
}
