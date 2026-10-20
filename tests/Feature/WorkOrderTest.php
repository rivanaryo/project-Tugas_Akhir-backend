<?php

namespace Tests\Feature;

use App\Modules\Admin\Models\Admin;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private SalesOrder $salesOrder;

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

        $customerId = Str::uuid();
        DB::table('m_customer')->insert([
            'id' => $customerId,
            'customer_name' => 'PT Mantra',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-001',
            'customer_id' => $customerId,
            'order_date' => '2026-10-17',
            'status' => 'confirmed',
            'subtotal' => 0,
            'total_tax' => 0,
            'grand_total' => 0,
        ]);
    }

    public function test_admin_can_list_paginated_work_orders_with_relations(): void
    {
        $this->authenticate();
        $this->createWorkOrder();

        $this->getJson('/api/production/work-orders?per_page=10')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Work orders retrieved successfully')
            ->assertJsonPath('data.current_page', 1)
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonPath('data.last_page', 1)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.sales_order.order_number', 'SO-2026-001')
            ->assertJsonPath('data.data.0.sales_order.customer.customer_name', 'PT Mantra')
            ->assertJsonPath('data.data.0.product.product_name', 'Hoodie Fleece Custom')
            ->assertJsonPath('data.data.0.priority', 'medium');
    }

    public function test_admin_can_filter_and_search_work_orders(): void
    {
        $this->authenticate();
        $this->createWorkOrder();
        $this->createWorkOrder([
            'wo_number' => 'SPK-2026-002',
            'product_id' => $this->otherProduct->id,
            'stage' => 'sewing',
            'status' => 'in-progress',
            'progress' => 50,
        ]);

        $this->getJson("/api/production/work-orders?product_id={$this->otherProduct->id}&stage=sewing&status=in-progress&q=002")
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.wo_number', 'SPK-2026-002');

        $this->getJson('/api/production/work-orders?stage=cutting,sewing')
            ->assertOk()
            ->assertJsonPath('data.total', 2);
    }

    public function test_admin_can_create_show_update_and_delete_work_order(): void
    {
        $this->authenticate();

        $createResponse = $this->postJson('/api/production/work-orders', $this->payload());

        $createResponse->assertCreated()
            ->assertJsonPath('data.wo_number', 'SPK-2026-001')
            ->assertJsonPath('data.progress', 0)
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.start_date', '2026-10-17');

        $workOrderId = $createResponse->json('data.id');

        $this->getJson("/api/production/work-orders/{$workOrderId}")
            ->assertOk()
            ->assertJsonPath('data.sales_order.order_number', 'SO-2026-001');

        $this->patchJson("/api/production/work-orders/{$workOrderId}", [
            'stage' => 'qc',
            'status' => 'in-progress',
            'priority' => 'low',
            'due_date' => '2026-10-26',
        ])->assertOk()
            ->assertJsonPath('data.stage', 'qc')
            ->assertJsonPath('data.progress', 75)
            ->assertJsonPath('data.priority', 'low')
            ->assertJsonPath('data.due_date', '2026-10-26');

        $this->deleteJson("/api/production/work-orders/{$workOrderId}")
            ->assertOk()
            ->assertJsonPath('message', 'Work order berhasil dihapus');

        $this->assertDatabaseMissing('work_orders', ['id' => $workOrderId]);
    }

    public function test_completed_status_forces_progress_to_one_hundred(): void
    {
        $this->authenticate();

        $this->postJson('/api/production/work-orders', $this->payload([
            'stage' => 'printing',
            'status' => 'completed',
        ]))->assertCreated()
            ->assertJsonPath('data.progress', 100);
    }

    public function test_cancelled_status_keeps_current_progress(): void
    {
        $this->authenticate();
        $workOrder = $this->createWorkOrder([
            'stage' => 'sewing',
            'status' => 'in-progress',
            'progress' => 50,
        ]);

        $this->patchJson("/api/production/work-orders/{$workOrder->id}", [
            'stage' => 'qc',
            'status' => 'cancelled',
        ])->assertOk()
            ->assertJsonPath('data.progress', 50);
    }

    public function test_admin_can_update_stage_and_progress_automatically(): void
    {
        $this->authenticate();
        $workOrder = $this->createWorkOrder();

        $this->patchJson("/api/production/work-orders/{$workOrder->id}/stage", [
            'stage' => 'sewing',
        ])->assertOk()
            ->assertJsonPath('data.stage', 'sewing')
            ->assertJsonPath('data.status', 'in-progress')
            ->assertJsonPath('data.progress', 50);

        $this->patchJson("/api/production/work-orders/{$workOrder->id}/stage", [
            'stage' => 'ready',
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.progress', 100);
    }

    public function test_store_validates_required_fields_dates_and_identifiers(): void
    {
        $this->authenticate();

        $this->postJson('/api/production/work-orders', [
            'wo_number' => 'SPK-INVALID',
            'sales_order_id' => 'invalid',
            'product_id' => 'invalid',
            'quantity' => 0,
            'stage' => 'packing',
            'status' => 'pending',
            'priority' => 'urgent',
            'start_date' => '17-10-2026',
            'due_date' => '2026-10-10',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'sales_order_id',
                'product_id',
                'quantity',
                'stage',
                'status',
                'priority',
                'start_date',
                'due_date',
            ]);
    }

    public function test_work_order_number_must_be_unique_except_current_record(): void
    {
        $this->authenticate();
        $workOrder = $this->createWorkOrder();
        $otherWorkOrder = $this->createWorkOrder(['wo_number' => 'SPK-2026-002']);

        $this->patchJson("/api/production/work-orders/{$workOrder->id}", [
            'wo_number' => 'SPK-2026-001',
        ])->assertOk();

        $this->patchJson("/api/production/work-orders/{$otherWorkOrder->id}", [
            'wo_number' => 'SPK-2026-001',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('wo_number');
    }

    public function test_unauthenticated_user_cannot_access_work_orders(): void
    {
        $this->getJson('/api/production/work-orders')->assertUnauthorized();
    }

    private function authenticate(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'wo_number' => 'SPK-2026-001',
            'sales_order_id' => $this->salesOrder->id,
            'product_id' => $this->product->id,
            'tech_pack_id' => null,
            'quantity' => 100,
            'stage' => 'cutting',
            'status' => 'draft',
            'priority' => 'high',
            'start_date' => '2026-10-17',
            'due_date' => '2026-10-24',
        ], $overrides);
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

    private function createWorkOrder(array $overrides = []): WorkOrder
    {
        return WorkOrder::create(array_merge([
            'wo_number' => 'SPK-2026-001',
            'sales_order_id' => $this->salesOrder->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'stage' => 'cutting',
            'status' => 'draft',
            'start_date' => '2026-10-17',
            'due_date' => '2026-10-24',
            'progress' => 0,
        ], $overrides));
    }
}
