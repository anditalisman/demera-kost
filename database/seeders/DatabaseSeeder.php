<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            DemoStaffSeeder::class,
            ContentPageSeeder::class,
            ApplicationSettingSeeder::class,
            FacilitySeeder::class,
            PropertySeeder::class,
            GallerySeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
            NotificationTemplateSeeder::class,
            TenantBookingSeeder::class,
        ]);
    }
}
