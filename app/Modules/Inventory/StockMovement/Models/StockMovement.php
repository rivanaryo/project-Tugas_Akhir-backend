<?php

namespace App\Modules\Inventory\StockMovement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\ManagementWarehouse\Models\Warehouse;

class StockMovement extends Model
{
    use HasUuids;
    protected  $fillable = [
        'm_products_id',
        'm_warehouses_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'referenceable_id',
        'referenceable_type',
        'reference_number',
        'description',
        'user_id',
    ];

    protected $appends = ['product_name', 'warehouse_name'];

    public function getProductNameAttribute(): ?string
    {
        return $this->product?->product_name;
    }

    public function getWarehouseNameAttribute(): ?string
    {
        return $this->warehouse?->name;
    }

    public function referenceable()
    {
        return $this->morphTo();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'm_products_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'm_warehouses_id');
    }
}
