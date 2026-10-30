<?php

namespace App\Modules\Sales\SalesQuotation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use App\Modules\Sales\SalesQuotation\Reference\Models\SalesQuotationItems;

class SalesQuotation extends Model
{
    use HasUuids;

    protected $table = 'sales_quotation';

    protected $fillable = [
        'quotation_number',
        'customer_id',
        'quotation_date',
        'valid_until',
        'status',
        'subtotal',
        'total_tax',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'float',
        'total_tax' => 'float',
        'grand_total' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function salesOrders()
    {
        return $this->hasMany(\App\Modules\Sales\SalesOrder\Models\SalesOrder::class, 'sales_quotation_id');
    }

    public function items()
    {
        return $this->hasMany(SalesQuotationItems::class, 'sales_quotation_id');
    }

    /**
     * Calculate subtotal, total tax, and grand total based on quotation items
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
    * Update quotation status
    */
    public function updateStatus(string $status): bool
    {
        $this->status = $status;
        return $this->save();
    }

}
