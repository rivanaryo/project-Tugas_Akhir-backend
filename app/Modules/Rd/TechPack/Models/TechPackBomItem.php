<?php

namespace App\Modules\Rd\TechPack\Models;

use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TechPackBomItem extends Model
{
    use HasUuids;

    protected $table = 'tech_pack_bom_items';

    protected $fillable = [
        'tech_pack_id',
        'material_product_id',
        'usage',
        'unit_id',
        'cost_per_unit',
        'total',
    ];

    protected $casts = [
        'usage' => 'decimal:4',
        'cost_per_unit' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function techPack()
    {
        return $this->belongsTo(TechPack::class, 'tech_pack_id');
    }

    public function material()
    {
        return $this->belongsTo(Product::class, 'material_product_id');
    }

    public function unit()
    {
        return $this->belongsTo(Units::class, 'unit_id');
    }
}
