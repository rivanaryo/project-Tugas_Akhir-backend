<?php

namespace App\Modules\Inventory\StockOpname\Models;

use App\Modules\Admin\Models\Admin;
use App\Modules\MasterData\ManagementWarehouse\Models\Warehouse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasUuids;

    protected $table = 'stock_opnames';

    protected $fillable = [
        'opname_number',
        'warehouse_id',
        'opname_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'opname_date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class, 'stock_opname_id');
    }
}
