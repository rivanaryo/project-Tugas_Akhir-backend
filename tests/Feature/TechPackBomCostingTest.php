<?php

namespace Tests\Feature;

use App\Modules\Admin\Models\Admin;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TechPackBomCostingTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Product $material1;

    private Product $material2;

    private Units $unit;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'id' => Str::uuid(),
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $categoryId = Str::uuid();
        DB::table('categories')->insert([
            'id' => $categoryId,
            'category_name' => 'Fabric',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->unit = new Units;
        $this->unit->id = Str::uuid();
        $this->unit->unit_code = 'MTR';
        $this->unit->unit_name = 'Meter';
        $this->unit->is_active = true;
        $this->unit->save();

        $this->material1 = new Product;
        $this->material1->id = Str::uuid();
        $this->material1->product_name = 'Cotton Fleece 330 GSM';
        $this->material1->category_id = $categoryId;
        $this->material1->unit_id = $this->unit->id;
        $this->material1->is_active = true;
        $this->material1->save();

        $this->material2 = new Product;
        $this->material2->id = Str::uuid();
        $this->material2->product_name = 'Drawcord';
        $this->material2->category_id = $categoryId;
        $this->material2->unit_id = $this->unit->id;
        $this->material2->is_active = true;
        $this->material2->save();

        $this->product = new Product;
        $this->product->id = Str::uuid();
        $this->product->product_name = 'Hoodie Fleece Custom';
        $this->product->category_id = $categoryId;
        $this->product->unit_id = $this->unit->id;
        $this->product->is_active = true;
        $this->product->save();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Hoodie Fleece Custom',
            'fabric' => 'Cotton Fleece',
            'gsm' => 330,
            'thread' => 'Polyester 30/2',
            'zipper' => 'YKK #5',
            'accessories' => ['drawcord', 'eyelet'],
            'status' => 'draft',
            'product_id' => $this->product->id,
            'size_charts' => [
                [
                    'size' => 'M',
                    'body_length' => 68,
                    'chest_width' => 50,
                    'sleeve_length' => 58,
                    'shoulder_width' => 40,
                ],
            ],
            'bom_items' => [
                [
                    'material_product_id' => $this->material1->id,
                    'usage' => 2.8,
                    'unit_id' => $this->unit->id,
                    'cost_per_unit' => 35000,
                ],
                [
                    'material_product_id' => $this->material2->id,
                    'usage' => 1,
                    'unit_id' => $this->unit->id,
                    'cost_per_unit' => 8000,
                ],
            ],
            'costing' => [
                'printing_cost' => 25000,
                'sewing_cost' => 35000,
                'additional_cost' => 0,
                'margin_type' => 'percentage',
                'margin' => 30,
            ],
        ], $overrides);
    }

    public function test_can_create_tech_pack_with_bom_and_costing(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload());

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tech pack berhasil dibuat')
            ->assertJsonPath('data.name', 'Hoodie Fleece Custom')
            ->assertJsonPath('data.bom_items', fn ($bomItems) => count($bomItems) === 2)
            ->assertJsonPath('data.costing.margin_type', 'percentage');
    }

    public function test_bom_item_total_is_calculated_by_backend(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload([
            'bom_items' => [
                [
                    'material_product_id' => $this->material1->id,
                    'usage' => 2.8,
                    'unit_id' => $this->unit->id,
                    'cost_per_unit' => 35000,
                ],
            ],
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.bom_items.0.total', 98000);
    }

    public function test_material_cost_is_sum_of_all_bom_totals(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload());

        $response->assertStatus(201)
            ->assertJsonPath('data.costing.material_cost', 106000);
    }

    public function test_hpp_is_calculated_correctly(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload());

        $response->assertStatus(201)
            ->assertJsonPath('data.costing.hpp', 166000);
    }

    public function test_sell_price_with_nominal_margin_is_calculated_correctly(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload([
            'costing' => [
                'printing_cost' => 25000,
                'sewing_cost' => 35000,
                'additional_cost' => 0,
                'margin_type' => 'nominal',
                'margin' => 50000,
            ],
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.costing.sell_price', 216000);
    }

    public function test_sell_price_with_percentage_margin_is_calculated_correctly(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload());

        $response->assertStatus(201)
            ->assertJsonPath('data.costing.sell_price', 215800);
    }

    public function test_request_rejected_when_material_not_found(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload([
            'bom_items' => [
                [
                    'material_product_id' => Str::uuid(),
                    'usage' => 2.8,
                    'unit_id' => $this->unit->id,
                    'cost_per_unit' => 35000,
                ],
            ],
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bom_items.0.material_product_id']);
    }

    public function test_request_rejected_when_unit_not_found(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload([
            'bom_items' => [
                [
                    'material_product_id' => $this->material1->id,
                    'usage' => 2.8,
                    'unit_id' => Str::uuid(),
                    'cost_per_unit' => 35000,
                ],
            ],
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bom_items.0.unit_id']);
    }

    public function test_request_rejected_when_usage_is_not_greater_than_zero(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload([
            'bom_items' => [
                [
                    'material_product_id' => $this->material1->id,
                    'usage' => 0,
                    'unit_id' => $this->unit->id,
                    'cost_per_unit' => 35000,
                ],
            ],
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bom_items.0.usage']);
    }

    public function test_request_rejected_when_cost_is_negative(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/rd/tech-packs', $this->payload([
            'costing' => [
                'printing_cost' => -1000,
                'sewing_cost' => 35000,
                'additional_cost' => 0,
                'margin_type' => 'percentage',
                'margin' => 30,
            ],
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['costing.printing_cost']);
    }

    public function test_update_bom_recalculates_costing(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $createResponse = $this->postJson('/api/rd/tech-packs', $this->payload());
        $techPackId = $createResponse->json('data.id');

        $response = $this->putJson("/api/rd/tech-packs/{$techPackId}", $this->payload([
            'bom_items' => [
                [
                    'material_product_id' => $this->material1->id,
                    'usage' => 3,
                    'unit_id' => $this->unit->id,
                    'cost_per_unit' => 35000,
                ],
            ],
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.costing.material_cost', 105000)
            ->assertJsonPath('data.costing.hpp', 165000);
    }

    public function test_deleting_tech_pack_deletes_bom_and_costing(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $createResponse = $this->postJson('/api/rd/tech-packs', $this->payload());
        $techPackId = $createResponse->json('data.id');

        $response = $this->deleteJson("/api/rd/tech-packs/{$techPackId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tech_packs', ['id' => $techPackId]);
        $this->assertDatabaseMissing('tech_pack_bom_items', ['tech_pack_id' => $techPackId]);
        $this->assertDatabaseMissing('tech_pack_costings', ['tech_pack_id' => $techPackId]);
    }

    public function test_failed_bom_creation_rolls_back_entire_transaction(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $payload = $this->payload([
            'bom_items' => [
                [
                    'material_product_id' => $this->material1->id,
                    'usage' => 2.8,
                    'unit_id' => $this->unit->id,
                    'cost_per_unit' => 35000,
                ],
                [
                    'material_product_id' => 'not-a-valid-uuid',
                    'usage' => 1,
                    'unit_id' => $this->unit->id,
                    'cost_per_unit' => 8000,
                ],
            ],
        ]);

        $response = $this->postJson('/api/rd/tech-packs', $payload);

        $response->assertStatus(422);
        $this->assertDatabaseCount('tech_packs', 0);
        $this->assertDatabaseCount('tech_pack_bom_items', 0);
        $this->assertDatabaseCount('tech_pack_costings', 0);
    }

    public function test_unauthenticated_user_receives_401(): void
    {
        $response = $this->postJson('/api/rd/tech-packs', $this->payload());

        $response->assertStatus(401);
    }
}
