<?php

namespace App\Modules\Production\PurchaseOrder\Reference\Models;

use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use App\Modules\Production\GoodReceipt\Reference\Models\GoodReceiptItems;
use App\Modules\Production\PurchaseOrder\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItems extends Model
{
    use HasUuids;

    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'unit_id',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'total_price',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Units::class, 'unit_id', 'id');
    }

    public function goodReceiptsItems()
    {
        return $this->hasMany(GoodReceiptItems::class, 'purchase_order_item_id', 'id');
    }
}
