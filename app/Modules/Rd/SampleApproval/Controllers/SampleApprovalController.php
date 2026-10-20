<?php

namespace App\Modules\Rd\SampleApproval\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rd\SampleApproval\Models\SampleApproval;
use App\Modules\Rd\SampleApproval\Request\StoreSampleApprovalRequest;
use App\Modules\Rd\SampleApproval\Request\UpdateSampleApprovalRequest;
use App\Modules\Rd\SampleApproval\Request\UpdateSampleApprovalStatusRequest;
use App\Modules\Rd\SampleApproval\Resources\SampleApprovalResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SampleApprovalController extends Controller
{
    private const RELATIONS = ['product', 'customer', 'salesOrder'];

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => 'nullable|in:pending,approved,rejected,revision',
            'product_id' => 'nullable|uuid|exists:m_products,id',
            'customer_id' => 'nullable|uuid|exists:m_customer,id',
            'q' => 'nullable|string',
        ]);

        $sampleApprovals = SampleApproval::with(self::RELATIONS)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['product_id'] ?? null, fn ($query, $id) => $query->where('product_id', $id))
            ->when($filters['customer_id'] ?? null, fn ($query, $id) => $query->where('customer_id', $id))
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where('sample_code', 'like', "%{$search}%"))
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar sample approval berhasil diambil',
            'data' => SampleApprovalResource::collection($sampleApprovals)->resolve(),
        ]);
    }

    public function store(StoreSampleApprovalRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (($data['revision_count'] ?? null) === null) {
            $data['revision_count'] = 0;
        }

        $sampleApproval = SampleApproval::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Sample approval berhasil dibuat',
            'data' => new SampleApprovalResource($sampleApproval->load(self::RELATIONS)),
        ], 201);
    }

    public function show(SampleApproval $sampleApproval): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail sample approval berhasil diambil',
            'data' => new SampleApprovalResource($sampleApproval->load(self::RELATIONS)),
        ]);
    }

    public function update(UpdateSampleApprovalRequest $request, SampleApproval $sampleApproval): JsonResponse
    {
        $data = $request->validated();
        if (array_key_exists('revision_count', $data) && $data['revision_count'] === null) {
            unset($data['revision_count']);
        }

        $sampleApproval->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Sample approval berhasil diperbarui',
            'data' => new SampleApprovalResource($sampleApproval->load(self::RELATIONS)),
        ]);
    }

    public function updateStatus(UpdateSampleApprovalStatusRequest $request, SampleApproval $sampleApproval): JsonResponse
    {
        $sampleApproval = DB::transaction(function () use ($request, $sampleApproval) {
            $sampleApproval = SampleApproval::lockForUpdate()->findOrFail($sampleApproval->id);
            $data = $request->validated();
            $sampleApproval->status = $data['status'];

            if ($data['status'] === 'revision') {
                $sampleApproval->revision_count++;
            }

            if (array_key_exists('notes', $data)) {
                $sampleApproval->notes = $data['notes'];
            }

            $sampleApproval->save();

            return $sampleApproval;
        });

        return response()->json([
            'success' => true,
            'message' => 'Status sample approval berhasil diperbarui',
            'data' => new SampleApprovalResource($sampleApproval->load(self::RELATIONS)),
        ]);
    }

    public function destroy(SampleApproval $sampleApproval): JsonResponse
    {
        $sampleApproval->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sample approval berhasil dihapus',
        ]);
    }
}
