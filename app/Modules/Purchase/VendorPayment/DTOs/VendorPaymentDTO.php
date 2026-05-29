<?php
namespace App\Modules\Purchase\VendorPayment\DTOs;

use App\Modules\Purchase\VendorPayment\Request\StoreVendorPaymentRequest;
use App\Modules\Purchase\VendorPayment\Request\UpdateVendorPaymentRequest;

class VendorPaymentDTO {
    public function __construct(
        public string $payment_number,
        public string $vendor_id,
        public ?string $vendor_invoice_number,
        public string $payment_type,
        public string $payment_date,
        public float $total_paid,
        public float $tax_rate,
        public string $account_id,
        public ?string $status = null,
    ) {}

    public static function fromStoreRequest(StoreVendorPaymentRequest $request): self
    {
        $validate = $request->validated();
        return new self(
            payment_number: $validate['payment_number'],
            vendor_id: $validate['vendor_id'],
            vendor_invoice_number: $validate['vendor_invoice_number'] ?? null,
            payment_type: $validate['payment_type'],
            payment_date: $validate['payment_date'],
            total_paid: (float) $validate['total_paid'],
            tax_rate: (float) ($validate['tax_rate'] ?? 0),
            account_id: $validate['account_id'],
            status: $validate['status'] ?? null,
        );
    }

    public static function fromUpdateRequest(UpdateVendorPaymentRequest $request): self
    {
        $validate = $request->validated();
        return new self(
            payment_number: $validate['payment_number'],
            vendor_id: $validate['vendor_id'],
            vendor_invoice_number: $validate['vendor_invoice_number'] ?? null,
            payment_type: $validate['payment_type'],
            payment_date: $validate['payment_date'],
            total_paid: (float) $validate['total_paid'],
            tax_rate: (float) ($validate['tax_rate'] ?? 0),
            account_id: $validate['account_id'],
            status: $validate['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'payment_number' => $this->payment_number,
            'vendor_id' => $this->vendor_id,
            'vendor_invoice_number' => $this->vendor_invoice_number,
            'payment_type' => $this->payment_type,
            'payment_date' => $this->payment_date,
            'total_paid' => $this->total_paid,
            'tax_rate' => $this->tax_rate,
            'account_id' => $this->account_id,
            'status' => $this->status,
        ];
    }






}
