<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\StockMovementType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Unit;
use App\Services\StockService;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function __construct(
        private StockService $stockService,
        private TenantContext $tenantContext,
    ) {}

    /**
     * Seed products, batches, suppliers, POs (with receive), and sales for all tenants.
     * Run after TenantSeeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->tenantContext->setTenant($tenant);
            $this->seedProductsAndBatches($tenant);
            $this->seedSuppliers($tenant);
            $this->seedPurchaseOrders($tenant);
            $this->seedTransactions($tenant);
        }

        $this->tenantContext->clear();
    }

    private function seedProductsAndBatches(Tenant $tenant): void
    {
        $categories = Category::where('tenant_id', $tenant->id)->pluck('id', 'name');
        $units = Unit::where('tenant_id', $tenant->id)->get()->keyBy('name');

        $productsData = [
            ['name' => 'Paracetamol 500mg', 'sku' => 'PARA-500', 'category' => 'Obat Bebas', 'unit' => 'Tablet', 'price' => 500000, 'min_stock' => 20, 'prescription' => false],
            ['name' => 'Amoxicillin 500mg', 'sku' => 'AMOX-500', 'category' => 'Obat Keras', 'unit' => 'Kapsul', 'price' => 1500000, 'min_stock' => 10, 'prescription' => true],
            ['name' => 'Ibuprofen 400mg', 'sku' => 'IBUP-400', 'category' => 'Obat Bebas', 'unit' => 'Tablet', 'price' => 600000, 'min_stock' => 15, 'prescription' => false],
            ['name' => 'Cetirizine 10mg', 'sku' => 'CETI-10', 'category' => 'Obat Bebas', 'unit' => 'Tablet', 'price' => 450000, 'min_stock' => 20, 'prescription' => false],
            ['name' => 'Omeprazole 20mg', 'sku' => 'OMEP-20', 'category' => 'Obat Bebas', 'unit' => 'Kapsul', 'price' => 800000, 'min_stock' => 15, 'prescription' => false],
            ['name' => 'Vitamin C 1000mg', 'sku' => 'VITC-1000', 'category' => 'Vitamin & Suplemen', 'unit' => 'Tablet', 'price' => 350000, 'min_stock' => 30, 'prescription' => false],
            ['name' => 'Metformin 500mg', 'sku' => 'METF-500', 'category' => 'Obat Keras', 'unit' => 'Tablet', 'price' => 1200000, 'min_stock' => 10, 'prescription' => true],
            ['name' => 'Amlodipine 5mg', 'sku' => 'AMLO-5', 'category' => 'Obat Keras', 'unit' => 'Tablet', 'price' => 900000, 'min_stock' => 10, 'prescription' => true],
            ['name' => 'Ranitidine 150mg', 'sku' => 'RANI-150', 'category' => 'Obat Bebas', 'unit' => 'Tablet', 'price' => 550000, 'min_stock' => 15, 'prescription' => false],
            ['name' => 'Dexamethasone 0.5mg', 'sku' => 'DEXA-05', 'category' => 'Obat Keras', 'unit' => 'Tablet', 'price' => 400000, 'min_stock' => 5, 'prescription' => true],
        ];

        foreach ($productsData as $i => $p) {
            $unit = $units[$p['unit']] ?? null;
            $baseUnit = $unit ? $unit->abbreviation : 'pcs';

            $product = Product::create([
                'tenant_id' => $tenant->id,
                'category_id' => $categories[$p['category']] ?? null,
                'unit_id' => $unit?->id,
                'base_unit' => $baseUnit,
                'sku' => $p['sku'],
                'name' => $p['name'],
                'selling_price' => $p['price'],
                'min_stock' => $p['min_stock'],
                'requires_prescription' => $p['prescription'],
                'is_active' => true,
            ]);

            $batchCount = rand(1, 3);
            $oneNearExpiry = ($i === 0);
            for ($b = 0; $b < $batchCount; $b++) {
                $qty = [50, 100, 150, 200][array_rand([50, 100, 150, 200])];
                $expiredAt = $oneNearExpiry && $b === 0
                    ? now()->addDays(rand(15, 45))
                    : now()->addMonths(rand(3, 24))->addDays(rand(0, 30));
                $receivedAt = now()->subMonths(rand(1, 6));
                $batch = Batch::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'batch_number' => 'BATCH-'.strtoupper(substr(md5((string) ($tenant->id.'-'.$product->id.'-'.$b)), 0, 6)),
                    'purchase_price' => (int) ($p['price'] * 0.6),
                    'quantity_received' => $qty,
                    'quantity_remaining' => $qty,
                    'expired_at' => $expiredAt,
                    'received_at' => $receivedAt,
                    'is_active' => true,
                ]);

                $createdBy = $tenant->users()->first()?->id ?? 1;
                StockMovement::create([
                    'tenant_id' => $tenant->id,
                    'batch_id' => $batch->id,
                    'product_id' => $product->id,
                    'type' => StockMovementType::In,
                    'quantity' => $qty,
                    'notes' => 'Stok awal / restock',
                    'created_by' => $createdBy,
                ]);
            }
        }
    }

    private function seedSuppliers(Tenant $tenant): void
    {
        $names = ['PT Kimia Farma', 'PT Kalbe Farma', 'PT Dexa Medica', 'PT Tempo Scan', 'PT Darya Varia'];
        foreach ($names as $name) {
            Supplier::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'contact_person' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->companyEmail(),
                'address' => fake()->address(),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Simulasi PO: buat beberapa PO, sebagian diterima (batch + stock movement), sebagian belum.
     */
    private function seedPurchaseOrders(Tenant $tenant): void
    {
        $suppliers = Supplier::where('tenant_id', $tenant->id)->get();
        $products = Product::where('tenant_id', $tenant->id)->with('unit')->get();
        $creator = $tenant->users()->whereIn('role', ['owner', 'pharmacist'])->first() ?? $tenant->users()->first();
        if (! $creator || $suppliers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $numPo = rand(3, 5);
        $receivedCount = (int) round($numPo * 0.7);

        for ($p = 0; $p < $numPo; $p++) {
            $supplier = $suppliers->random();
            $orderedAt = now()->subDays(rand(5, 60));
            $poProducts = $products->random(min(4, $products->count()));
            $totalAmount = 0;
            $poItemsData = [];

            foreach ($poProducts as $product) {
                $qty = rand(10, 50);
                $unitPrice = (int) ($product->selling_price * 0.5);
                $subtotal = $qty * $unitPrice;
                $totalAmount += $subtotal;
                $orderUnitName = $product->unit?->abbreviation ?? $product->base_unit ?? 'pcs';
                $poItemsData[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'order_unit_name' => $orderUnitName,
                    'conversion_factor' => 1,
                ];
            }

            $po = PurchaseOrder::create([
                'tenant_id' => $tenant->id,
                'supplier_id' => $supplier->id,
                'invoice_number' => 'PO-DEMO-'.str_pad((string) ($p + 1), 4, '0', STR_PAD_LEFT),
                'total_amount' => $totalAmount,
                'notes' => 'Demo PO untuk testing',
                'ordered_at' => $orderedAt,
                'created_by' => $creator->id,
            ]);

            foreach ($poItemsData as $row) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $row['product']->id,
                    'order_unit_name' => $row['order_unit_name'],
                    'conversion_factor' => $row['conversion_factor'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                ]);
            }

            $shouldReceive = $p < $receivedCount;
            if ($shouldReceive) {
                $receivedAt = $orderedAt->copy()->addDays(rand(1, 5));
                $po->load('items');
                foreach ($po->items as $item) {
                    $item->load('product');
                    $quantityBase = $item->quantity * $item->conversion_factor;
                    $purchasePricePerBase = (int) round($item->unit_price / max(1, $item->conversion_factor));
                    $batch = Batch::create([
                        'tenant_id' => $tenant->id,
                        'product_id' => $item->product_id,
                        'batch_number' => 'PO-'.strtoupper(substr(uniqid(), -6)),
                        'purchase_price' => $purchasePricePerBase,
                        'quantity_received' => $quantityBase,
                        'quantity_remaining' => $quantityBase,
                        'expired_at' => $receivedAt->copy()->addMonths(12),
                        'received_at' => $receivedAt,
                        'is_active' => true,
                    ]);
                    $item->update(['batch_id' => $batch->id]);
                    StockMovement::create([
                        'tenant_id' => $tenant->id,
                        'batch_id' => $batch->id,
                        'product_id' => $item->product_id,
                        'type' => StockMovementType::In,
                        'quantity' => $quantityBase,
                        'reference_type' => PurchaseOrder::class,
                        'reference_id' => $po->id,
                        'notes' => 'Penerimaan PO '.$po->invoice_number,
                        'created_by' => $creator->id,
                    ]);
                }
                $po->update(['received_at' => $receivedAt]);
            }
        }
    }

    private function seedTransactions(Tenant $tenant): void
    {
        $cashiers = $tenant->users()->whereIn('role', ['owner', 'pharmacist', 'cashier'])->get();
        if ($cashiers->isEmpty()) {
            return;
        }

        $buyerSamples = [
            ['name' => 'Budi Santoso', 'phone' => '08123456789'],
            ['name' => 'Siti Rahayu', 'phone' => '08234567890'],
            ['name' => 'Ahmad Wijaya', 'phone' => null],
            ['name' => 'Dewi Lestari', 'phone' => '08567890123'],
            ['name' => 'Eko Prasetyo', 'phone' => '081122334455'],
            ['name' => 'Fitri Handayani', 'phone' => null],
        ];

        for ($t = 0; $t < 25; $t++) {
            $products = Product::where('tenant_id', $tenant->id)
                ->with(['batches' => fn ($q) => $q->where('quantity_remaining', '>', 0)->where('expired_at', '>', now())])
                ->with('unit')
                ->get()
                ->filter(fn ($p) => $p->batches->sum('quantity_remaining') > 0);

            if ($products->isEmpty()) {
                break;
            }

            $productIds = $products->pluck('id')->all();
            $productMap = $products->keyBy('id');

            $cashier = $cashiers->random();
            $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23));

            $subtotal = 0;
            $items = [];
            $numItems = rand(1, 5);
            $usedProducts = [];

            for ($i = 0; $i < $numItems; $i++) {
                $productId = $productIds[array_rand($productIds)];
                $product = $productMap[$productId];
                $available = $product->batches->sum('quantity_remaining');
                if ($available < 1 || in_array($productId, $usedProducts)) {
                    continue;
                }
                $usedProducts[] = $productId;
                $qty = min(rand(1, 5), (int) $available);
                $unitPrice = $product->selling_price;
                $itemSubtotal = $unitPrice * $qty;
                $subtotal += $itemSubtotal;
                $unitName = $product->unit?->abbreviation ?? $product->base_unit ?? 'pcs';
                $items[] = [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                    'unit_name' => $unitName,
                    'conversion_factor' => 1,
                ];
            }

            if (empty($items)) {
                continue;
            }

            $discountAmount = rand(0, 1) ? rand(0, min(500000, (int) ($subtotal * 0.05))) : 0;
            $totalAmount = $subtotal - $discountAmount;
            $paymentMethod = [PaymentMethod::Cash, PaymentMethod::Transfer, PaymentMethod::Qris][array_rand([PaymentMethod::Cash, PaymentMethod::Transfer, PaymentMethod::Qris])];
            $amountPaid = $totalAmount + (rand(0, 1) ? rand(0, 100000) : 0);
            $changeAmount = max(0, $amountPaid - $totalAmount);

            $withBuyer = rand(1, 100) <= 40;
            $buyer = $withBuyer ? $buyerSamples[array_rand($buyerSamples)] : ['name' => null, 'phone' => null];

            $transaction = Transaction::create([
                'tenant_id' => $tenant->id,
                'user_id' => $cashier->id,
                'invoice_number' => 'INV-DEMO-'.str_pad((string) ($t + 1), 4, '0', STR_PAD_LEFT),
                'type' => TransactionType::Sale,
                'status' => TransactionStatus::Completed,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'buyer_name' => $buyer['name'],
                'buyer_phone' => $buyer['phone'],
                'completed_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($items as $item) {
                $transactionItem = TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'unit_name' => $item['unit_name'],
                    'conversion_factor' => $item['conversion_factor'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => 0,
                    'subtotal' => $item['subtotal'],
                ]);

                $this->stockService->deductFEFO(
                    $item['product_id'],
                    $item['quantity'],
                    $transactionItem->id,
                    $cashier->id,
                );
            }
        }
    }
}
