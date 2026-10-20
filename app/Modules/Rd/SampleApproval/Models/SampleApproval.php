<?php

namespace App\Modules\Rd\SampleApproval\Models;

use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SampleApproval extends Model
{
    use HasUuids;

    protected $table = 'sample_approvals';

    protected $fillable = [
        'sample_code',
        'product_id',
        'customer_id',
        'sales_order_id',
        'submitted_date',
        'status',
        'notes',
        'revision_count',
    ];

    protected $casts = [
        'submitted_date' => 'date',
        'revision_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
}
