<?php

namespace App\Modules\MasterData\ManagementVendor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Modules\MasterData\ManagementPricelist\Models\Pricelist;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\Production\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Finance\VendorPayment\Models\VendorPayment;


class Vendor extends Model
{
    use HasUuids;

    protected $table = 'm_vendor';

    protected $fillable = [
        'vendor_name',
        'contact_person',
        'phone_number',
        'email',
        'address',
    ];

    public function pricelists()
    {
        return $this->hasMany(Pricelist::class, 'vendor_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'm_pricelists', 'vendor_id', 'product_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id');
    }

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class, 'vendor_id');
    }

}
