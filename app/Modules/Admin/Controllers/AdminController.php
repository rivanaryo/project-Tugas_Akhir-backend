<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\DTOs\AdminDTO;
use App\Modules\Admin\Repositories\AdminRepositories;
use App\Modules\Admin\Request\StoreAdminRequest;
use App\Modules\Admin\Request\UpdateAdminRequest;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function __construct(private readonly AdminRepositories $repo)
    {
    }

    public function index(): JsonResponse
    {
        $admins = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar admin berhasil diambil',
            'data' => $admins,
        ], 200);
    }

    public function store(StoreAdminRequest $request): JsonResponse
    {
        $adminDto = AdminDTO::fromRequest($request);
        $admin = $this->repo->create($adminDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil dibuat',
            'data' => $admin,
        ], 201);
    }

    public function show(int|string $id): JsonResponse
    {
        $admin = $this->repo->findById($id);

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail admin berhasil diambil',
            'data' => $admin,
        ], 200);
    }

    public function update(int|string $id, UpdateAdminRequest $request): JsonResponse
    {
        $adminDto = AdminDTO::fromUpdateRequest($request);
        $admin = $this->repo->update($id, $adminDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil diperbarui',
            'data' => $admin,
        ], 200);
    }

    public function destroy(int|string $id): JsonResponse
    {
        $this->repo->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil dihapus',
        ], 200);
    }
}
