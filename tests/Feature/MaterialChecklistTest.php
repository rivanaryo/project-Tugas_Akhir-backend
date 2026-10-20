<?php

namespace Tests\Feature;

use App\Modules\Admin\Models\Admin;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use App\Modules\Rd\TechPack\Models\TechPack;
use App\Modules\Rd\TechPack\Models\TechPackBomItem;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaterialChecklistTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private WorkOrder $workOrder;

    private Product $finishedProduct;

    private Product $shortageMaterial;

    private Product $pendingMaterial;

    private Product $sufficientMaterial;

    private Units $unit;

    private string $vendorId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'id' => Str::uuid(),
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $categoryId = (string) Str::uuid();
        DB::table('categories')->insert([
            'id' => $categoryId,
            'category_name' => 'Material',
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

        $this->finishedProduct = $this->createProduct('Hoodie Fleece Custom', $categoryId);
        $this->shortageMaterial = $this->createProduct('Kain Fleece', $categoryId);
        $this->pendingMaterial = $this->createProduct('Benang Polyester', $categoryId);
        $this->sufficientMaterial = $this->createProduct('Rib Hoodie', $categoryId);

        $customerId = (string) Str::uuid();
        DB::table('m_customer')->insert([
            'id' => $customerId,
            'customer_name' => 'PT Mantra',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-001',
            'customer_id' => $customerId,
            'order_date' => '2026-10-17',
            'status' => 'approved',
        ]);

        $techPack = TechPack::create([
            'name' => 'Hoodie Tech Pack',
            'status' => 'approved',
            'product_id' => $this->finishedProduct->id,
        ]);

        foreach ([$this->shortageMaterial, $this->pendingMaterial, $this->sufficientMaterial] as $material) {
            TechPackBomItem::create([
                'tech_pack_id' => $techPack->id,
                'material_product_id' => $material->id,
                'usage' => 1,
                'unit_id' => $this->unit->id,
                'cost_per_unit' => 10000,
                'total' => 10000,
            ]);
        }

        $this->workOrder = WorkOrder::create([
            'wo_number' => 'SPK-2026-001',
            'sales_order_id' => $salesOrder->id,
            'product_id' => $this->finishedProduct->id,
            'tech_pack_id' => null,
            'quantity' => 100,
            'stage' => 'cutting',
            'status' => 'in-progress',
            'start_date' => '2026-10-17',
            'due_date' => '2026-10-24',
            'progress' => 0,
        ]);

        $this->vendorId = (string) Str::uuid();
        DB::table('m_vendor')->insert([
            'id' => $this->vendorId,
            'vendor_name' => 'PT Supplier',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->createPurchase('PO-APPROVED', 'approved', $this->shortageMaterial, 60);
        $receivedItemId = $this->createPurchase('PO-RECEIVED', 'received', $this->sufficientMaterial, 200);
        $this->createReceipt($receivedItemId, $this->sufficientMaterial, 120);
    }

    public function test_material_checklist_calculates_shortage_pending_and_sufficient(): void
    {
        $this->authenticate();

        $response = $this->getJson('/api/production/material-checklist');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Material checklist berhasil diambil')
            ->assertJsonCount(3, 'data');

        $items = collect($response->json('data'))->keyBy('material_id');

        $shortage = $items[(string) $this->shortageMaterial->id];
        $pending = $items[(string) $this->pendingMaterial->id];
        $sufficient = $items[(string) $this->sufficientMaterial->id];

        $this->assertSame(100, $shortage['required']);
        $this->assertSame(60, $shortage['purchased']);
        $this->assertSame('shortage', $shortage['status']);
        $this->assertSame(0, $pending['purchased']);
        $this->assertSame('pending', $pending['status']);
        $this->assertSame(120, $sufficient['purchased']);
        $this->assertSame('sufficient', $sufficient['status']);
        $this->assertSame('MTR', $sufficient['unit']);
        $this->assertSame('SPK-2026-001', $sufficient['work_order']['wo_number']);
    }

    public function test_material_checklist_supports_all_filters(): void
    {
        $this->authenticate();

        $this->getJson("/api/production/material-checklist?work_order_id={$this->workOrder->id}&product_id={$this->finishedProduct->id}&material_id={$this->shortageMaterial->id}&status=shortage")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.material_id', (string) $this->shortageMaterial->id);
    }

    public function test_draft_work_order_is_included_but_completed_work_order_is_excluded(): void
    {
        $this->authenticate();

        $this->workOrder->update(['status' => 'draft']);
        $this->getJson('/api/production/material-checklist')->assertJsonCount(3, 'data');

        $this->workOrder->update(['status' => 'completed']);
        $this->getJson('/api/production/material-checklist')->assertJsonCount(0, 'data');
    }

    public function test_invalid_filter_returns_validation_error(): void
    {
        $this->authenticate();

        $this->getJson('/api/production/material-checklist?status=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_unauthenticated_user_cannot_access_material_checklist(): void
    {
        $this->getJson('/api/production/material-checklist')->assertUnauthorized();
    }

    private function authenticate(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');
    }

    private function createProduct(string $name, string $categoryId): Product
    {
        $product = new Product;
        $product->id = Str::uuid();
        $product->product_name = $name;
        $product->category_id = $categoryId;
        $product->unit_id = $this->unit->id;
        $product->is_active = true;
        $product->save();

        return $product;
    }

    private function createPurchase(string $number, string $status, Product $material, int $quantity): string
    {
        $purchaseOrderId = (string) Str::uuid();
        $itemId = (string) Str::uuid();

        DB::table('purchase_order')->insert([
            'id' => $purchaseOrderId,
            'po_number' => $number,
            'vendor_id' => $this->vendorId,
            'status' => $status,
            'po_date' => '2026-10-17',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('purchase_order_items')->insert([
            'id' => $itemId,
            'purchase_order_id' => $purchaseOrderId,
            'product_id' => $material->id,
            'unit_id' => $this->unit->id,
            'quantity' => $quantity,
            'unit_price' => 10000,
            'total_price' => $quantity * 10000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $itemId;
    }

    private function createReceipt(string $purchaseOrderItemId, Product $material, int $quantity): void
    {
        $purchaseOrderId = DB::table('purchase_order_items')->where('id', $purchaseOrderItemId)->value('purchase_order_id');
        $receiptId = (string) Str::uuid();

        DB::table('good_receipt')->insert([
            'id' => $receiptId,
            'gr_number' => 'GR-2026-001',
            'purchase_order_id' => $purchaseOrderId,
            'vendor_id' => $this->vendorId,
            'receipt_date' => '2026-10-18',
            'status' => 'received',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('good_receipt_items')->insert([
            'id' => Str::uuid(),
            'good_receipt_id' => $receiptId,
            'purchase_order_item_id' => $purchaseOrderItemId,
            'product_id' => $material->id,
            'qty_received' => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
