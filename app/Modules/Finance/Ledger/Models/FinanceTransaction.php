<?php

namespace App\Modules\Finance\Ledger\Models;

use App\Modules\Admin\Models\Admin;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FinanceTransaction extends Model
{
    use HasUuids;

    protected $table = 'finance_transactions';

    protected $fillable = [
        'account_id',
        'transaction_date',
        'direction',
        'amount',
        'reference_type',
        'reference_id',
        'reference_number',
        'description',
        'status',
        'transfer_group_id',
        'reversal_of_id',
        'created_by',
        'voided_at',
        'voided_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'voided_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function voidedBy()
    {
        return $this->belongsTo(Admin::class, 'voided_by');
    }

    public function reversalOf()
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversals()
    {
        return $this->hasMany(self::class, 'reversal_of_id');
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }
}
