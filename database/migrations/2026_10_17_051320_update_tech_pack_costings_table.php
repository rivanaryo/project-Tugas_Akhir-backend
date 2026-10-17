<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tech_pack_costings', function (Blueprint $table) {
            $table->dropColumn(['material', 'printing', 'sewing', 'margin']);

            $table->decimal('material_cost', 15, 2)->default(0)->after('tech_pack_id');
            $table->decimal('printing_cost', 15, 2)->default(0)->after('material_cost');
            $table->decimal('sewing_cost', 15, 2)->default(0)->after('printing_cost');
            $table->decimal('additional_cost', 15, 2)->default(0)->after('sewing_cost');
            $table->decimal('hpp', 15, 2)->default(0)->after('additional_cost');
            $table->enum('margin_type', ['nominal', 'percentage'])->default('nominal')->after('hpp');
            $table->decimal('margin', 15, 2)->default(0)->after('margin_type');
            $table->decimal('sell_price', 15, 2)->default(0)->after('margin');
        });
    }

    public function down(): void
    {
        Schema::table('tech_pack_costings', function (Blueprint $table) {
            $table->dropColumn([
                'material_cost',
                'printing_cost',
                'sewing_cost',
                'additional_cost',
                'hpp',
                'margin_type',
                'margin',
                'sell_price',
            ]);

            $table->decimal('material', 15, 2)->default(0);
            $table->decimal('printing', 15, 2)->default(0);
            $table->decimal('sewing', 15, 2)->default(0);
            $table->decimal('margin', 15, 2)->default(0);
        });
    }
};
