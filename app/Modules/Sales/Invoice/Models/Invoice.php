<?php

namespace App\Modules\Sales\Invoice\Models;

use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasUuids;
    protected $table = 'invoices';
    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'customer_id',
        'sales_order_id',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'invoice_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }
    
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
    
}
