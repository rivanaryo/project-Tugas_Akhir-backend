<?php

namespace App\Modules\MasterData\ManagementPricelist\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\ManagementPricelist\DTOs\PricelistDTO;
use App\Modules\MasterData\ManagementPricelist\Repositories\PricelistRepositories;
use App\Modules\MasterData\ManagementPricelist\Request\StorePricelistRequest;
use App\Modules\MasterData\ManagementPricelist\Request\UpdatePricelistRequest;
use Illuminate\Http\JsonResponse;

class PricelistController extends Controller
{
    public function __construct(private readonly PricelistRepositories $repo) {}

    public function index(): JsonResponse
    {
        $pricelists = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pricelist berhasil diambil',
            'data'    => $pricelists,
        ], 200);
    }

    public function store(StorePricelistRequest $request): JsonResponse
    {
        $dto       = PricelistDTO::fromRequest($request);
        $pricelist = $this->repo->create($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Pricelist berhasil dibuat',
            'data'    => $pricelist,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $pricelist = $this->repo->findById($id);

        if (! $pricelist) {
            return response()->json([
                'success' => false,
                'message' => 'Pricelist tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pricelist berhasil diambil',
            'data'    => $pricelist,
        ], 200);
    }

    public function update(string $id, UpdatePricelistRequest $request): JsonResponse
    {
        $pricelist = $this->repo->findById($id);

        if (! $pricelist) {
            return response()->json([
                'success' => false,
                'message' => 'Pricelist tidak ditemukan',
            ], 404);
        }

        $dto     = PricelistDTO::fromUpdateRequest($request);
        $updated = $this->repo->update($id, $dto->toArray(true));

        return response()->json([
            'success' => true,
            'message' => 'Pricelist berhasil diperbarui',
            'data'    => $updated,
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $pricelist = $this->repo->findById($id);

        if (! $pricelist) {
            return response()->json([
                'success' => false,
                'message' => 'Pricelist tidak ditemukan',
            ], 404);
        }

        $this->repo->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Pricelist berhasil dihapus',
        ], 200);
    }
}

