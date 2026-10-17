<?php

namespace App\Modules\Rd\TechPack\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TechPackCosting extends Model
{
    use HasUuids;

    protected $table = 'tech_pack_costings';

    protected $fillable = [
        'tech_pack_id',
        'printing_cost',
        'sewing_cost',
        'additional_cost',
        'margin_type',
        'margin',
    ];

    protected $casts = [
        'material_cost' => 'decimal:2',
        'printing_cost' => 'decimal:2',
        'sewing_cost' => 'decimal:2',
        'additional_cost' => 'decimal:2',
        'hpp' => 'decimal:2',
        'margin' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    public function techPack()
    {
        return $this->belongsTo(TechPack::class, 'tech_pack_id');
    }
}
