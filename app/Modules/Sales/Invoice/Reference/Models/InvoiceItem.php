<?php

namespace App\Modules\Sales\Invoice\Reference\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
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
        'tax_rate',
        'tax_amount',
        'total_price',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    protected $appends = ['size'];

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItems::class, 'sales_order_item_id');
    }

    public function getSizeAttribute(): ?string
    {
        return $this->salesOrderItem?->size;
    }
}
