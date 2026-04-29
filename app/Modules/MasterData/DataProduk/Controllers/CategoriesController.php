<?php

namespace App\Modules\MasterData\DataProduk\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\DataProduk\Request\Categories\StoreCategoriesRequest;
use App\Modules\MasterData\DataProduk\Request\Categories\UpdateCategoriesRequest;
use App\Modules\MasterData\DataProduk\DTOs\CategoriesDTO;
use App\Modules\MasterData\DataProduk\Repositories\CategoryRepositories;
use Illuminate\Http\JsonResponse;


class CategoriesController extends Controller
{
    protected CategoryRepositories $repo;

    public function __construct(CategoryRepositories $repo)
    {
        $this->repo = $repo;
    }

    public function index(): JsonResponse
    {
        $categories = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Kategori berhasil diambil',
            'data' => $categories,
        ],200);
    }

    public function store(StoreCategoriesRequest $request): JsonResponse
    {
        $categoryDto = CategoriesDTO::fromRequest($request);
        $category = $this->repo->create($categoryDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dibuat',
            'data' => $category,
        ],201);
    }

    public function show(string $id): JsonResponse
    {
        $category = $this->repo->findById($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ],404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditemukan',
            'data' => $category,
        ],200);
    }
    
    public function update(string $id, UpdateCategoriesRequest $request): JsonResponse
    {
        $categoryDto = CategoriesDTO::fromUpdateRequest($request);
        $category = $this->repo->update($id, $categoryDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui',
            'data' => $category,
        ],200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus',
        ],200);
    }




}
