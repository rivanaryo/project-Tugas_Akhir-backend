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
