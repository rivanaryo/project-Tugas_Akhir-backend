<?php

namespace App\Modules\Sales\SalesDelivery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Sales\SalesDelivery\Reference\Models\SalesDeliveryItems;
use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use App\Modules\Sales\SalesOrder\Reference\Models\SalesOrderItems;

class SalesDelivery extends Model
{
    use HasUuids;

    protected $table = 'sales_delivery';

    protected $fillable = [
        'delivery_number',
        'customer_id',
        'sales_order_id',
        'delivery_date',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(SalesDeliveryItems::class, 'sales_delivery_id');
    }

    public function stockMovements()
    {
        return $this->morphMany(\App\Modules\Inventory\StockMovement\Models\StockMovement::class, 'referenceable');
    }
    
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function salesOrder() {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }






}
