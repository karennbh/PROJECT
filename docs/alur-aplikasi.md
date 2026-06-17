# Alur Aplikasi TA2025

Dokumen ini menjelaskan alur aplikasi berdasarkan kode yang ada di project `TA2025`. Aplikasi memakai Laravel, Filament Admin Panel, Eloquent Model, Controller untuk halaman anggota, dan beberapa support class untuk perhitungan otomatis.

## 1. Struktur Umum Aplikasi

Aplikasi dibagi menjadi dua sisi utama:

- Admin: dikelola lewat Filament Resource di `app/Filament/Admin/Resources`.
- Anggota/User: dikelola lewat route dan controller biasa di `routes/web.php` dan `app/Http/Controllers`.

File konfigurasi admin utama ada di:

- `app/Providers/Filament/AdminPanelProvider.php`

Fungsinya:

- Menentukan panel `/admin`.
- Menentukan login Filament memakai `App\Filament\Auth\Login`.
- Mengatur grup navigasi: Master Data, Transaksi, Pengajuan, Laporan, Pengaturan.
- Memasang middleware autentikasi dan idle timeout.
- Memasang script tambahan:
  - `partials.tab-auth-guard`
  - `partials.feature-back-guard`
  - `filament/admin/hooks/navigation-guard.blade.php`
  - `partials.admin-session-timeout`

## 2. Login dan Hak Akses

### File utama

- `app/Http/Controllers/AuthController.php`
- `app/Filament/Auth/Login.php`
- `app/Filament/Auth/LogoutResponse.php`
- `resources/views/auth/login.blade.php`
- `resources/views/partials/tab-auth-login-script.blade.php`
- `resources/views/partials/tab-auth-guard.blade.php`
- `app/Http/Middleware/AnggotaMiddleware.php`
- `app/Http/Middleware/EnforceSessionIdleTimeout.php`
- `.env`

### Alur login

1. User membuka `/login`.
2. `AuthController@showLoginForm` mengecek apakah user sudah login.
3. Jika sudah login:
   - `admin` diarahkan ke `/admin`.
   - `anggota` diarahkan ke `/dashboard`.
   - role tidak valid langsung logout.
4. Saat submit login, `AuthController@login` melakukan validasi `username`, `password`, dan optional `next`.
5. Jika `Auth::attempt()` berhasil:
   - session diregenerasi.
   - `last_activity_at` disimpan.
   - response mengembalikan `redirect`, `user_id`, dan `user_group`.
6. Jika gagal, response berisi error username/password.

### Multi akun/tab

Script `tab-auth-login-script.blade.php` dan `tab-auth-guard.blade.php` memakai:

- `sessionStorage` untuk token tab.
- `sessionStorage` untuk `user_id`.
- endpoint `/auth/session-user` dari `AuthController@sessionUser`.

Tujuannya agar jika browser membuka beberapa akun/tab, tab bisa mengecek apakah session server masih milik user yang sama.

### Timeout

Timeout session diambil dari `.env`:

```env
SESSION_LIFETIME=45
```

Artinya jika tidak ada aktivitas selama 45 menit, sistem mengarahkan user ke login. Di admin, nilai ini dipakai oleh `partials.admin-session-timeout`; di route umum juga diamankan oleh `EnforceSessionIdleTimeout`.

## 3. Master Data

### 3.1 User

File:

- `app/Models/User.php`
- `app/Filament/Admin/Resources/Users/UserResource.php`
- `app/Filament/Admin/Resources/Users/Schemas/UserForm.php`
- `app/Filament/Admin/Resources/Users/Tables/UsersTable.php`

Fungsi:

- Menyimpan akun pengguna.
- Field penting: `name`, `username`, `password`, `user_group`.
- `user_group` menentukan akses: `admin` atau `anggota`.
- `User::canAccessPanel()` menentukan apakah user boleh masuk panel Filament.

Relasi penting:

- `PeminjamanBarang` belongsTo `User`.
- `PemakaianBHP` belongsTo `User`.
- `PengajuanPembelianBarang` belongsTo `User`.

### 3.2 COA

File:

- `app/Models/coa.php`
- `app/Filament/Admin/Resources/COAS/COAResource.php`
- `app/Filament/Admin/Resources/COAS/Schemas/COAForm.php`
- `app/Filament/Admin/Resources/COAS/Tables/COASTable.php`

Fungsi:

- Menyimpan daftar akun akuntansi.
- Field penting: `kode_akun`, `nama_akun`, `header_akun`, `saldo`, `jumlah_saldo`.
- Akun yang sudah dibuat tidak bisa dipilih lagi di form COA.
- Jika COA baru dibuat, model `Coa` memanggil `syncPendingTransactionJournals()` untuk mencoba membuat jurnal transaksi yang sebelumnya belum bisa dibuat karena akun belum tersedia.

Fungsi penting di model:

- `jurnalDetails()`: relasi COA ke detail jurnal melalui `kode_akun`.
- `isNormalDebit()`: membaca normal saldo dari `header_akun`.
- `boot()`: mengatur efek saat COA dihapus, dibuat, atau kode akun diubah.
- `syncPendingTransactionJournals()`: membuat ulang jurnal tertunda untuk Perolehan Barang, Pendapatan Hibah, dan Pengisian Kas Kecil.

Catatan:

- Buku Besar saat ini memakai kolom `saldo` COA untuk menentukan saldo berjalan debit/kredit.

### 3.3 Kategori Aset Tetap

File:

- `app/Models/KategoriAsetTetap.php`
- `app/Filament/Admin/Resources/KategoriAsets/KategoriAsetResource.php`
- `app/Filament/Admin/Resources/KategoriAsets/Schemas/KategoriAsetForm.php`
- `app/Filament/Admin/Resources/KategoriAsets/Tables/KategoriAsetsTable.php`

Fungsi:

- Menyimpan kelompok aset tetap, umur ekonomis, dan keterangan kategori.
- Digunakan saat input aset di Barang Kantor dan Perolehan Barang.

Relasi:

- `KategoriAsetTetap` hasMany `BarangKantor`.
- `KategoriAsetTetap` hasMany `PerolehanBarangDetail`.

### 3.4 Barang Kantor

File:

- `app/Models/BarangKantor.php`
- `app/Filament/Admin/Resources/BarangKantors/BarangKantorResource.php`
- `app/Filament/Admin/Resources/BarangKantors/Schemas/BarangKantorForm.php`
- `app/Filament/Admin/Resources/BarangKantors/Tables/BarangKantorsTable.php`
- `app/Filament/Admin/Resources/BarangKantors/Widgets/*`
- `app/Filament/Admin/Resources/BarangKantors/Pages/ScanBarangKantor.php`
- `resources/views/filament/admin/resources/barang-kantors/pages/scan-barang-kantor.blade.php`

Fungsi:

- Menjadi master barang utama, baik Aset Tetap maupun BHP.
- Field penting: `kategori_barang`, `kode_barang`, `barcode`, `nama_barang`, `stok`, `satuan`, `jenis_aset`, `jenis_bhp`, `status_penggunaan`, `tanggal_diterima`, `harga_perolehan`, `status_barang`, `status_pinjam`.

Jenis data:

- `aset`: aset tetap, bisa punya penyusutan.
- `bhp`: barang habis pakai, stok bertambah/berkurang.

Fungsi penting di model:

- `kategoriAset()`: barang belongsTo kategori aset.
- `penyusutans()`: barang hasMany penyusutan.
- `perolehanBarangDetail()`: barang berasal dari detail perolehan.
- `tanggalPembelianPerolehan()`: mengambil tanggal pembelian dari transaksi perolehan asal.
- `getBarcodeTargetUrlAttribute()`: membuat URL tujuan QR/barcode.
- `getBarcodeQrImageUrlAttribute()`: membuat gambar QR dengan layanan `https://quickchart.io/qr`.
- `isAvailableToBorrow()`: mengecek apakah barang boleh dipinjam.
- `markAsBorrowed()`: mengurangi stok dan mengubah status pinjam.
- `markAsReturned()`: menambah stok dan mengembalikan status pinjam.
- `getKategoriBarangLabelAttribute()`, `getJenisBarangLabelAttribute()`, `getJenisAsetLabelAttribute()`: label tampilan.
- `scopeBorrowableForPeminjaman()`: filter barang yang boleh diajukan peminjaman.
- `syncAssetStatuses()`: sinkron status aset ke data penyusutan.
- `isSiapPakai()`: aset dianggap siap jika status `siap_digunakan` dan `tanggal_diterima` terisi.
- `syncPenyusutanData()`: update data penyusutan mengikuti data barang kantor.
- event `created`: membuat barcode otomatis dan membuat data penyusutan otomatis untuk aset.
- event `saved`: sinkron status dan data penyusutan.

Barcode/QR:

- Barcode disimpan sebagai kode teks, contoh `BRG-000001`.
- QR image dibuat dari QuickChart: `https://quickchart.io/qr?...`.
- Scanner di halaman scan memakai JavaScript library `Html5Qrcode`.
- Hasil scan dicocokkan dengan `barcode`, `kode_barang`, atau `nama_barang`, lalu redirect ke detail barang.

## 4. Transaksi Admin

### 4.1 Pendapatan Hibah

File:

- `app/Models/PendapatanHibah.php`
- `app/Filament/Admin/Resources/PendapatanHibahs/PendapatanHibahResource.php`
- `app/Filament/Admin/Resources/PendapatanHibahs/Schemas/PendapatanHibahForm.php`
- `app/Filament/Admin/Resources/PendapatanHibahs/Tables/PendapatanHibahsTable.php`

Fungsi:

- Mencatat hibah uang yang diterima.
- Digunakan sebagai sumber dana untuk Perolehan Barang jenis Hibah Uang.

Field penting:

- `no_hibah`, `tanggal_hibah`, `sumber_hibah`, `jenis_hibah`, `akun_bank_hibah`, `akun_pendapatan_hibah`, `nilai_hibah`, `keterangan`.

Fungsi penting:

- `jurnal()`: relasi ke Jurnal Umum.
- `perolehanBarangs()`: relasi ke perolehan yang memakai hibah ini.
- `getDigunakanAttribute()`: jumlah dana hibah yang sudah dipakai.
- `getSisaAttribute()`: sisa dana hibah.
- `usedAmount()`: menghitung penggunaan dana dari `PerolehanBarangDetail.total_harga_perolehan`.
- `syncJurnalUmum()`: membuat jurnal pendapatan hibah.
- `generateNoHibah()`: nomor otomatis `PDH-0001`.

Jurnal otomatis:

- Debit: `Kas Bank Hibah`.
- Kredit: `Pendapatan Donasi Hibah`.

Jika akun COA belum ada:

- Form menampilkan `Tambahkan akun COA`.
- Model menolak penyimpanan dengan pesan validasi, bukan error SQL.

### 4.2 Pengisian Kas Kecil

File:

- `app/Models/PengisianKasKecil.php`
- `app/Filament/Admin/Resources/PengisianKasKecils/PengisianKasKecilResource.php`
- `app/Filament/Admin/Resources/PengisianKasKecils/Schemas/PengisianKasKecilForm.php`
- `app/Filament/Admin/Resources/PengisianKasKecils/Tables/PengisianKasKecilsTable.php`

Fungsi:

- Mencatat pengisian saldo kas kecil dari Kas Pengeluaran Institusi.

Field penting:

- `no_transaksi`, `tanggal`, `akun_kas_kecil`, `akun_sumber_dana`, `nominal`, `bukti`, `keterangan`.

Fungsi penting:

- `jurnal()`: relasi ke Jurnal Umum.
- `syncJurnalUmum()`: membuat jurnal pengisian kas kecil.
- `generateNoTransaksi()`: nomor otomatis `PKK-0001`.

Jurnal otomatis:

- Debit: `Kas Kecil`.
- Kredit: `Kas Pengeluaran Institusi`.

### 4.3 Perolehan Barang

File:

- `app/Models/PerolehanBarang.php`
- `app/Models/PerolehanBarangDetail.php`
- `app/Support/PerolehanBarangAllocator.php`
- `app/Support/KasKecilBalance.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/PerolehanBarangResource.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/Schemas/PerolehanBarangForm.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/Tables/PerolehanBarangsTable.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/Pages/CreatePerolehanBarang.php`
- `app/Filament/Admin/Resources/PerolehanBarangs/Pages/EditPerolehanBarang.php`

Sumber perolehan:

- Pembelian: kode `PRL-PB-0001`.
- Hibah Uang: kode `PRL-HU-0002`.
- Hibah Barang: kode `PRL-HB-0003`.

Nomor dibuat oleh:

- `PerolehanBarangResource::generatePerolehanId()`.

Relasi:

- `PerolehanBarang` hasMany `PerolehanBarangDetail`.
- `PerolehanBarang` belongsTo `PendapatanHibah`.
- `PerolehanBarang` hasMany `JurnalUmum`.
- `PerolehanBarangDetail` belongsTo `PerolehanBarang`.
- `PerolehanBarangDetail` belongsTo `BarangKantor` untuk BHP.
- `PerolehanBarangDetail` hasMany `BarangKantor` untuk aset yang dibuat dari detail tersebut.

Alur input:

1. Admin memilih sumber perolehan.
2. Sistem membuat nomor perolehan.
3. Admin mengisi tanggal pembelian atau tanggal diterima.
4. Admin mengisi daftar barang.
5. Untuk pembelian, admin mengisi harga satuan, diskon, dan biaya lainnya.
6. Tombol `Hitung Alokasi` memanggil `PerolehanBarangAllocator::allocate()`.
7. Setelah simpan:
   - detail aset membuat record Barang Kantor aset.
   - detail BHP menambah stok atau membuat BHP baru.
   - jurnal umum dibuat oleh `PerolehanBarang::syncJurnalUmum()`.

Perhitungan perolehan:

- `total_harga = jumlah_perolehan * harga_satuan`.
- `subtotal_barang = jumlah seluruh total_harga`.
- `alokasi_diskon = diskon_total * total_harga_item / subtotal_barang`.
- `alokasi_biaya_lainnya = biaya_lainnya_total * total_harga_item / subtotal_barang`.
- `total_harga_perolehan = total_harga - alokasi_diskon + alokasi_biaya_lainnya`.
- `harga_perolehan = total_harga_perolehan / jumlah`.
- `grand_total = jumlah seluruh total_harga_perolehan`.

Catatan pembulatan:

- `PerolehanBarangAllocator` memakai pembulatan `round(..., PHP_ROUND_HALF_UP)`.
- `grand_total` diambil dari jumlah `total_harga_perolehan` agar jurnal debit/kredit seimbang dengan tampilan.

Pembuatan Barang Kantor dari detail:

- Jika detail `aset`:
  - Membuat Barang Kantor sebanyak `jumlah_perolehan`.
  - Kode barang: `ASET-00001`, dst.
  - Harga perolehan per unit dibagi dari total detail.
  - Barang otomatis membuat record penyusutan.
- Jika detail `bhp`:
  - Mencari BHP dengan nama yang sama.
  - Jika ada, stok ditambah.
  - Jika belum ada, dibuat kode `BHP-00001`, dst.
  - Satuan stok mengikuti satuan perolehan tanpa konversi.

Jurnal otomatis perolehan:

- Debit:
  - Aset Sarana Pendidikan Laboratorium -> COA `Sarana Pendidikan Laboratorium`.
  - Aset Inventaris Kantor -> COA `Inventaris Kantor`.
  - Aset Kendaraan -> COA `Kendaraan Bermotor`.
  - BHP ATK -> COA `Beban ATK Operasional`.
  - BHP Inventaris Kantor -> COA `BPP Inventaris Kantor`.
- Kredit:
  - Pembelian -> `Kas Kecil`.
  - Hibah Barang -> `Penerimaan Hibah Barang`.
  - Hibah Uang -> `Kas Bank Hibah` atau akun bank dari Pendapatan Hibah.

Fungsi jurnal di `PerolehanBarang`:

- `syncJurnalUmum()`: hapus jurnal lama, hitung ulang nominal, buat jurnal baru.
- `assetAccountNameForJenis()`: menentukan akun debit aset.
- `bhpAccountNameForJenis()`: menentukan akun debit BHP.

Validasi saldo kas kecil:

- `KasKecilBalance::available()` menghitung saldo Kas Kecil dari saldo awal COA + jurnal debit - jurnal kredit.
- Jika grand total pembelian melebihi kas kecil, form menolak penyimpanan.

### 4.4 Penyusutan Aset Tetap

File:

- `app/Models/PenyusutanAsetTetap.php`
- `app/Models/PenyusutanDetail.php`
- `app/Filament/Admin/Resources/Penyusutans/PenyusutanResource.php`
- `app/Filament/Admin/Resources/Penyusutans/Tables/PenyusutansTable.php`
- `app/Filament/Admin/Resources/Penyusutans/Pages/ListPenyusutans.php`
- `app/Filament/Admin/Resources/Penyusutans/Pages/PenyusutanKartuPage.php`
- `resources/views/filament/admin/resources/penyusutans/kartu.blade.php`
- `resources/views/filament/admin/resources/penyusutans/kartu-pdf.blade.php`

Alur data:

1. Saat Barang Kantor kategori aset dibuat, sistem otomatis membuat `PenyusutanAsetTetap`.
2. Penyusutan belum membuat jurnal bulanan sampai admin menjalankan `Proses Akhir Periode`.
3. Admin memilih bulan dan tahun.
4. Sistem hanya memproses aset yang:
   - status barang `Aktif`.
   - status penggunaan `siap_digunakan`.
   - punya `tanggal_diterima`.
   - periode target sudah boleh diproses.
   - periode target berada dalam umur ekonomis aset.
   - belum pernah diproses untuk periode tersebut.

Rumus penyusutan:

- `beban_penyusutan_bulanan = (harga_perolehan - nilai_residu) / (umur_ekonomis_tahun * 12)`.

Aturan mulai penyusutan:

- Fungsi: `PenyusutanAsetTetap::bulanMulaiPenyusutanDariTanggal()`.
- Jika tanggal diterima tanggal 1 sampai 15: mulai bulan itu.
- Jika tanggal diterima di atas tanggal 15: mulai bulan berikutnya.

Proses akhir periode:

- File: `ListPenyusutans::postingPeriode()`.
- Membuat `JurnalUmum` tipe `penyusutan`.
- Membuat dua `JurnalDetail`:
  - Debit `5611104` Beban Penyusutan.
  - Kredit `1264101` Akumulasi Penyusutan.
- Membuat `PenyusutanDetail` berisi:
  - periode.
  - beban penyusutan bulanan.
  - akumulasi.
  - nilai buku.
  - referensi jurnal.

Keterangan kelengkapan:

- Fungsi: `buildKeteranganKelengkapan()`.
- `Belum Siap Digunakan`: aset belum punya tanggal diterima.
- `Belum Waktunya`: belum masuk periode wajib penyusutan.
- `Lengkap`: semua periode wajib sudah ada detail penyusutannya.
- `Bolong: ...`: ada bulan wajib yang belum diproses.

### 4.5 Jurnal Umum

File:

- `app/Models/JurnalUmum.php`
- `app/Models/JurnalDetail.php`
- `app/Filament/Admin/Resources/JurnalUmums/JurnalUmumResource.php`
- `app/Filament/Admin/Resources/JurnalUmums/Widgets/JurnalUmumOverview.php`
- `resources/views/filament/admin/resources/jurnal-umums/widgets/jurnal-umum-overview.blade.php`
- `resources/views/filament/admin/resources/jurnal-umums/widgets/jurnal-umum-pdf.blade.php`

Fungsi:

- Menampilkan jurnal otomatis dari:
  - Pendapatan Hibah.
  - Pengisian Kas Kecil.
  - Perolehan Barang.
  - Penyusutan.

Relasi:

- `JurnalUmum` hasMany `JurnalDetail`.
- `JurnalUmum` belongsTo `PerolehanBarang`.
- `JurnalUmum` belongsTo `PenyusutanAsetTetap`.
- `JurnalUmum` belongsTo `PengisianKasKecil`.
- `JurnalUmum` belongsTo `PendapatanHibah`.
- `JurnalDetail` belongsTo `JurnalUmum`.
- `JurnalDetail` belongsTo `Coa`.

Fungsi penting:

- `JurnalUmum::getReffTransaksiAttribute()`: menentukan nomor bukti yang ditampilkan.
- `JurnalUmum::referensi()`: menentukan relasi berdasarkan `tipe_transaksi`.
- Global scope `ref_exists`: menjaga jurnal perolehan tidak tampil jika referensi perolehannya tidak ada.
- `JurnalDetail::saving()`: memastikan nilai debit/kredit valid.

### 4.6 Buku Besar

File:

- `app/Filament/Admin/Resources/BukuBesars/BukuBesarResource.php`
- `app/Filament/Admin/Resources/BukuBesars/Widgets/BukuBesarTableOverview.php`
- `resources/views/filament/admin/resources/buku-besars/widgets/buku-besar-overview.blade.php`
- `resources/views/filament/admin/resources/buku-besars/widgets/buku-besar-pdf-layout.blade.php`

Fungsi:

- Menampilkan mutasi per akun COA.
- Bisa filter periode dan akun.
- Bisa cetak PDF.

Alur perhitungan:

1. `filter()` mengambil periode awal dan akhir.
2. Sistem mengambil daftar COA.
3. Untuk setiap COA, sistem menghitung saldo awal dari `jumlah_saldo` + transaksi sebelum periode.
4. Sistem mengambil jurnal dalam periode.
5. Tampilan memecah jurnal menjadi baris buku besar memakai `ledgerRowsForDetail()`.
6. Saldo berjalan dihitung berdasarkan saldo normal COA.

Fungsi penting:

- `filter()`: mengambil data jurnal dan saldo awal.
- `updatedPeriodeAwal()`: menjaga periode akhir tidak lebih kecil dari periode awal.
- `cetakLaporan()`: membuat PDF dengan DomPDF.
- `ledgerRowsForDetail()`: menentukan akun lawan dan membagi nominal jika lawan akun lebih dari satu.
- `isNormalKredit()`: menentukan akun normal kredit berdasarkan kolom `saldo` COA.
- `distributeAmount()`: membagi nominal lawan akun agar total tetap seimbang.

## 5. Pengajuan Anggota dan Approval Admin

### 5.1 Peminjaman Barang

File:

- `app/Models/PeminjamanBarang.php`
- `app/Http/Controllers/PeminjamanBarangController.php`
- `app/Filament/Admin/Resources/PeminjamanBarangs/PeminjamanBarangResource.php`
- `app/Filament/Admin/Resources/PeminjamanBarangs/Tables/PeminjamanBarangsTable.php`
- `resources/views/peminjaman-barang/index.blade.php`
- `resources/views/peminjaman-barang/riwayat.blade.php`

Alur anggota:

1. Anggota membuka halaman peminjaman.
2. Controller menampilkan barang yang bisa dipinjam.
3. Anggota memilih barang, jumlah, tanggal pinjam/kembali, alasan, dan bukti.
4. Data disimpan dengan status `pending`.

Alur admin:

1. Admin melihat daftar pengajuan di Filament.
2. Admin bisa setujui atau tolak.
3. Jika setujui:
   - status berubah `disetujui`.
   - stok barang dikurangi oleh `BarangKantor::markAsBorrowed()`.
4. Jika user mengembalikan:
   - status menjadi `menunggu_verifikasi_pengembalian`.
   - setelah admin verifikasi, stok dikembalikan oleh `markAsReturned()`.

Status:

- `pending`
- `disetujui`
- `menunggu_verifikasi_pengembalian`
- `kembali`
- `ditolak`
- `expired`

### 5.2 Pemakaian BHP

File:

- `app/Models/PemakaianBHP.php`
- `app/Http/Controllers/PemakaianBHPController.php`
- `app/Filament/Admin/Resources/Pemakaianbhps/PemakaianbhpResource.php`
- `app/Filament/Admin/Resources/Pemakaianbhps/Tables/PemakaianbhpsTable.php`
- `resources/views/pemakaian/index.blade.php`
- `resources/views/pemakaian/riwayat.blade.php`

Alur:

1. Anggota memilih BHP yang stoknya tersedia.
2. Satuan pemakaian mengikuti satuan utama barang kantor.
3. Tidak ada konversi satuan; jika barang stoknya Kotak, pemakaian juga Kotak.
4. Pengajuan disimpan dengan status `pending`.
5. Admin memproses pengajuan di Filament.
6. Jika disetujui, stok BHP dikurangi.

Fungsi penting:

- `PemakaianBHP::barang()`: relasi ke Barang Kantor.
- `PemakaianBHP::user()`: relasi ke User.
- scope `byStatus`, `byDateRange`, `forUser`.
- event `saving`: mengisi `nama_barang` dari `kode_barang`.

### 5.3 Pengajuan Pembelian Barang

File:

- `app/Models/PengajuanPembelianBarang.php`
- `app/Http/Controllers/PembelianBarangController.php`
- `app/Filament/Admin/Resources/PengajuanPembelianBarangs/PengajuanPembelianBarangResource.php`
- `app/Filament/Admin/Resources/PengajuanPembelianBarangs/Tables/PengajuanPembelianBarangsTable.php`
- `resources/views/pembelian/index.blade.php`
- `resources/views/pembelian/riwayat.blade.php`

Alur:

1. Anggota mengajukan barang yang ingin dibeli.
2. Input berisi nama barang, jumlah, kategori barang, perkiraan harga, link barang, alasan, dan bukti pendukung.
3. Controller menghitung `sub_total = perkiraan_harga * jumlah`.
4. Data tersimpan di tabel `pembelian_barangs`.
5. Admin melihat dan memproses pengajuan dari Filament.

## 6. Laporan

### Laporan yang ada

- Jurnal Umum: `JurnalUmumOverview` dan PDF.
- Buku Besar: `BukuBesarTableOverview` dan PDF.
- Kartu Penyusutan: `PenyusutanKartuPage`.
- Laporan Peminjaman: `ListPeminjamanBarangs` dan PDF view.
- Laporan Pemakaian BHP: `ListPemakaianbhps` dan PDF view.
- Laporan Pengajuan Pembelian: `ListPengajuanPembelianBarangs` dan PDF view.

### Prinsip laporan

- Laporan admin umumnya mengambil data dari model utama dengan relasi.
- PDF memakai `Barryvdh\DomPDF\Facade\Pdf`.
- Jurnal dan Buku Besar bukan input manual utama, melainkan hasil sinkronisasi transaksi.

## 7. Relasi Utama Antar Tabel

Relasi inti aplikasi:

- `users.id` -> `peminjaman_barangs.user_id`
- `users.id` -> `pemakaian_bhp.user_id`
- `users.id` -> `pembelian_barangs.user_id`
- `kategori_aset_tetap.id_kategori_aset` -> `barang_kantors.kategori_aset_id`
- `kategori_aset_tetap.id_kategori_aset` -> `perolehan_barang_detail.kategori_aset_id`
- `perolehan_barang.id_perolehan_barang` -> `perolehan_barang_detail.perolehan_barang_id`
- `pendapatan_hibah.no_hibah` -> `perolehan_barang.pendapatan_hibah_id`
- `perolehan_barang_detail.id` -> `barang_kantors.perolehan_barang_detail_id`
- `barang_kantors.id` -> `penyusutan_aset_tetap.barang_kantor_id`
- `penyusutan_aset_tetap.id_penyusutan` -> `penyusutan_details.penyusutan_id`
- `jurnals.id_jurnal_umum` -> `jurnal_details.id_jurnal_umum`
- `coa.kode_akun` -> `jurnal_details.kode_akun`
- `jurnals.reff_perolehan_barang` -> `perolehan_barang.id_perolehan_barang`
- `jurnals.reff_penyusutan` -> `penyusutan_aset_tetap.id_penyusutan`
- `jurnals.reff_pengisian_kas_kecil` -> `pengisian_kas_kecil.no_transaksi`
- `jurnals.reff_pendapatan_hibah` -> `pendapatan_hibah.no_hibah`

## 8. Urutan Ideal Pemakaian Aplikasi

1. Buat User.
2. Buat COA yang dibutuhkan.
3. Buat Kategori Aset Tetap.
4. Buat Barang Kantor manual jika diperlukan, atau biarkan dibuat otomatis dari Perolehan Barang.
5. Isi Pengisian Kas Kecil jika akan melakukan Perolehan Pembelian.
6. Isi Pendapatan Hibah jika akan melakukan Perolehan Hibah Uang.
7. Input Perolehan Barang.
8. Sistem otomatis:
   - membuat Barang Kantor,
   - menambah stok BHP,
   - membuat data Penyusutan untuk aset,
   - membuat Jurnal Umum.
9. Pada akhir bulan, admin menjalankan Proses Akhir Periode Penyusutan.
10. Sistem membuat Jurnal Penyusutan dan Detail Penyusutan.
11. Laporan Jurnal Umum dan Buku Besar dapat dilihat/dicetak.
12. Anggota dapat mengajukan peminjaman, pemakaian BHP, dan pembelian barang.
13. Admin memproses pengajuan di menu Pengajuan.

## 9. Fungsi Support

### `PerolehanBarangAllocator`

File: `app/Support/PerolehanBarangAllocator.php`

- `allocate()`: menghitung alokasi diskon/biaya lain, harga perolehan, total harga perolehan, dan grand total.
- `calculationSignature()`: membuat tanda perhitungan untuk mendeteksi perubahan input.
- `normalizeNumber()`: membersihkan input rupiah/angka menjadi integer.
- `normalizeSignedNumber()`: mendukung angka negatif jika dibutuhkan.
- `distributeAmount()`: pembagian nominal proporsional.
- `distributeSignedAmount()`: pembagian nominal bertanda negatif/positif.

### `KasKecilBalance`

File: `app/Support/KasKecilBalance.php`

- `available()`: menghitung saldo Kas Kecil dari COA dan jurnal.
- Dipakai untuk mencegah perolehan pembelian melebihi saldo kas kecil.

### `JurnalAutoSyncService`

File: `app/Support/JurnalAutoSyncService.php`

- `syncAll()`: menjalankan sinkronisasi semua jurnal.
- `syncPerolehanBarangJournals()`: sinkron jurnal perolehan.
- `syncPenyusutanJournals()`: sinkron jurnal penyusutan.

## 10. Kesimpulan Alur Besar

Alur inti aplikasi adalah:

Master Data COA/Kategori/Barang/User -> Transaksi Perolehan/Pendapatan/Pengisian -> Barang Kantor dan Stok ter-update -> Penyusutan terbentuk untuk aset -> Jurnal Umum otomatis -> Buku Besar membaca jurnal -> Laporan ditampilkan atau dicetak.

Pengajuan anggota berjalan paralel:

User login sebagai anggota -> mengajukan peminjaman/pemakaian/pembelian -> admin memproses di Filament -> stok/status barang berubah sesuai keputusan.

