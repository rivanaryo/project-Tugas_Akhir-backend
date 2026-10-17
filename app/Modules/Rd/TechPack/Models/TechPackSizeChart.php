<?php

namespace App\Modules\Rd\TechPack\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TechPackSizeChart extends Model
{
    use HasUuids;

    protected $table = 'tech_pack_size_charts';

    protected $fillable = [
        'tech_pack_id',
        'size',
        'body_length',
        'chest_width',
        'sleeve_length',
        'shoulder_width',
    ];

    protected $casts = [
        'body_length' => 'decimal:2',
        'chest_width' => 'decimal:2',
        'sleeve_length' => 'decimal:2',
        'shoulder_width' => 'decimal:2',
    ];

    public function techPack()
    {
        return $this->belongsTo(TechPack::class, 'tech_pack_id');
    }
}
