<?php

namespace App\Modules\Inventory\StockOpname\Models;

use App\Modules\MasterData\DataProduk\Models\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    use HasUuids;

    protected $table = 'stock_opname_items';

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_qty',
        'physical_qty',
        'difference_qty',
        'notes',
    ];

    protected $casts = [
        'system_qty' => 'integer',
        'physical_qty' => 'integer',
        'difference_qty' => 'integer',
    ];

    public function opname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
