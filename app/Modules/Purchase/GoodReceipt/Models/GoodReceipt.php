<?php

namespace App\Modules\Purchase\GoodReceipt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\ManagementVendor\Models\Vendor;
use App\Modules\Purchase\GoodReceipt\Reference\Models\GoodReceiptItems;
use App\Modules\Purchase\PurchaseOrder\Models\PurchaseOrder;

class GoodReceipt extends Model
{
    use HasUuids;  
    protected $table = 'good_receipt';

    protected $fillable = [
        'gr_number',
        'purchase_order_id',
        'vendor_id',
        'receipt_date',
        'status',
    ];  

    protected $casts = [
        'id' => 'string',
        'receipt_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

   public function items()
   {
        return $this->hasMany(GoodReceiptItems::class, 'good_receipt_id', 'id');
   }


}
