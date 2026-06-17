# Kamus Fungsi dan Alur Kode Aplikasi

Dokumen ini dibuat supaya kamu bisa menjawab pertanyaan dosen penguji tentang kode: function apa yang dipakai, gunanya apa, alurnya bagaimana, dan kalau ingin mengubah fitur harus edit bagian mana.

## 1. Pola Kode yang Dipakai

### Model

Lokasi: `app/Models`

Model adalah penghubung antara kode Laravel dengan tabel database.

Bagian yang sering muncul:

- `protected $table`: menentukan nama tabel database.
- `protected $primaryKey`: menentukan primary key jika bukan `id`.
- `$incrementing = false`: dipakai jika primary key berbentuk string, misalnya `PRL-PB-0001`.
- `protected $fillable`: daftar kolom yang boleh diisi lewat form.
- `protected $casts`: mengubah tipe data otomatis, misalnya tanggal atau integer.
- `belongsTo()`: relasi banyak data menuju satu data induk.
- `hasMany()`: relasi satu data punya banyak data anak.
- `hasOne()`: relasi satu data punya satu data lain.
- `booted()`: event otomatis saat model dibuat, disimpan, dihapus.
- `saving`, `creating`, `created`, `saved`, `deleting`: lifecycle event Eloquent.

### Filament Resource

Lokasi: `app/Filament/Admin/Resources`

Resource menghubungkan model dengan halaman admin Filament.

Function umum:

- `form(Schema $schema)`: menentukan form create/edit.
- `table(Table $table)`: menentukan tabel daftar data.
- `infolist(Schema $schema)`: menentukan tampilan detail.
- `getPages()`: menentukan route halaman index/create/edit/view.
- `canCreate()`, `canEdit()`: mengatur boleh tidaknya aksi.

### Schema/Form

Lokasi umum: `app/Filament/Admin/Resources/*/Schemas`

Schema berisi field input.

Method yang sering dipakai:

- `TextInput::make()`: input teks/angka.
- `Select::make()`: dropdown.
- `DatePicker::make()`: input tanggal.
- `FileUpload::make()`: upload file.
- `Repeater::make()`: input daftar item berulang.
- `Hidden::make()`: input tersembunyi.
- `Placeholder::make()`: teks/catatan dalam form.
- `->required()`: wajib diisi.
- `->visible(fn ...)`: tampil jika kondisi benar.
- `->disabled(fn ...)`: tidak bisa diedit jika kondisi benar.
- `->dehydrated()`: nilai field ikut dikirim ke database.
- `->live()` atau `->reactive()`: field berubah langsung memicu update field lain.
- `->afterStateUpdated()`: aksi setelah nilai field berubah.
- `->afterStateHydrated()`: aksi saat form mengambil data dari database.
- `->rule()`: validasi custom.
- `->validationMessages()`: pesan error custom.

### Table

Lokasi umum: `app/Filament/Admin/Resources/*/Tables`

Table mengatur daftar data admin.

Method yang sering dipakai:

- `TextColumn::make()`: kolom teks.
- `ImageColumn::make()`: kolom gambar.
- `BadgeColumn`/`->badge()`: tampilan status.
- `->searchable()`: kolom bisa dicari.
- `->sortable()`: kolom bisa diurutkan.
- `->formatStateUsing()`: format tampilan data.
- `->getStateUsing()`: ambil nilai tampilan custom.
- `Action::make()`: tombol aksi custom.
- `ViewAction`, `EditAction`, `DeleteAction`: aksi bawaan Filament.
- `->filters()`: filter tabel.

### Controller

Lokasi: `app/Http/Controllers`

Controller dipakai untuk halaman anggota/non-admin.

Method umum:

- `index()`: menampilkan halaman utama.
- `store()`: menyimpan data baru.
- `riwayatSemua()`: menampilkan riwayat.
- `approve()`: menyetujui pengajuan.
- `kembalikan()`: proses pengembalian barang.

## 2. Login dan Session

### `AuthController`

File: `app/Http/Controllers/AuthController.php`

#### `showLoginForm(Request $request)`

Gunanya:

- Menampilkan halaman login.
- Jika user sudah login, langsung diarahkan:
  - admin ke `/admin`.
  - anggota ke `/dashboard`.
- Jika role tidak valid, user dilogout.
- Memberi header `Cache-Control` agar halaman login tidak disimpan cache browser.

Kalau ingin mengubah redirect setelah login:

- Ubah bagian kondisi `user_group === 'admin'` atau `user_group === 'anggota'`.

#### `login(Request $request)`

Gunanya:

- Validasi username dan password.
- Login menggunakan `Auth::attempt()`.
- Regenerate session agar aman.
- Menentukan redirect berdasarkan role.
- Mengembalikan response JSON jika login dari JavaScript.

Alur:

1. Validasi input.
2. Buat credentials dari username/password.
3. Jika cocok, session dibuat ulang.
4. Ambil user login.
5. Jika admin redirect `/admin`.
6. Jika anggota redirect `/dashboard`.
7. Jika role tidak dikenal, logout.

Kalau ingin menambah role baru:

- Tambahkan kondisi baru di `login()`.
- Tambahkan juga di `showLoginForm()`.
- Tambahkan middleware/route sesuai role.

#### `logout(Request $request)`

Gunanya:

- Logout user.
- Invalidate session.
- Regenerate token CSRF.
- Redirect ke login.

#### `sessionUser(Request $request)`

Gunanya:

- Endpoint untuk mengecek session aktif.
- Dipakai oleh `tab-auth-guard.blade.php`.
- Mengembalikan `authenticated`, `user_id`, dan `user_group`.

#### `ubahpassword()` dan `prosesubahpassword()`

Gunanya:

- Menampilkan form ubah password.
- Menyimpan password baru dengan `Hash::make()`.

### `App\Filament\Auth\Login`

File: `app/Filament/Auth/Login.php`

Fungsi:

- Custom login Filament agar tetap diarahkan ke halaman login utama.
- Mengatur field login memakai username, bukan email.

### `tab-auth-login-script.blade.php`

Gunanya:

- Login via fetch/AJAX.
- Menyimpan token tab di `sessionStorage`.
- Menyimpan user id tab di `sessionStorage`.
- Menangani remember username.
- Mencegah bug back browser setelah login.

### `tab-auth-guard.blade.php`

Gunanya:

- Mengecek apakah tab masih sesuai dengan user yang login.
- Jika ada tab berbeda akun, sistem minta re-login tab.
- Menggunakan endpoint `auth.session-user`.

### `admin-session-timeout.blade.php` dan `anggota-session-timeout.blade.php`

Gunanya:

- Menghitung aktivitas terakhir user.
- Jika tidak aktif selama `SESSION_LIFETIME`, logout otomatis.
- Saat ini `.env` memakai `SESSION_LIFETIME=45`.

## 3. User

### Model `User`

File: `app/Models/User.php`

Fungsi penting:

#### `casts()`

Gunanya:

- Mengatur `password` otomatis di-hash.
- Mengatur tipe data field lain.

#### `canAccessPanel(Panel $panel)`

Gunanya:

- Menentukan user mana yang boleh akses Filament Admin.
- Umumnya hanya `user_group = admin`.

Kalau admin tidak bisa masuk panel:

- Cek function ini.
- Cek nilai `user_group` di database.

### `UserResource`

File:

- `app/Filament/Admin/Resources/Users/UserResource.php`
- `Schemas/UserForm.php`
- `Tables/UsersTable.php`

Function:

- `form()`: memanggil `UserForm`.
- `table()`: memanggil `UsersTable`.
- `infolist()`: memanggil `UserInfolist`.
- `getPages()`: route daftar, tambah, lihat.

### `CreateUser::mutateFormDataBeforeCreate()`

Gunanya:

- Mengubah data sebelum disimpan.
- Biasanya untuk hash password atau default role.

Kalau ingin mengubah input user:

- Form field: `UserForm.php`.
- Tabel daftar: `UsersTable.php`.
- Simpan data sebelum create: `CreateUser.php`.

## 4. COA

### Model `Coa`

File: `app/Models/coa.php`

#### `jurnalDetails()`

Gunanya:

- Relasi COA ke detail jurnal.
- Menghubungkan `coa.kode_akun` dengan `jurnal_details.kode_akun`.

#### `isNormalDebit()`

Gunanya:

- Mengecek apakah akun normal debit berdasarkan header akun.
- Saat ini Buku Besar memakai helper sendiri `isNormalKredit()` agar mengikuti kolom `saldo`.

#### `boot()`

Gunanya:

- Menjalankan event model COA.

Isi penting:

- `deleting`: jika COA dihapus, jurnal yang memakai akun tersebut ikut dihapus agar tidak menggantung.
- `created`: memanggil `syncPendingTransactionJournals()`.
- `updating`: jika kode akun berubah, kode akun di detail jurnal ikut diubah.

#### `syncPendingTransactionJournals()`

Gunanya:

- Jika dulu transaksi dibuat saat COA belum lengkap, jurnalnya mungkin belum terbentuk.
- Saat COA baru dibuat, function ini mencoba membuat jurnal yang belum ada.

Yang disinkronkan:

- Perolehan Barang.
- Pendapatan Hibah.
- Pengisian Kas Kecil.

### `COAForm`

File: `app/Filament/Admin/Resources/COAS/Schemas/COAForm.php`

Function penting:

#### `configure(Schema $schema)`

Gunanya:

- Menentukan field input COA:
  - `header_akun`
  - `nama_akun`
  - `kode_akun`
  - `saldo`
  - `jumlah_saldo`

Alur:

1. Admin memilih `header_akun`.
2. Dropdown `nama_akun` berubah sesuai header.
3. Jika nama akun dipilih, kode akun dan saldo normal otomatis terisi dari `$map`.
4. Akun yang sudah dibuat akan disable/tidak bisa dipilih lagi.

Kalau ingin menambah akun COA baru:

- Tambah opsi di bagian `nama_akun`.
- Tambah mapping kode di `$map`.

#### `normalizeRupiah()`

Gunanya:

- Mengubah input rupiah seperti `1.000.000` menjadi integer `1000000`.

#### `formatRupiah()`

Gunanya:

- Menampilkan angka integer menjadi format Indonesia, contoh `1000000` jadi `1.000.000`.

#### `saldoNormalForHeader()`

Gunanya:

- Default saldo normal berdasarkan header:
  - Harta/Beban -> Debit.
  - Pendapatan -> Kredit.

## 5. Kategori Aset Tetap

### Model `KategoriAsetTetap`

File: `app/Models/KategoriAsetTetap.php`

#### `barangKantors()`

Gunanya:

- Relasi kategori aset ke banyak barang kantor.

#### `barangMasukDetails()`

Gunanya:

- Relasi kategori aset ke detail perolehan barang.

#### `boot()`

Gunanya:

- Membuat kode kategori otomatis saat data dibuat.

### `KategoriAsetForm`

Function utama:

- `configure()`: field input kategori aset.
- `afterStateUpdated()`: biasanya dipakai untuk update otomatis field tertentu saat input berubah.

Kalau ingin mengubah daftar/field kategori:

- Ubah `KategoriAsetForm.php`.
- Ubah tabel di `KategoriAsetsTable.php`.

## 6. Barang Kantor

### Model `BarangKantor`

File: `app/Models/BarangKantor.php`

#### `kategoriAset()`

Gunanya:

- Relasi barang kantor ke kategori aset tetap.

#### `penyusutans()`

Gunanya:

- Relasi aset ke data penyusutan.
- Satu barang aset bisa punya record penyusutan.

#### `perolehanBarangDetail()`

Gunanya:

- Mengetahui barang ini berasal dari detail perolehan mana.

#### `tanggalPembelianPerolehan()`

Gunanya:

- Mengambil tanggal pembelian dari transaksi perolehan asal.
- Dipakai untuk validasi agar `tanggal_diterima` tidak lebih awal dari tanggal pembelian.

#### `getBarcodeTargetUrlAttribute()`

Gunanya:

- Membuat URL tujuan ketika QR/barcode discan.
- URL menuju route detail barang: `barang.detail`.

#### `getBarcodeQrImageUrlAttribute()`

Gunanya:

- Membuat URL gambar QR.
- Menggunakan layanan QuickChart:
  - `https://quickchart.io/qr?text=...`

Jadi barcode di aplikasi berupa kode internal, sedangkan gambar QR dibuat dari URL QuickChart.

#### `isAvailableToBorrow(int $jumlah = 1)`

Gunanya:

- Mengecek apakah barang boleh dipinjam.
- Syarat umum:
  - status barang Aktif.
  - stok cukup.
- Jika aset:
  - harus siap digunakan.
  - status pinjam Tersedia.
- Jika BHP:
  - hanya BHP Inventaris Kantor yang boleh dipinjam.

#### `markAsBorrowed(int $jumlah = 1)`

Gunanya:

- Mengurangi stok saat peminjaman disetujui.
- Jika aset atau stok habis, status pinjam menjadi Dipinjam.

#### `markAsReturned(int $jumlah = 1)`

Gunanya:

- Menambah stok saat pengembalian diverifikasi.
- Mengembalikan status pinjam menjadi Tersedia jika barang masih Aktif.

#### `getKategoriBarangLabelAttribute()`

Gunanya:

- Menampilkan label `Aset Tetap` atau `Barang Habis Pakai`.

#### `getJenisBarangLabelAttribute()`

Gunanya:

- Menampilkan jenis barang sesuai kategori:
  - aset memakai `jenis_aset_label`.
  - BHP memakai ATK Operasional Kantor/BPP Inventaris Kantor.

#### `getJenisAsetLabelAttribute()`

Gunanya:

- Mengubah kode jenis aset menjadi label:
  - `sarana_pendidikan_laboratorium`
  - `inventaris_kantor`
  - `kendaraan`

#### `scopeBorrowableForPeminjaman($query)`

Gunanya:

- Filter barang yang boleh masuk daftar peminjaman.
- Aset boleh dipinjam.
- BHP yang boleh dipinjam hanya BPP Inventaris Kantor.

#### `syncAssetStatuses()`

Gunanya:

- Jika status barang berubah, status penyusutan ikut diperbarui.

#### `isSiapPakai()`

Gunanya:

- Mengecek aset sudah siap digunakan.
- Syarat:
  - `status_penggunaan = siap_digunakan`
  - `tanggal_diterima` terisi.

#### `syncPenyusutanData()`

Gunanya:

- Menyamakan data penyusutan dengan data barang.
- Jika nama barang, harga, nilai residu, umur ekonomis, atau tanggal diterima berubah, data penyusutan ikut update.

#### `booted()`

Gunanya:

- Menjalankan logika otomatis saat barang disimpan/dibuat.

Isi penting:

- `saving`:
  - Jika BHP, field aset dikosongkan.
  - Jika aset, field BHP dikosongkan.
  - Validasi tanggal diterima tidak boleh sebelum tanggal pembelian.
  - Aset Tidak Aktif tidak bisa diaktifkan lagi.
  - Status pinjam default Tersedia.
- `created`:
  - Membuat barcode `BRG-000001`.
  - Jika kategori aset, membuat data `PenyusutanAsetTetap`.
- `saved`:
  - Sinkron status aset.
  - Sinkron data penyusutan.

Kalau ingin ubah aturan barcode:

- Edit `BarangKantor::created()`.

Kalau ingin ubah aturan status siap digunakan:

- Edit `isSiapPakai()`, `saving`, dan form Barang Kantor.

### `BarangKantorResource`

Function:

- `form()`: memanggil `BarangKantorForm`.
- `infolist()`: memanggil `BarangKantorInfolist`.
- `table()`: memanggil `BarangKantorsTable`.
- `getGloballySearchableAttributes()`: field yang bisa dicari global.
- `getPages()`: route index/create/scan/edit.

Helper:

- `normalizeRupiah()`: format input rupiah ke integer.
- `formatRupiah()`: integer ke format rupiah.
- `nonNegativeNominalRule()`: validasi tidak boleh minus.
- `positiveNominalRangeRule()`: validasi harus lebih dari 0 dan maksimal 100 juta.
- `residualNotGreaterThanAcquisitionRule()`: nilai residu tidak boleh lebih besar dari harga perolehan.
- `isStatusPenggunaanLocked()`: status penggunaan terkunci jika sudah siap digunakan dan tanggal diterima terisi.

### `ScanBarangKantor`

File: `app/Filament/Admin/Resources/BarangKantors/Pages/ScanBarangKantor.php`

#### `submit()`

Gunanya:

- Menerima barcode/kode/nama dari input scanner.
- Mencari barang berdasarkan:
  - barcode
  - kode_barang
  - nama_barang
- Jika ketemu, redirect ke detail barang.

View scanner:

- `resources/views/filament/admin/resources/barang-kantors/pages/scan-barang-kantor.blade.php`
- Menggunakan JavaScript `Html5Qrcode`.

## 7. Pendapatan Hibah

### Model `PendapatanHibah`

#### `jurnal()`

Gunanya:

- Satu pendapatan hibah punya satu jurnal umum.

#### `perolehanBarangs()`

Gunanya:

- Satu pendapatan hibah bisa dipakai oleh banyak perolehan hibah uang.

#### `getDigunakanAttribute()`

Gunanya:

- Attribute virtual `digunakan`.
- Mengambil total dana hibah yang sudah digunakan.

#### `getSisaAttribute()`

Gunanya:

- Attribute virtual `sisa`.
- Rumus: `nilai_hibah - usedAmount()`.

#### `usedAmount(?string $excludePerolehanId = null)`

Gunanya:

- Menghitung total penggunaan dana hibah dari detail perolehan barang.
- Parameter `excludePerolehanId` dipakai saat edit agar transaksi yang sedang diedit tidak dihitung dua kali.

#### `syncJurnalUmum()`

Gunanya:

- Membuat ulang jurnal pendapatan hibah.
- Menghapus jurnal lama dulu.
- Membuat jurnal baru jika nilai hibah > 0.

Jurnal:

- Debit akun bank hibah.
- Kredit akun pendapatan hibah.

#### `booted()`

Isi:

- `creating`: membuat nomor hibah jika kosong, dan set jenis hibah `hibah_uang`.
- `saving`: mengisi akun default dan validasi COA wajib ada.
- `saved`: memanggil `syncJurnalUmum()`.
- `deleting`: menghapus jurnal terkait.

#### `generateNoHibah()`

Gunanya:

- Membuat nomor `PDH-0001`, `PDH-0002`, dst.

### `PendapatanHibahResource`

Helper:

- `normalizeRupiah()`
- `formatRupiah()`
- `coaDisplayName()`: tampilkan nama akun jika ada, jika belum ada tampilkan `Tambahkan akun COA`.
- `coaExists()`: cek akun ada di COA.
- `coaCode()`: ambil kode akun berdasarkan nama akun.

Kalau ingin ubah akun default hibah:

- Edit `PendapatanHibahForm.php`.
- Edit `PendapatanHibah::saving()`.

## 8. Pengisian Kas Kecil

### Model `PengisianKasKecil`

#### `jurnal()`

Gunanya:

- Relasi ke jurnal pengisian kas kecil.

#### `syncJurnalUmum()`

Gunanya:

- Membuat ulang jurnal pengisian kas kecil.

Jurnal:

- Debit `akun_kas_kecil`.
- Kredit `akun_sumber_dana`.

#### `booted()`

Isi:

- `creating`: membuat nomor transaksi jika kosong.
- `saved`: memanggil `syncJurnalUmum()`.
- `deleting`: menghapus jurnal terkait.

#### `generateNoTransaksi(mixed $tanggal)`

Gunanya:

- Membuat nomor `PKK-0001`, `PKK-0002`, dst.

### `PengisianKasKecilResource`

Helper:

- `normalizeRupiah()`
- `formatRupiah()`
- `kasKecilCode()`
- `kasPengeluaranInstitusiCode()`
- `coaDisplayName()`
- `coaExists()`

Kalau ingin ganti akun kas kecil:

- Edit helper `kasKecilCode()` atau form pengisian.

Kalau ingin ganti akun sumber dana:

- Edit `kasPengeluaranInstitusiCode()` dan form.

## 9. Perolehan Barang

### Model `PerolehanBarang`

#### `details()`

Gunanya:

- Relasi ke daftar barang pada transaksi perolehan.

#### `pendapatanHibah()`

Gunanya:

- Relasi ke sumber hibah uang.

#### `jurnal()`

Gunanya:

- Relasi ke jurnal umum transaksi perolehan.

#### `booted()`

Gunanya:

- Saat perolehan dihapus:
  - detail perolehan dihapus.
  - jurnal perolehan dihapus.

#### `isPembelian()`

Gunanya:

- Cek apakah sumber perolehan adalah pembelian.

#### `isHibah()`

Gunanya:

- Cek apakah sumber perolehan adalah hibah barang atau hibah uang.

#### `isHibahBarang()`

Gunanya:

- Cek khusus hibah barang.

#### `isHibahUang()`

Gunanya:

- Cek khusus hibah uang.

#### `getNilaiTransaksiAttribute()`

Gunanya:

- Attribute virtual `nilai_transaksi`.
- Untuk hibah mengambil `total_nilai_hibah`.
- Untuk pembelian mengambil `grand_total`.
- Jika kosong, fallback ke total detail.

#### `getTotalHargaPerolehanAttribute()`

Gunanya:

- Menjumlahkan semua `total_harga_perolehan` detail.

#### `getJumlahItemAttribute()`

Gunanya:

- Menghitung jumlah detail barang.

#### `syncJurnalUmum()`

Gunanya:

- Membuat jurnal otomatis perolehan barang.

Alur function:

1. Load detail.
2. Hitung subtotal, total nilai perolehan, diskon, biaya lain, grand total.
3. Untuk hibah:
   - grand total dibuat 0.
   - total nilai hibah diisi.
4. Untuk pembelian:
   - grand total dipakai sebagai total dibayar.
5. Jika data total di model beda, update dengan `saveQuietly()`.
6. Hapus jurnal lama.
7. Tentukan akun kredit:
   - Hibah Barang -> Penerimaan Hibah Barang.
   - Hibah Uang -> Kas Bank Hibah.
   - Pembelian -> Kas Kecil.
8. Kelompokkan detail ke akun debit berdasarkan jenis aset/BHP.
9. Jika ada selisih pembulatan, selisih dimasukkan ke akun debit terbesar.
10. Buat jurnal umum.
11. Buat detail debit.
12. Buat detail kredit.

#### `assetAccountNameForJenis(?string $jenisAset)`

Gunanya:

- Menentukan nama akun debit aset:
  - Sarana Pendidikan Laboratorium.
  - Inventaris Kantor.
  - Kendaraan Bermotor.

#### `bhpAccountNameForJenis(?string $jenisBhp)`

Gunanya:

- Menentukan nama akun debit BHP:
  - BPP Inventaris Kantor.
  - Beban ATK Operasional.

Kalau jurnal perolehan salah akun:

- Cek `assetAccountNameForJenis()`.
- Cek `bhpAccountNameForJenis()`.
- Cek COA sudah ada atau belum.

### Model `PerolehanBarangDetail`

#### `satuanPerolehanOptions()`

Gunanya:

- Daftar satuan perolehan:
  - Pcs
  - Unit
  - Pack
  - Kotak
  - Rim

#### `convertToPcs()`

Gunanya:

- Saat ini tidak melakukan konversi.
- Jumlah yang masuk = jumlah yang diinput.
- Ini sesuai aturan: sistem tidak mengubah 1 Kotak menjadi beberapa Pcs.

#### `resolveBarangKantorSatuan()`

Gunanya:

- Menentukan satuan stok barang kantor mengikuti satuan perolehan.

#### `booted()`

Isi:

- `saving`:
  - Melengkapi nama barang BHP dari master barang.
  - Membersihkan field yang tidak sesuai kategori.
  - Menghitung default total harga jika kosong.
- `created`:
  - Jika aset, membuat Barang Kantor aset sebanyak jumlah.
  - Jika BHP, menambah stok barang atau membuat BHP baru.
- `updated`:
  - Jika BHP, update stok sesuai selisih jumlah.
  - Jika aset, tambah/kurangi/update data aset turunan.
- `deleting`:
  - Jika BHP, stok dikurangi atau barang dihapus jika stok habis.
  - Jika aset, barang aset dan penyusutannya dihapus.

#### `distributeUnitPrices(int $total, int $jumlah)`

Gunanya:

- Membagi total harga perolehan ke setiap unit aset.
- Jika tidak habis dibagi, sisa pembulatan disebar ke unit awal.

#### `tanggalSiapPakaiAset()`

Gunanya:

- Jika status belum siap digunakan, tanggal diterima aset dibuat null.
- Jika siap digunakan, tanggal diterima mengikuti perolehan.

#### `statusSiapPakaiAset()`

Gunanya:

- Status aset mengikuti `status_penggunaan` transaksi perolehan.

### `PerolehanBarangResource`

Helper penting:

- `generatePerolehanId()`: nomor `PRL-PB`, `PRL-HU`, `PRL-HB`.
- `isPembelianSource()`: cek pembelian.
- `isHibahSource()`: cek hibah barang/uang.
- `isHibahUangSource()`: cek hibah uang.
- `sumberPerolehanOptions()`: opsi sumber.
- `statusPenggunaanOptions()`: opsi status penggunaan.
- `kategoriBarangOptions()`: opsi aset/BHP.
- `jenisAsetOptions()`: opsi jenis aset.
- `jenisBhpOptions()`: opsi jenis BHP.
- `satuanBarangOptions()`: opsi satuan.
- `statusPenggunaanLabel()`: label status.
- `fillPendapatanHibahInfo()`: mengisi data sumber hibah.
- `pendapatanHibahOptions()`: daftar hibah uang yang tanggalnya <= tanggal pembelian.
- `isPendapatanHibahAvailableForTanggal()`: validasi tanggal hibah tidak boleh setelah tanggal pembelian.
- `parseTanggalPerolehan()`: parsing tanggal aman.

### `PerolehanBarangAllocator`

File: `app/Support/PerolehanBarangAllocator.php`

#### `allocate(array $data)`

Gunanya:

- Fungsi utama perhitungan perolehan.

Alur:

1. Normalisasi jumlah dan harga satuan.
2. Hitung `total_harga`.
3. Hitung subtotal semua barang.
4. Ambil diskon dan biaya lainnya.
5. Hitung alokasi diskon per item secara proporsional.
6. Hitung alokasi biaya lainnya per item secara proporsional.
7. Hitung total final item.
8. Hitung harga perolehan per unit.
9. Hitung grand total dari semua total final item.

#### `calculationSignature(array $data)`

Gunanya:

- Membuat hash dari input perhitungan.
- Berguna untuk mendeteksi apakah input berubah.

#### `normalizeNumber(mixed $value)`

Gunanya:

- Mengubah angka/rupiah string menjadi integer.

#### `normalizeSignedNumber(mixed $value)`

Gunanya:

- Sama seperti normalize, tetapi mendukung angka negatif.

#### `distributeAmount()`

Gunanya:

- Membagi nominal berdasarkan bobot.
- Dipakai jika perlu pembagian proporsional.

#### `distributeSignedAmount()`

Gunanya:

- Membagi nominal bertanda plus/minus.

## 10. Penyusutan

### Model `PenyusutanAsetTetap`

#### `booted()`

Isi:

- `deleting`: hapus semua detail penyusutan agar jurnal detail ikut terhapus.
- `creating`:
  - Generate nomor `PST-0001`.
  - Ambil data aset dari Barang Kantor.
  - Hitung beban penyusutan bulanan.
- `saving`:
  - Sinkron data penyusutan dari Barang Kantor.
- `created`:
  - Sinkron keterangan kelengkapan.

#### `barangKantor()`

Gunanya:

- Relasi penyusutan ke barang kantor.

#### `details()`

Gunanya:

- Relasi ke detail penyusutan per bulan.

#### `jurnal()`

Gunanya:

- Relasi ke jurnal penyusutan.

#### `bulanAkhirUmurEkonomis()`

Gunanya:

- Menghitung bulan terakhir aset boleh disusutkan.
- Rumus: bulan mulai + total bulan umur ekonomis - 1.

#### `bulanMulaiPenyusutan()`

Gunanya:

- Mengambil bulan mulai berdasarkan `tanggal_diterima`.

#### `bulanMulaiPenyusutanDariTanggal()`

Gunanya:

- Aturan cut-off tanggal 15.
- Tanggal <= 15 mulai bulan itu.
- Tanggal > 15 mulai bulan berikutnya.

#### `isAktif()`

Gunanya:

- Aset boleh diproses penyusutan jika status penyusutan Aktif dan siap pakai.

#### `isSiapPakai()`

Gunanya:

- Cek status penggunaan siap dan tanggal diterima ada.

#### `formatIdNumber()` dan `extractNumericPart()`

Gunanya:

- Membuat dan membaca nomor penyusutan `PST-0001`.

#### `syncTotalBiayaPenyusutan()`

Gunanya:

- Menjumlah semua detail penyusutan ke total biaya penyusutan.

#### `syncKeteranganKelengkapan()`

Gunanya:

- Update status kelengkapan penyusutan.

#### `buildKeteranganKelengkapan()`

Gunanya:

- Menentukan apakah penyusutan:
  - Belum Siap Digunakan.
  - Belum Waktunya.
  - Lengkap.
  - Bolong bulan tertentu.

#### `batasPeriodeTertutup()`

Gunanya:

- Menentukan sampai bulan mana sistem wajib mengecek kelengkapan.
- Jika hari ini belum akhir bulan, bulan berjalan belum dianggap wajib.

#### `formatRentangBulanBolong()` dan `formatSatuRentangBulan()`

Gunanya:

- Menampilkan bulan yang belum disusutkan dalam format mudah dibaca.

### `ListPenyusutans`

File: `app/Filament/Admin/Resources/Penyusutans/Pages/ListPenyusutans.php`

#### `getHeaderActions()`

Gunanya:

- Membuat tombol `Proses Akhir Periode`.
- Form memilih bulan dan tahun.

#### `periodeSudahBolehDiposting(Carbon $targetEnd)`

Gunanya:

- Validasi penyusutan hanya bisa diproses pada atau setelah akhir bulan.

#### `postingPeriode(int $bulan, int $tahun)`

Gunanya:

- Proses inti penyusutan bulanan.

Alur:

1. Tentukan awal dan akhir bulan target.
2. Ambil semua data penyusutan aset.
3. Lewati aset yang belum aktif/siap.
4. Lewati jika periode sudah pernah diposting.
5. Lewati jika sebelum bulan mulai penyusutan.
6. Lewati jika lewat umur ekonomis.
7. Buat jurnal umum penyusutan.
8. Buat jurnal detail debit Beban Penyusutan.
9. Buat jurnal detail kredit Akumulasi Penyusutan.
10. Hitung akumulasi baru.
11. Buat `PenyusutanDetail`.

Kalau jurnal penyusutan salah akun:

- Ubah kode akun di `postingPeriode()`:
  - `5611104`
  - `1264101`

## 11. Jurnal Umum

### Model `JurnalUmum`

#### `booted()`

Gunanya:

- `deleting`: jika jurnal dihapus, detail jurnal ikut dihapus.
- global scope `ref_exists`: menjaga jurnal perolehan hanya tampil jika perolehan masih ada.

#### `details()`

Gunanya:

- Relasi jurnal ke detail jurnal.

#### `perolehanBarang()`, `penyusutan()`, `pengisianKasKecil()`, `pendapatanHibah()`

Gunanya:

- Relasi jurnal ke transaksi asal.

#### `getReffTransaksiAttribute()`

Gunanya:

- Menentukan nomor referensi yang ditampilkan di jurnal:
  - penyusutan
  - perolehan
  - pengisian kas kecil
  - pendapatan hibah

#### `referensi()`

Gunanya:

- Mengembalikan relasi sesuai `tipe_transaksi`.

### Model `JurnalDetail`

Function penting:

- `booted()`: validasi debit/kredit saat detail disimpan.
- `jurnalUmum()`: relasi ke jurnal umum.
- `coa()`: relasi ke COA.

Kalau nominal jurnal salah:

- Untuk perolehan: cek `PerolehanBarang::syncJurnalUmum()`.
- Untuk pendapatan hibah: cek `PendapatanHibah::syncJurnalUmum()`.
- Untuk pengisian kas kecil: cek `PengisianKasKecil::syncJurnalUmum()`.
- Untuk penyusutan: cek `ListPenyusutans::postingPeriode()`.

## 12. Buku Besar

### `BukuBesarTableOverview`

File: `app/Filament/Admin/Resources/BukuBesars/Widgets/BukuBesarTableOverview.php`

#### `mount()`

Gunanya:

- Saat widget dibuka, set periode default dan panggil `filter()`.

#### `hydrate()`

Gunanya:

- Saat Livewire refresh, memastikan periode tetap ada.

#### `filter()`

Gunanya:

- Mengambil data COA, saldo awal, dan jurnal sesuai periode.

Alur:

1. Validasi periode.
2. Ambil COA.
3. Hitung saldo awal dari transaksi sebelum periode.
4. Ambil jurnal dalam periode.
5. Simpan hasil ke property widget.

#### `updatedPeriodeAwal($value)`

Gunanya:

- Jika periode awal lebih besar dari periode akhir, periode akhir ikut disamakan.

#### `cetakLaporan()`

Gunanya:

- Membuat PDF Buku Besar.
- Mengirim file download.

#### `getViewData()`

Gunanya:

- Mengirim data dari widget ke blade view.

#### `ledgerRowsForDetail(JurnalUmum $jurnal, JurnalDetail $detail)`

Gunanya:

- Membuat baris buku besar dari detail jurnal.
- Menentukan akun lawan.
- Jika akun lawan lebih dari satu, nominal dibagi proporsional.

#### `isNormalKredit(Coa $coa)`

Gunanya:

- Menentukan saldo berjalan memakai normal kredit atau debit.
- Saat ini membaca kolom `saldo` COA.

#### `ensureDefaultPeriode()`

Gunanya:

- Default periode adalah bulan berjalan.

#### `distributeAmount()`

Gunanya:

- Membagi nominal ke beberapa akun lawan agar total tetap sama.

Kalau Buku Besar minus/plus salah:

- Cek `isNormalKredit()`.
- Cek kolom `saldo` di COA.

## 13. Peminjaman Barang

### Model `PeminjamanBarang`

#### `barang()`

Gunanya:

- Relasi ke Barang Kantor berdasarkan `kode_barang`.

#### `user()`

Gunanya:

- Relasi ke user peminjam.

#### `getIsTerlambatAttribute()`

Gunanya:

- Attribute virtual untuk mengetahui apakah peminjaman terlambat.

#### `getSisaHariAttribute()`

Gunanya:

- Menghitung sisa hari sebelum tanggal pengembalian.

#### `getHariTerlambatAttribute()`

Gunanya:

- Menghitung jumlah hari terlambat.

#### `getStatusTenggatAttribute()`

Gunanya:

- Memberi status notifikasi:
  - besok
  - hari_ini
  - terlambat

#### `getIsExpiredAttribute()`

Gunanya:

- Mengecek status expired.

#### `expirePendingOverdue()`

Gunanya:

- Mengubah pengajuan pending yang melewati tanggal pengembalian menjadi expired.

#### `getJudulPeminjamanAttribute()`

Gunanya:

- Membuat judul peminjaman untuk tampilan/notifikasi.

#### `booted()`

Gunanya:

- Saat saving, nama dan kategori barang diisi dari Barang Kantor.

### `PeminjamanBarangController`

#### `index(Request $request)`

Gunanya:

- Menampilkan halaman peminjaman anggota.
- Mengambil barang yang tersedia.
- Menampilkan riwayat singkat.
- Menampilkan notifikasi jatuh tempo.

#### `store(Request $request)`

Gunanya:

- Menyimpan pengajuan peminjaman.

Alur:

1. Validasi tanggal, alasan, bukti, item.
2. Upload bukti.
3. Loop item.
4. Cek barang tersedia dengan `isAvailableToBorrow()`.
5. Simpan status `pending`.

#### `approve($id)`

Gunanya:

- Menyetujui peminjaman dari sisi controller.
- Mengunci data dengan `lockForUpdate()`.
- Mengurangi stok dengan `markAsBorrowed()`.

#### `kembalikan($id)`

Gunanya:

- User mengirim bukti pengembalian.
- Status menjadi `menunggu_verifikasi_pengembalian`.

#### `riwayatSemua(Request $request)`

Gunanya:

- Menampilkan riwayat lengkap dengan filter tanggal/status.

Kalau stok tidak berkurang saat peminjaman:

- Cek `approve()` controller.
- Cek action admin di `PeminjamanBarangsTable.php`.
- Cek `BarangKantor::markAsBorrowed()`.

## 14. Pemakaian BHP

### Model `PemakaianBHP`

#### `user()`

Gunanya:

- Relasi pemakaian ke user.

#### `barang()`

Gunanya:

- Relasi pemakaian ke Barang Kantor.

#### `scopeByStatus()`

Gunanya:

- Filter query berdasarkan status.

#### `scopeByDateRange()`

Gunanya:

- Filter query berdasarkan rentang tanggal pemakaian.

#### `scopeForUser()`

Gunanya:

- Filter query berdasarkan user.

#### `booted()`

Gunanya:

- Saat saving, nama barang otomatis diisi dari kode barang.

### `PemakaianBHPController`

#### `index(Request $request)`

Gunanya:

- Menampilkan form pemakaian BHP.
- Menampilkan barang BHP stok > 0.
- Menampilkan riwayat user.

#### `riwayatSemua(Request $request)`

Gunanya:

- Menampilkan semua riwayat pemakaian sesuai hak akses.

#### `store(Request $request)`

Gunanya:

- Menyimpan pengajuan pemakaian BHP.

Alur:

1. Validasi alasan, item, kode barang, jumlah.
2. Set tanggal pemakaian hari ini.
3. Simpan tiap item dengan status `pending`.

Kalau satuan pemakaian ingin diubah:

- Cek view pemakaian.
- Cek Barang Kantor `satuan`.
- Cek logic approval di table admin jika stok dikurangi.

## 15. Pengajuan Pembelian Barang

### Model `PengajuanPembelianBarang`

#### `user()`

Gunanya:

- Relasi pengajuan pembelian ke user.

### `PembelianBarangController`

#### `index()`

Gunanya:

- Menampilkan halaman pengajuan pembelian.
- Menampilkan riwayat singkat.

#### `store(Request $request)`

Gunanya:

- Menyimpan pengajuan pembelian.

Alur:

1. Validasi user, alasan, bukti, dan items.
2. Upload bukti pendukung.
3. Loop tiap item.
4. Hitung `sub_total = perkiraan_harga * jumlah`.
5. Simpan status `pending`.

#### `riwayatSemua(Request $request)`

Gunanya:

- Menampilkan riwayat lengkap dengan filter.

Kalau ingin mengubah status approval:

- Cek `PengajuanPembelianBarangsTable.php`.

## 16. Ketersediaan Barang

### `KetersediaanController`

File: `app/Http/Controllers/KetersediaanController.php`

#### `index(Request $request)`

Gunanya:

- Menampilkan daftar ketersediaan barang untuk anggota.
- Bisa filter jenis barang.
- Bisa cari nama/kode barang.
- Bisa filter status ready/unavailable.

Kondisi ready:

- Status barang Aktif.
- Stok > 0.
- Untuk aset: status pinjam Tersedia, siap digunakan, tanggal diterima ada.
- Untuk BHP: cukup stok tersedia.

## 17. Laporan PDF

PDF memakai:

- `Barryvdh\DomPDF\Facade\Pdf`

Lokasi PDF:

- Buku Besar: `buku-besar-pdf-layout.blade.php`
- Jurnal Umum: `jurnal-umum-pdf.blade.php`
- Kartu Penyusutan: `kartu-pdf.blade.php`
- Peminjaman: `peminjaman-barangs/laporan-pdf.blade.php`
- Pemakaian BHP: `pemakaianbhps/laporan-pdf.blade.php`
- Pengajuan Pembelian: `pembelian-barangs/laporan-pdf.blade.php`

Kalau tampilan PDF ingin diubah:

- Ubah file blade PDF-nya.

Kalau data PDF salah:

- Cek page class yang memanggil `Pdf::loadView()`.
- Contoh Buku Besar: `BukuBesarTableOverview::cetakLaporan()`.

## 18. Panduan Cepat Jika Dosen Bertanya

### "Di mana proses perhitungan perolehan?"

Jawab:

- Di `app/Support/PerolehanBarangAllocator.php`, function `allocate()`.
- Hasilnya disimpan ke detail perolehan dan grand total.
- Jurnalnya dibuat di `PerolehanBarang::syncJurnalUmum()`.

### "Di mana aset otomatis masuk master barang?"

Jawab:

- Di `PerolehanBarangDetail::created()`.
- Jika kategori aset, sistem membuat record `BarangKantor`.
- Setelah Barang Kantor dibuat, event `BarangKantor::created()` membuat data penyusutan.

### "Di mana stok BHP bertambah?"

Jawab:

- Di `PerolehanBarangDetail::created()`.
- Jika BHP sudah ada, stok di-increment.
- Jika belum ada, dibuat Barang Kantor BHP baru.

### "Di mana stok berkurang saat peminjaman?"

Jawab:

- Di `BarangKantor::markAsBorrowed()`.
- Dipanggil saat peminjaman disetujui.

### "Di mana stok kembali setelah pengembalian?"

Jawab:

- Di `BarangKantor::markAsReturned()`.
- Dipanggil saat pengembalian diverifikasi admin.

### "Di mana penyusutan dihitung?"

Jawab:

- Beban bulanan dihitung di `PenyusutanAsetTetap::creating()`.
- Proses bulanan dan jurnal penyusutan dibuat di `ListPenyusutans::postingPeriode()`.

### "Kenapa penyusutan mulai bulan berikutnya?"

Jawab:

- Karena function `bulanMulaiPenyusutanDariTanggal()` memakai cut-off tanggal 15.
- Jika tanggal diterima > 15, mulai bulan berikutnya.

### "Di mana jurnal otomatis dibuat?"

Jawab:

- Perolehan: `PerolehanBarang::syncJurnalUmum()`.
- Pendapatan Hibah: `PendapatanHibah::syncJurnalUmum()`.
- Pengisian Kas Kecil: `PengisianKasKecil::syncJurnalUmum()`.
- Penyusutan: `ListPenyusutans::postingPeriode()`.

### "Di mana Buku Besar mengambil data?"

Jawab:

- Dari `JurnalUmum` dan `JurnalDetail`.
- Diproses di `BukuBesarTableOverview::filter()`.
- Saldo berjalan dihitung di blade dengan bantuan `isNormalKredit()`.

### "Barcode barang pakai apa?"

Jawab:

- Kode barcode dibuat otomatis di `BarangKantor::created()`.
- Gambar QR dibuat melalui QuickChart di `getBarcodeQrImageUrlAttribute()`.
- Scanner memakai `Html5Qrcode` di view scan.

### "Kalau mau tambah akun COA baru edit mana?"

Jawab:

- Edit opsi dan mapping di `COAForm.php`.
- Pastikan nama akun juga dipakai di model transaksi jika jurnal membutuhkan akun tersebut.

### "Kalau mau ubah akun jurnal perolehan edit mana?"

Jawab:

- Edit `PerolehanBarang::assetAccountNameForJenis()` untuk aset.
- Edit `PerolehanBarang::bhpAccountNameForJenis()` untuk BHP.
- Edit pemilihan akun kredit di `syncJurnalUmum()`.

### "Kalau mau ubah aturan saldo Buku Besar edit mana?"

Jawab:

- Edit `BukuBesarTableOverview::isNormalKredit()`.

