<?php

namespace Tests\Feature;

use App\Modules\Admin\Models\Admin;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use App\Modules\MasterData\SizeChart\Models\SizeChart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SizeChartTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Product $product;

    private Product $otherProduct;

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
            'category_name' => 'Apparel',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unit = new Units;
        $unit->id = Str::uuid();
        $unit->unit_code = 'PCS';
        $unit->unit_name = 'Piece';
        $unit->is_active = true;
        $unit->save();

        $this->product = $this->createProduct('Hoodie Fleece Custom', $categoryId, $unit->id);
        $this->otherProduct = $this->createProduct('Basic T-Shirt', $categoryId, $unit->id);
    }

    public function test_admin_can_list_paginated_size_charts_with_product_relation(): void
    {
        $this->authenticate();
        $this->createSizeChart($this->product, 'M');

        $response = $this->getJson('/api/master-data/size-charts?per_page=10');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.current_page', 1)
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.product.product_name', 'Hoodie Fleece Custom')
            ->assertJsonPath('data.data.0.size', 'M');
    }

    public function test_admin_can_filter_size_charts_by_product_and_search_query(): void
    {
        $this->authenticate();
        $this->createSizeChart($this->product, 'M');
        $this->createSizeChart($this->otherProduct, 'L');

        $this->getJson("/api/master-data/size-charts?product_id={$this->product->id}")
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.product_id', (string) $this->product->id);

        $this->getJson('/api/master-data/size-charts?q=Basic')
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.product_id', (string) $this->otherProduct->id);
    }

    public function test_admin_can_create_update_show_and_delete_size_chart(): void
    {
        $this->authenticate();

        $createResponse = $this->postJson('/api/master-data/size-charts', [
            'product_id' => $this->product->id,
            'size' => 'S',
            'chest_width' => 48,
            'body_length' => 66,
            'sleeve_length' => 57,
            'shoulder_width' => 39,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product.product_name', 'Hoodie Fleece Custom')
            ->assertJsonPath('data.size', 'S');

        $sizeChartId = $createResponse->json('data.id');

        $this->getJson("/api/master-data/size-charts/{$sizeChartId}")
            ->assertOk()
            ->assertJsonPath('data.id', $sizeChartId);

        $this->putJson("/api/master-data/size-charts/{$sizeChartId}", [
            'product_id' => $this->product->id,
            'size' => 'M',
            'chest_width' => 50,
            'body_length' => 68,
            'sleeve_length' => 58,
            'shoulder_width' => 40,
        ])->assertOk()
            ->assertJsonPath('data.size', 'M')
            ->assertJsonPath('data.chest_width', '50.00');

        $this->deleteJson("/api/master-data/size-charts/{$sizeChartId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('size_charts', ['id' => $sizeChartId]);
    }

    public function test_product_cannot_have_duplicate_size(): void
    {
        $this->authenticate();
        $this->createSizeChart($this->product, 'M');

        $this->postJson('/api/master-data/size-charts', [
            'product_id' => $this->product->id,
            'size' => 'M',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('size')
            ->assertJsonPath('errors.size.0', 'Size tersebut sudah terdaftar untuk produk yang dipilih.');
    }

    public function test_updating_to_duplicate_product_size_is_rejected(): void
    {
        $this->authenticate();
        $this->createSizeChart($this->product, 'M');
        $sizeChart = $this->createSizeChart($this->otherProduct, 'M');

        $this->patchJson("/api/master-data/size-charts/{$sizeChart->id}", [
            'product_id' => $this->product->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('size');
    }

    public function test_size_chart_requires_valid_measurements_and_enum_size(): void
    {
        $this->authenticate();

        $this->postJson('/api/master-data/size-charts', [
            'product_id' => $this->product->id,
            'size' => 'XXXL',
            'chest_width' => -1,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['size', 'chest_width']);
    }

    public function test_unauthenticated_user_cannot_access_size_charts(): void
    {
        $this->getJson('/api/master-data/size-charts')->assertUnauthorized();
    }

    private function authenticate(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');
    }

    private function createProduct(string $name, string $categoryId, string $unitId): Product
    {
        $product = new Product;
        $product->id = Str::uuid();
        $product->product_name = $name;
        $product->category_id = $categoryId;
        $product->unit_id = $unitId;
        $product->is_active = true;
        $product->save();

        return $product;
    }

    private function createSizeChart(Product $product, string $size): SizeChart
    {
        return SizeChart::create([
            'product_id' => $product->id,
            'size' => $size,
            'chest_width' => 50,
            'body_length' => 68,
            'sleeve_length' => 58,
            'shoulder_width' => 40,
        ]);
    }
}
