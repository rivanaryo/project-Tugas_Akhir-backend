<?php

namespace App\Modules\MasterData\DataProduk\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\DataProduk\Request\Units\StoreUnitRequest;
use App\Modules\MasterData\DataProduk\Request\Units\UpdateUnitRequest;
use App\Modules\MasterData\DataProduk\DTOs\UnitsDTO;
use App\Modules\MasterData\DataProduk\Repositories\UnitsRepository;
use App\Modules\MasterData\DataProduk\Models\Units;
use Illuminate\Http\JsonResponse;



class UnitsController extends Controller
{
    protected UnitsRepository $repo;

    public function __construct(UnitsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(): JsonResponse
    {
        $units = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Unit berhasil diambil',
            'data' => $units,
        ],200);
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unitDto = UnitsDTO::fromRequest($request);
        $unit = $this->repo->create($unitDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Unit berhasil dibuat',
            'data' => $unit,
        ],201);
    }

    public function show(string $id): JsonResponse
    {
        $unit = $this->repo->findById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail Unit berhasil diambil',
            'data' => $unit,
        ],200);
    }

    public function update(string $id, UpdateUnitRequest $request): JsonResponse
    {
        $unitDto = UnitsDTO::fromUpdateRequest($request);
        $unit = $this->repo->update($id, $unitDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Unit berhasil diperbarui',
            'data' => $unit,
        ],200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit berhasil dihapus',
        ],200); 
    }

}
