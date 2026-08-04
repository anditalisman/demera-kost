<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\ApplicationSetting;
use App\Domain\Platform\Models\ContentPage;
use App\Domain\Platform\Models\Faq;
use App\Domain\Platform\Models\NotificationTemplate;
use App\Domain\Platform\Models\Testimonial;
use App\Models\User;
use Database\Seeders\ApplicationSettingSeeder;
use Database\Seeders\ContentPageSeeder;
use Database\Seeders\DemoStaffSeeder;
use Database\Seeders\FacilitySeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Database\Seeders\TestimonialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Covers the seeders that don't touch object storage (no MinIO round-trips
 * in the automated suite). PropertySeeder/GallerySeeder/TenantBookingSeeder
 * upload real images and are verified manually/in staging instead — see
 * docs/ROADMAP.md and the Tahap 1 verification notes.
 */
class SeederIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseline(): void
    {
        $this->seed([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            DemoStaffSeeder::class,
            ContentPageSeeder::class,
            ApplicationSettingSeeder::class,
            FacilitySeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            NotificationTemplateSeeder::class,
        ]);
    }

    public function test_all_six_roles_exist_with_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $roles = Role::pluck('name')->all();
        foreach (['super-admin', 'admin', 'property-manager', 'finance', 'customer', 'tenant'] as $expected) {
            $this->assertContains($expected, $roles);
        }

        $this->assertGreaterThan(0, Permission::count());
        $this->assertTrue(Role::findByName('super-admin')->permissions->count() === Permission::count());
    }

    public function test_super_admin_is_seeded_from_env_and_must_change_password(): void
    {
        $this->seed([RolePermissionSeeder::class, SuperAdminSeeder::class]);

        $admin = User::query()->where('email', env('SUPERADMIN_EMAIL', 'superadmin@demera.my.id'))->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('super-admin'));
        $this->assertTrue($admin->must_change_password);
    }

    public function test_demo_staff_accounts_have_correct_single_roles(): void
    {
        $this->seed([RolePermissionSeeder::class, DemoStaffSeeder::class]);

        $this->assertTrue(User::where('email', 'admin@demera.my.id')->first()?->hasRole('admin'));
        $this->assertTrue(User::where('email', 'pengelola@demera.my.id')->first()?->hasRole('property-manager'));
        $this->assertTrue(User::where('email', 'finance@demera.my.id')->first()?->hasRole('finance'));
    }

    public function test_policy_pages_required_by_register_and_footer_are_seeded(): void
    {
        $this->seed(ContentPageSeeder::class);

        foreach (['syarat-penggunaan', 'kebijakan-privasi', 'kebijakan-pembayaran'] as $slug) {
            $page = ContentPage::query()->where('group', 'policy')->where('key', $slug)->first();
            $this->assertNotNull($page, "Policy page [{$slug}] must be seeded or /kebijakan/{$slug} 404s.");
            $this->assertTrue($page->is_published);
        }
    }

    public function test_content_and_reference_data_seeders_are_idempotent(): void
    {
        $this->seedBaseline();
        $faqCount = Faq::count();
        $testimonialCount = Testimonial::count();
        $templateCount = NotificationTemplate::count();

        // Re-running must not duplicate rows (FAQ/Testimonial guard on
        // existing rows; ContentPage/Settings/NotificationTemplate upsert).
        $this->seedBaseline();

        $this->assertSame($faqCount, Faq::count());
        $this->assertSame($testimonialCount, Testimonial::count());
        $this->assertSame($templateCount, NotificationTemplate::count());
    }

    public function test_public_application_settings_are_seeded(): void
    {
        $this->seed(ApplicationSettingSeeder::class);

        $this->assertNotEmpty(ApplicationSetting::get('contact_whatsapp'));
        $this->assertNotEmpty(ApplicationSetting::get('seo_default_title'));
    }
}
