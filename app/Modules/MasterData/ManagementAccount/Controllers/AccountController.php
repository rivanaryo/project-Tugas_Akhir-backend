<?php

namespace App\Modules\MasterData\ManagementAccount\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\ManagementAccount\Repositories\AccountRepositories;
use App\Modules\MasterData\ManagementAccount\Request\StoreAccountRequest;
use App\Modules\MasterData\ManagementAccount\Request\UpdateAccountRequest;
use App\Modules\MasterData\ManagementAccount\DTOs\AccountDTO;
use Illuminate\Http\JsonResponse;


class AccountController extends Controller
{
    public function __construct(private readonly AccountRepositories $repo)
    {}

    public function index () : JsonResponse
    {
        $accounts = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar akun berhasil diambil',
            'data' => $accounts,
        ], 200);
    }

    public function store(StoreAccountRequest $request) : JsonResponse
    {
        $accountDto = AccountDTO::fromRequest($request);
        $account = $this->repo->create($accountDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dibuat',
            'data' => $account,
        ], 201);
    }

    public function show(string $id) : JsonResponse
    {
        $account = $this->repo->findById($id);

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail akun berhasil diambil',
            'data' => $account,
        ], 200);
    }

    public function update(string $id, UpdateAccountRequest $request) : JsonResponse
    {
        $account = $this->repo->findById($id);

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan',
            ], 404);
        }

        $accountDto = AccountDTO::fromRequestUpdate($request);
        $updatedAccount = $this->repo->update($id, $accountDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diperbarui',
            'data' => $updatedAccount,
        ], 200);
    }
    
    public function destroy(string $id) : JsonResponse
    {
        $account = $this->repo->findById($id);

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan',
            ], 404);
        }

        $deleted = $this->repo->delete($id);

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus akun',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus',
        ], 200);
    }

}
