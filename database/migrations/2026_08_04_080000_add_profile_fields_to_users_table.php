<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_number', 20)->unique()->after('email');
            $table->timestamp('whatsapp_verified_at')->nullable()->after('whatsapp_number');
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->timestamp('terms_accepted_at')->nullable()->after('must_change_password');
            $table->timestamp('last_login_at')->nullable()->after('terms_accepted_at');
            $table->string('avatar_path')->nullable()->after('last_login_at');
            $table->boolean('is_active')->default(true)->after('avatar_path');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number',
                'whatsapp_verified_at',
                'must_change_password',
                'terms_accepted_at',
                'last_login_at',
                'avatar_path',
                'is_active',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
