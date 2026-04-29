<?php

namespace App\Modules\MasterData\DataProduk\References\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\MasterData\DataProduk\Models\Product;

class Units extends Model
{
    use HasUuids;
    protected $table = 'units';

    protected $fillable = [
        'unit_code',
        'unit_name',
        'is_active',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id', 'id');
    }




}
