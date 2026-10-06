<?php

namespace App\Modules\Inventory\GoodReceipt\Reference\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\ManagementPricelist\Models\Pricelist;
use App\Modules\Inventory\GoodReceipt\Models\GoodReceipt;
use App\Modules\Inventory\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use App\Modules\Inventory\PurchaseOrder\Models\PurchaseOrder;

class GoodReceiptItems extends Model
{
    use HasUuids;  
    protected $table = 'good_receipt_items';

    protected $fillable = [
        'good_receipt_id',
        'purchase_order_item_id',
        'pricelist_id',
        'product_id',
        'qty_received',
    ];  

    protected $casts = [
        'id' => 'string',
    ];

    public function goodReceipt()
    {
        return $this->belongsTo(GoodReceipt::class, 'good_receipt_id', 'id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItems::class, 'purchase_order_item_id', 'id');
    }

    public function pricelist()
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
