<?php

namespace App\Modules\Rd\TechPack\Models;

use App\Modules\MasterData\DataProduk\Models\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TechPack extends Model
{
    use HasUuids;

    protected $table = 'tech_packs';

    protected $fillable = [
        'name',
        'fabric',
        'gsm',
        'thread',
        'zipper',
        'accessories',
        'status',
        'product_id',
    ];

    protected $casts = [
        'accessories' => 'array',
        'gsm' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sizeCharts()
    {
        return $this->hasMany(TechPackSizeChart::class, 'tech_pack_id');
    }

    public function costing()
    {
        return $this->hasOne(TechPackCosting::class, 'tech_pack_id');
    }

    public function bomItems()
    {
        return $this->hasMany(TechPackBomItem::class, 'tech_pack_id');
    }
}
