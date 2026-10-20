<?php

namespace App\Modules\Production\PurchaseOrder\Models;

use App\Modules\Inventory\StockMovement\Models\StockMovement;
use App\Modules\MasterData\ManagementVendor\Models\Vendor;
use App\Modules\Production\GoodReceipt\Models\GoodReceipt;
use App\Modules\Production\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasUuids;

    protected $table = 'purchase_order';

    protected $fillable = [
        'po_number',
        'vendor_id',
        'status',
        'po_date',
    ];

    protected $casts = [
        'id' => 'string',
        'po_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItems::class, 'purchase_order_id', 'id');
    }

    public function goodReceipts()
    {
        return $this->hasMany(GoodReceipt::class, 'purchase_order_id', 'id');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'referenceable');
    }
}
