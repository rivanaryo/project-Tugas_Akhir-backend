<?php

namespace App\Modules\Sales\SalesQuotation\Reference\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Sales\SalesQuotation\Models\SalesQuotation;
use App\Modules\MasterData\DataProduk\Models\Product;

class SalesQuotationItems extends Model
{
    use HasUuids;

    protected $table = 'sales_quotation_items';

    protected $fillable = [
        'sales_quotation_id',
        'product_id',
        'size',
        'quantity',
        'unit_price',
        'total_price',
        'tax_rate',
        'tax_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function salesQuotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Calculate total price based on quantity and unit price
     */
    public function calculateTotalPrice(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Calculate tax amount based on total price and tax rate
     */
    public function calculateTaxAmount(): float
    {
        return $this->total_price * $this->tax_rate / 100;
    }

    /**
     * Update total price and save
     */
    public function updateTotalPrice(): bool
    {
        $this->total_price = $this->calculateTotalPrice();
        return $this->save();
    }

    /**
     * Update tax amount and save
     */
    public function updateTaxAmount(): bool
    {
        $this->tax_amount = $this->calculateTaxAmount();
        return $this->save();
    }

}
