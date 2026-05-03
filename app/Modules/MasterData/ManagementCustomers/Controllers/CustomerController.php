<?php

namespace App\Modules\MasterData\ManagementCustomers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\ManagementCustomers\Models\Customer;
use App\Modules\MasterData\ManagementCustomers\Repositories\CustomerRepositories;
use App\Modules\MasterData\ManagementCustomers\Request\StoreCustomerRequest;
use App\Modules\MasterData\ManagementCustomers\Request\UpdateCustomerRequest;
use App\Modules\MasterData\ManagementCustomers\DTOs\CustomerDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerRepositories $repo)
    {}

    public function index () : JsonResponse
    {
        $customers = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pelanggan berhasil diambil',
            'data' => $customers,
        ], 200);
    }

    public function store(StoreCustomerRequest $request) : JsonResponse
    {
        $customerDto = CustomerDTO::fromRequest($request);
        $customer = $this->repo->create($customerDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dibuat',
            'data' => $customer,
        ], 201);
    }

    public function show(string $id) : JsonResponse
    {
        $customer = $this->repo->findById($id);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pelanggan berhasil diambil',
            'data' => $customer,
        ], 200);
    }

    public function update(string $id, UpdateCustomerRequest $request) : JsonResponse
    {
        $customerDto = CustomerDTO::fromUpdateRequest($request);
        $customer = $this->repo->update($id, $customerDto->toArray(true));

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil diperbarui',
            'data' => $customer,
        ], 200);
    }

    public function destroy(string $id) : JsonResponse
    {
        $deleted = $this->repo->delete($id);

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan atau gagal dihapus',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus',
        ], 200);
    }
}
