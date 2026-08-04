<?php

namespace Tests\Feature\Admin;

use App\Domain\Platform\Models\ContentPage;
use App\Domain\Platform\Models\Faq;
use App\Domain\Platform\Models\Gallery;
use App\Domain\Platform\Models\Testimonial;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function customer(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');

        return $user;
    }

    public function test_admin_dashboard_renders_for_super_admin(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/dashboard')->assertOk();
    }

    public function test_customer_cannot_access_admin_content_pages(): void
    {
        $this->actingAs($this->customer())->get('/admin/content/pages')->assertForbidden();
    }

    public function test_super_admin_can_create_update_and_delete_content_page(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/admin/content/pages', [
            'group' => 'business_info',
            'key' => 'about-living',
            'title' => 'Tentang Demera Living',
            'is_published' => true,
            'sort_order' => 0,
        ])->assertRedirect();

        $page = ContentPage::query()->where('key', 'about-living')->firstOrFail();
        $this->assertSame('Tentang Demera Living', $page->title);

        $this->actingAs($admin)->put("/admin/content/pages/{$page->id}", [
            'group' => 'business_info',
            'key' => 'about-living',
            'title' => 'Tentang Demera Living (Updated)',
            'is_published' => true,
            'sort_order' => 1,
        ])->assertRedirect();

        $this->assertSame('Tentang Demera Living (Updated)', $page->fresh()->title);

        $this->actingAs($admin)->delete("/admin/content/pages/{$page->id}")->assertRedirect();
        $this->assertSoftDeleted($page);
    }

    public function test_super_admin_can_upload_gallery_photo(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/admin/content/galleries', [
            'category' => 'property',
            'image' => UploadedFile::fake()->image('exterior.jpg', 1200, 800),
            'is_published' => true,
        ])->assertRedirect();

        $this->assertDatabaseCount('galleries', 1);
        $gallery = Gallery::first();
        $this->assertNotNull($gallery->image_path);
        $this->assertNotNull($gallery->thumbnail_path);
    }

    public function test_super_admin_can_manage_testimonials(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/admin/content/testimonials', [
            'author_name' => 'Budi Santoso',
            'content' => 'Kostnya nyaman dan bersih.',
            'source' => 'living',
            'rating' => 5,
            'is_published' => true,
            'sort_order' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('testimonials', ['author_name' => 'Budi Santoso']);
    }

    public function test_super_admin_can_manage_faqs(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/admin/content/faqs', [
            'question' => 'Apakah bisa bayar bulanan?',
            'answer' => 'Bisa, sewa dapat dibayar per bulan.',
            'category' => 'payment',
            'is_published' => true,
            'sort_order' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('faqs', ['question' => 'Apakah bisa bayar bulanan?']);
    }

    public function test_super_admin_can_update_settings(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put('/admin/settings', [
            'values' => [
                'contact_whatsapp' => '+6281234567890',
                'social_instagram' => 'https://instagram.com/demera',
            ],
        ])->assertRedirect();

        $this->assertSame('+6281234567890', \App\Domain\Platform\Models\ApplicationSetting::get('contact_whatsapp'));
    }
}
