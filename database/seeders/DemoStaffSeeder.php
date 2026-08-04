<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoStaffSeeder extends Seeder
{
    /**
     * A second demo admin account (distinct from the env-seeded root admin
     * in SuperAdminSeeder) so "log in as a regular admin" can be tested
     * without touching the root account. Demo password only — never used
     * in a real production deploy.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'admin@demera.my.id'],
            [
                'name' => 'Admin Demera',
                'whatsapp_number' => '+628160000001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'whatsapp_verified_at' => now(),
                'terms_accepted_at' => now(),
                'is_active' => true,
            ],
        );

        $user->syncRoles(['admin']);
    }
}
