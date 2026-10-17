<?php

namespace App\Modules\Rd\Sample\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rd\Sample\Models\Sample;
use App\Modules\Rd\Sample\Request\StoreSampleRequest;
use App\Modules\Rd\Sample\Request\UpdateSampleRequest;
use App\Modules\Rd\Sample\Request\UpdateSampleStatusRequest;
use App\Modules\Rd\Sample\Resources\SampleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SampleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Sample::with(['product', 'customer', 'techPack'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('q')) {
            $query->where('sample_number', 'like', "%{$search}%");
        }

        $perPage = (int) $request->get('per_page', 15);
        $samples = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar sample berhasil diambil',
            'data' => SampleResource::collection($samples),
        ]);
    }

    public function store(StoreSampleRequest $request): JsonResponse
    {
        $sample = Sample::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sample berhasil dibuat',
            'data' => new SampleResource($sample->load(['product', 'customer', 'techPack'])),
        ], 201);
    }

    public function show(Sample $sample): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail sample berhasil diambil',
            'data' => new SampleResource($sample->load(['product', 'customer', 'techPack'])),
        ]);
    }

    public function update(UpdateSampleRequest $request, Sample $sample): JsonResponse
    {
        $sample->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sample berhasil diperbarui',
            'data' => new SampleResource($sample->load(['product', 'customer', 'techPack'])),
        ]);
    }

    public function destroy(Sample $sample): JsonResponse
    {
        $sample->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sample berhasil dihapus',
        ]);
    }

    public function updateStatus(UpdateSampleStatusRequest $request, Sample $sample): JsonResponse
    {
        $data = $request->validated();
        $update = ['status' => $data['status']];

        if ($data['status'] === 'revision') {
            $update['revision_count'] = $sample->revision_count + 1;
        }

        if (array_key_exists('notes', $data)) {
            $update['notes'] = $data['notes'];
        }

        $sample->update($update);

        return response()->json([
            'success' => true,
            'message' => 'Status sample berhasil diperbarui',
            'data' => new SampleResource($sample->load(['product', 'customer', 'techPack'])),
        ]);
    }
}
