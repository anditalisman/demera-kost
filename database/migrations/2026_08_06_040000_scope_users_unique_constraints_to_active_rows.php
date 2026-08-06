<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The plain unique(email)/unique(whatsapp_number) indexes count
 * soft-deleted rows — a deleted account still blocks that email/number
 * from ever being registered again, even though app-level validation
 * (Rule::unique()->whereNull('deleted_at')) says it's free, producing a raw
 * "Duplicate entry" SQL error instead of a validation message.
 *
 * MySQL doesn't support partial/filtered unique indexes directly, so this
 * uses the standard workaround: a generated column that's NULL for deleted
 * rows (MySQL unique indexes never conflict on NULL, so any number of
 * deleted rows can share the same original email) and the real value for
 * active ones (where uniqueness must still be enforced).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->dropUnique('users_whatsapp_number_unique');
        });

        DB::statement('ALTER TABLE users ADD COLUMN email_active VARCHAR(255) GENERATED ALWAYS AS (IF(deleted_at IS NULL, email, NULL)) VIRTUAL');
        DB::statement('ALTER TABLE users ADD COLUMN whatsapp_number_active VARCHAR(20) GENERATED ALWAYS AS (IF(deleted_at IS NULL, whatsapp_number, NULL)) VIRTUAL');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email_active', 'users_email_active_unique');
            $table->unique('whatsapp_number_active', 'users_whatsapp_number_active_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_active_unique');
            $table->dropUnique('users_whatsapp_number_active_unique');
            $table->dropColumn(['email_active', 'whatsapp_number_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
            $table->unique('whatsapp_number');
        });
    }
};
