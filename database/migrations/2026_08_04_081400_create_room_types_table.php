<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('base_deposit', 12, 2)->default(0);
            $table->decimal('size_sqm', 5, 2)->nullable();
            $table->unsignedTinyInteger('default_capacity')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
