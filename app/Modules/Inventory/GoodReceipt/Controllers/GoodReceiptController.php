<?php

namespace App\Modules\Inventory\GoodReceipt\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Inventory\GoodReceipt\Repositories\GoodReceiptRepositories;
use App\Modules\Inventory\GoodReceipt\Request\StoreGoodReceiptRequest;
use App\Modules\Inventory\GoodReceipt\Request\UpdateGoodReceiptRequest;
use App\Modules\Inventory\GoodReceipt\DTOs\GoodReceiptDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GoodReceiptController extends Controller
{
    public function __construct(private readonly GoodReceiptRepositories $repo)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $goodReceipts = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar good receipt berhasil diambil',
            'data' => $goodReceipts,
        ], 200);
    }

    public function show(string $goodReceipt): JsonResponse
    {
        $goodReceipt = $this->repo->findById($goodReceipt);

        if (! $goodReceipt) {
            return response()->json([
                'success' => false,
                'message' => 'Good receipt tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail good receipt berhasil diambil',
            'data' => $goodReceipt,
        ], 200);
    }

    public function store(StoreGoodReceiptRequest $request): JsonResponse
    {
        $dto = GoodReceiptDTO::fromRequest($request);
        $goodReceipt = $this->repo->create($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Good receipt berhasil dibuat',
            'data' => $goodReceipt,
        ], 201);
    }

    public function update(string $goodReceipt, UpdateGoodReceiptRequest $request): JsonResponse
    {
        $existing = $this->repo->findById($goodReceipt);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Good receipt tidak ditemukan',
            ], 404);
        }

        $dto = GoodReceiptDTO::fromUpdateRequest($request);
        $updated = $this->repo->update($goodReceipt, $dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Good receipt berhasil diperbarui',
            'data' => $updated,
        ], 200);
    }

    public function destroy(string $goodReceipt): JsonResponse
    {
        $existing = $this->repo->findById($goodReceipt);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Good receipt tidak ditemukan',
            ], 404);
        }

        $this->repo->delete($goodReceipt);

        return response()->json([
            'success' => true,
            'message' => 'Good receipt berhasil dihapus',
        ], 200);
    }
    

}



