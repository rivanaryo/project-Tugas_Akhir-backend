# Project Notes

## Module Structure

This Laravel backend is organized into domain modules under `app/Modules/`.

### Inventory & Procurement

All procurement features are grouped under `App\Modules\Inventory`:

- `PurchaseOrder` — purchase orders and line items
- `GoodReceipt` — goods receipt and line items
- `StockMovement` — stock movements
- `StockOpname` — stock opname
- `Prediction` — ROP (reorder point) ML prediction
- `Exports` — purchase reports (Excel)

Base route prefix: `api/inventory`

Example endpoints:

- `GET/POST    api/inventory/purchase-orders`
- `GET/PUT/DEL api/inventory/purchase-orders/{purchaseOrder}`
- `GET/POST    api/inventory/good-receipts`
- `GET/PUT/DEL api/inventory/good-receipts/{goodReceipt}`
- `GET         api/inventory/export`

### Finance

Financial operations live under `App\Modules\Finance`.

Currently contains:

- `VendorPayment` — vendor bill payments

Base route prefix: `api/finance`

Example endpoints:

- `GET/POST    api/finance/vendor-payments`
- `GET/PUT/DEL api/finance/vendor-payments/{vendorPayment}`

Customer invoices & payments remain in the `Sales` module (`api/sales/invoices`).

## Build / Test Commands

```bash
composer dump-autoload
php artisan route:list
php artisan test
php artisan migrate
```

## Routing & Middleware

- RouteServiceProvider tidak digunakan; route dikonfigurasi di `bootstrap/app.php` dan didaftarkan oleh module ServiceProviders.
- Endpoint admin/modul menggunakan middleware: `['api', 'auth:sanctum', 'role.admin']`.
- Alias middleware:
  - `jwt.auth` => `App\Modules\Admin\Middleware\AuthenticateJwt`
  - `role.admin` => `App\Modules\Admin\Middleware\EnsureAdminRole`

## Authentication

- Model user admin: `App\Modules\Admin\Models\Admin`.
- Tabel: `users` dengan UUID primary key.
- Sanctum digunakan untuk middleware `auth:sanctum`.
- Untuk feature test, autentikasi menggunakan `Laravel\Sanctum\Sanctum::actingAs($admin, [], 'sanctum')`.

## Response Format

- Format JSON standar:

  ```json
  {
    "success": true,
    "message": "Pesan",
    "data": {}
  }
  ```

## Testing

- PHPUnit dikonfigurasi di `phpunit.xml`.
- Database test menggunakan SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).
- Jalankan test: `php artisan test`.

## Code Style

- Project menggunakan Laravel Pint.
- Jalankan formatter: `vendor/bin/pint {path}`.

## Tech Pack

- Modul berada di `app/Modules/Rd/TechPack`.
- Model: `TechPack`, `TechPackSizeChart`, `TechPackCosting`, `TechPackBomItem`.
- Service perhitungan: `App\Modules\Rd\TechPack\Services\TechPackCostingService`.
- Field hasil kalkulasi (`bom_items.total`, `costing.material_cost`, `costing.hpp`, `costing.sell_price`) tidak boleh diisi frontend; dihitung backend.
