<?php

namespace Tests\Feature;

use App\Modules\Admin\Models\Admin;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private string $customerId;

    private string $salesOrderId;

    private string $productId;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-24 12:00:00');

        $this->admin = Admin::create([
            'id' => Str::uuid(),
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->customerId = (string) Str::uuid();
        DB::table('m_customer')->insert([
            'id' => $this->customerId,
            'customer_name' => 'PT Maju Jaya',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = (string) Str::uuid();
        DB::table('categories')->insert([
            'id' => $categoryId,
            'category_name' => 'Apparel',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unitId = (string) Str::uuid();
        DB::table('units')->insert([
            'id' => $unitId,
            'unit_code' => 'PCS',
            'unit_name' => 'Piece',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->productId = (string) Str::uuid();
        DB::table('m_products')->insert([
            'id' => $this->productId,
            'product_name' => 'Hoodie Fleece Custom',
            'category_id' => $categoryId,
            'unit_id' => $unitId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->salesOrderId = (string) Str::uuid();
        DB::table('sales_order')->insert([
            'id' => $this->salesOrderId,
            'order_number' => 'SO-2026-001',
            'customer_id' => $this->customerId,
            'order_date' => '2026-10-01',
            'status' => 'approved',
            'subtotal' => 10000000,
            'total_tax' => 0,
            'grand_total' => 10000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_returns_stable_shape_when_database_is_empty(): void
    {
        $this->authenticate();

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpi.revenue_month.value', 0)
            ->assertJsonPath('data.kpi.active_po.value', 0)
            ->assertJsonPath('data.kpi.unpaid_invoices.value', 0)
            ->assertJsonPath('data.pipeline_summary.paid_amount', 0)
            ->assertJsonPath('data.pipeline_summary.pending_amount', 0)
            ->assertJsonPath('data.pipeline.current_page', 1)
            ->assertJsonPath('data.pipeline.total', 0)
            ->assertJsonPath('data.pipeline.data', [])
            ->assertJsonPath('data.active_work_orders', [])
            ->assertJsonPath('data.active_work_orders_total', 0);
    }

    public function test_pipeline_paginates_receivable_invoices_only(): void
    {
        $this->authenticate();

        for ($i = 1; $i <= 3; $i++) {
            $this->createInvoice("INV-2026-00{$i}", 1000000, 'pending', "2026-10-0{$i}");
        }

        // Vendor payment must not appear in the receivable pipeline.
        $this->createVendorPayment('VP-2026-001', 500000, 'paid');

        $this->getJson('/api/dashboard?page=1&per_page=2')
            ->assertOk()
            ->assertJsonPath('data.pipeline.current_page', 1)
            ->assertJsonPath('data.pipeline.per_page', 2)
            ->assertJsonPath('data.pipeline.total', 3)
            ->assertJsonPath('data.pipeline.last_page', 2)
            ->assertJsonCount(2, 'data.pipeline.data')
            ->assertJsonPath('data.pipeline.data.0.module', 'Sales')
            ->assertJsonPath('data.pipeline.data.0.type', 'incoming');

        $this->getJson('/api/dashboard?page=2&per_page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data.pipeline.data');
    }

    public function test_dashboard_rejects_invalid_pagination_parameters(): void
    {
        $this->authenticate();

        $this->getJson('/api/dashboard?page=0')->assertUnprocessable();
        $this->getJson('/api/dashboard?per_page=0')->assertUnprocessable();
        $this->getJson('/api/dashboard?per_page=101')->assertUnprocessable();
        $this->getJson('/api/dashboard?active_work_orders_limit=0')->assertUnprocessable();
        $this->getJson('/api/dashboard?active_work_orders_limit=101')->assertUnprocessable();
    }

    public function test_dp_and_pelunasan_do_not_double_count_paid_amount(): void
    {
        $this->authenticate();

        // Order total 10jt: DP invoice 5jt + pelunasan 5jt. Payment 5jt paid
        // is allocated FIFO to the DP invoice.
        $this->createInvoice('INV-DP-001', 5000000, 'pending', '2026-10-01', 'dp');
        $this->createInvoice('INV-PL-001', 5000000, 'pending', '2026-10-10', 'pelunasan');
        $this->createCustomerPayment('PAY-001', 5000000, 'paid');

        $response = $this->getJson('/api/dashboard?per_page=10')->assertOk();

        $rows = collect($response->json('data.pipeline.data'))->keyBy('invoice_number');

        $this->assertEquals(5000000, $rows['INV-DP-001']['paid_amount']);
        $this->assertEquals(0, $rows['INV-DP-001']['outstanding_amount']);
        $this->assertEquals('paid', $rows['INV-DP-001']['status']);
        $this->assertEquals('DP', $rows['INV-DP-001']['invoice_type']);

        $this->assertEquals(0, $rows['INV-PL-001']['paid_amount']);
        $this->assertEquals(5000000, $rows['INV-PL-001']['outstanding_amount']);
        $this->assertEquals('pending', $rows['INV-PL-001']['status']);
        $this->assertEquals('Pelunasan', $rows['INV-PL-001']['invoice_type']);

        // Summary: paid 5jt, outstanding 5jt — never exceeds the 10jt order.
        $this->assertEquals(5000000, $response->json('data.pipeline_summary.paid_amount'));
        $this->assertEquals(5000000, $response->json('data.pipeline_summary.pending_amount'));
        $this->assertEquals(5000000, $response->json('data.kpi.unpaid_invoices.value'));
        $this->assertEquals(1, $response->json('data.kpi.unpaid_invoices.count'));
    }

    public function test_partial_payment_counts_as_outstanding_and_cancelled_invoice_is_excluded(): void
    {
        $this->authenticate();

        $this->createInvoice('INV-2026-010', 4000000, 'pending', '2026-10-05');
        $this->createInvoice('INV-2026-011', 2000000, 'cancelled', '2026-10-05');
        $this->createCustomerPayment('PAY-010', 1500000, 'paid');
        // Pending payments are not "pembayaran sah".
        $this->createCustomerPayment('PAY-011', 500000, 'pending');

        $response = $this->getJson('/api/dashboard')->assertOk();

        $this->assertEquals(1, $response->json('data.pipeline.total'));
        $this->assertEquals(2500000, $response->json('data.pipeline.data.0.outstanding_amount'));
        $this->assertEquals(1500000, $response->json('data.pipeline.data.0.paid_amount'));
        $this->assertEquals(1500000, $response->json('data.pipeline_summary.paid_amount'));
        $this->assertEquals(2500000, $response->json('data.pipeline_summary.pending_amount'));
        $this->assertEquals(0, $response->json('data.pipeline_summary.overdue_amount'));
    }

    public function test_pipeline_summary_is_independent_of_current_page(): void
    {
        $this->authenticate();

        for ($i = 1; $i <= 5; $i++) {
            $this->createInvoice("INV-2026-1{$i}0", 1000000 * $i, 'pending', "2026-10-1{$i}");
        }

        $pageOne = $this->getJson('/api/dashboard?page=1&per_page=2')->assertOk();
        $pageTwo = $this->getJson('/api/dashboard?page=2&per_page=2')->assertOk();

        $this->assertEquals(
            $pageOne->json('data.pipeline_summary'),
            $pageTwo->json('data.pipeline_summary')
        );
        $this->assertEquals(15000000, $pageOne->json('data.pipeline_summary.pending_amount'));
        $this->assertEquals(5, $pageOne->json('data.pipeline_summary.pending_count'));
    }

    public function test_overdue_status_when_due_date_passed_with_outstanding(): void
    {
        $this->authenticate();

        // Jatuh tempo lewat + masih ada outstanding -> overdue.
        $this->createInvoice('INV-OD-001', 3000000, 'pending', '2026-09-01', 'pelunasan', '2026-10-01');
        // Jatuh tempo lewat tapi sudah lunas -> paid.
        $this->createInvoice('INV-OD-002', 2000000, 'paid', '2026-09-01', 'pelunasan', '2026-10-01');
        // Belum jatuh tempo -> pending.
        $this->createInvoice('INV-OD-003', 1000000, 'pending', '2026-10-20', 'pelunasan', '2026-11-20');

        $response = $this->getJson('/api/dashboard?per_page=10')->assertOk();
        $rows = collect($response->json('data.pipeline.data'))->keyBy('invoice_number');

        $this->assertEquals('overdue', $rows['INV-OD-001']['status']);
        $this->assertEquals('2026-10-01', $rows['INV-OD-001']['due_date']);
        $this->assertEquals('paid', $rows['INV-OD-002']['status']);
        $this->assertEquals('pending', $rows['INV-OD-003']['status']);

        $this->assertEquals(3000000, $response->json('data.pipeline_summary.overdue_amount'));
        $this->assertEquals(1, $response->json('data.pipeline_summary.overdue_count'));
        $this->assertEquals(4000000, $response->json('data.pipeline_summary.pending_amount'));
    }

    public function test_revenue_growth_is_deterministic_when_previous_period_is_zero(): void
    {
        $this->authenticate();

        // Invoice only this month -> previous = 0, current > 0 -> growth 100.
        $this->createInvoice('INV-2026-100', 3000000, 'paid', '2026-10-10');

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.kpi.revenue_month.value', 3000000)
            ->assertJsonPath('data.kpi.revenue_month.growth', 100);
    }

    public function test_month_boundary_uses_app_timezone(): void
    {
        $this->authenticate();

        // Last month invoice counts toward previous period, not current.
        $this->createInvoice('INV-2026-090', 2000000, 'paid', '2026-09-30');
        $this->createInvoice('INV-2026-101', 3000000, 'paid', '2026-10-01');

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.kpi.revenue_month.value', 3000000);
    }

    public function test_active_work_orders_exclude_completed_and_cancelled(): void
    {
        $this->authenticate();

        $this->createWorkOrder('SPK-001', 'in-progress', 'sewing', 50, 'high', '2026-10-25');
        $this->createWorkOrder('SPK-002', 'draft', 'cutting', 0, 'low', '2026-10-26');
        $this->createWorkOrder('SPK-003', 'completed', 'ready', 100, 'high', '2026-10-20');
        $this->createWorkOrder('SPK-004', 'cancelled', 'cutting', 0, 'medium', '2026-10-21');

        $response = $this->getJson('/api/dashboard')->assertOk();

        $this->assertEquals(2, $response->json('data.active_work_orders_total'));
        $this->assertEquals('SPK-001', $response->json('data.active_work_orders.0.work_order_number'));
        $this->assertEquals('Sewing', $response->json('data.active_work_orders.0.stage'));
        $this->assertEquals(50, $response->json('data.active_work_orders.0.progress'));
        $this->assertEquals('in-progress', $response->json('data.active_work_orders.0.status'));
    }

    public function test_work_order_limit_limits_items_but_not_total(): void
    {
        $this->authenticate();

        for ($i = 1; $i <= 6; $i++) {
            $this->createWorkOrder("SPK-L-{$i}", 'in-progress', 'cutting', 0, 'medium', "2026-10-2{$i}");
        }

        $response = $this->getJson('/api/dashboard?active_work_orders_limit=4')->assertOk();

        $this->assertEquals(6, $response->json('data.active_work_orders_total'));
        $this->assertCount(4, $response->json('data.active_work_orders'));
    }

    public function test_kpi_endpoint_matches_dashboard_kpi_definition(): void
    {
        $this->authenticate();

        $this->createInvoice('INV-2026-200', 7000000, 'pending', '2026-10-10');
        $this->createCustomerPayment('PAY-200', 2000000, 'paid');

        $dashboard = $this->getJson('/api/dashboard')->assertOk();
        $kpi = $this->getJson('/api/dashboard/kpi')->assertOk();

        $this->assertEquals($dashboard->json('data.kpi'), $kpi->json('data'));
        $this->assertEquals(5000000, $kpi->json('data.unpaid_invoices.value'));
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
        $this->getJson('/api/dashboard/kpi')->assertUnauthorized();
    }

    private function createInvoice(
        string $number,
        float $grandTotal,
        string $status,
        string $invoiceDate,
        string $type = 'pelunasan',
        ?string $dueDate = null,
    ): string {
        $id = (string) Str::uuid();
        DB::table('invoices')->insert([
            'id' => $id,
            'invoice_number' => $number,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'customer_id' => $this->customerId,
            'sales_order_id' => $this->salesOrderId,
            'type' => $type,
            'status' => $status,
            'subtotal' => $grandTotal,
            'tax_total' => 0,
            'grand_total' => $grandTotal,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createCustomerPayment(string $number, float $amount, string $status): void
    {
        DB::table('m_accounts')->insertOrIgnore([
            'id' => 'acc-001',
            'account_name' => 'BCA',
            'account_number' => '123',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customer_payments')->insert([
            'id' => (string) Str::uuid(),
            'payment_number' => $number,
            'sales_order_id' => $this->salesOrderId,
            'customer_id' => $this->customerId,
            'type' => 'dp',
            'amount' => $amount,
            'account_id' => 'acc-001',
            'payment_date' => '2026-10-15',
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createVendorPayment(string $number, float $totalPaid, string $status): void
    {
        DB::table('m_vendor')->insertOrIgnore([
            'id' => 'vendor-001',
            'vendor_name' => 'PT Kain',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('m_accounts')->insertOrIgnore([
            'id' => 'acc-002',
            'account_name' => 'Mandiri',
            'account_number' => '456',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('vendor_payments')->insert([
            'id' => (string) Str::uuid(),
            'payment_number' => $number,
            'vendor_id' => 'vendor-001',
            'vendor_invoice_number' => 'VINV-1',
            'payment_type' => 'transfer',
            'payment_date' => '2026-10-15',
            'total_paid' => $totalPaid,
            'account_id' => 'acc-002',
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createWorkOrder(
        string $woNumber,
        string $status,
        string $stage,
        int $progress,
        string $priority,
        string $dueDate,
    ): void {
        DB::table('work_orders')->insert([
            'id' => (string) Str::uuid(),
            'wo_number' => $woNumber,
            'sales_order_id' => $this->salesOrderId,
            'product_id' => $this->productId,
            'quantity' => 100,
            'stage' => $stage,
            'status' => $status,
            'priority' => $priority,
            'start_date' => '2026-10-01',
            'due_date' => $dueDate,
            'progress' => $progress,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function authenticate(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');
    }
}
