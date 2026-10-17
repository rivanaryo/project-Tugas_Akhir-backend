<?php

namespace App\Modules\Rd\Sample\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use App\Modules\Rd\TechPack\Models\TechPack;

class Sample extends Model
{
    use HasUuids;

    protected $table = 'samples';

    protected $fillable = [
        'sample_number',
        'product_id',
        'customer_id',
        'tech_pack_id',
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

    public function techPack()
    {
        return $this->belongsTo(TechPack::class, 'tech_pack_id');
    }
}
