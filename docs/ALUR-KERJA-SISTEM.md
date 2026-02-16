# Dokumentasi Alur Kerja Sistem PharmaOS

Dokumen ini merangkum semua alur kerja yang ada di sistem untuk keperluan analisa.

---

## 1. Ringkasan Sistem

- **Nama:** PharmaOS  
- **Tipe:** SaaS aplikasi apotek (multi-tenant)  
- **Stack:** Laravel 12, Livewire 4, Flux UI Pro, MySQL, Tailwind CSS v4  
- **Penyediaan:** Laravel Herd (`https://pharma-o-s.test` atau sesuai nama folder)

**Konsep utama:**
- Satu database, isolasi data per **tenant** via `tenant_id` dan global scope.
- Dua jenis pengguna: **Super Admin** (tenant_id null) dan **pengguna tenant** (Owner, Apoteker, Kasir).
- Harga dan amount di database disimpan dalam **sen** (Rp 10.000 = 1.000.000). Di form PO, input/output dalam **Rupiah**, konversi ke sen hanya saat simpan.

---

## 2. Autentikasi & Otorisasi

### 2.1 Role (User)

| Role         | Kode          | Deskripsi              | Akses khas                          |
|-------------|---------------|------------------------|-------------------------------------|
| Super Admin | `super_admin` | Pengelola platform     | Menu Admin, kelola tenant & langganan |
| Owner       | `owner`       | Pemilik apotek         | Laporan (Penjualan, Stok, Kadaluarsa) |
| Apoteker    | `pharmacist`  | Staf apotek            | Inventaris, POS, Supplier, PO       |
| Kasir       | `cashier`     | Kasir                  | POS, riwayat transaksi              |

- **Super Admin:** `tenant_id = null`, hanya bisa akses route dengan middleware `super.admin`.
- **Tenant user:** `tenant_id` terisi, akses route dengan `tenant.user`; menu Laporan hanya untuk **Owner** (`owner` middleware).

### 2.2 Middleware

| Middleware              | Fungsi |
|-------------------------|--------|
| `auth`, `verified`      | Harus login & email terverifikasi |
| `SetTenantContext`      | Set konteks tenant dari `user->tenant_id` (untuk query scope) |
| `tenant.user`           | Hanya pengguna tenant; tolak Super Admin |
| `super.admin`           | Hanya Super Admin; tolak tenant user |
| `owner`                 | Hanya role Owner (untuk laporan) |
| `subscription.active`   | Cek langganan aktif/trial; redirect ke `subscription.expired` jika tidak valid |
| `check.feature:{name}`  | Cek fitur paket (mis. `supplier_management`) untuk Supplier & PO |

### 2.3 Urutan request (umum)

1. `auth` → `verified` → `SetTenantContext` (web)
2. Lalu salah satu: `tenant.user` + `subscription.active` (+ `owner` atau `check.feature:...`) **atau** `super.admin`

---

## 3. Alur Multi-Tenant & Langganan

### 3.1 Konteks tenant

- Setiap request (setelah login), jika user punya `tenant_id`, **TenantContext** di-set dari model `Tenant`.
- Model dengan trait **BelongsToTenant**:
  - Pakai **TenantScope** (query otomatis filter `tenant_id`).
  - Saat **creating**, `tenant_id` diisi dari TenantContext bila kosong.
- Model terkait: Product, Category, Unit, Batch, StockMovement, Transaction, Supplier, PurchaseOrder, dll.

### 3.2 Langganan (Subscription)

- Satu tenant punya **activeSubscription** (status Active atau Trial, terbaru).
- **EnsureSubscriptionActive:** cek subscription ada, `isUsable()`, dan trial tidak kedaluwarsa; kalau tidak → redirect `subscription.expired`.
- **PlanLimitService** dipakai untuk:
  - Batas produk, user, transaksi/bulan sesuai paket (Basic / Pro / Enterprise).
  - Fitur per paket: `supplier_management` (Pro, Enterprise), `reports_full`, `reports_export`, `white_label`, `multi_unit_conversion`.

### 3.3 Menu berdasarkan role

- **Semua (auth):** Dashboard, Settings (profile, password, appearance, branding, 2FA).
- **Hanya tenant user:** Kasir/POS, Inventaris (Produk/Obat, Supplier, Purchase Order jika punya fitur).
- **Hanya Owner:** Laporan (Penjualan, Stok, Kadaluarsa).
- **Hanya Super Admin:** Admin → Kelola Tenant, System Dashboard.

---

## 4. Alur Inventaris

### 4.1 Produk (Products)

- **Route:** `inventory/products`, `inventory/products/create`, `inventory/products/{id}/edit`
- **Middleware:** `auth`, `verified`, `tenant.user`, `subscription.active`
- **Komponen:** ProductIndex, ProductForm
- **Alur:** CRUD produk per tenant (nama, kategori, unit, harga jual, stok minimum, aktif/non-aktif). Batas jumlah produk menurut **PlanLimitService** (paket).

### 4.2 Batch (stok per batch)

- **Route:** `inventory/products/{productId}/batches`, `.../batches/create`, `.../batches/{batchId}/edit`
- **Komponen:** BatchIndex, BatchForm
- **Alur:**
  - **Tambah batch (restock manual):** Form isi batch_number, purchase_price (sen), quantity_received, expired_at, received_at → create **Batch** + **StockMovement** type **In** (notes: "Batch baru: ..."). `quantity_remaining = quantity_received`.
  - **Edit batch:** Update batch_number, purchase_price, expired_at, received_at (tidak mengubah quantity atau membuat StockMovement baru).
- Stok tersedia = jumlah `quantity_remaining` dari batch aktif, belum kedaluwarsa (`expired_at > now()`).

### 4.3 Supplier

- **Route:** `suppliers`, `suppliers/create`, `suppliers/{id}/edit`
- **Middleware:** + `check.feature:supplier_management`
- **Komponen:** SupplierIndex, SupplierForm
- **Alur:** CRUD supplier per tenant. Hanya muncul jika paket punya fitur `supplier_management`.

### 4.4 Purchase Order (PO)

- **Route:** `purchase-orders`, `purchase-orders/create`, `purchase-orders/{id}`, `purchase-orders/{id}/receive`
- **Middleware:** sama seperti Supplier (+ `check.feature:supplier_management`)
- **Komponen:** PurchaseOrderIndex, PurchaseOrderForm, PurchaseOrderDetail, PurchaseOrderReceive

**Alur buat PO:**
1. Pilih supplier, tanggal order, tambah baris item (pilih produk, qty, harga beli).
2. **Harga beli di form dalam Rupiah;** saat pilih produk, default dari harga beli terakhir (batch) atau 60% harga jual, dikonversi dari sen ke Rupiah.
3. Subtotal & total di form dalam Rupiah. Saat **simpan:** konversi ke sen → simpan `PurchaseOrder` (total_amount sen) dan `PurchaseOrderItem` (unit_price, subtotal dalam sen).

**Alur terima PO (receive):**
1. Halaman receive: isi batch_number & expired_at per item, tanggal received_at.
2. **Simpan:** untuk tiap item PO → create **Batch** (tenant_id, product_id, batch_number, purchase_price dari item, quantity_received/remaining, expired_at, received_at) → create **StockMovement** type **In** (reference PO) → update PO `received_at`.
3. Tidak ada pemotongan stok di sini; hanya penambahan stok lewat batch baru.

**Hapus/batalkan PO:** Hanya untuk PO yang belum diterima (`received_at === null`).

---

## 5. Alur POS (Kasir)

- **Route:** `pos/cashier`, `pos/transactions`
- **Middleware:** `auth`, `verified`, `tenant.user`, `subscription.active`
- **Komponen:** Cashier, TransactionHistory

### 5.1 Proses penjualan (ProcessSale)

1. Kasir pilih produk, qty, diskon (per item / global), lalu bayar (modal pembayaran).
2. **ProcessSale::execute:**  
   - Cek **PlanLimitService::canCreateTransaction()** (batas transaksi/bulan).  
   - Dalam DB transaction:  
     - Buat **Transaction** (type Sale, status Completed, subtotal, discount, total_amount, payment_method, amount_paid, change_amount, completed_at).  
     - Untuk tiap item: buat **TransactionItem**, lalu panggil **StockService::deductFEFO**.
3. **FEFO (First Expired First Out):**  
   - Ambil batch produk yang: `quantity_remaining > 0`, `expired_at > today`, `is_active = true`, urut **expired_at** ascending.  
   - Kurangi `quantity_remaining` batch (bisa beberapa batch sampai qty terpenuhi), buat **BatchDeduction** (transaction_item_id, batch_id, quantity_deducted), buat **StockMovement** type **Out** (reference TransactionItem).  
   - Jika stok tidak cukup → **InsufficientStockException**.

### 5.2 Void transaksi

- **VoidTransaction::execute:** Hanya untuk status Completed. Ambil semua BatchDeduction dari item transaksi → **StockService::restoreFromDeductions** (increment `quantity_remaining` batch, buat StockMovement type **Return**) → update status transaksi jadi **Voided**.

### 5.3 Harga di POS

- Harga di kasir dan di **Transaction** / **TransactionItem** disimpan dalam **sen** (konsisten dengan Product selling_price).

---

## 6. Alur Stok (Ringkasan)

- **Masuk:**  
  - **Manual:** Tambah Batch (BatchForm) → Batch + StockMovement In.  
  - **PO receive:** Satu Batch per item PO + StockMovement In (reference PO).
- **Keluar:**  
  - **POS:** ProcessSale → deductFEFO → BatchDeduction + StockMovement Out (reference TransactionItem).  
  - **Void:** restoreFromDeductions → StockMovement Return.
- **FEFO:** Selalu pakai batch dengan **expired_at** terdekat dulu; stok dihitung dari `quantity_remaining` batch aktif & belum expired.

---

## 7. Alur Laporan

- **Route:** `reports/sales`, `reports/stock`, `reports/expiry`
- **Middleware:** `auth`, `verified`, `tenant.user`, **owner**, `subscription.active`
- **Komponen:** SalesReport, StockReport, ExpiryReport
- **Akses:** Hanya **Owner**. Data di-scope tenant (TenantScope).

---

## 8. Alur Super Admin

- **Route:** `admin/dashboard`, `admin/tenants`, `admin/tenants/create`, `admin/tenants/{id}/edit`, `admin/tenants/{id}/subscription`
- **Middleware:** `auth`, `verified`, `super.admin`
- **Komponen:** SystemDashboard, TenantIndex, TenantForm, SubscriptionManager
- **Alur:** CRUD tenant (nama, slug, kontak, alamat, logo, warna, license, is_active, settings). Kelola langganan per tenant (plan, status, batas produk/user/transaksi, trial, periode). Super Admin tidak kena TenantScope untuk data tenant (bisa lihat semua tenant).

---

## 9. Dashboard Tenant

- **Route:** `dashboard`
- **Komponen:** Dashboard\Overview
- **Data:** Hanya jika konteks tenant ada: revenue hari ini & bulan ini, jumlah transaksi hari ini, total produk aktif, produk stok rendah (min_stock), batch mendekati kadaluarsa (90 hari), transaksi terakhir. Semua query memakai scope tenant.

---

## 10. Daftar Route & Menu (Ringkasan)

| Area        | Route / Nama              | Middleware / Role          |
|------------|---------------------------|----------------------------|
| Umum       | `/` (welcome)             | -                          |
| Auth       | `/dashboard`              | auth, verified             |
|            | `/subscription/expired`    | auth                       |
| Settings   | `/settings/*`             | auth (verified untuk password, 2FA, dll.) |
| Inventaris | `/inventory/products*`    | auth, verified, tenant.user, subscription.active |
|            | `/inventory/products/{id}/batches*` | sama                       |
|            | `/suppliers*`             | + check.feature:supplier_management |
|            | `/purchase-orders*`      | + check.feature:supplier_management |
| POS        | `/pos/cashier`, `/pos/transactions` | tenant.user, subscription.active |
| Laporan    | `/reports/sales|stock|expiry` | tenant.user, **owner**, subscription.active |
| Admin      | `/admin/*`                | **super.admin**            |

---

## 11. File Penting (Referensi)

- **Middleware:** `app/Http/Middleware/` (SetTenantContext, EnsureTenantUser, EnsureOwner, EnsureSuperAdmin, EnsureSubscriptionActive, CheckFeatureLimit).
- **Tenant & scope:** `app/Concerns/BelongsToTenant.php`, `app/Models/Scopes/TenantScope.php`, `app/Services/TenantContext.php`.
- **Langganan & batas:** `app/Services/PlanLimitService.php`, `app/Models/Subscription.php`, `app/Enums/SubscriptionPlan.php`.
- **Stok:** `app/Services/StockService.php` (deductFEFO, restoreFromDeductions, addStock), `app/Models/Batch.php`, `app/Models/StockMovement.php`, `app/Models/BatchDeduction.php`.
- **POS:** `app/Actions/POS/ProcessSale.php`, `app/Actions/POS/VoidTransaction.php`, `app/Livewire/POS/Cashier.php`.
- **PO:** `app/Livewire/PurchaseOrder/PurchaseOrderForm.php` (form Rupiah → simpan sen), `app/Livewire/PurchaseOrder/PurchaseOrderReceive.php` (batch + StockMovement In).
- **Inventaris batch manual:** `app/Livewire/Inventory/BatchForm.php`.
- **Sidebar (menu):** `resources/views/layouts/app/sidebar.blade.php`.
- **Route:** `routes/web.php` + `routes/inventory.php`, `pos.php`, `suppliers.php`, `purchase_orders.php`, `reports.php`, `super-admin.php`, `settings.php`.

---

Dokumentasi ini menggambarkan alur kerja yang **sudah ada** di sistem. Gunakan untuk analisa fitur, keamanan, dan pengembangan lanjutan.
