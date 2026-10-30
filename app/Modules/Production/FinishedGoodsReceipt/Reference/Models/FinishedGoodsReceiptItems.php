<?php

namespace App\Modules\Production\FinishedGoodsReceipt\Reference\Models;

use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\Production\FinishedGoodsReceipt\Models\FinishedGoodsReceipt;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FinishedGoodsReceiptItems extends Model
{
    use HasUuids;

    protected $table = 'finished_goods_receipt_items';

    protected $fillable = [
        'finished_goods_receipt_id',
        'work_order_id',
        'product_id',
        'size',
        'qty_good',
        'qty_reject',
    ];

    protected $casts = [
        'qty_good' => 'integer',
        'qty_reject' => 'integer',
    ];

    public function finishedGoodsReceipt()
    {
        return $this->belongsTo(FinishedGoodsReceipt::class, 'finished_goods_receipt_id');
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
