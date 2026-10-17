<?php

namespace App\Modules\MasterData\SizeChart\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\Rd\TechPack\Models\TechPack;

class SizeChart extends Model
{
    use HasUuids;

    protected $table = 'size_charts';

    protected $fillable = [
        'product_id',
        'tech_pack_id',
        'size',
        'chest_width',
        'body_length',
        'sleeve_length',
        'shoulder_width',
    ];

    protected $casts = [
        'chest_width' => 'decimal:2',
        'body_length' => 'decimal:2',
        'sleeve_length' => 'decimal:2',
        'shoulder_width' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function techPack()
    {
        return $this->belongsTo(TechPack::class, 'tech_pack_id');
    }
}
