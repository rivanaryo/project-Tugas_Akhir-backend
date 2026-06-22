<?php

namespace App\Modules\MasterData\ManagementWarehouse\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\ManagementWarehouse\Models\Warehouse;
use App\Modules\MasterData\ManagementWarehouse\Repositories\WarehouseRepositories;
use App\Modules\MasterData\ManagementWarehouse\Request\StoreWarehouseRequest;
use App\Modules\MasterData\ManagementWarehouse\Request\UpdateWarehouseRequest;
use App\Modules\MasterData\ManagementWarehouse\DTOs\WarehouseDTO;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    protected WarehouseRepositories $repo;

    public function __construct(WarehouseRepositories $repo)
    {
        $this->repo = $repo;
    }

    public function index() : JsonResponse
    {
        $warehouses = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar gudang berhasil diambil',
            'data' => $warehouses,
        ], 200);
    }

    public function store(StoreWarehouseRequest $request) : JsonResponse
    {
        $warehouseDto = WarehouseDTO::fromRequest($request);
        $warehouse = $this->repo->create($warehouseDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Gudang berhasil dibuat',
            'data' => $warehouse,
        ], 201);
    }

    public function show(string $id) : JsonResponse
    {
        $warehouse = Warehouse::find($id);

        if (! $warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Gudang tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail gudang berhasil diambil',
            'data' => $warehouse,
        ], 200);
    }

    public function update(string $id, UpdateWarehouseRequest $request) : JsonResponse
    {
        $warehouse = Warehouse::find($id);

        if (! $warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Gudang tidak ditemukan',
            ], 404);
        }

        $warehouseDto = WarehouseDTO::fromUpdateRequest($request);
        $warehouse->update($warehouseDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Gudang berhasil diperbarui',
            'data' => $warehouse,
        ], 200);
    }

    public function destroy(string $id) : JsonResponse
    {
        $warehouse = Warehouse::find($id);

        if (! $warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Gudang tidak ditemukan',
            ], 404);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gudang berhasil dihapus',
        ], 200);
    }
}
