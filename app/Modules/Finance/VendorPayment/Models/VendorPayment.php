<?php

namespace App\Modules\Finance\VendorPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\ManagementVendor\Models\Vendor;
use App\Modules\MasterData\ManagementAccount\Models\Account; 

class VendorPayment extends Model
{
    use HasUuids;
    protected $table = 'vendor_payments';

    protected $fillable = [
        'payment_number',
        'vendor_id',
        'vendor_invoice_number',
        'payment_type',
        'payment_date',
        'total_paid',
        'tax_rate',
        'tax_amount',
        'account_id',
        'status',
    ];

    protected $casts = [
        'total_paid' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'payment_date' => 'date',
    ];

    public function vendors(){
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
    
    public function accounts(){
        return $this->belongsTo(Account::class, 'account_id');
    }

}
