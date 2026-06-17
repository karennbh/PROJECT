# Alur Fitur dan Test Case Manual

Dokumen ini disusun dari alur fitur yang tersedia di aplikasi `TA2025` berdasarkan route Laravel dan resource Filament yang aktif saat ini.

## 1. Tujuan

Dokumen ini dipakai untuk:
- memandu tester masuk ke setiap fitur,
- memastikan alur utama sistem berjalan,
- menjadi dasar test case manual/UAT,
- memeriksa validasi form, status proses, dan hasil akhir data.

## 2. Role Pengguna

### Admin
- Login dari `/login`
- Setelah berhasil login diarahkan ke `/admin`
- Mengakses menu Filament:
  - Dashboard
  - Master Data
  - Transaksi
  - Pengajuan
  - Laporan

### Anggota
- Login dari `/login`
- Setelah berhasil login diarahkan ke `/dashboard`
- Mengakses halaman:
  - Dashboard
  - Ketersediaan
  - Peminjaman Aset
  - Pemakaian BHP
  - Pembelian Barang
  - Riwayat Aktivitas
  - Ubah Password

## 3. Prasyarat Testing

Sebelum testing, siapkan:
- 1 akun `admin`
- 1 akun `anggota`
- minimal 1 kategori aset tetap
- minimal 1 data COA
- minimal 1 barang `aset` aktif dan tersedia
- minimal 1 barang `bhp` dengan stok lebih dari 0
- file gambar `.jpg`/`.png` untuk bukti peminjaman, bukti pengembalian, bukti pendukung, dan foto nota

Contoh akun uji:
- Admin: `username=admin`, `password=secret123`
- Anggota: `username=anggota1`, `password=secret123`

## 4. Alur Besar Sistem

Urutan fitur yang paling aman untuk diuji:
1. Login
2. Master Data Admin
3. Barang Masuk
4. Barang Kantor
5. Penyusutan
6. Pengajuan oleh Anggota
7. Approval oleh Admin
8. Laporan
9. Riwayat dan logout

## 5. Test Case Umum Login dan Akses

### TC-LOGIN-01 - Login admin berhasil
- Role: Admin
- Langkah:
  1. Buka `/login`
  2. Isi `username`
  3. Isi `password`
  4. Klik login
- Hasil yang diharapkan:
  - login berhasil
  - user diarahkan ke `/admin`
  - dashboard admin tampil

### TC-LOGIN-02 - Login anggota berhasil
- Role: Anggota
- Langkah:
  1. Buka `/login`
  2. Isi akun anggota yang valid
  3. Klik login
- Hasil yang diharapkan:
  - login berhasil
  - user diarahkan ke `/dashboard`

### TC-LOGIN-03 - Login gagal karena password salah
- Role: Semua role
- Langkah:
  1. Buka `/login`
  2. Isi username valid
  3. Isi password salah
  4. Klik login
- Hasil yang diharapkan:
  - login gagal
  - muncul pesan `Username atau password salah.`

### TC-LOGIN-04 - Redirect role sesuai hak akses
- Role: Semua role
- Langkah:
  1. Login sebagai admin
  2. Logout
  3. Login sebagai anggota
- Hasil yang diharapkan:
  - admin selalu ke `/admin`
  - anggota selalu ke `/dashboard`

### TC-LOGIN-05 - Logout berhasil
- Role: Semua role
- Langkah:
  1. Login
  2. Klik logout
- Hasil yang diharapkan:
  - session terhapus
  - diarahkan ke `/login`
  - muncul pesan `Berhasil logout.`

## 6. Test Case Admin - Dashboard

### TC-ADM-DASH-01 - Dashboard admin tampil
- Path: `/admin`
- Langkah:
  1. Login sebagai admin
  2. Buka dashboard admin
- Hasil yang diharapkan:
  - widget statistik tampil
  - widget pengajuan tampil
  - widget inventaris tampil

## 7. Test Case Admin - Master Data

### A. User

### TC-ADM-USER-01 - Buka menu user
- Path: `Admin > Master Data > User`
- Langkah:
  1. Login admin
  2. Klik menu `User`
- Hasil yang diharapkan:
  - daftar user tampil
  - kolom `Nama`, `Username`, `User Group` tampil

### TC-ADM-USER-02 - Tambah user berhasil
- Langkah:
  1. Klik `Create`
  2. Isi `Nama`
  3. Isi `Username` huruf kecil
  4. Isi `Password` minimal 6 karakter
  5. Pilih `User Group`
  6. Simpan
- Hasil yang diharapkan:
  - data user tersimpan
  - user muncul di tabel

### TC-ADM-USER-03 - Validasi nama user
- Langkah:
  1. Isi `Nama` dengan angka/simbol
  2. Simpan
- Hasil yang diharapkan:
  - muncul pesan `Nama hanya boleh berisi huruf.`

### TC-ADM-USER-04 - Validasi username
- Langkah:
  1. Isi `Username` dengan huruf kapital, spasi, atau hanya angka
  2. Simpan
- Hasil yang diharapkan:
  - muncul pesan validasi username

### TC-ADM-USER-05 - Hapus user
- Langkah:
  1. Dari tabel user klik `Delete`
  2. Konfirmasi hapus
- Hasil yang diharapkan:
  - data user terhapus dari tabel

### B. Kategori Aset Tetap

### TC-ADM-KAT-01 - Tambah kategori aset berhasil
- Path: `Admin > Master Data > Kategori Aset Tetap`
- Langkah:
  1. Klik `Create`
  2. Pilih salah satu `Kelompok Aset Tetap`
  3. Simpan
- Hasil yang diharapkan:
  - ID kategori otomatis terbentuk
  - `Umur Ekonomis`, `Tarif Penyusutan`, dan `Keterangan` otomatis terisi
  - data tersimpan

### TC-ADM-KAT-02 - Cegah kategori duplikat
- Langkah:
  1. Tambahkan kategori yang sama dua kali
- Hasil yang diharapkan:
  - sistem menolak data duplikat
  - muncul pesan `Kelompok aset sudah terdaftar.`

### TC-ADM-KAT-03 - Batas maksimal kategori
- Langkah:
  1. Tambah kategori sampai 4 data
  2. Cek kembali halaman list
- Hasil yang diharapkan:
  - tombol create tidak muncul jika jumlah kategori sudah 4

### C. COA

### TC-ADM-COA-01 - Tambah COA berhasil
- Path: `Admin > Master Data > COA`
- Langkah:
  1. Klik `Create`
  2. Pilih `Header Akun`
  3. Pilih `Nama Akun`
  4. Isi `Jumlah Saldo`
  5. Simpan
- Hasil yang diharapkan:
  - `Kode Akun` otomatis terisi
  - `Saldo Normal` otomatis terisi
  - data COA tersimpan

### TC-ADM-COA-02 - Validasi kombinasi header dan nama akun
- Langkah:
  1. Buat kombinasi header dan nama akun yang sama dua kali
- Hasil yang diharapkan:
  - data kedua ditolak
  - muncul pesan nama akun sudah digunakan

### TC-ADM-COA-03 - Validasi jumlah saldo tidak boleh minus
- Langkah:
  1. Isi `Jumlah Saldo` dengan nilai negatif
  2. Simpan
- Hasil yang diharapkan:
  - data ditolak
  - muncul pesan `Jumlah saldo tidak boleh kurang dari 0.`

### D. Barang Kantor

### TC-ADM-BRG-01 - Tambah barang aset berhasil
- Path: `Admin > Master Data > Barang Kantor`
- Langkah:
  1. Klik `Create`
  2. Pilih `Kategori Barang = aset`
  3. Isi `Tanggal Perolehan`
  4. Pilih `Jenis Aset`
  5. Pilih `Kategori Aset Tetap`
  6. Isi `Nama Barang`
  7. Pastikan `Stok = 1`
  8. Isi `Nilai Residu`
  9. Isi `Nilai Perolehan`
  10. Upload foto bila perlu
  11. Simpan
- Hasil yang diharapkan:
  - `Kode Barang` otomatis format `ASET-xxxxx`
  - `Umur Ekonomis` otomatis mengikuti kategori aset
  - data barang aset tersimpan

### TC-ADM-BRG-02 - Tambah barang BHP berhasil
- Langkah:
  1. Klik `Create`
  2. Pilih `Kategori Barang = bhp`
  3. Isi `Nama Barang`
  4. Isi `Stok`
  5. Pilih `Satuan`
  6. Simpan
- Hasil yang diharapkan:
  - `Kode Barang` otomatis format `BHP-xxxxx`
  - data barang BHP tersimpan

### TC-ADM-BRG-03 - Validasi nama BHP tidak boleh duplikat
- Langkah:
  1. Buat dua barang BHP dengan nama sama
- Hasil yang diharapkan:
  - data kedua ditolak
  - muncul pesan `Nama barang BHP sudah ada dan tidak boleh duplikat.`

### TC-ADM-BRG-04 - Filter barang kantor
- Langkah:
  1. Buka list barang kantor
  2. Gunakan filter `status_barang`
  3. Gunakan filter `status_pinjam`
  4. Gunakan filter `kategori_barang`
- Hasil yang diharapkan:
  - data yang tampil sesuai filter

## 8. Test Case Admin - Transaksi

### A. Barang Masuk

### TC-ADM-MSK-01 - Tambah barang masuk berhasil
- Path: `Admin > Transaksi > Barang Masuk`
- Langkah:
  1. Klik `Create`
  2. Pastikan `ID Barang Masuk` otomatis
  3. Isi `Tanggal Perolehan`
  4. Upload `Foto Nota`
  5. Isi `Keterangan`
  6. Pada repeater `Daftar Barang`, isi minimal 1 item
  7. Isi kategori, nama barang, jumlah, harga
  8. Klik `Hitung Alokasi Invoice`
  9. Simpan
- Hasil yang diharapkan:
  - data barang masuk tersimpan
  - total harga dan grand total terhitung
  - item tampil di list barang masuk

### TC-ADM-MSK-02 - Tambah item BHP dari form barang masuk
- Langkah:
  1. Tambah detail dengan kategori `bhp`
  2. Pada `Nama Barang (BHP)` pilih create option
  3. Isi nama barang dan satuan
  4. Simpan barang masuk
- Hasil yang diharapkan:
  - barang BHP baru ikut terbentuk di master barang kantor

### TC-ADM-MSK-03 - Filter barang masuk berdasarkan tanggal
- Langkah:
  1. Buka list barang masuk
  2. Isi tanggal awal/akhir atau pilih bulan dan tahun
- Hasil yang diharapkan:
  - list sesuai periode filter

### B. Penyusutan Aset Tetap

### TC-ADM-SUT-01 - Buka daftar penyusutan
- Path: `Admin > Transaksi > Penyusutan Aset Tetap`
- Langkah:
  1. Buka menu penyusutan
- Hasil yang diharapkan:
  - daftar aset dan status penyusutan tampil
  - tidak ada tombol create manual

### TC-ADM-SUT-02 - Filter status penyusutan
- Langkah:
  1. Gunakan filter bulan dan tahun
  2. Gunakan `Status Aset`
  3. Gunakan `Status Penyusutan`
- Hasil yang diharapkan:
  - data berubah sesuai filter

### TC-ADM-SUT-03 - Lihat kartu aset
- Langkah:
  1. Klik aksi `Kartu` pada salah satu aset
- Hasil yang diharapkan:
  - halaman kartu aset terbuka
  - informasi aset, harga, umur ekonomis, metode penyusutan tampil

### TC-ADM-SUT-04 - Ganti tampilan kartu aset
- Langkah:
  1. Pada halaman kartu aset klik `Tampilan`
  2. Pilih `Per Bulan`
  3. Ulangi pilih `Per Tahun`
- Hasil yang diharapkan:
  - tabel kartu aset berubah sesuai mode

### TC-ADM-SUT-05 - Cetak kartu aset
- Langkah:
  1. Klik `Cetak`
- Hasil yang diharapkan:
  - file PDF kartu aset berhasil diunduh

## 9. Test Case Anggota

### A. Dashboard Anggota

### TC-AGT-DASH-01 - Dashboard anggota tampil
- Path: `/dashboard`
- Langkah:
  1. Login sebagai anggota
- Hasil yang diharapkan:
  - halaman dashboard anggota tampil normal

### B. Ketersediaan Barang

### TC-AGT-KSD-01 - Lihat daftar ketersediaan
- Path: `/ketersediaan`
- Langkah:
  1. Login anggota
  2. Buka menu ketersediaan
- Hasil yang diharapkan:
  - daftar barang tampil
  - data bisa dipaginasi

### TC-AGT-KSD-02 - Cari barang
- Langkah:
  1. Isi kolom pencarian nama atau kode barang
- Hasil yang diharapkan:
  - hanya barang yang cocok yang tampil

### TC-AGT-KSD-03 - Filter berdasarkan jenis dan status
- Langkah:
  1. Pilih `jenis`
  2. Pilih `status = ready`
  3. Ulangi dengan `status = unavailable`
- Hasil yang diharapkan:
  - `ready` hanya menampilkan barang aktif, stok > 0, dan tersedia
  - `unavailable` menampilkan barang tidak aktif, stok habis, atau sedang dipinjam

### C. Peminjaman Aset

### TC-AGT-PJM-01 - Ajukan peminjaman aset berhasil
- Path: `/peminjaman`
- Langkah:
  1. Buka halaman peminjaman
  2. Isi `Tanggal Pinjam`
  3. Isi `Tanggal Pengembalian`
  4. Isi `Alasan Peminjaman`
  5. Upload `Bukti Peminjaman`
  6. Tambahkan minimal 1 item aset dan jumlah pinjam
  7. Kirim pengajuan
- Hasil yang diharapkan:
  - pengajuan tersimpan
  - status awal `pending`
  - muncul pesan sukses

### TC-AGT-PJM-02 - Validasi tanggal pengembalian
- Langkah:
  1. Isi `Tanggal Pengembalian` sebelum `Tanggal Pinjam`
  2. Kirim
- Hasil yang diharapkan:
  - data ditolak
  - validasi tanggal muncul

### TC-AGT-PJM-03 - Validasi bukti peminjaman wajib
- Langkah:
  1. Isi form tanpa upload bukti
  2. Kirim
- Hasil yang diharapkan:
  - data ditolak

### TC-AGT-PJM-04 - Lihat riwayat peminjaman
- Path: `/peminjaman/riwayat`
- Langkah:
  1. Buka riwayat peminjaman
  2. Filter tanggal dan status
- Hasil yang diharapkan:
  - hanya riwayat milik user login yang tampil
  - filter bekerja

### TC-AGT-PJM-05 - Ajukan pengembalian barang
- Prasyarat:
  - ada peminjaman dengan status `disetujui`
- Langkah:
  1. Buka menu peminjaman
  2. Upload `Bukti Pengembalian`
  3. Klik kirim pengembalian
- Hasil yang diharapkan:
  - status berubah menjadi `menunggu_verifikasi_pengembalian`
  - muncul pesan sukses

### D. Pemakaian BHP

### TC-AGT-PMK-01 - Ajukan pemakaian BHP berhasil
- Path: `/pemakaian`
- Langkah:
  1. Isi `Tanggal Pemakaian`
  2. Isi `Alasan Kebutuhan`
  3. Tambahkan item BHP dan jumlah
  4. Upload bukti bila ada
  5. Simpan
- Hasil yang diharapkan:
  - pengajuan tersimpan
  - status awal `pending`

### TC-AGT-PMK-02 - Validasi tanggal pemakaian
- Langkah:
  1. Isi tanggal sebelum hari ini
  2. Simpan
- Hasil yang diharapkan:
  - data ditolak
  - muncul pesan `Tanggal pemakaian tidak boleh sebelum hari ini.`

### TC-AGT-PMK-03 - Validasi jumlah minimal 1
- Langkah:
  1. Isi jumlah `0`
  2. Simpan
- Hasil yang diharapkan:
  - data ditolak
  - muncul pesan jumlah minimal 1

### TC-AGT-PMK-04 - Lihat riwayat pemakaian
- Path: `/pemakaian/riwayat`
- Langkah:
  1. Buka halaman riwayat
  2. Gunakan filter tanggal/status
- Hasil yang diharapkan:
  - hanya data user login yang tampil

### E. Pembelian Barang

### TC-AGT-PBL-01 - Ajukan pembelian barang berhasil
- Path: `/pembelian`
- Langkah:
  1. Isi `Tanggal Pengajuan`
  2. Isi `Alasan`
  3. Upload `Bukti Pendukung`
  4. Tambahkan item barang:
     - nama barang
     - jumlah
     - kategori barang
     - perkiraan harga
     - link barang opsional
  5. Kirim
- Hasil yang diharapkan:
  - pengajuan tersimpan
  - `sub_total` tiap item terhitung
  - status awal `pending`

### TC-AGT-PBL-02 - Validasi item pembelian
- Langkah:
  1. Kosongkan `nama_barang` atau isi `jumlah=0`
  2. Kirim
- Hasil yang diharapkan:
  - sistem menolak input tidak valid

### TC-AGT-PBL-03 - Validasi link barang
- Langkah:
  1. Isi `link_barang` dengan format bukan URL
  2. Kirim
- Hasil yang diharapkan:
  - sistem menolak nilai link tidak valid

### TC-AGT-PBL-04 - Lihat riwayat pembelian
- Path: `/pembelian/riwayat`
- Langkah:
  1. Buka riwayat pembelian
  2. Filter tanggal/status
- Hasil yang diharapkan:
  - hanya data milik user login yang tampil

### F. Riwayat Aktivitas

### TC-AGT-RWT-01 - Riwayat aktivitas gabungan tampil
- Path: `/riwayat`
- Langkah:
  1. Login anggota
  2. Buka menu riwayat aktivitas
- Hasil yang diharapkan:
  - data peminjaman, pemakaian, dan pembelian tampil dalam satu halaman

### TC-AGT-RWT-02 - Filter modul pada riwayat
- Langkah:
  1. Pilih filter modul `peminjaman`
  2. Ulangi untuk `pemakaian`
  3. Ulangi untuk `pembelian`
- Hasil yang diharapkan:
  - hanya data modul yang dipilih yang tampil

### G. Ubah Password

### TC-AGT-PWD-01 - Ubah password berhasil
- Path: `/ubah-password`
- Langkah:
  1. Isi password baru
  2. Isi konfirmasi password
  3. Simpan
- Hasil yang diharapkan:
  - password berhasil diperbarui
  - user diarahkan ke dashboard

### TC-AGT-PWD-02 - Konfirmasi password tidak sama
- Langkah:
  1. Isi password dan konfirmasi berbeda
  2. Simpan
- Hasil yang diharapkan:
  - data ditolak

## 10. Test Case Admin - Approval Pengajuan

### A. Approval Peminjaman Aset

### TC-ADM-APR-PJM-01 - Setujui peminjaman aset
- Path: `Admin > Pengajuan > Peminjaman Aset`
- Langkah:
  1. Buka data status `pending`
  2. Klik `Setujui`
  3. Konfirmasi
- Hasil yang diharapkan:
  - status berubah menjadi `disetujui`
  - stok/ketersediaan barang ikut berubah
  - notifikasi terkirim ke user

### TC-ADM-APR-PJM-02 - Tolak peminjaman aset
- Langkah:
  1. Klik `Tolak` pada data pending
- Hasil yang diharapkan:
  - status berubah menjadi `ditolak`
  - notifikasi terkirim ke user

### TC-ADM-APR-PJM-03 - Verifikasi pengembalian aset
- Prasyarat:
  - anggota sudah mengirim bukti pengembalian
- Langkah:
  1. Buka data status `menunggu_verifikasi_pengembalian`
  2. Klik `Verifikasi Pengembalian`
- Hasil yang diharapkan:
  - status berubah menjadi `kembali`
  - ketersediaan barang kembali bertambah

### B. Approval Pemakaian BHP

### TC-ADM-APR-PMK-01 - Setujui pemakaian BHP
- Path: `Admin > Pengajuan > Pemakaian BHP`
- Langkah:
  1. Pilih data `pending`
  2. Klik `Setujui`
- Hasil yang diharapkan:
  - status menjadi `disetujui`
  - stok barang BHP berkurang sesuai jumlah

### TC-ADM-APR-PMK-02 - Tolak pemakaian BHP
- Langkah:
  1. Klik `Tolak` pada data pending
- Hasil yang diharapkan:
  - status berubah menjadi `ditolak`

### TC-ADM-APR-PMK-03 - Cegah approval jika stok tidak cukup
- Prasyarat:
  - jumlah pengajuan lebih besar dari stok
- Langkah:
  1. Klik `Setujui`
- Hasil yang diharapkan:
  - approval gagal
  - muncul notifikasi stok tidak mencukupi

### C. Approval Pembelian Barang

### TC-ADM-APR-PBL-01 - Setujui pembelian barang
- Path: `Admin > Pengajuan > Pembelian Barang`
- Langkah:
  1. Pilih data `pending`
  2. Klik `Setujui`
- Hasil yang diharapkan:
  - status berubah menjadi `disetujui`
  - notifikasi terkirim ke user

### TC-ADM-APR-PBL-02 - Tolak pembelian barang
- Langkah:
  1. Pilih data `pending`
  2. Klik `Tolak`
- Hasil yang diharapkan:
  - status berubah menjadi `ditolak`
  - notifikasi terkirim ke user

## 11. Test Case Admin - Laporan

### A. Jurnal Umum

### TC-ADM-JUR-01 - Buka jurnal umum
- Path: `Admin > Laporan > Jurnal Umum`
- Langkah:
  1. Buka menu jurnal umum
- Hasil yang diharapkan:
  - daftar jurnal tampil
  - kolom tanggal, nomor bukti, deskripsi, dan tipe transaksi tampil

### TC-ADM-JUR-02 - Pastikan jurnal barang masuk dan penyusutan muncul
- Langkah:
  1. Cari transaksi hasil barang masuk
  2. Cari transaksi hasil penyusutan
- Hasil yang diharapkan:
  - kedua tipe transaksi muncul sesuai data

### B. Buku Besar

### TC-ADM-BBK-01 - Buka buku besar
- Path: `Admin > Laporan > Buku Besar`
- Langkah:
  1. Buka menu buku besar
- Hasil yang diharapkan:
  - data buku besar tampil

### TC-ADM-BBK-02 - Lihat detail buku besar
- Langkah:
  1. Klik salah satu record
- Hasil yang diharapkan:
  - halaman detail buku besar terbuka

## 12. Test Case Scan Barang

### TC-SCAN-01 - Scan barang berdasarkan barcode
- Path: `/scan/barang/{barcode}`
- Langkah:
  1. Ambil barcode/kode barang dari data barang kantor
  2. Buka URL scan sesuai barcode
- Hasil yang diharapkan:
  - halaman detail scan barang tampil
  - jika barcode valid data ditemukan

### TC-SCAN-02 - Barcode tidak ditemukan
- Langkah:
  1. Buka URL scan dengan barcode yang tidak ada
- Hasil yang diharapkan:
  - sistem menampilkan not found / gagal menemukan data

## 13. Skenario End-to-End Yang Disarankan

### Skenario 1 - Dari master data sampai peminjaman selesai
1. Admin buat kategori aset tetap
2. Admin buat barang aset
3. Anggota login dan ajukan peminjaman aset
4. Admin setujui peminjaman
5. Anggota ajukan pengembalian
6. Admin verifikasi pengembalian
7. Cek status akhir `kembali`

### Skenario 2 - Pengajuan BHP sampai stok berkurang
1. Admin pastikan barang BHP punya stok
2. Anggota ajukan pemakaian BHP
3. Admin setujui
4. Cek stok barang kantor berkurang

### Skenario 3 - Pengajuan pembelian sampai approval
1. Anggota ajukan pembelian barang
2. Admin buka menu pembelian barang
3. Admin setujui atau tolak
4. Cek perubahan status di riwayat anggota

## 14. Catatan Penting Saat Testing

- Beberapa menu admin bersifat `view/approval only`, bukan create manual:
  - Peminjaman Aset
  - Pemakaian BHP
  - Pembelian Barang
  - Penyusutan Aset Tetap
- Data pengajuan untuk menu di atas dibuat dari sisi anggota.
- Penyusutan diuji dari hasil data aset yang sudah ada.
- Barang aset menggunakan stok tetap `1` saat create.
- Barang BHP bisa dipakai berulang selama stok masih tersedia.

## 15. Rekomendasi Eksekusi Testing

Prioritas tinggi:
1. Login
2. User
3. Kategori aset
4. Barang kantor
5. Peminjaman aset
6. Pemakaian BHP
7. Pembelian barang
8. Approval admin

Prioritas menengah:
1. Barang masuk
2. Penyusutan
3. Jurnal umum
4. Buku besar
5. Scan barang

