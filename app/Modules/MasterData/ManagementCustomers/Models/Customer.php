<?php

namespace App\Modules\MasterData\ManagementCustomers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use App\Modules\Sales\SalesDelivery\Models\SalesDelivery;
use App\Modules\Sales\Invoice\Models\Invoice;

class Customer extends Model
{
    use HasUuids;

    protected $table = 'm_customer';

    protected $fillable = [
        'customer_name',
        'contact_person',
        'phone_number',
        'email',
        'address',
        'is_active',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }

    public function salesDeliveries()
    {
        return $this->hasMany(SalesDelivery::class, 'customer_id');
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    
}
