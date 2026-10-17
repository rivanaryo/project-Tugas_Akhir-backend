<?php

namespace App\Modules\Rd\TechPack\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Rd\TechPack\Models\TechPack;
use App\Modules\Rd\TechPack\Request\StoreTechPackRequest;
use App\Modules\Rd\TechPack\Request\UpdateTechPackRequest;
use App\Modules\Rd\TechPack\Request\UpdateTechPackStatusRequest;
use App\Modules\Rd\TechPack\Resources\TechPackResource;
use App\Modules\Rd\TechPack\Services\TechPackCostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TechPackController extends Controller
{
    public function __construct(private readonly TechPackCostingService $costingService) {}

    public function index(Request $request): JsonResponse
    {
        $query = TechPack::with(['product', 'costing'])
            ->withCount('bomItems')
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('fabric', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->get('per_page', 15);
        $techPacks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar tech pack berhasil diambil',
            'data' => TechPackResource::collection($techPacks),
        ]);
    }

    public function store(StoreTechPackRequest $request): JsonResponse
    {
        $data = $request->validated();

        $techPack = DB::transaction(function () use ($data) {
            $techPack = TechPack::create([
                'name' => $data['name'],
                'fabric' => $data['fabric'] ?? null,
                'gsm' => $data['gsm'] ?? null,
                'thread' => $data['thread'] ?? null,
                'zipper' => $data['zipper'] ?? null,
                'accessories' => $data['accessories'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'product_id' => $data['product_id'] ?? null,
            ]);

            if (! empty($data['size_charts'])) {
                $techPack->sizeCharts()->createMany($data['size_charts']);
            }

            if (! empty($data['bom_items'])) {
                $this->syncBomItems($techPack, $data['bom_items']);
            }

            if (! empty($data['costing'])) {
                $this->costingService->recalculateWithCostingData($techPack, $data['costing']);
            } elseif (! empty($data['bom_items'])) {
                $this->costingService->recalculate($techPack);
            }

            return $techPack->load([
                'product',
                'sizeCharts',
                'bomItems.material',
                'bomItems.unit',
                'costing',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Tech pack berhasil dibuat',
            'data' => new TechPackResource($techPack),
        ], 201);
    }

    public function show(TechPack $techPack): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail tech pack berhasil diambil',
            'data' => new TechPackResource($techPack->load([
                'product',
                'sizeCharts',
                'bomItems.material',
                'bomItems.unit',
                'costing',
            ])),
        ]);
    }

    public function update(UpdateTechPackRequest $request, TechPack $techPack): JsonResponse
    {
        $data = $request->validated();

        $techPack = DB::transaction(function () use ($data, $techPack) {
            $techPack->update([
                'name' => $data['name'] ?? $techPack->name,
                'fabric' => $data['fabric'] ?? $techPack->fabric,
                'gsm' => $data['gsm'] ?? $techPack->gsm,
                'thread' => $data['thread'] ?? $techPack->thread,
                'zipper' => $data['zipper'] ?? $techPack->zipper,
                'accessories' => $data['accessories'] ?? $techPack->accessories,
                'status' => $data['status'] ?? $techPack->status,
                'product_id' => array_key_exists('product_id', $data) ? $data['product_id'] : $techPack->product_id,
            ]);

            if (isset($data['size_charts'])) {
                $techPack->sizeCharts()->delete();
                $techPack->sizeCharts()->createMany($data['size_charts']);
            }

            $bomItemsChanged = isset($data['bom_items']);
            if ($bomItemsChanged) {
                $techPack->bomItems()->delete();
                $this->syncBomItems($techPack, $data['bom_items']);
            }

            if (isset($data['costing'])) {
                $this->costingService->recalculateWithCostingData($techPack, $data['costing']);
            } elseif ($bomItemsChanged) {
                $this->costingService->recalculate($techPack);
            }

            return $techPack->load([
                'product',
                'sizeCharts',
                'bomItems.material',
                'bomItems.unit',
                'costing',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Tech pack berhasil diperbarui',
            'data' => new TechPackResource($techPack),
        ]);
    }

    public function destroy(TechPack $techPack): JsonResponse
    {
        $techPack->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tech pack berhasil dihapus',
        ]);
    }

    public function updateStatus(UpdateTechPackStatusRequest $request, TechPack $techPack): JsonResponse
    {
        $techPack->update(['status' => $request->validated('status')]);

        return response()->json([
            'success' => true,
            'message' => 'Status tech pack berhasil diperbarui',
            'data' => new TechPackResource($techPack->load([
                'product',
                'sizeCharts',
                'bomItems.material',
                'bomItems.unit',
                'costing',
            ])),
        ]);
    }

    private function syncBomItems(TechPack $techPack, array $bomItems): void
    {
        $items = [];

        foreach ($bomItems as $item) {
            $items[] = [
                'material_product_id' => $item['material_product_id'],
                'usage' => $item['usage'],
                'unit_id' => $item['unit_id'],
                'cost_per_unit' => $item['cost_per_unit'],
                'total' => $this->costingService->calculateItemTotal(
                    (float) $item['usage'],
                    (float) $item['cost_per_unit']
                ),
            ];
        }

        $techPack->bomItems()->createMany($items);
    }
}
