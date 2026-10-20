<?php

namespace Tests\Feature;

use App\Modules\Admin\Models\Admin;
use App\Modules\MasterData\DataProduk\Models\Product;
use App\Modules\MasterData\DataProduk\References\Models\Units;
use App\Modules\Rd\SampleApproval\Models\SampleApproval;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SampleApprovalTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Product $product;

    private Product $otherProduct;

    private string $customerId;

    private string $otherCustomerId;

    private SalesOrder $salesOrder;

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
        $this->customerId = $this->createCustomer('PT Maju Jaya Retail');
        $this->otherCustomerId = $this->createCustomer('PT Mantra Fashion');

        $this->salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-001',
            'customer_id' => $this->customerId,
            'order_date' => '2026-10-19',
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_create_show_update_and_delete_sample_approval(): void
    {
        $this->authenticate();

        $createResponse = $this->postJson('/api/rd/sample-approvals', $this->payload());

        $createResponse->assertCreated()
            ->assertJsonPath('message', 'Sample approval berhasil dibuat')
            ->assertJsonPath('data.sample_code', 'SMP-001')
            ->assertJsonPath('data.product.product_name', 'Hoodie Fleece Custom')
            ->assertJsonPath('data.customer.customer_name', 'PT Maju Jaya Retail')
            ->assertJsonPath('data.sales_order.order_number', 'SO-2026-001')
            ->assertJsonPath('data.revision_count', 0);

        $id = $createResponse->json('data.id');

        $this->getJson("/api/rd/sample-approvals/{$id}")
            ->assertOk()
            ->assertJsonPath('message', 'Detail sample approval berhasil diambil');

        $this->patchJson("/api/rd/sample-approvals/{$id}", [
            'sample_code' => 'SMP-001',
            'notes' => 'Catatan diperbarui.',
            'revision_count' => 2,
        ])->assertOk()
            ->assertJsonPath('data.notes', 'Catatan diperbarui.')
            ->assertJsonPath('data.revision_count', 2);

        $this->deleteJson("/api/rd/sample-approvals/{$id}")
            ->assertOk()
            ->assertJsonPath('message', 'Sample approval berhasil dihapus');

        $this->assertDatabaseMissing('sample_approvals', ['id' => $id]);
    }

    public function test_list_supports_status_product_customer_and_search_filters(): void
    {
        $this->authenticate();
        $this->createSampleApproval();
        $this->createSampleApproval([
            'sample_code' => 'OTHER-002',
            'product_id' => $this->otherProduct->id,
            'customer_id' => $this->otherCustomerId,
            'sales_order_id' => null,
            'status' => 'approved',
        ]);

        $this->getJson("/api/rd/sample-approvals?status=pending&product_id={$this->product->id}&customer_id={$this->customerId}&q=SMP")
            ->assertOk()
            ->assertJsonPath('message', 'Daftar sample approval berhasil diambil')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sample_code', 'SMP-001');
    }

    public function test_revision_status_increments_revision_count(): void
    {
        $this->authenticate();
        $sampleApproval = $this->createSampleApproval();

        $this->patchJson("/api/rd/sample-approvals/{$sampleApproval->id}/status", [
            'status' => 'revision',
            'notes' => 'Logo harus diperbesar.',
        ])->assertOk()
            ->assertJsonPath('message', 'Status sample approval berhasil diperbarui')
            ->assertJsonPath('data.status', 'revision')
            ->assertJsonPath('data.notes', 'Logo harus diperbesar.')
            ->assertJsonPath('data.revision_count', 1);

        $this->patchJson("/api/rd/sample-approvals/{$sampleApproval->id}/status", [
            'status' => 'approved',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.revision_count', 1);
    }

    public function test_status_endpoint_rejects_pending_status(): void
    {
        $this->authenticate();
        $sampleApproval = $this->createSampleApproval();

        $this->patchJson("/api/rd/sample-approvals/{$sampleApproval->id}/status", [
            'status' => 'pending',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_sample_code_must_be_unique_except_current_record(): void
    {
        $this->authenticate();
        $sampleApproval = $this->createSampleApproval();
        $otherSampleApproval = $this->createSampleApproval(['sample_code' => 'SMP-002']);

        $this->patchJson("/api/rd/sample-approvals/{$sampleApproval->id}", [
            'sample_code' => 'SMP-001',
        ])->assertOk();

        $this->patchJson("/api/rd/sample-approvals/{$otherSampleApproval->id}", [
            'sample_code' => 'SMP-001',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('sample_code');
    }

    public function test_store_validates_required_fields_and_identifiers(): void
    {
        $this->authenticate();

        $this->postJson('/api/rd/sample-approvals', [
            'sample_code' => 'SMP-INVALID',
            'product_id' => 'invalid',
            'customer_id' => 'invalid',
            'sales_order_id' => 'invalid',
            'submitted_date' => '19-10-2026',
            'status' => 'draft',
            'revision_count' => -1,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'product_id',
                'customer_id',
                'sales_order_id',
                'submitted_date',
                'status',
                'revision_count',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_sample_approvals(): void
    {
        $this->getJson('/api/rd/sample-approvals')->assertUnauthorized();
    }

    private function authenticate(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'sample_code' => 'SMP-001',
            'product_id' => $this->product->id,
            'customer_id' => $this->customerId,
            'sales_order_id' => $this->salesOrder->id,
            'submitted_date' => '2026-10-19',
            'status' => 'pending',
            'notes' => 'Sample pertama untuk approval.',
        ], $overrides);
    }

    private function createSampleApproval(array $overrides = []): SampleApproval
    {
        return SampleApproval::create($this->payload($overrides));
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

    private function createCustomer(string $name): string
    {
        $id = (string) Str::uuid();
        DB::table('m_customer')->insert([
            'id' => $id,
            'customer_name' => $name,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
