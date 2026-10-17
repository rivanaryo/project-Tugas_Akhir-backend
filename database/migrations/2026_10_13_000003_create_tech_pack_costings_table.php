<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_pack_costings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tech_pack_id')->unique()->constrained('tech_packs')->cascadeOnDelete();
            $table->decimal('material', 15, 2)->default(0);
            $table->decimal('printing', 15, 2)->default(0);
            $table->decimal('sewing', 15, 2)->default(0);
            $table->decimal('margin', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tech_pack_costings');
    }
};
