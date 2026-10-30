<?php

namespace App\Modules\Sales\SalesDelivery\Reference\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\Sales\SalesDelivery\Models\SalesDelivery;
use App\Modules\Sales\SalesOrder\Reference\Models\SalesOrderItems;
use Illuminate\Database\Eloquent\Model;

class SalesDeliveryItems extends Model
{
    use HasUuids;

    protected $table = 'sales_delivery_items';

    protected $fillable = [
        'sales_delivery_id',
        'sales_order_item_id',
        'qty_delivered',
    ];

    protected $casts = [
        'id' => 'string',
        'qty_delivered' => 'integer',
    ];

    protected $appends = ['size'];

    public function getSizeAttribute(): ?string
    {
        return $this->salesOrderItem?->size;
    }

    public function salesDelivery()
    {
        return $this->belongsTo(SalesDelivery::class, 'sales_delivery_id');
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItems::class, 'sales_order_item_id');
    }
}
