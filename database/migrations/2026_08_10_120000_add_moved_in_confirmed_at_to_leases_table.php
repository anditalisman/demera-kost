<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            // Null means start_date is still the provisional 7-day-grace default set at
            // booking confirmation (BookingLifecycleService::confirm()); set once an admin
            // confirms the tenant's actual move-in date (LeaseManagementService::confirmMoveIn()).
            $table->timestamp('moved_in_confirmed_at')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('moved_in_confirmed_at');
        });
    }
};
