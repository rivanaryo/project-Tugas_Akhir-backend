<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_pack_size_charts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tech_pack_id')->constrained('tech_packs')->cascadeOnDelete();
            $table->string('size');
            $table->decimal('body_length', 8, 2)->nullable();
            $table->decimal('chest_width', 8, 2)->nullable();
            $table->decimal('sleeve_length', 8, 2)->nullable();
            $table->decimal('shoulder_width', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tech_pack_size_charts');
    }
};
