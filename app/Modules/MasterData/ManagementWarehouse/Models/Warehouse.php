<?php

namespace App\Modules\MasterData\ManagementWarehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\Inventory\StockMovement\Models\StockMovement;

class Warehouse extends Model
{
    use HasUuids;

    protected $table = 'warehouses';

    protected $fillable = [
        'code',
        'name',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'warehouse_id');
    }
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'm_warehouses_id');
    }
}
