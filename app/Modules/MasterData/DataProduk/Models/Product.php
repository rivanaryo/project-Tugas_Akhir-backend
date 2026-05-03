<?php

namespace App\Modules\MasterData\DataProduk\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\MasterData\DataProduk\References\Models\Category;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\ManagementPricelist\Models\Pricelist;
use App\Modules\MasterData\ManagementVendor\Models\Vendor;

class Product extends Model
{
    use HasUuids;
    protected $table = 'm_products';


    protected $fillable = [
        'product_name',
        'category_id',
        'unit_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Units::class, 'unit_id', 'id');
    }
    
    public function pricelists()
    {
        return $this->hasMany(Pricelist::class, 'product_id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'm_pricelists', 'product_id', 'vendor_id');
    }

}
