<?php

namespace App\Modules\Finance\CustomerPayment\Models;

use App\Modules\MasterData\ManagementAccount\Models\Account;
use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasUuids;

    protected $table = 'customer_payments';

    protected $fillable = [
        'payment_number',
        'sales_order_id',
        'customer_id',
        'type',
        'amount',
        'account_id',
        'payment_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
