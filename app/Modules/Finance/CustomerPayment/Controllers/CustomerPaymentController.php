<?php

namespace App\Modules\Finance\CustomerPayment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\CustomerPayment\Models\CustomerPayment;
use App\Modules\Finance\CustomerPayment\Request\StoreCustomerPaymentRequest;
use App\Modules\Finance\CustomerPayment\Resources\CustomerPaymentResource;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CustomerPayment::with(['salesOrder', 'customer', 'account'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($salesOrderId = $request->get('sales_order_id')) {
            $query->where('sales_order_id', $salesOrderId);
        }

        if ($search = $request->get('q')) {
            $query->where('payment_number', 'like', "%{$search}%");
        }

        $perPage = (int) $request->get('per_page', 15);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar customer payment berhasil diambil',
            'data' => CustomerPaymentResource::collection($payments),
        ]);
    }

    public function store(StoreCustomerPaymentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $salesOrder = SalesOrder::findOrFail($data['sales_order_id']);
        $grandTotal = (float) $salesOrder->grand_total;

        $paid = (float) CustomerPayment::where('sales_order_id', $salesOrder->id)
            ->where('status', 'paid')
            ->sum('amount');

        if ($paid + (float) $data['amount'] > $grandTotal) {
            return response()->json([
                'success' => false,
                'message' => 'Total pembayaran melebihi grand total sales order',
            ], 422);
        }

        $payment = DB::transaction(function () use ($data, $salesOrder) {
            $payment = CustomerPayment::create($data);

            if ($data['status'] === 'paid') {
                $this->syncSalesOrderStatus($salesOrder);
            }

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Customer payment berhasil dibuat',
            'data' => new CustomerPaymentResource($payment->load(['salesOrder', 'customer', 'account'])),
        ], 201);
    }

    public function show(CustomerPayment $customerPayment): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail customer payment berhasil diambil',
            'data' => new CustomerPaymentResource($customerPayment->load(['salesOrder', 'customer', 'account'])),
        ]);
    }

    public function destroy(CustomerPayment $customerPayment): JsonResponse
    {
        $salesOrder = $customerPayment->salesOrder;

        DB::transaction(function () use ($customerPayment, $salesOrder) {
            $customerPayment->delete();
            $this->syncSalesOrderStatus($salesOrder);
        });

        return response()->json([
            'success' => true,
            'message' => 'Customer payment berhasil dihapus',
        ]);
    }

    private function syncSalesOrderStatus(SalesOrder $salesOrder): void
    {
        $totalPaid = (float) CustomerPayment::where('sales_order_id', $salesOrder->id)
            ->where('status', 'paid')
            ->sum('amount');

        $grandTotal = (float) $salesOrder->grand_total;

        if ($totalPaid <= 0) {
            $salesOrder->updateStatus('draft');
        } elseif ($totalPaid >= $grandTotal) {
            $salesOrder->updateStatus('completed');
        } else {
            $salesOrder->updateStatus('dp_paid');
        }
    }
}
