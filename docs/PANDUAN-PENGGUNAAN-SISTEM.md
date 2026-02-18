# Panduan Penggunaan Sistem PharmaOS

Dokumen ini menjelaskan tata cara penggunaan fitur-fitur utama sistem untuk pengguna apotek (Owner, Apoteker, Kasir).

---

## Daftar Isi

1. [Tata Cara Manajemen User oleh Owner](#1-tata-cara-manajemen-user-oleh-owner)
2. [Pengaturan Branding / White Label](#2-pengaturan-branding--white-label)
3. [Menambah Daftar Obat (Produk)](#3-menambah-daftar-obat-produk)
4. [Menambah Supplier](#4-menambah-supplier)
5. [Menambah Stok / Batch dengan Purchase Order](#5-menambah-stok--batch-dengan-purchase-order)
6. [Menambah Stok / Batch dari Inventaris (Produk)](#6-menambah-stok--batch-dari-inventaris-produk)
7. [Menggunakan Fitur POS / Kasir](#7-menggunakan-fitur-pos--kasir)

---

## 1. Tata Cara Manajemen User oleh Owner

**Akses:** Hanya **Owner** yang dapat mengelola user apotek.  
**Menu:** Sidebar → **Pengaturan Apotek** → **Manajemen User**  
**URL:** `/settings/users`

### Yang bisa dilakukan

- **Melihat daftar user** apotek (nama, email, role, status aktif/nonaktif).
- **Mencari user** dengan nama atau email (kolom pencarian di atas tabel).
- **Menambah user baru** (Apoteker atau Kasir). Owner tidak bisa ditambah dari sini; akun Owner dibuat saat tenant didaftarkan oleh Super Admin.
- **Mengubah** nama, email, role, dan status aktif/nonaktif user.
- **Mengaktifkan / menonaktifkan** user (kecuali akun sendiri).

### Langkah menambah user baru

1. Buka **Pengaturan Apotek** → **Manajemen User**.
2. Klik tombol **Tambah User**.
3. Isi form:
   - **Nama** (wajib)
   - **Email** (wajib, harus unik di sistem)
   - **Kata sandi** (wajib, minimal sesuai kebijakan password)
   - **Role:** pilih **Apoteker** atau **Kasir**
4. Klik **Simpan**.

**Catatan:** Jumlah user dibatasi sesuai paket langganan. Jika batas tercapai, tombol **Tambah User** tidak tersedia dan akan muncul pesan bahwa batas user paket telah tercapai.

### Langkah mengubah user

1. Di tabel user, klik tombol **Ubah** (ikon pensil) pada baris user yang ingin diubah.
2. Ubah **Nama**, **Email**, **Role** (Owner / Apoteker / Kasir), atau **Status** (Aktif/Nonaktif). Isi **Kata sandi** hanya jika ingin mengubah password; kosongkan jika tidak mengubah.
3. Klik **Simpan**.

Anda tidak dapat mengubah role atau menonaktifkan **akun Anda sendiri**.

### Langkah mengaktifkan / menonaktifkan user

- Klik **Nonaktifkan** untuk menonaktifkan user (user tidak bisa login).
- Klik **Aktifkan** untuk mengaktifkan kembali.
- Konfirmasi akan muncul sebelum aksi dilakukan. Akun sendiri tidak bisa dinonaktifkan dari sini.

---

## 2. Pengaturan Branding / White Label

**Akses:** Hanya **Owner**.  
**Menu:** Sidebar → **Pengaturan Apotek** → **White Label**  
**URL:** `/settings/white-label`

Fitur ini mengatur tampilan aplikasi dan struk penjualan sesuai identitas apotek Anda.

### Yang bisa diatur

1. **Nama Apotek**  
   Nama tampilan yang muncul di sidebar, logo area, dan identitas aplikasi.

2. **Informasi di struk**  
   - **Alamat** apotek  
   - **No. HP / Telepon**  
   - **Website** (opsional, format URL)

3. **Logo**  
   - Upload logo (JPG, PNG, SVG; maks. 2MB). Logo tampil di sidebar dan struk.

4. **Warna**  
   - **Warna Utama** dan **Warna Sekunder** (format heks, misal `#0d9488`).  
   Digunakan untuk aksen tombol, struk, dan elemen branding.

### Langkah menyimpan

1. Isi atau ubah field yang diinginkan.
2. Klik **Simpan** di bagian bawah halaman.

Perubahan langsung berlaku di aplikasi dan struk cetak.

---

## 3. Menambah Daftar Obat (Produk)

**Akses:** Pengguna tenant dengan akses **Inventaris** (Owner, Apoteker).  
**Menu:** Sidebar → **Inventaris** → **Produk / Obat**  
**URL:** `/inventory/products`

### Langkah menambah produk baru

1. Buka **Inventaris** → **Produk / Obat**.
2. Klik **Tambah Produk**.
3. Isi form:

   **Informasi dasar**
   - **SKU / Kode Produk** (wajib, unik)
   - **Barcode** (opsional)
   - **Nama Produk** (wajib)
   - **Nama Generik** (opsional)
   - **Kategori** (pilih dari daftar atau tanpa kategori)
   - **Satuan Dasar** (Tablet, Kapsul, Strip, dll.)
   - **Base Unit** (satuan terkecil untuk stok, misal: Butir, pcs)

   **Harga & stok**
   - **Harga Jual (Rp)** (wajib)
   - **Stok Minimum** (peringatan jika stok di bawah nilai ini)
   - **Butuh Resep** (centang jika obat keras)

   **Satuan bertingkat (opsional)**  
   Jika produk punya satuan jual lain (misal 1 Strip = 10 Butir), tambah baris: Nama Satuan, Conversion Factor, Harga Jual (Rp).

4. Klik **Simpan**.

**Catatan:** Jumlah produk dibatasi sesuai paket langganan. Jika batas tercapai, sistem akan menampilkan pesan error.

### Mengubah atau melihat stok produk

- **Ubah:** Klik ikon pensil pada baris produk di daftar.
- **Lihat / kelola batch:** Klik ikon mata pada baris produk untuk masuk ke daftar batch produk tersebut (lihat juga bagian 6).

---

## 4. Menambah Supplier

**Akses:** Pengguna tenant dengan akses Inventaris **dan** paket yang menyertakan fitur Supplier (Pro/Enterprise).  
**Menu:** Sidebar → **Inventaris** → **Supplier**  
**URL:** `/suppliers`

Supplier digunakan untuk membuat Purchase Order (PO). Jika menu Supplier tidak muncul, paket Anda belum menyertakan fitur ini.

### Langkah menambah supplier

1. Buka **Inventaris** → **Supplier**.
2. Klik **Tambah Supplier**.
3. Isi **Nama**, **Contact Person**, **Telepon**, **Email**, **Alamat**. Sesuaikan dengan field yang wajib di form.
4. Klik **Simpan**.

### Mengubah supplier

- Di daftar supplier, klik tombol **Ubah** (ikon pensil) pada baris supplier yang ingin diedit, lalu simpan perubahan.

---

## 5. Menambah Stok / Batch dengan Purchase Order

**Akses:** Sama seperti Supplier (fitur **Supplier Management**).  
**Menu:** Sidebar → **Inventaris** → **Purchase Order**  
**URL:** `/purchase-orders`

PO dipakai untuk mencatat pembelian dari supplier. Setelah PO **diterima**, stok/batch akan bertambah di sistem.

### Langkah membuat PO

1. Buka **Inventaris** → **Purchase Order**.
2. Klik **Buat PO** (atau **Tambah Purchase Order**).
3. Isi header:
   - **Supplier** (pilih dari daftar)
   - **Tanggal Order**
   - **Catatan** (opsional)
4. Tambah **item**:
   - Pilih **Produk**
   - Isi **Jumlah** dan **Harga Beli (Rp)** per satuan. Harga bisa terisi otomatis dari harga beli terakhir atau default sistem.
5. Pastikan total sesuai, lalu klik **Simpan**.

Setelah disimpan, Anda akan diarahkan ke halaman **Terima Barang** untuk PO tersebut.

### Langkah menerima PO (menambah stok dari PO)

1. Dari daftar PO, klik **Terima** pada PO yang belum diterima, atau dari detail PO klik **Terima Barang**.
2. Isi **Tanggal Diterima**.
3. Untuk **setiap item** PO, isi:
   - **Nomor Batch**
   - **Tanggal Kadaluarsa**
4. Klik **Simpan Penerimaan**.

Sistem akan:
- Membuat **Batch** baru per item (dengan quantity sesuai item PO),
- Mencatat **pergerakan stok masuk** (reference PO),
- Menandai PO sebagai **sudah diterima**.

Stok produk di inventaris dan di POS akan bertambah sesuai batch yang baru dibuat.

---

## 6. Menambah Stok / Batch dari Inventaris (Produk)

**Akses:** Pengguna dengan akses Inventaris.  
**Menu:** Dari daftar **Produk / Obat** → pilih produk → **Stok / Batch**  
**URL:** `/inventory/products/{productId}/batches`

Cara ini untuk menambah stok **tanpa** PO (misal restock manual, stok awal, atau penerimaan di luar alur PO).

### Langkah menambah batch baru

1. Buka **Inventaris** → **Produk / Obat**.
2. Klik ikon **mata** pada produk yang ingin ditambah stoknya (buka halaman batch produk).
3. Klik **Tambah Batch**.
4. Isi form:
   - **Nomor Batch** (wajib)
   - **Harga Beli per Satuan (Rp)**
   - **Jumlah Diterima**
   - **Tanggal Diterima**
   - **Tanggal Kadaluarsa**
5. Klik **Tambah Batch** (atau **Simpan**).

Sistem akan membuat batch baru dan mencatat pergerakan stok **Masuk**. **Jumlah Diterima** tidak dapat diubah setelah batch dibuat; untuk koreksi bisa lewat prosedur yang diatur kebijakan apotek.

### Mengubah data batch

- Di halaman daftar batch produk, klik **Ubah** pada baris batch. Anda bisa mengubah nomor batch, harga beli, tanggal diterima, dan tanggal kadaluarsa. **Jumlah** tidak dapat diubah.

---

## 7. Menggunakan Fitur POS / Kasir

**Akses:** Semua pengguna tenant (Owner, Apoteker, Kasir).  
**Menu:** Sidebar → **Kasir / POS**  
**URL:** `/pos/cashier`

POS dipakai untuk mencatat penjualan dan mencetak struk. Pengurangan stok mengikuti **FEFO** (First Expired, First Out): sistem secara otomatis mengurangi dari batch yang kadaluarsa paling dulu.

### Alur penjualan

1. **Cari produk**  
   Di kolom pencarian, ketik nama, SKU, atau barcode produk. Pilih produk dari hasil pencarian.

2. **Pilih satuan dan jumlah (jika ada)**  
   Jika produk punya beberapa satuan jual (misal Butir dan Strip), pilih satuan dan jumlah yang akan dijual, lalu tambah ke keranjang.

3. **Keranjang**  
   - Ubah **qty** atau **diskon per item** jika perlu.  
   - **Diskon global** bisa diisi di panel Ringkasan (dalam Rupiah).  
   - **Catatan** dan **Pembeli (opsional):** nama dan no. telepon pembeli bisa diisi untuk dicetak di struk dan laporan.

4. **Bayar**  
   - Klik **Bayar (F8)** atau tombol **Bayar**.  
   - Di modal pembayaran:  
     - Isi **Pembeli** (opsional): nama dan no. telepon.  
     - Pilih **Metode Pembayaran** (Tunai, Transfer, QRIS, dll.).  
     - Isi **Jumlah Bayar (Rp)**. Jika lebih dari total, sistem menampilkan kembalian.  
   - Klik **Proses Pembayaran**.

5. **Selesai**  
   - Transaksi tersimpan, stok berkurang secara FEFO.  
   - Struk bisa dicetak (browser print).  
   - Keranjang kosong untuk transaksi berikutnya.

### Shortcut

- **F2:** Fokus ke kolom pencarian  
- **F8:** Buka modal pembayaran  
- **Esc:** Tutup modal

### Riwayat transaksi

- Menu **Kasir / POS** → **Riwayat Transaksi** (atau dari sidebar/pos/transactions) untuk melihat daftar transaksi dan opsi void (batalkan) jika kebijakan mengizinkan.

### Catatan penting

- **Stok:** Jika stok tidak cukup untuk qty yang diminta, sistem akan menolak dan menampilkan pesan error.  
- **Struk:** Logo, nama apotek, alamat, telepon, dan website di struk mengikuti **Pengaturan White Label**.  
- **Pembeli:** Nama dan no. telepon pembeli (jika diisi) muncul di struk dan di **Laporan Penjualan** (untuk Owner).

---

## Ringkasan Akses per Role

| Fitur | Owner | Apoteker | Kasir |
|-------|--------|----------|--------|
| Manajemen User | ✅ | ❌ | ❌ |
| White Label | ✅ | ❌ | ❌ |
| Daftar Obat (CRUD) | ✅ | ✅ | ❌ |
| Supplier & PO | ✅* | ✅* | ❌ |
| Batch (tambah/edit dari inventaris) | ✅ | ✅ | ❌ |
| POS / Kasir | ✅ | ✅ | ✅ |
| Laporan (Penjualan, Stok, Kadaluarsa) | ✅ | ❌ | ❌ |

\* Jika paket langganan menyertakan fitur Supplier Management (Pro/Enterprise).

---

*Dokumentasi ini mengacu pada struktur dan alur yang ada di sistem. Untuk detail teknis alur kerja, lihat `docs/ALUR-KERJA-SISTEM.md`.*
