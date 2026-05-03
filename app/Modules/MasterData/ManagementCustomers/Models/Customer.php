<?php

namespace App\Modules\MasterData\ManagementCustomers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Customer extends Model
{
    use HasUuids;

    protected $table = 'm_customer';

    protected $fillable = [
        'customer_name',
        'contact_person',
        'phone_number',
        'email',
        'address',
        'is_active',
    ];

    
}
