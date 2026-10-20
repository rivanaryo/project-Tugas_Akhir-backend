<?php

namespace App\Modules\Finance\Ledger\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_account_id' => ['required', 'uuid', 'exists:m_accounts,id', 'different:destination_account_id'],
            'destination_account_id' => ['required', 'uuid', 'exists:m_accounts,id', 'different:source_account_id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_account_id.required' => 'Akun sumber wajib dipilih.',
            'source_account_id.exists' => 'Akun sumber tidak ditemukan.',
            'source_account_id.different' => 'Akun sumber dan tujuan harus berbeda.',
            'destination_account_id.required' => 'Akun tujuan wajib dipilih.',
            'destination_account_id.exists' => 'Akun tujuan tidak ditemukan.',
            'destination_account_id.different' => 'Akun sumber dan tujuan harus berbeda.',
            'amount.required' => 'Jumlah transfer wajib diisi.',
            'amount.gt' => 'Jumlah transfer harus lebih besar dari 0.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date_format' => 'Format tanggal harus Y-m-d.',
        ];
    }
}
