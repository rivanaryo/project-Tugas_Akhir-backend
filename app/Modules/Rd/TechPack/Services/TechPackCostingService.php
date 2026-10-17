<?php

namespace App\Modules\Rd\TechPack\Services;

use App\Modules\Rd\TechPack\Models\TechPack;
use App\Modules\Rd\TechPack\Models\TechPackCosting;

class TechPackCostingService
{
    public function calculateItemTotal(float $usage, float $costPerUnit): float
    {
        return round($usage * $costPerUnit, 2);
    }

    public function recalculate(TechPack $techPack): TechPackCosting
    {
        $materialCost = $techPack->bomItems()->sum('total');
        $materialCost = (float) $materialCost;

        $costing = $techPack->costing;

        $printingCost = $costing?->printing_cost ?? 0;
        $sewingCost = $costing?->sewing_cost ?? 0;
        $additionalCost = $costing?->additional_cost ?? 0;
        $marginType = $costing?->margin_type ?? 'nominal';
        $margin = $costing?->margin ?? 0;

        $hpp = $materialCost + $printingCost + $sewingCost + $additionalCost;

        $sellPrice = $this->calculateSellPrice($hpp, $marginType, (float) $margin);

        $costing = $techPack->costing()->firstOrNew(['tech_pack_id' => $techPack->id]);
        $costing->material_cost = $materialCost;
        $costing->hpp = $hpp;
        $costing->sell_price = $sellPrice;
        $costing->save();

        return $costing;
    }

    public function recalculateWithCostingData(TechPack $techPack, array $costingData): TechPackCosting
    {
        $materialCost = $techPack->bomItems()->sum('total');
        $materialCost = (float) $materialCost;

        $printingCost = (float) ($costingData['printing_cost'] ?? 0);
        $sewingCost = (float) ($costingData['sewing_cost'] ?? 0);
        $additionalCost = (float) ($costingData['additional_cost'] ?? 0);
        $marginType = $costingData['margin_type'] ?? 'nominal';
        $margin = (float) ($costingData['margin'] ?? 0);

        $hpp = $materialCost + $printingCost + $sewingCost + $additionalCost;
        $sellPrice = $this->calculateSellPrice($hpp, $marginType, $margin);

        $costing = $techPack->costing()->firstOrNew(['tech_pack_id' => $techPack->id]);
        $costing->printing_cost = $printingCost;
        $costing->sewing_cost = $sewingCost;
        $costing->additional_cost = $additionalCost;
        $costing->margin_type = $marginType;
        $costing->margin = $margin;
        $costing->material_cost = $materialCost;
        $costing->hpp = $hpp;
        $costing->sell_price = $sellPrice;
        $costing->save();

        return $costing;
    }

    private function calculateSellPrice(float $hpp, string $marginType, float $margin): float
    {
        if ($marginType === 'percentage') {
            return round($hpp + ($hpp * $margin / 100), 2);
        }

        return round($hpp + $margin, 2);
    }
}
