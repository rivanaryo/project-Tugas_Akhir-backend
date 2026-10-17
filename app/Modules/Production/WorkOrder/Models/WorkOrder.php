<?php

namespace App\Modules\Production\WorkOrder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\Rd\TechPack\Models\TechPack;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;

class WorkOrder extends Model
{
    use HasUuids;

    protected $table = 'work_orders';

    protected $fillable = [
        'wo_number',
        'sales_order_id',
        'product_id',
        'tech_pack_id',
        'quantity',
        'stage',
        'status',
        'start_date',
        'due_date',
        'progress',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'progress' => 'integer',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function techPack()
    {
        return $this->belongsTo(TechPack::class, 'tech_pack_id');
    }

    public static function stageToProgress(string $stage): int
    {
        return match ($stage) {
            'cutting' => 20,
            'printing' => 40,
            'sewing' => 60,
            'qc' => 80,
            'ready' => 100,
            default => 0,
        };
    }
}
