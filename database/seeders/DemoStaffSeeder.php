<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoStaffSeeder extends Seeder
{
    /**
     * Demo accounts for roles the credentials wizard doesn't cover. All use
     * the same demo password and are only meant for local/staging review —
     * never seeded with these credentials in a real production deploy.
     */
    public function run(): void
    {
        $accounts = [
            ['email' => 'admin@demera.my.id', 'name' => 'Admin Demera', 'role' => 'admin'],
            ['email' => 'pengelola@demera.my.id', 'name' => 'Pengelola Kost', 'role' => 'property-manager'],
            ['email' => 'finance@demera.my.id', 'name' => 'Tim Finance', 'role' => 'finance'],
        ];

        foreach ($accounts as $index => $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'whatsapp_number' => '+62816'.str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'whatsapp_verified_at' => now(),
                    'terms_accepted_at' => now(),
                    'is_active' => true,
                ],
            );

            $user->syncRoles([$account['role']]);
        }
    }
}
