<?php

namespace App\Modules\Production\Bom\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use App\Modules\Rd\TechPack\Models\TechPack;

class BomItem extends Model
{
    use HasUuids;

    protected $table = 'bom_items';

    protected $fillable = [
        'tech_pack_id',
        'product_id',
        'usage',
        'unit_id',
        'notes',
    ];

    protected $casts = [
        'usage' => 'decimal:4',
    ];

    public function techPack()
    {
        return $this->belongsTo(TechPack::class, 'tech_pack_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(Units::class, 'unit_id');
    }
}
