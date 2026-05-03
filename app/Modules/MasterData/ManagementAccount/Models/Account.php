<?php

namespace App\Modules\MasterData\ManagementAccount\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Account extends Model
{
    use HasUuids;

    protected $table = 'm_accounts';



    protected $fillable = [
        'account_name',
        'account_number',
        'bank_name',
        'is_active',
    ];
}
