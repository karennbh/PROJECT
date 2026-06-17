# Alur Step-by-Step Kode Aplikasi

Dokumen ini menjelaskan alur kode dengan bahasa yang lebih mudah. Fokusnya: ketika user/admin melakukan suatu aksi, file mana yang pertama berjalan, function apa yang dipanggil, data berpindah ke mana, lalu efek akhirnya apa.

Cara membaca:

- `File` = lokasi coding yang dipakai.
- `Function` = nama function/method yang berjalan.
- `Alur` = urutan proses dari awal sampai akhir.
- `Kalau mau ubah` = file yang perlu diedit jika ingin mengubah fitur itu.

## 1. Alur Login

### File yang terlibat

- `routes/web.php`
- `app/Http/Controllers/AuthController.php`
- `app/Filament/Auth/Login.php`
- `app/Filament/Auth/LogoutResponse.php`
- `resources/views/auth/login.blade.php`
- `resources/views/partials/tab-auth-login-script.blade.php`
- `resources/views/partials/tab-auth-guard.blade.php`
- `resources/views/partials/admin-session-timeout.blade.php`
- `resources/views/partials/anggota-session-timeout.blade.php`
- `app/Http/Middleware/AnggotaMiddleware.php`
- `app/Http/Middleware/EnforceSessionIdleTimeout.php`

### Alur saat membuka aplikasi

1. User membuka `/`.
2. Route `/` ada di `routes/web.php`.
3. Sistem cek `auth()->check()`.
4. Jika belum login, diarahkan ke `/login`.
5. Jika sudah login:
   - `user_group = admin` diarahkan ke `/admin`.
   - `user_group = anggota` diarahkan ke `/dashboard`.
   - role tidak dikenal akan logout dan balik ke `/login`.

### Alur saat login

1. User membuka `/login`.
2. Route memanggil `AuthController::showLoginForm()`.
3. Function ini mengecek apakah user sudah login.
4. Jika belum login, tampilkan view `auth.login`.
5. Di halaman login, script `tab-auth-login-script.blade.php` menangani submit form.
6. Saat tombol login ditekan, script mengirim request ke `AuthController::login()`.
7. `login()` memvalidasi:
   - username wajib.
   - password wajib dan minimal 6.
8. Sistem menjalankan `Auth::attempt($credentials, remember)`.
9. Jika berhasil:
   - session dibuat ulang dengan `$request->session()->regenerate()`.
   - `last_activity_at` disimpan.
   - sistem cek role.
   - admin ke `/admin`.
   - anggota ke `/dashboard`.
10. Jika gagal:
   - muncul pesan `Username atau password salah`.

### Alur multi tab / beberapa akun

1. Setelah login berhasil, `tab-auth-login-script.blade.php` menyimpan:
   - token tab di `sessionStorage`.
   - user id tab di `sessionStorage`.
2. Di halaman admin, `tab-auth-guard.blade.php` berjalan.
3. Script ini memanggil `/auth/session-user`.
4. Route `/auth/session-user` menjalankan `AuthController::sessionUser()`.
5. Function `sessionUser()` mengembalikan:
   - `authenticated`
   - `user_id`
   - `user_group`
6. Jika user id di tab tidak sama dengan session server, tab diarahkan login ulang.

### Alur timeout 45 menit

1. Nilai timeout ada di `.env`: `SESSION_LIFETIME=45`.
2. Admin panel membaca nilai ini di `AdminPanelProvider`.
3. Script `admin-session-timeout.blade.php` menghitung aktivitas terakhir.
4. Jika tidak ada aktivitas selama 45 menit, sistem logout.
5. Middleware `EnforceSessionIdleTimeout` juga menjaga timeout dari sisi server.

### Kalau mau ubah login

- Ubah tujuan redirect login: `AuthController::login()`.
- Ubah role yang boleh masuk admin: `User::canAccessPanel()`.
- Ubah timeout: `.env` bagian `SESSION_LIFETIME`.
- Ubah tampilan login: `resources/views/auth/login.blade.php`.
- Ubah proteksi multi tab: `tab-auth-guard.blade.php`.

## 2. Alur Master Data COA

### File yang terlibat

- `app/Models/coa.php`
- `app/Filament/Admin/Resources/COAS/COAResource.php`
- `app/Filament/Admin/Resources/COAS/Schemas/COAForm.php`
- `app/Filament/Admin/Resources/COAS/Tables/COASTable.php`
- `app/Filament/Admin/Resources/COAS/Pages/CreateCOA.php`
- `app/Filament/Admin/Resources/COAS/Pages/EditCOA.php`

### Alur saat admin membuat COA

1. Admin masuk menu COA.
2. Filament memakai `COAResource`.
3. `COAResource::form()` memanggil `COAForm::configure()`.
4. Admin memilih `Header Akun`.
5. Field `nama_akun` menampilkan pilihan sesuai header:
   - Harta.
   - Beban.
   - Pendapatan.
6. Jika nama akun sudah ada di database, option dibuat disable oleh `disableOptionWhen()`.
7. Setelah nama akun dipilih, `afterStateUpdated()` mengisi otomatis:
   - `kode_akun`.
   - `saldo`.
8. Saat disimpan, data masuk ke model `Coa`.
9. Event `Coa::created()` menjalankan `syncPendingTransactionJournals()`.
10. Jika ada transaksi yang sebelumnya belum punya jurnal karena COA belum dibuat, sistem mencoba membuat jurnalnya.

### Function penting

#### `COAForm::configure()`

Gunanya menyusun field form COA.

#### `disableOptionWhen()`

Gunanya agar akun yang sudah dibuat tidak bisa dipilih lagi.

#### `afterStateUpdated()`

Gunanya mengisi kode akun dan saldo normal otomatis.

#### `Coa::jurnalDetails()`

Gunanya menghubungkan COA ke detail jurnal berdasarkan `kode_akun`.

#### `Coa::syncPendingTransactionJournals()`

Gunanya menyinkronkan jurnal transaksi lama yang belum terbentuk.

### Kalau mau ubah COA

- Tambah nama akun baru: edit `COAForm.php`, bagian options `nama_akun`.
- Tambah kode akun baru: edit `$map` di `COAForm.php`.
- Ubah saldo normal akun tertentu: edit `$map` di `COAForm.php`.
- Ubah efek saat COA dibuat/dihapus: edit model `Coa`.

## 3. Alur Master Data Kategori Aset

### File yang terlibat

- `app/Models/KategoriAsetTetap.php`
- `app/Filament/Admin/Resources/KategoriAsets/KategoriAsetResource.php`
- `app/Filament/Admin/Resources/KategoriAsets/Schemas/KategoriAsetForm.php`
- `app/Filament/Admin/Resources/KategoriAsets/Tables/KategoriAsetsTable.php`

### Alur

1. Admin membuka menu Kategori Aset Tetap.
2. `KategoriAsetResource::form()` memanggil `KategoriAsetForm`.
3. Admin mengisi nama kategori, umur ekonomis, dan keterangan.
4. Model `KategoriAsetTetap` menyimpan data ke tabel kategori.
5. Kategori ini nanti dipakai di:
   - Form Barang Kantor.
   - Form Perolehan Barang detail aset.
   - Penyusutan untuk menentukan umur ekonomis.

### Function penting

#### `barangKantors()`

Relasi dari kategori aset ke barang kantor.

#### `barangMasukDetails()`

Relasi dari kategori aset ke detail perolehan.

### Kalau mau ubah kategori aset

- Ubah input form: `KategoriAsetForm.php`.
- Ubah tampilan tabel: `KategoriAsetsTable.php`.
- Ubah relasi: `KategoriAsetTetap.php`.

## 4. Alur Master Data Barang Kantor

### File yang terlibat

- `app/Models/BarangKantor.php`
- `app/Filament/Admin/Resources/BarangKantors/BarangKantorResource.php`
- `app/Filament/Admin/Resources/BarangKantors/Schemas/BarangKantorForm.php`
- `app/Filament/Admin/Resources/BarangKantors/Tables/BarangKantorsTable.php`
- `app/Filament/Admin/Resources/BarangKantors/Widgets/AsetTetapTable.php`
- `app/Filament/Admin/Resources/BarangKantors/Widgets/BhpTable.php`

### Alur saat barang dibuat manual

1. Admin membuka menu Barang Kantor.
2. Klik tambah.
3. `BarangKantorResource::form()` memanggil `BarangKantorForm::configure()`.
4. Admin memilih kategori barang:
   - Aset Tetap.
   - Barang Habis Pakai.
5. Jika Aset Tetap:
   - pilih jenis aset.
   - pilih kategori aset.
   - isi umur ekonomis.
   - isi nilai residu.
   - isi harga perolehan.
   - pilih status penggunaan.
6. Jika BHP:
   - pilih jenis BHP.
   - isi stok dan satuan.
7. Saat disimpan, model `BarangKantor::saving()` berjalan.
8. Jika barang BHP, field aset dikosongkan.
9. Jika barang aset, field BHP dikosongkan.
10. Jika data sudah valid, record disimpan.
11. Setelah record dibuat, `BarangKantor::created()` berjalan.
12. Sistem membuat barcode otomatis.
13. Jika barang adalah aset, sistem otomatis membuat data `PenyusutanAsetTetap`.

### Function penting di `BarangKantor`

#### `saving`

Tempat validasi sebelum data barang disimpan.

Contoh yang dilakukan:

- BHP tidak boleh menyimpan field aset.
- Aset tidak boleh menyimpan field BHP.
- Tanggal diterima tidak boleh sebelum tanggal pembelian perolehan.
- Aset Tidak Aktif tidak boleh diaktifkan lagi.
- Status pinjam default menjadi Tersedia.

#### `created`

Berjalan setelah barang berhasil dibuat.

Yang dilakukan:

- membuat barcode `BRG-000001`.
- membuat data penyusutan jika kategori barang aset.

#### `saved`

Berjalan setelah barang disimpan.

Yang dilakukan:

- sinkron status penyusutan.
- sinkron data penyusutan.

#### `isSiapPakai()`

Mengecek apakah aset siap disusutkan/dipinjam.

Syarat:

- `status_penggunaan = siap_digunakan`.
- `tanggal_diterima` tidak kosong.

#### `isAvailableToBorrow()`

Mengecek barang boleh dipinjam atau tidak.

Syarat:

- barang Aktif.
- stok cukup.
- aset harus siap digunakan dan tersedia.
- BHP yang boleh dipinjam adalah BPP Inventaris Kantor.

### Kalau mau ubah Barang Kantor

- Ubah field input: `BarangKantorForm.php`.
- Ubah tabel: `BarangKantorsTable.php`.
- Ubah validasi/status otomatis: `BarangKantor.php`.
- Ubah kode barcode: `BarangKantor::created()`.
- Ubah syarat peminjaman: `BarangKantor::isAvailableToBorrow()`.

## 5. Alur Scan Barcode / QR Barang

Bagian ini penting karena alurnya melibatkan model, route, halaman scan, JavaScript scanner, dan halaman detail hasil scan.

### File yang terlibat

- `app/Models/BarangKantor.php`
- `app/Filament/Admin/Resources/BarangKantors/Pages/ScanBarangKantor.php`
- `resources/views/filament/admin/resources/barang-kantors/pages/scan-barang-kantor.blade.php`
- `routes/web.php`
- `resources/views/scan/barang.blade.php`
- `resources/views/filament/colums/barcode.blade.php`
- `app/Filament/Admin/Resources/BarangKantors/Tables/BarangKantorsTable.php`
- `app/Filament/Admin/Resources/BarangKantors/Widgets/AsetTetapTable.php`
- `app/Filament/Admin/Resources/BarangKantors/Widgets/BhpTable.php`

### Bagian 1: Barcode dibuat dari mana?

Barcode dibuat otomatis di model `BarangKantor`.

File:

- `app/Models/BarangKantor.php`

Function/event:

- `created(function (self $barang) { ... })`

Alur:

1. Barang Kantor berhasil dibuat.
2. Event `created` berjalan.
3. Sistem cek apakah kolom `barcode` masih kosong.
4. Jika kosong, sistem isi:
   - `BRG-` + id barang.
   - contoh: `BRG-000001`.
5. Sistem menyimpan dengan `saveQuietly()` supaya tidak memicu event berulang.

### Bagian 2: QR image dibuat dari mana?

QR image dibuat oleh accessor di model `BarangKantor`.

Function:

- `getBarcodeTargetUrlAttribute()`
- `getBarcodeQrImageUrlAttribute()`

Alur:

1. Sistem ingin menampilkan QR barang.
2. Kode memanggil `$record->barcode_qr_image_url`.
3. Laravel menjalankan `getBarcodeQrImageUrlAttribute()`.
4. Function ini membuat URL gambar QR dari QuickChart:
   - `https://quickchart.io/qr?text=...`
5. Isi QR adalah URL detail barang, bukan hanya teks barcode.
6. URL detail dibuat oleh `getBarcodeTargetUrlAttribute()`.

Contoh:

- QR berisi URL `/barang-kantor/detail/ASET-00001`.
- Saat discan, browser membuka detail barang tersebut.

### Bagian 3: QR ditampilkan di mana?

QR ditampilkan di beberapa tempat:

- `BarangKantorsTable.php`
- `AsetTetapTable.php`
- `BhpTable.php`
- `resources/views/filament/colums/barcode.blade.php`

Alurnya:

1. Tabel mengambil data Barang Kantor.
2. Kolom barcode menampilkan gambar dari `$record->barcode_qr_image_url`.
3. User bisa melihat QR untuk setiap barang.

### Bagian 4: Halaman scan dibuka dari mana?

File:

- `app/Filament/Admin/Resources/BarangKantors/Pages/ListBarangKantors.php`
- `app/Filament/Admin/Resources/BarangKantors/BarangKantorResource.php`

Alur:

1. Admin membuka daftar Barang Kantor.
2. Ada action `scanBarcode`.
3. Action mengarah ke route page scan:
   - `BarangKantorResource::getUrl('scan')`
4. Route page scan didefinisikan di `BarangKantorResource::getPages()`.
5. Page yang dibuka adalah `ScanBarangKantor`.

### Bagian 5: Cara scanner bekerja

File:

- `resources/views/filament/admin/resources/barang-kantors/pages/scan-barang-kantor.blade.php`

Library:

- `Html5Qrcode`
- di-load dari:
  - `https://unpkg.com/html5-qrcode`

Function JavaScript:

#### `startScanPreferBack()`

Gunanya:

- Memulai kamera.
- Memilih kamera belakang jika tersedia.

Alur:

1. Cek library `Html5Qrcode` sudah siap.
2. Panggil `initCameras()`.
3. Pilih kamera belakang dengan `pickBackCameraIndex()`.
4. Jalankan `startWithCameraIndex()`.

#### `initCameras()`

Gunanya:

- Mengambil daftar kamera perangkat.

#### `pickBackCameraIndex(cameras)`

Gunanya:

- Mencari kamera dengan label back/rear/environment/belakang.
- Jika tidak ketemu, pakai kamera pertama.

#### `startWithCameraIndex(index)`

Gunanya:

- Menyalakan kamera dan mulai membaca QR.

Alur:

1. Stop scanner lama jika masih berjalan.
2. Buat object `new Html5Qrcode("reader")`.
3. Jalankan `html5QrCode.start()`.
4. Jika QR terbaca, callback menerima `decodedText`.
5. Callback mengisi Livewire property:
   - `@this.set('barcode', decodedText)`
6. Setelah itu langsung memanggil:
   - `@this.call('submit')`

#### `stopScan()`

Gunanya:

- Menghentikan kamera.

### Bagian 6: Setelah QR terbaca, masuk ke function apa?

File:

- `app/Filament/Admin/Resources/BarangKantors/Pages/ScanBarangKantor.php`

Function:

- `submit()`

Alur `submit()`:

1. Ambil input dari property `$barcode`.
2. Trim spasi.
3. Jika kosong, tampilkan notifikasi `Input pencarian kosong`.
4. Jika input berupa URL, ambil bagian terakhir dari URL.
   - Contoh URL `/barang-kantor/detail/ASET-00001`.
   - Yang diambil: `ASET-00001`.
5. Query ke `BarangKantor`.
6. Dicari berdasarkan:
   - `barcode`
   - `kode_barang`
   - `nama_barang`
   - `nama_barang like keyword`
7. Urutan prioritas:
   - barcode cocok persis.
   - kode barang cocok persis.
   - nama barang cocok persis.
   - nama barang mirip.
8. Jika tidak ketemu, tampilkan notifikasi barang tidak ditemukan.
9. Jika ketemu, redirect ke route:
   - `barang.detail`
10. Parameter yang dikirim:
   - `kodeBarang = $barang->kode_barang`.

### Bagian 7: Route detail barang

File:

- `routes/web.php`

Route:

- `/barang-kantor/detail/{kodeBarang}`

Alur:

1. Route menerima `kodeBarang`.
2. Query `BarangKantor`.
3. Dicari berdasarkan:
   - `barcode`
   - `kode_barang`
   - `nama_barang`
4. Jika barang kategori aset:
   - sistem mencari data `PenyusutanAsetTetap`.
   - sistem membuat URL kartu penyusutan.
5. Sistem menampilkan view:
   - `resources/views/scan/barang.blade.php`.

### Kalau scan barcode ingin diubah

- Ubah format barcode: `BarangKantor::created()`.
- Ubah isi QR: `getBarcodeTargetUrlAttribute()`.
- Ubah generator QR: `getBarcodeQrImageUrlAttribute()`.
- Ubah tampilan scanner: `scan-barang-kantor.blade.php`.
- Ubah pencarian hasil scan: `ScanBarangKantor::submit()`.
- Ubah halaman detail setelah scan: route `barang.detail` di `routes/web.php` dan view `scan/barang.blade.php`.

## 6. Alur Perolehan Barang

Bagian ini adalah alur paling penting karena menghubungkan transaksi, master barang, stok, aset, penyusutan, jurnal umum, dan buku besar.

### File yang terlibat

- `app/Filament/Admin/Resources/PerolehanBarangs/PerolehanBarangResource.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/Schemas/PerolehanBarangForm.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/Pages/CreatePerolehanBarang.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/Pages/EditPerolehanBarang.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/Tables/PerolehanBarangsTable.php`
- `app/Models/PerolehanBarang.php`
- `app/Models/PerolehanBarangDetail.php`
- `app/Models/BarangKantor.php`
- `app/Models/PendapatanHibah.php`
- `app/Support/PerolehanBarangAllocator.php`
- `app/Support/KasKecilBalance.php`

### Bagian 1: Admin membuka form perolehan

1. Admin klik menu Perolehan Barang.
2. Filament membuka `PerolehanBarangResource`.
3. `PerolehanBarangResource::form()` memanggil:
   - `PerolehanBarangForm::configure()`.
4. `PerolehanBarangForm` menampilkan semua input transaksi.

### Bagian 2: Admin memilih sumber perolehan

Field:

- `sumber_perolehan`

Pilihan:

- Pembelian.
- Hibah Barang.
- Hibah Uang.

Function yang dipakai:

- `PerolehanBarangResource::sumberPerolehanOptions()`
- `PerolehanBarangResource::generatePerolehanId()`
- `PerolehanBarangResource::isHibahSource()`
- `PerolehanBarangResource::isHibahUangSource()`

Alur:

1. Saat sumber perolehan dipilih, `afterStateUpdated()` berjalan.
2. Jika create, nomor perolehan dibuat ulang.
3. Jika Hibah Barang:
   - status penggunaan dibuat `Siap Digunakan`.
   - tanggal diterima mengikuti tanggal pembelian/tanggal diterima.
4. Jika Pembelian atau Hibah Uang:
   - status default `Belum Siap Digunakan`.
5. Jika sumber hibah:
   - kategori detail dipaksa menjadi aset.
   - BHP tidak dipakai.

### Bagian 3: Nomor perolehan dibuat

File:

- `PerolehanBarangResource.php`

Function:

- `generatePerolehanId(string $source)`

Alur:

1. Sistem menentukan prefix:
   - Pembelian -> `PRL-PB`.
   - Hibah Uang -> `PRL-HU`.
   - Hibah Barang -> `PRL-HB`.
2. Sistem mengambil semua `id_perolehan_barang`.
3. Sistem mengambil angka terbesar dari semua nomor.
4. Angka berikutnya dipakai untuk nomor baru.
5. Hasil contoh:
   - `PRL-PB-0001`
   - `PRL-HU-0002`
   - `PRL-HB-0003`

### Bagian 4: Admin mengisi detail barang

Field utama di repeater:

- `kategori_barang`
- `nama_barang`
- `jenis_aset`
- `jenis_bhp`
- `kategori_aset_id`
- `umur_ekonomis`
- `nilai_residu`
- `barang_kantor_id`
- `satuan_perolehan`
- `jumlah_perolehan`
- `harga_satuan`
- `total_harga`
- `persentase_subtotal`
- `alokasi_diskon`
- `alokasi_biaya_lainnya`
- `harga_perolehan`
- `total_harga_perolehan`

Alur jika kategori Aset:

1. Admin isi nama barang.
2. Pilih jenis aset.
3. Pilih kategori aset tetap.
4. Umur ekonomis otomatis diambil dari kategori aset.
5. Isi nilai residu.
6. Isi jumlah dan harga.

Alur jika kategori BHP:

1. Pilih jenis BHP.
2. Pilih nama barang BHP yang sudah ada atau buat baru.
3. Jika buat baru, data masuk ke `BarangKantor`.
4. Satuan perolehan mengikuti satuan barang BHP.
5. Tidak ada konversi satuan.

### Bagian 5: Hitung alokasi pembelian

File:

- `PerolehanBarangForm.php`
- `PerolehanBarangAllocator.php`

Tombol:

- `Hitung Alokasi`

Function:

- `PerolehanBarangAllocator::allocate()`

Rumus:

1. `total_harga = jumlah * harga_satuan`.
2. `subtotal_barang = total semua total_harga`.
3. `alokasi_diskon = diskon_total * total_harga_item / subtotal_barang`.
4. `alokasi_biaya_lainnya = biaya_lainnya_total * total_harga_item / subtotal_barang`.
5. `total_harga_perolehan = total_harga - alokasi_diskon + alokasi_biaya_lainnya`.
6. `harga_perolehan = total_harga_perolehan / jumlah`.
7. `grand_total = jumlah semua total_harga_perolehan`.

Alur function `allocate()`:

1. Ambil data detail.
2. Normalisasi angka dengan `normalizeNumber()`.
3. Hitung total harga per item.
4. Hitung subtotal semua item.
5. Hitung diskon dan biaya lain.
6. Hitung alokasi setiap item.
7. Masukkan hasil ke detail.
8. Return data yang sudah lengkap.

### Bagian 6: Validasi kas kecil

File:

- `CreatePerolehanBarang.php`
- `EditPerolehanBarang.php`
- `KasKecilBalance.php`

Function:

- `validateKasKecilBalance()`
- `KasKecilBalance::available()`

Alur:

1. Setelah grand total dihitung, sistem cek saldo Kas Kecil.
2. `KasKecilBalance::available()` menghitung:
   - saldo awal Kas Kecil dari COA.
   - tambah jurnal debit Kas Kecil.
   - kurang jurnal kredit Kas Kecil.
3. Jika grand total lebih besar dari saldo tersedia:
   - simpan ditolak.
   - muncul pesan saldo kas kecil tidak mencukupi.

### Bagian 7: Saat tombol simpan ditekan

File:

- `CreatePerolehanBarang.php`

Function:

- `mutateFormDataBeforeCreate()`

Alur:

1. Data form masuk ke page create.
2. Sistem normalisasi status siap pakai dengan `normalizeStatusSiapPakai()`.
3. Jika sumber hibah:
   - jalankan `normalizeHibahData()`.
4. Jika pembelian:
   - jalankan `PerolehanBarangAllocator::allocate()`.
   - cek saldo kas kecil.
5. Data final dikirim ke model untuk disimpan.

Setelah record dibuat:

Function:

- `afterCreate()`

Alur:

1. Record perolehan di-refresh.
2. Sistem memanggil:
   - `$this->getRecord()->syncJurnalUmum()`.
3. Jurnal umum perolehan dibuat.

### Bagian 8: Detail perolehan membuat Barang Kantor

File:

- `app/Models/PerolehanBarangDetail.php`

Event:

- `created`

Alur jika detail Aset:

1. Detail perolehan berhasil dibuat.
2. Sistem cek `kategori_barang === 'aset'`.
3. Sistem membuat kode aset:
   - `ASET-00001`, dst.
4. Jika jumlah perolehan lebih dari 1, dibuat barang sebanyak jumlah.
5. Harga per unit dibagi dengan `distributeUnitPrices()`.
6. Sistem membuat record `BarangKantor`.
7. Setelah `BarangKantor` dibuat, model Barang Kantor otomatis membuat data penyusutan.

Alur jika detail BHP:

1. Sistem cek `kategori_barang === 'bhp'`.
2. Jumlah masuk dihitung dengan `convertToPcs()`.
3. Karena tidak ada konversi, jumlah tetap sama.
4. Sistem cari Barang Kantor BHP dengan nama yang sama.
5. Jika ada:
   - update satuan.
   - update jenis BHP.
   - stok bertambah.
6. Jika belum ada:
   - buat BHP baru dengan kode `BHP-00001`.
   - stok diisi sesuai jumlah.

### Bagian 9: Perolehan membuat jurnal umum

File:

- `app/Models/PerolehanBarang.php`

Function:

- `syncJurnalUmum()`

Alur:

1. Load detail.
2. Hitung subtotal, diskon, biaya lain, grand total.
3. Tentukan total dibayar:
   - pembelian memakai `grand_total`.
   - hibah memakai `total_nilai_hibah`.
4. Hapus jurnal lama untuk perolehan yang sama.
5. Tentukan akun kredit:
   - Pembelian -> Kas Kecil.
   - Hibah Barang -> Penerimaan Hibah Barang.
   - Hibah Uang -> Kas Bank Hibah.
6. Kelompokkan detail ke akun debit:
   - aset laboratorium -> Sarana Pendidikan Laboratorium.
   - inventaris kantor -> Inventaris Kantor.
   - kendaraan -> Kendaraan Bermotor.
   - BHP ATK -> Beban ATK Operasional.
   - BHP Inventaris -> BPP Inventaris Kantor.
7. Jika ada selisih pembulatan, selisih dimasukkan ke akun debit terbesar.
8. Buat `JurnalUmum`.
9. Buat `JurnalDetail` debit.
10. Buat `JurnalDetail` kredit.

### Bagian 10: Edit perolehan

File:

- `EditPerolehanBarang.php`

Function penting:

- `mutateFormDataBeforeFill()`
- `mutateFormDataBeforeSave()`
- `afterSave()`
- `handleRecordDeletion()`

Alur edit:

1. Saat halaman edit dibuka, `mutateFormDataBeforeFill()` menyiapkan data form.
2. Jika pembelian, data dihitung ulang dengan allocator.
3. Saat simpan, `mutateFormDataBeforeSave()` validasi nominal.
4. Jika pembelian, hitung ulang alokasi dan cek kas kecil.
5. Setelah simpan, `afterSave()` update detail dan aset turunannya.
6. Jurnal umum di-sync ulang.

### Kalau mau ubah Perolehan Barang

- Ubah input form: `PerolehanBarangForm.php`.
- Ubah nomor transaksi: `PerolehanBarangResource::generatePerolehanId()`.
- Ubah rumus alokasi: `PerolehanBarangAllocator::allocate()`.
- Ubah validasi kas kecil: `KasKecilBalance.php` atau `validateKasKecilBalance()`.
- Ubah pembuatan aset/BHP: `PerolehanBarangDetail.php`.
- Ubah jurnal perolehan: `PerolehanBarang::syncJurnalUmum()`.
- Ubah tampilan tabel/detail: `PerolehanBarangsTable.php`.

## 7. Alur Penyusutan Aset Tetap

### File yang terlibat

- `app/Models/BarangKantor.php`
- `app/Models/PenyusutanAsetTetap.php`
- `app/Models/PenyusutanDetail.php`
- `app/Filament/Admin/Resources/Penyusutans/PenyusutanResource.php`
- `app/Filament/Admin/Resources/Penyusutans/Tables/PenyusutansTable.php`
- `app/Filament/Admin/Resources/Penyusutans/Pages/ListPenyusutans.php`
- `app/Filament/Admin/Resources/Penyusutans/Pages/PenyusutanKartuPage.php`
- `resources/views/filament/admin/resources/penyusutans/kartu.blade.php`
- `resources/views/filament/admin/resources/penyusutans/kartu-pdf.blade.php`

### Bagian 1: Data penyusutan dibuat dari mana?

Data penyusutan dibuat otomatis saat Barang Kantor kategori aset dibuat.

File:

- `BarangKantor.php`

Event:

- `created`

Alur:

1. Perolehan Barang membuat Barang Kantor aset, atau admin membuat aset manual.
2. Setelah Barang Kantor dibuat, event `created` berjalan.
3. Jika `kategori_barang === 'aset'`, sistem membuat `PenyusutanAsetTetap`.
4. Data yang dikirim:
   - `barang_kantor_id`
   - `kode_barang`
   - `nama_aset`
   - `status_penggunaan`
   - `tanggal_diterima`
   - `harga_perolehan`
   - `nilai_residu`
   - `umur_ekonomis_tahun`
   - `status_penyusutan`

### Bagian 2: Saat `PenyusutanAsetTetap` dibuat

File:

- `PenyusutanAsetTetap.php`

Event:

- `creating`

Alur:

1. Jika `id_penyusutan` kosong, sistem membuat nomor otomatis.
2. Nomor memakai format:
   - `PST-0001`
3. Sistem ambil data aset dari `BarangKantor`.
4. Sistem menghitung beban penyusutan bulanan.

Rumus:

```text
beban_penyusutan_bulanan =
(harga_perolehan - nilai_residu) / (umur_ekonomis_tahun * 12)
```

Contoh:

```text
Harga perolehan = 12.000.000
Nilai residu = 0
Umur ekonomis = 4 tahun
Total bulan = 48
Beban per bulan = 12.000.000 / 48 = 250.000
```

### Bagian 3: Aturan mulai penyusutan

File:

- `PenyusutanAsetTetap.php`

Function:

- `bulanMulaiPenyusutanDariTanggal()`

Alur:

1. Sistem membaca `tanggal_diterima`.
2. Jika tanggal diterima tanggal 1 sampai 15:
   - penyusutan mulai bulan itu.
3. Jika tanggal diterima tanggal 16 sampai akhir bulan:
   - penyusutan mulai bulan berikutnya.

Contoh:

- Tanggal diterima 10 Maret 2026 -> mulai Maret 2026.
- Tanggal diterima 20 Maret 2026 -> mulai April 2026.

### Bagian 4: Admin memproses akhir periode

File:

- `ListPenyusutans.php`

Tombol:

- `Proses Akhir Periode`

Function:

- `getHeaderActions()`
- `postingPeriode()`

Alur tombol:

1. Admin klik `Proses Akhir Periode`.
2. Modal muncul.
3. Admin memilih bulan dan tahun.
4. Action memvalidasi:
   - periode tidak boleh masa depan.
   - periode hanya boleh diproses pada atau setelah akhir bulan.
5. Jika valid, jalankan `postingPeriode($bulan, $tahun)`.

### Bagian 5: Function `postingPeriode()`

Alur sangat rinci:

1. Sistem membuat `targetStart`, yaitu awal bulan target.
2. Sistem membuat `targetEnd`, yaitu akhir bulan target.
3. Sistem mengambil semua data `PenyusutanAsetTetap`.
4. Untuk setiap aset:
   - cek aset aktif dengan `isAktif()`.
   - jika belum siap digunakan, dilewati.
   - cek apakah bulan itu sudah pernah diposting.
   - jika sudah ada detail penyusutan, dilewati.
   - cek bulan mulai penyusutan.
   - jika periode target sebelum bulan mulai, dilewati.
   - cek akhir umur ekonomis.
   - jika periode lewat umur ekonomis, dilewati.
5. Jika aset lolos validasi:
   - ambil beban penyusutan bulanan.
   - buat `JurnalUmum`.
   - buat jurnal detail debit.
   - buat jurnal detail kredit.
   - hitung akumulasi baru.
   - buat `PenyusutanDetail`.

Jurnal yang dibuat:

- Debit `5611104` Beban Penyusutan.
- Kredit `1264101` Akumulasi Penyusutan.

### Bagian 6: Detail penyusutan

File:

- `PenyusutanDetail.php`

Data yang disimpan:

- `penyusutan_id`
- `periode`
- `beban_penyusutan_bulanan`
- `akumulasi`
- `nilai_buku`
- `jurnal_umum_id`

Rumus nilai buku:

```text
nilai_buku = harga_perolehan - akumulasi
```

Tetapi tidak boleh lebih kecil dari nilai residu:

```php
max(harga_perolehan - akumulasi, nilai_residu)
```

### Bagian 7: Keterangan kelengkapan

File:

- `PenyusutanAsetTetap.php`

Function:

- `buildKeteranganKelengkapan()`

Alur:

1. Jika tanggal diterima kosong:
   - return `Belum Siap Digunakan`.
2. Hitung bulan mulai penyusutan.
3. Hitung akhir umur ekonomis.
4. Tentukan batas periode yang wajib dicek.
5. Jika belum ada periode wajib:
   - return `Belum Waktunya`.
6. Loop dari bulan mulai sampai batas cek.
7. Untuk setiap bulan, cek apakah detail penyusutan sudah ada.
8. Jika semua ada:
   - return `Lengkap`.
9. Jika ada bulan yang belum ada:
   - return `Bolong: Nama Bulan`.

### Bagian 8: Kartu penyusutan

File:

- `PenyusutanKartuPage.php`
- `kartu.blade.php`
- `kartu-pdf.blade.php`

Alur:

1. Admin klik tombol `Kartu`.
2. Page menerima record penyusutan.
3. `mount()` mengambil data penyusutan.
4. `getAssetInformation()` menyiapkan informasi aset.
5. `getKartuRows()` membuat baris kartu penyusutan.
6. Jika cetak, `cetakKartu()` membuat PDF.

### Kalau mau ubah Penyusutan

- Ubah rumus beban bulanan: `PenyusutanAsetTetap::creating()`.
- Ubah aturan tanggal 15: `bulanMulaiPenyusutanDariTanggal()`.
- Ubah validasi boleh proses periode: `ListPenyusutans::periodeSudahBolehDiposting()`.
- Ubah jurnal penyusutan: `ListPenyusutans::postingPeriode()`.
- Ubah status lengkap/bolong: `buildKeteranganKelengkapan()`.
- Ubah kartu penyusutan: `PenyusutanKartuPage.php` dan view kartu.

## 8. Alur Pendapatan Hibah

### File yang terlibat

- `app/Models/PendapatanHibah.php`
- `app/Filament/Admin/Resources/PendapatanHibahs/PendapatanHibahResource.php`
- `app/Filament/Admin/Resources/PendapatanHibahs/Schemas/PendapatanHibahForm.php`
- `app/Filament/Admin/Resources/PendapatanHibahs/Tables/PendapatanHibahsTable.php`

### Alur

1. Admin membuka Pendapatan Hibah.
2. Form dibuat oleh `PendapatanHibahForm`.
3. Admin mengisi tanggal hibah, sumber hibah, nilai hibah, dan keterangan.
4. Akun Kas Bank Hibah dan Pendapatan Donasi Hibah diambil dari COA.
5. Saat simpan, model `PendapatanHibah::creating()` membuat nomor `PDH-0001`.
6. `PendapatanHibah::saving()` validasi akun COA wajib ada.
7. Setelah data tersimpan, `saved` memanggil `syncJurnalUmum()`.
8. Jurnal otomatis dibuat.

Jurnal:

- Debit Kas Bank Hibah.
- Kredit Pendapatan Donasi Hibah.

### Function penting

- `generateNoHibah()`: buat nomor hibah.
- `usedAmount()`: menghitung dana hibah yang sudah dipakai perolehan.
- `getSisaAttribute()`: menghitung sisa dana hibah.
- `syncJurnalUmum()`: membuat jurnal otomatis.

### Kalau mau ubah Pendapatan Hibah

- Ubah input: `PendapatanHibahForm.php`.
- Ubah jurnal: `PendapatanHibah::syncJurnalUmum()`.
- Ubah nomor: `generateNoHibah()`.
- Ubah tampilan: `PendapatanHibahsTable.php`.

## 9. Alur Pengisian Kas Kecil

### File yang terlibat

- `app/Models/PengisianKasKecil.php`
- `app/Filament/Admin/Resources/PengisianKasKecils/PengisianKasKecilResource.php`
- `app/Filament/Admin/Resources/PengisianKasKecils/Schemas/PengisianKasKecilForm.php`
- `app/Filament/Admin/Resources/PengisianKasKecils/Tables/PengisianKasKecilsTable.php`

### Alur

1. Admin membuka Pengisian Kas Kecil.
2. Form menampilkan akun Kas Kecil dan Kas Pengeluaran Institusi.
3. Admin mengisi tanggal, nominal, bukti, dan keterangan.
4. Saat simpan, `PengisianKasKecil::creating()` membuat nomor `PKK-0001`.
5. Setelah simpan, `saved` memanggil `syncJurnalUmum()`.
6. Jurnal otomatis dibuat.

Jurnal:

- Debit Kas Kecil.
- Kredit Kas Pengeluaran Institusi.

### Function penting

- `generateNoTransaksi()`: buat nomor transaksi.
- `syncJurnalUmum()`: buat jurnal.

### Kalau mau ubah Pengisian Kas Kecil

- Ubah form: `PengisianKasKecilForm.php`.
- Ubah akun default: `PengisianKasKecilResource.php`.
- Ubah jurnal: `PengisianKasKecil::syncJurnalUmum()`.

## 10. Alur Jurnal Umum

### File yang terlibat

- `app/Models/JurnalUmum.php`
- `app/Models/JurnalDetail.php`
- `app/Filament/Admin/Resources/JurnalUmums/JurnalUmumResource.php`
- `app/Filament/Admin/Resources/JurnalUmums/Tables/JurnalUmumsTable.php`
- `app/Filament/Admin/Resources/JurnalUmums/Widgets/JurnalUmumOverview.php`

### Alur

1. Transaksi dibuat.
2. Model transaksi memanggil function `syncJurnalUmum()` atau proses penyusutan membuat jurnal langsung.
3. Sistem membuat record `JurnalUmum`.
4. Sistem membuat beberapa `JurnalDetail`.
5. Jurnal tampil di menu Jurnal Umum.
6. Buku Besar membaca data dari Jurnal Detail.

### Sumber jurnal

- Pendapatan Hibah -> `PendapatanHibah::syncJurnalUmum()`.
- Pengisian Kas Kecil -> `PengisianKasKecil::syncJurnalUmum()`.
- Perolehan Barang -> `PerolehanBarang::syncJurnalUmum()`.
- Penyusutan -> `ListPenyusutans::postingPeriode()`.

### Function penting di `JurnalUmum`

- `details()`: relasi ke detail.
- `perolehanBarang()`: relasi ke perolehan.
- `penyusutan()`: relasi ke penyusutan.
- `pengisianKasKecil()`: relasi ke pengisian.
- `pendapatanHibah()`: relasi ke pendapatan hibah.
- `getReffTransaksiAttribute()`: menentukan nomor bukti.
- `referensi()`: menentukan relasi asal transaksi.

## 11. Alur Buku Besar

### File yang terlibat

- `app/Filament/Admin/Resources/BukuBesars/Widgets/BukuBesarTableOverview.php`
- `resources/views/filament/admin/resources/buku-besars/widgets/buku-besar-overview.blade.php`
- `resources/views/filament/admin/resources/buku-besars/widgets/buku-besar-pdf-layout.blade.php`

### Alur

1. Admin membuka Buku Besar.
2. Widget `BukuBesarTableOverview` berjalan.
3. `mount()` menentukan periode default.
4. `filter()` mengambil COA dan jurnal.
5. Sistem menghitung saldo awal.
6. View menampilkan mutasi per akun.
7. Untuk setiap detail jurnal, sistem mencari akun lawan dengan `ledgerRowsForDetail()`.
8. Saldo berjalan dihitung berdasarkan `isNormalKredit()`.
9. Jika cetak, `cetakLaporan()` membuat PDF.

### Function penting

- `filter()`: mengambil data.
- `ledgerRowsForDetail()`: membuat baris buku besar dan akun lawan.
- `isNormalKredit()`: menentukan saldo normal.
- `distributeAmount()`: membagi nominal jika akun lawan lebih dari satu.
- `cetakLaporan()`: cetak PDF.

### Kalau mau ubah Buku Besar

- Ubah data query: `filter()`.
- Ubah cara saldo dihitung: `isNormalKredit()` atau view blade.
- Ubah PDF: `buku-besar-pdf-layout.blade.php`.

## 12. Alur Pengajuan Peminjaman Barang

### File yang terlibat

- `app/Http/Controllers/PeminjamanBarangController.php`
- `app/Models/PeminjamanBarang.php`
- `app/Models/BarangKantor.php`
- `resources/views/peminjaman-barang/index.blade.php`
- `resources/views/peminjaman-barang/riwayat.blade.php`
- `app/Filament/Admin/Resources/PeminjamanBarangs/Tables/PeminjamanBarangsTable.php`

### Alur anggota mengajukan

1. Anggota membuka `/peminjaman`.
2. Route memanggil `PeminjamanBarangController::index()`.
3. Controller mengambil barang yang boleh dipinjam.
4. Barang difilter dengan:
   - `borrowableForPeminjaman()`.
   - status Aktif.
   - stok > 0.
   - aset harus siap digunakan.
5. Anggota isi form dan submit.
6. Route POST memanggil `PeminjamanBarangController::store()`.
7. Controller validasi input.
8. Bukti peminjaman diupload.
9. Untuk setiap item:
   - cari Barang Kantor.
   - cek `isAvailableToBorrow()`.
   - simpan `PeminjamanBarang` status `pending`.

### Alur admin menyetujui

1. Admin membuka menu Peminjaman Barang.
2. Tabel memakai `PeminjamanBarangsTable`.
3. Admin klik `Setujui`.
4. Action `setujui` berjalan.
5. Sistem lock barang dengan `lockForUpdate()`.
6. Cek barang masih tersedia.
7. Status peminjaman diubah `disetujui`.
8. Barang menjalankan `markAsBorrowed()`.
9. Stok berkurang.
10. Status pinjam berubah jika perlu.

### Alur pengembalian

1. Anggota upload bukti pengembalian.
2. Status menjadi `menunggu_verifikasi_pengembalian`.
3. Admin klik `Verifikasi Pengembalian`.
4. Action `kembali` berjalan.
5. Barang menjalankan `markAsReturned()`.
6. Stok bertambah.
7. Status peminjaman menjadi `kembali`.

### Kalau mau ubah Peminjaman

- Ubah form anggota: view `peminjaman-barang/index.blade.php`.
- Ubah simpan pengajuan: `PeminjamanBarangController::store()`.
- Ubah syarat barang boleh dipinjam: `BarangKantor::isAvailableToBorrow()`.
- Ubah approval admin: `PeminjamanBarangsTable.php`.

## 13. Alur Pengajuan Pemakaian BHP

### File yang terlibat

- `app/Http/Controllers/PemakaianBHPController.php`
- `app/Models/PemakaianBHP.php`
- `app/Models/BarangKantor.php`
- `resources/views/pemakaian/index.blade.php`
- `resources/views/pemakaian/riwayat.blade.php`
- `app/Filament/Admin/Resources/Pemakaianbhps/Tables/PemakaianbhpsTable.php`

### Alur anggota mengajukan

1. Anggota membuka `/pemakaian`.
2. Controller `index()` mengambil BHP dengan stok > 0.
3. Anggota memilih barang dan jumlah.
4. Satuan mengikuti satuan barang kantor.
5. Submit memanggil `PemakaianBHPController::store()`.
6. Controller validasi input.
7. Set tanggal pemakaian = hari ini.
8. Simpan data status `pending`.

### Alur admin menyetujui

1. Admin buka menu Pemakaian BHP.
2. Tabel memakai `PemakaianbhpsTable`.
3. Admin klik `Setujui`.
4. Sistem lock barang.
5. Cek stok cukup.
6. Stok barang dikurangi dengan `decrement()`.
7. Status pemakaian menjadi `disetujui`.

### Kalau mau ubah Pemakaian BHP

- Ubah halaman anggota: `pemakaian/index.blade.php`.
- Ubah simpan pengajuan: `PemakaianBHPController::store()`.
- Ubah approval/stok: `PemakaianbhpsTable.php`.
- Ubah satuan stok: `BarangKantor.satuan` dan form Barang Kantor/Perolehan.

## 14. Alur Pengajuan Pembelian Barang

### File yang terlibat

- `app/Http/Controllers/PembelianBarangController.php`
- `app/Models/PengajuanPembelianBarang.php`
- `resources/views/pembelian/index.blade.php`
- `resources/views/pembelian/riwayat.blade.php`
- `app/Filament/Admin/Resources/PengajuanPembelianBarangs/Tables/PengajuanPembelianBarangsTable.php`

### Alur anggota mengajukan

1. Anggota membuka `/pembelian`.
2. Controller `index()` menampilkan form dan riwayat.
3. Anggota mengisi barang yang ingin dibeli.
4. Submit memanggil `PembelianBarangController::store()`.
5. Controller validasi:
   - user.
   - alasan.
   - bukti pendukung.
   - daftar item.
6. Bukti diupload.
7. Setiap item dihitung:
   - `sub_total = perkiraan_harga * jumlah`.
8. Data disimpan status `pending`.

### Alur admin memproses

1. Admin buka Pengajuan Pembelian.
2. Tabel memakai `PengajuanPembelianBarangsTable`.
3. Admin klik Setujui atau Tolak.
4. Jika setujui:
   - status menjadi `disetujui`.
   - notifikasi dikirim ke user.
5. Jika tolak:
   - status menjadi `ditolak`.
   - notifikasi dikirim ke user.

### Kalau mau ubah Pengajuan Pembelian

- Ubah form anggota: `pembelian/index.blade.php`.
- Ubah hitung subtotal: `PembelianBarangController::store()`.
- Ubah approval: `PengajuanPembelianBarangsTable.php`.

## 15. Alur Ketersediaan Barang

### File yang terlibat

- `app/Http/Controllers/KetersediaanController.php`
- `resources/views/ketersediaan/index.blade.php`
- `app/Models/BarangKantor.php`

### Alur

1. Anggota membuka `/ketersediaan`.
2. Route memanggil `KetersediaanController::index()`.
3. Controller membuat query Barang Kantor.
4. Jika filter jenis dipilih, query filter `kategori_barang`.
5. Jika search diisi, query cari `nama_barang` atau `kode_barang`.
6. Jika filter ready:
   - status barang Aktif.
   - stok > 0.
   - aset harus Tersedia, siap digunakan, punya tanggal diterima.
7. Jika filter unavailable:
   - barang tidak aktif, stok habis, dipinjam, atau aset belum siap.
8. Data dikirim ke view.

### Kalau mau ubah Ketersediaan

- Ubah query: `KetersediaanController::index()`.
- Ubah tampilan: `ketersediaan/index.blade.php`.

## 16. Alur Laporan

### Jurnal Umum

File:

- `JurnalUmumsTable.php`
- `JurnalUmumOverview.php`
- `jurnal-umum-overview.blade.php`
- `jurnal-umum-pdf.blade.php`

Alur:

1. Jurnal sudah dibuat otomatis oleh transaksi.
2. Resource Jurnal Umum mengambil data `JurnalUmum`.
3. Tabel menampilkan tanggal, nomor bukti, deskripsi, tipe transaksi.
4. Detail jurnal menampilkan debit/kredit.
5. PDF dibuat dari view PDF.

### Buku Besar

File:

- `BukuBesarTableOverview.php`
- `buku-besar-overview.blade.php`
- `buku-besar-pdf-layout.blade.php`

Alur:

1. Buku Besar membaca Jurnal Umum dan Jurnal Detail.
2. Data dikelompokkan per COA.
3. Saldo awal dihitung dari saldo COA + transaksi sebelum periode.
4. Mutasi periode ditampilkan.
5. Saldo akhir dihitung berjalan.
6. PDF dicetak dengan DomPDF.

### Kartu Penyusutan

File:

- `PenyusutanKartuPage.php`
- `kartu.blade.php`
- `kartu-pdf.blade.php`

Alur:

1. Admin klik kartu.
2. Sistem mengambil aset dan detail penyusutan.
3. Sistem menyusun baris periode, beban, akumulasi, nilai buku.
4. Jika cetak, PDF dibuat.

## 17. Ringkasan Jika Ingin Mengubah Kode

### Ubah login

- `AuthController.php`
- `tab-auth-login-script.blade.php`
- `tab-auth-guard.blade.php`
- `AdminPanelProvider.php`

### Ubah COA

- `COAForm.php`
- `Coa.php`

### Ubah Barang Kantor

- `BarangKantorForm.php`
- `BarangKantorsTable.php`
- `BarangKantor.php`

### Ubah scan barcode

- `BarangKantor.php`
- `ScanBarangKantor.php`
- `scan-barang-kantor.blade.php`
- `routes/web.php`
- `scan/barang.blade.php`

### Ubah Perolehan

- `PerolehanBarangForm.php`
- `CreatePerolehanBarang.php`
- `EditPerolehanBarang.php`
- `PerolehanBarang.php`
- `PerolehanBarangDetail.php`
- `PerolehanBarangAllocator.php`

### Ubah Penyusutan

- `PenyusutanAsetTetap.php`
- `PenyusutanDetail.php`
- `ListPenyusutans.php`
- `PenyusutansTable.php`
- `PenyusutanKartuPage.php`

### Ubah Jurnal

- `PerolehanBarang::syncJurnalUmum()`
- `PendapatanHibah::syncJurnalUmum()`
- `PengisianKasKecil::syncJurnalUmum()`
- `ListPenyusutans::postingPeriode()`

### Ubah Buku Besar

- `BukuBesarTableOverview.php`
- `buku-besar-overview.blade.php`
- `buku-besar-pdf-layout.blade.php`

### Ubah Pengajuan Anggota

- Peminjaman: `PeminjamanBarangController.php`, `PeminjamanBarangsTable.php`.
- Pemakaian BHP: `PemakaianBHPController.php`, `PemakaianbhpsTable.php`.
- Pembelian: `PembelianBarangController.php`, `PengajuanPembelianBarangsTable.php`.

