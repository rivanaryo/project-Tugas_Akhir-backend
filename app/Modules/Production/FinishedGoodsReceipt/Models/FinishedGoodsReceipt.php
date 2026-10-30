<?php

namespace App\Modules\Production\FinishedGoodsReceipt\Models;

use App\Modules\Inventory\StockMovement\Models\StockMovement;
use App\Modules\MasterData\ManagementWarehouse\Models\Warehouse;
use App\Modules\Production\FinishedGoodsReceipt\Reference\Models\FinishedGoodsReceiptItems;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FinishedGoodsReceipt extends Model
{
    use HasUuids;

    protected $table = 'finished_goods_receipts';

    protected $fillable = [
        'fgr_number',
        'warehouse_id',
        'receipt_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(FinishedGoodsReceiptItems::class, 'finished_goods_receipt_id');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'referenceable');
    }
}
