<?php

namespace App\Modules\MasterData\DataProduk\References\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\DataProduk\Models\Product;

class Category extends Model
{
    use HasUuids;
    protected $table = 'categories';


    protected $fillable = [
        'category_name',
        'is_active',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }


}
