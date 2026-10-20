<?php

namespace Tests\Feature;

use App\Modules\Admin\Models\Admin;
use App\Modules\Finance\CustomerPayment\Models\CustomerPayment;
use App\Modules\Finance\Ledger\Services\FinanceLedgerService;
use App\Modules\Finance\VendorPayment\Models\VendorPayment;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinanceLedgerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private Account $account;

    private FinanceLedgerService $ledgerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'id' => Str::uuid(),
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->ledgerService = app(FinanceLedgerService::class);

        $this->account = Account::create([
            'account_name' => 'BCA Operasional',
            'account_number' => '1234567890',
            'type' => 'bank',
            'bank_name' => 'BCA',
            'opening_balance' => 10000000,
            'balance' => 10000000,
            'is_active' => true,
        ]);
    }

    private function createCustomer(): string
    {
        $id = Str::uuid();
        DB::table('m_customer')->insert([
            'id' => $id,
            'customer_name' => 'Customer Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createSalesOrder(string $customerId): string
    {
        $id = Str::uuid();
        DB::table('sales_order')->insert([
            'id' => $id,
            'order_number' => 'SO-'.Str::random(6),
            'customer_id' => $customerId,
            'order_date' => now(),
            'grand_total' => 1000000,
            'status' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createVendor(): string
    {
        $id = Str::uuid();
        DB::table('m_vendor')->insert([
            'id' => $id,
            'vendor_name' => 'Vendor Test',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function test_customer_payment_paid_creates_in_transaction(): void
    {
        $customerId = $this->createCustomer();
        $salesOrderId = $this->createSalesOrder($customerId);

        $payment = CustomerPayment::create([
            'payment_number' => 'PAY-001',
            'sales_order_id' => $salesOrderId,
            'customer_id' => $customerId,
            'type' => 'pelunasan',
            'amount' => 500000,
            'account_id' => $this->account->id,
            'payment_date' => now(),
            'status' => 'paid',
        ]);

        $transaction = $this->ledgerService->postCustomerPayment($payment, $this->admin);

        $this->assertNotNull($transaction);
        $this->assertEquals('in', $transaction->direction);
        $this->assertEquals(500000, $transaction->amount);
        $this->assertDatabaseHas('finance_transactions', [
            'reference_type' => 'customer_payment',
            'reference_id' => $payment->id,
            'direction' => 'in',
        ]);

        $this->account->refresh();
        $this->assertEquals(10500000, (float) $this->account->balance);
    }

    public function test_customer_payment_pending_does_not_create_ledger(): void
    {
        $customerId = $this->createCustomer();
        $salesOrderId = $this->createSalesOrder($customerId);

        $payment = CustomerPayment::create([
            'payment_number' => 'PAY-002',
            'sales_order_id' => $salesOrderId,
            'customer_id' => $customerId,
            'type' => 'dp',
            'amount' => 500000,
            'account_id' => $this->account->id,
            'payment_date' => now(),
            'status' => 'pending',
        ]);

        $transaction = $this->ledgerService->postCustomerPayment($payment, $this->admin);

        $this->assertNull($transaction);
        $this->assertDatabaseCount('finance_transactions', 0);
    }

    public function test_customer_payment_cannot_be_posted_twice(): void
    {
        $customerId = $this->createCustomer();
        $salesOrderId = $this->createSalesOrder($customerId);

        $payment = CustomerPayment::create([
            'payment_number' => 'PAY-003',
            'sales_order_id' => $salesOrderId,
            'customer_id' => $customerId,
            'type' => 'pelunasan',
            'amount' => 500000,
            'account_id' => $this->account->id,
            'payment_date' => now(),
            'status' => 'paid',
        ]);

        $first = $this->ledgerService->postCustomerPayment($payment, $this->admin);
        $second = $this->ledgerService->postCustomerPayment($payment, $this->admin);

        $this->assertEquals($first->id, $second->id);
        $this->assertDatabaseCount('finance_transactions', 1);
    }

    public function test_vendor_payment_paid_creates_out_transaction(): void
    {
        $vendorId = $this->createVendor();

        $payment = VendorPayment::create([
            'payment_number' => 'VP-001',
            'vendor_id' => $vendorId,
            'vendor_invoice_number' => 'INV-001',
            'payment_type' => 'transfer',
            'payment_date' => now(),
            'total_paid' => 300000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'account_id' => $this->account->id,
            'status' => 'paid',
        ]);

        $transaction = $this->ledgerService->postVendorPayment($payment, $this->admin);

        $this->assertNotNull($transaction);
        $this->assertEquals('out', $transaction->direction);
        $this->assertEquals(300000, $transaction->amount);
        $this->assertDatabaseHas('finance_transactions', [
            'reference_type' => 'vendor_payment',
            'reference_id' => $payment->id,
            'direction' => 'out',
        ]);

        $this->account->refresh();
        $this->assertEquals(9700000, (float) $this->account->balance);
    }

    public function test_vendor_payment_unpaid_does_not_affect_balance(): void
    {
        $vendorId = $this->createVendor();

        $payment = VendorPayment::create([
            'payment_number' => 'VP-002',
            'vendor_id' => $vendorId,
            'vendor_invoice_number' => 'INV-002',
            'payment_type' => 'transfer',
            'payment_date' => now(),
            'total_paid' => 300000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'account_id' => $this->account->id,
            'status' => 'unpaid',
        ]);

        $transaction = $this->ledgerService->postVendorPayment($payment, $this->admin);

        $this->assertNull($transaction);
        $this->assertDatabaseCount('finance_transactions', 0);
    }

    public function test_account_balance_matches_opening_plus_in_minus_out(): void
    {
        $this->ledgerService->postAdjustment(
            $this->account,
            'in',
            '2000000',
            now(),
            'Test in',
            $this->admin
        );

        $this->ledgerService->postAdjustment(
            $this->account,
            'out',
            '500000',
            now(),
            'Test out',
            $this->admin
        );

        $this->account->refresh();

        $this->assertEquals(11500000, (float) $this->account->balance);
    }

    public function test_void_transaction_restores_balance(): void
    {
        $transaction = $this->ledgerService->postAdjustment(
            $this->account,
            'in',
            '1000000',
            now(),
            'Test in',
            $this->admin
        );

        $this->ledgerService->voidTransaction($transaction, $this->admin, 'Koreksi');

        $this->account->refresh();
        $this->assertEquals(10000000, (float) $this->account->balance);
        $this->assertDatabaseHas('finance_transactions', [
            'id' => $transaction->id,
            'status' => 'void',
        ]);
    }

    public function test_adjustment_in_increases_balance(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/finance/account-adjustments', [
            'account_id' => $this->account->id,
            'direction' => 'in',
            'amount' => 100000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Koreksi saldo awal',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.account.balance', 10100000);
    }

    public function test_adjustment_out_decreases_balance(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/finance/account-adjustments', [
            'account_id' => $this->account->id,
            'direction' => 'out',
            'amount' => 100000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Koreksi saldo',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.account.balance', 9900000);
    }

    public function test_adjustment_out_rejected_if_balance_insufficient(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/finance/account-adjustments', [
            'account_id' => $this->account->id,
            'direction' => 'out',
            'amount' => 20000000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Koreksi saldo',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_transfer_creates_two_transactions_with_same_transfer_group(): void
    {
        $destination = Account::create([
            'account_name' => 'Kas Kecil',
            'account_number' => 'KAS-001',
            'type' => 'cash',
            'bank_name' => null,
            'opening_balance' => 0,
            'balance' => 0,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/finance/account-transfers', [
            'source_account_id' => $this->account->id,
            'destination_account_id' => $destination->id,
            'amount' => 1000000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Pengisian kas',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.source_account.balance', 9000000)
            ->assertJsonPath('data.destination_account.balance', 1000000);

        $transferGroupId = $response->json('data.transfer_group_id');
        $this->assertDatabaseCount('finance_transactions', 2);
        $this->assertDatabaseHas('finance_transactions', [
            'transfer_group_id' => $transferGroupId,
            'direction' => 'out',
        ]);
        $this->assertDatabaseHas('finance_transactions', [
            'transfer_group_id' => $transferGroupId,
            'direction' => 'in',
        ]);
    }

    public function test_transfer_decreases_source_and_increases_destination(): void
    {
        $destination = Account::create([
            'account_name' => 'Kas Kecil',
            'account_number' => 'KAS-002',
            'type' => 'cash',
            'bank_name' => null,
            'opening_balance' => 0,
            'balance' => 0,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->admin, [], 'sanctum');

        $this->postJson('/api/finance/account-transfers', [
            'source_account_id' => $this->account->id,
            'destination_account_id' => $destination->id,
            'amount' => 1000000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Pengisian kas',
        ]);

        $this->account->refresh();
        $destination->refresh();

        $this->assertEquals(9000000, (float) $this->account->balance);
        $this->assertEquals(1000000, (float) $destination->balance);
    }

    public function test_transfer_does_not_change_total_company_balance(): void
    {
        $destination = Account::create([
            'account_name' => 'Kas Kecil',
            'account_number' => 'KAS-003',
            'type' => 'cash',
            'bank_name' => null,
            'opening_balance' => 0,
            'balance' => 0,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->admin, [], 'sanctum');

        $this->postJson('/api/finance/account-transfers', [
            'source_account_id' => $this->account->id,
            'destination_account_id' => $destination->id,
            'amount' => 1000000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $total = Account::sum('balance');
        $this->assertEquals(10000000, (float) $total);
    }

    public function test_summary_calculates_period_balances_correctly(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $this->ledgerService->postAdjustment(
            $this->account,
            'in',
            '5000000',
            Carbon::parse('2026-10-15'),
            'Masuk',
            $this->admin
        );

        $this->ledgerService->postAdjustment(
            $this->account,
            'out',
            '1500000',
            Carbon::parse('2026-10-16'),
            'Keluar',
            $this->admin
        );

        $response = $this->getJson('/api/finance/accounts/summary?'.http_build_query([
            'account_id' => $this->account->id,
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.opening_balance', 10000000)
            ->assertJsonPath('data.cash_in', 5000000)
            ->assertJsonPath('data.cash_out', 1500000)
            ->assertJsonPath('data.closing_balance', 13500000);
    }

    public function test_transaction_filter_by_direction_works(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $this->ledgerService->postAdjustment($this->account, 'in', '100000', now(), 'In', $this->admin);
        $this->ledgerService->postAdjustment($this->account, 'out', '50000', now(), 'Out', $this->admin);

        $response = $this->getJson("/api/finance/accounts/{$this->account->id}/transactions?direction=in");

        $response->assertStatus(200)
            ->assertJsonPath('data.data.0.direction', 'in');
    }

    public function test_unauthenticated_user_receives_401(): void
    {
        $response = $this->getJson('/api/finance/accounts/summary');
        $response->assertStatus(401);
    }

    public function test_zero_or_negative_amount_rejected(): void
    {
        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson('/api/finance/account-adjustments', [
            'account_id' => $this->account->id,
            'direction' => 'in',
            'amount' => 0,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }
}
