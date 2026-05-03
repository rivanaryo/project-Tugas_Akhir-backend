<?php

namespace App\Modules\MasterData\ManagementPricelist\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\ManagementVendor\Models\Vendor;
use App\Modules\MasterData\DataProduk\Models\Product;

class Pricelist extends Model
{
    use HasUuids;
    protected $table = 'm_pricelists';

    protected $fillable = [
        'vendor_id',
        'product_id',
        'price',
        'currency',
        'is_active',
    ];
    
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
