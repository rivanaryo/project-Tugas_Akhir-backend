<?php

namespace App\Modules\MasterData\ManagementAccount\Models;

use App\Modules\Finance\Ledger\Models\FinanceTransaction;
use App\Modules\Finance\VendorPayment\Models\VendorPayment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasUuids;

    protected $table = 'm_accounts';

    protected $fillable = [
        'account_name',
        'account_number',
        'type',
        'opening_balance',
        'balance',
        'bank_name',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class, 'account_id');
    }

    public function financeTransactions()
    {
        return $this->hasMany(FinanceTransaction::class, 'account_id');
    }
}
