<?php

namespace App\Modules\Sales\Invoice\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Sales\Invoice\Models\Invoice;
use App\Modules\Sales\SalesOrder\Reference\Models\SalesOrderItems;

class InvoiceItem extends Model
{
    use HasUuids;

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'sales_order_item_id',
        'qty_invoice',
        'unit_price',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_price',
    ];

    protected $casts = [
        'id' => 'string',
        'qty_invoice' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItems::class, 'sales_order_item_id');
    }
}
