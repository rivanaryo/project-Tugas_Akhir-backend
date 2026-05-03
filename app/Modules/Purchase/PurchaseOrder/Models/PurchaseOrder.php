<?php

namespace App\Modules\Purchase\PurchaseOrder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\ManagementVendor\Models\Vendor;
use App\Modules\Purchase\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use App\Modules\Purchase\GoodReceipt\Models\GoodReceipt;

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



}
