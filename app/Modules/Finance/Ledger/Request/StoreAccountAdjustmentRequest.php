<?php

namespace App\Modules\Finance\Ledger\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['required', 'uuid', 'exists:m_accounts,id'],
            'direction' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => 'Akun wajib dipilih.',
            'account_id.exists' => 'Akun tidak ditemukan.',
            'direction.required' => 'Arah transaksi wajib dipilih.',
            'direction.in' => 'Arah transaksi harus in atau out.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.gt' => 'Jumlah harus lebih besar dari 0.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date_format' => 'Format tanggal harus Y-m-d.',
            'description.required' => 'Deskripsi wajib diisi.',
        ];
    }
}
