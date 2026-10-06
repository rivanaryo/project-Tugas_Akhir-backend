<?php

namespace App\Modules\Inventory\PurchaseOrder\Reference\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\ManagementPricelist\Models\Pricelist;
use App\Modules\Inventory\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Inventory\GoodReceipt\Reference\Models\GoodReceiptItems;


class PurchaseOrderItems extends Model
{
    use HasUuids;
    protected $table = 'purchase_order_items';
    
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'pricelist_id',
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

    public function pricelist()
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id', 'id');
    }

    public function goodReceiptsItems()
    {
        return $this->hasMany(GoodReceiptItems::class, 'purchase_order_item_id', 'id');
    }
}
