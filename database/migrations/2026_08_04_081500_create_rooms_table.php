<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('room_number', 20);
            $table->string('slug')->unique();
            $table->string('name')->nullable();
            $table->string('status', 20)->default('available');
            $table->decimal('size_sqm', 5, 2)->nullable();
            $table->unsignedTinyInteger('capacity')->default(1);
            $table->decimal('monthly_price', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->json('additional_fees')->nullable();
            $table->longText('description')->nullable();
            $table->date('available_from')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'room_number']);
            $table->index('status');
            $table->index(['property_id', 'status']);
            $table->index(['room_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
