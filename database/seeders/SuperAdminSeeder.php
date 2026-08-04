<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL', 'superadmin@demera.my.id');
        $whatsapp = env('SUPERADMIN_WHATSAPP', '+6281200000000');
        $password = env('SUPERADMIN_PASSWORD');

        if (empty($password)) {
            $password = Str::password(16);
            $this->command?->warn("SUPERADMIN_PASSWORD not set in .env — generated random password: {$password}");
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SUPERADMIN_NAME', 'Demera Super Admin'),
                'whatsapp_number' => $whatsapp,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'whatsapp_verified_at' => now(),
                'terms_accepted_at' => now(),
                'must_change_password' => true,
                'is_active' => true,
            ],
        );

        $user->syncRoles(['super-admin']);
    }
}
