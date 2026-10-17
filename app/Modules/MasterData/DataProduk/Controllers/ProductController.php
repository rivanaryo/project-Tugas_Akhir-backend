<?php

namespace App\Modules\MasterData\DataProduk\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\DataProduk\DTOs\ProductDTO;
use App\Modules\MasterData\DataProduk\Repositories\ProductRepositories;
use App\Modules\MasterData\DataProduk\Request\Product\StoreProductRequest;
use App\Modules\MasterData\DataProduk\Request\Product\UpdateProductRequest;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected ProductRepositories $repo;

    public function __construct(ProductRepositories $repo)
    {
        $this->repo = $repo;
    }

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $products = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar Produk berhasil diambil',
            'data' => $products,
        ], 200);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $productDto = ProductDTO::fromRequest($request);
        $product = $this->repo->create($productDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dibuat',
            'data' => $product,
        ],201);
    }

    public function show(string $id): JsonResponse
    {
        $product = $this->repo->findById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail Produk berhasil diambil',
            'data' => $product,
        ],200);
    }

    public function update(string $id, UpdateProductRequest $request): JsonResponse
    {
        $productDto = ProductDTO::fromUpdateRequest($request);
        $product = $this->repo->update($id, $productDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $product,
        ],200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus',
        ],200);
    }
}
