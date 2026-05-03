<?php

namespace App\Modules\MasterData\ManagementVendor\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\ManagementVendor\Models\Vendor;
use App\Modules\MasterData\ManagementVendor\Repositories\VendorRepositories;
use App\Modules\MasterData\ManagementVendor\Request\StoreVendorRequest;
use App\Modules\MasterData\ManagementVendor\Request\UpdateVendorRequest;
use App\Modules\MasterData\ManagementVendor\DTOs\VendorDTO;
use Illuminate\Http\JsonResponse;


class VendorController extends Controller
{
    protected VendorRepositories $repo;

    public function __construct(VendorRepositories $repo)
    {
        $this->repo = $repo;
    }

    public function index() : JsonResponse
    {
        $vendors = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar vendor berhasil diambil',
            'data' => $vendors,
        ], 200);
    }

    public function store(StoreVendorRequest $request) : JsonResponse
    {
        $vendorDto = VendorDTO::fromRequest($request);
        $vendor = $this->repo->create($vendorDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Vendor berhasil dibuat',
            'data' => $vendor,
        ], 201);
    }

    public function show(string $id) : JsonResponse
    {
        $vendor = Vendor::find($id);

        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail vendor berhasil diambil',
            'data' => $vendor,
        ], 200);
    }

    public function update(string $id, UpdateVendorRequest $request) : JsonResponse
    {
        $vendor = Vendor::find($id);

        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor tidak ditemukan',
            ], 404);
        }

        $vendorDto = VendorDTO::fromUpdateRequest($request);
        $vendor->update($vendorDto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Vendor berhasil diperbarui',
            'data' => $vendor,
        ], 200);
    }

    public function destroy(string $id) : JsonResponse
    {
        $vendor = Vendor::find($id);

        if (! $vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor tidak ditemukan',
            ], 404);
        }

        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor berhasil dihapus',
        ], 200);
    }

    

}
