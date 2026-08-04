<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fashion_launch_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('source', 50)->default('coming_soon_page');
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamps();

            $table->unique('email');
            $table->unique('whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fashion_launch_subscribers');
    }
};
