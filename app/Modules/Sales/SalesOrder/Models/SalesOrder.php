<?php

namespace App\Modules\Sales\SalesOrder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use App\Modules\Sales\SalesOrder\Reference\Models\SalesOrderItems;
use App\Modules\Sales\SalesDelivery\Models\SalesDelivery;
use App\Modules\Sales\Invoice\Models\Invoice;
use App\Modules\Inventory\StockMovement\Models\StockMovement;

class SalesOrder extends Model
{
    use HasUuids;

    protected $table = 'sales_order';

    protected $fillable = [
        'order_number',
        'customer_id',
        'order_date',
        'status',
        'subtotal',
        'total_tax',
        'grand_total',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'float',
        'total_tax' => 'float',
        'grand_total' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItems::class, 'sales_order_id');
    }

    public function deliveries()
    {
        return $this->hasMany(SalesDelivery::class, 'sales_order_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'sales_order_id');
    }

    /**
     * Calculate subtotal, total tax, and grand total based on order items
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;
        $totalTax = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->total_price;
            $totalTax += $item->tax_amount;
        }

        $this->subtotal = $subtotal;
        $this->total_tax = $totalTax;
        $this->grand_total = $subtotal + $totalTax;
        $this->save();
    }

    /**
    * Update order status
    */
    public function updateStatus(string $status): bool
    {
        $this->status = $status;
        return $this->save();
    }
     
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'referenceable');
    }
    

}
