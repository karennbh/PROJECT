# Black Box Testing Aplikasi Pengelolaan Aset dan BHP

Dokumen ini dipakai untuk menguji aplikasi dari sisi pengguna tanpa melihat kode program saat proses pengujian. Fokus pengujian adalah apakah input, validasi, proses transaksi, jurnal, buku besar, dan laporan sudah sesuai dengan kebutuhan sistem.

## A. Ketentuan Umum Pengujian

Ketentuan validasi yang digunakan dalam test case:

1. Field bertanda wajib harus menampilkan pesan validasi merah jika kosong.
2. Field nominal tidak boleh menerima nilai kurang dari 0.
3. Nominal yang wajib bernilai transaksi, seperti saldo pengisian kas kecil dan nilai hibah, harus lebih dari 0.
4. Harga satuan dan harga perolehan digunakan untuk transaksi perolehan, sehingga nilai 0, minus, atau nilai di bawah Rp1.000 harus ditolak.
5. Diskon dan biaya lainnya pada perolehan boleh bernilai 0, tetapi tidak boleh kosong saat proses hitung alokasi dijalankan.
6. Nilai residu tidak boleh lebih besar dari harga perolehan.
7. Jumlah barang/stok/jumlah perolehan/pemakaian/peminjaman harus lebih dari 0.
8. Tanggal transaksi tidak boleh melebihi hari ini jika form membatasi transaksi sampai hari berjalan.
9. Tanggal diterima aset tidak boleh lebih awal dari tanggal pembelian/perolehan.
10. Perolehan dari hibah uang hanya boleh memilih pendapatan hibah yang tanggal hibahnya sama atau sebelum tanggal pembelian.
11. Total harga perolehan/grand total pada Perolehan Barang harus sama dengan nominal jurnal umum dan buku besar untuk transaksi tersebut.
12. Akun COA yang belum dibuat tidak boleh menyebabkan error sistem. Sistem harus memberi keterangan agar akun COA ditambahkan.
13. Data master yang sudah digunakan dalam transaksi tidak boleh membuat transaksi lama rusak.
14. Tombol kembali dari detail barang, kartu penyusutan, dan halaman detail harus kembali ke fitur asal yang sesuai.

Catatan pengisian hasil:

- `Actual Result` diisi setelah testing dilakukan.
- `Status` diisi `Berhasil` jika hasil sesuai expected result, atau `Gagal` jika tidak sesuai.

## B. Test Case Login dan Session

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 1 | Membuka halaman login | Akses `/login` | Sistem menampilkan halaman login dengan field username dan password |  |  |
| 1.1 | Login admin berhasil | Username admin valid, password valid | Sistem mengarahkan user ke dashboard admin `/admin` |  |  |
| 1.2 | Login anggota berhasil | Username anggota valid, password valid | Sistem mengarahkan user ke dashboard anggota `/dashboard` |  |  |
| 1.3 | Login username kosong | Username kosong, password diisi | Sistem menampilkan pesan validasi `Username wajib diisi.` dan tidak login |  |  |
| 1.4 | Login password kosong | Username diisi, password kosong | Sistem menampilkan pesan validasi `Password wajib diisi.` dan tidak login |  |  |
| 1.5 | Login password salah | Username valid, password salah | Sistem menampilkan pesan login gagal dan tidak masuk dashboard |  |  |
| 1.6 | Login beberapa akun di tab berbeda | Login admin di satu tab, login akun lain di tab lain | Sistem tidak error; session aktif mengikuti user yang sedang login pada tab tersebut |  |  |
| 1.7 | Idle session 45 menit | Tidak ada aktivitas selama 45 menit | Sistem logout otomatis dan mengarahkan ke login dengan pesan sesi berakhir |  |  |
| 1.8 | Logout | Klik logout | Session terhapus dan user diarahkan ke login |  |  |

## C. Test Case Master Data COA

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 2 | Membuka menu COA | Klik menu `COA` | Sistem menampilkan daftar COA |  |  |
| 2.1 | Membuka form tambah COA | Klik `Tambah COA` | Sistem menampilkan form tambah COA |  |  |
| 2.2 | Tambah COA normal | Header Akun: Harta; Nama Akun: Kas Kecil; Kode Akun otomatis; Saldo Normal otomatis; Jumlah Saldo: 6.700.000 | Data COA berhasil disimpan dan muncul di tabel |  |  |
| 2.3 | Header akun kosong | Header Akun tidak dipilih | Muncul pesan merah `Header akun wajib dipilih.` dan data tidak disimpan |  |  |
| 2.4 | Nama akun kosong | Header dipilih, Nama Akun kosong | Muncul pesan merah `Nama akun wajib dipilih.` dan data tidak disimpan |  |  |
| 2.5 | Nama akun ganda | Nama Akun yang sama sudah pernah dibuat pada header yang sama | Muncul pesan merah `Nama akun sudah digunakan pada header ...` dan data tidak disimpan |  |  |
| 2.6 | Kode akun kosong | Paksa kode akun kosong | Muncul pesan merah `Kode akun wajib diisi.` |  |  |
| 2.7 | Saldo normal kosong | Paksa saldo normal kosong | Muncul pesan merah `Saldo normal wajib dipilih.` |  |  |
| 2.8 | Jumlah saldo minus | Jumlah Saldo: -1 | Muncul pesan merah `Jumlah saldo tidak boleh kurang dari 0.` dan data tidak disimpan |  |  |
| 2.9 | Jumlah saldo 0 | Jumlah Saldo: 0 | Data boleh disimpan karena saldo awal boleh 0 |  |  |
| 2.10 | Akun sudah dibuat | Buka pilihan Nama Akun setelah akun dibuat | Akun yang sudah dibuat tidak dapat dipilih lagi/menjadi disabled |  |  |

## D. Test Case Master Data User

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 3 | Membuka menu User | Klik menu `User` | Sistem menampilkan daftar user |  |  |
| 3.1 | Tambah user normal | Nama: Karen Natalia; Username: karennatalia; Password: 123456; User Group: admin/anggota | User berhasil disimpan |  |  |
| 3.2 | Nama kosong | Nama tidak diisi | Muncul pesan merah `Nama wajib diisi.` |  |  |
| 3.3 | Nama berisi angka | Nama: Karen123 | Muncul pesan bahwa nama hanya boleh huruf/spasi |  |  |
| 3.4 | Nama ganda | Nama sudah terdaftar | Muncul pesan merah `Nama sudah terdaftar.` |  |  |
| 3.5 | Username kosong | Username tidak diisi | Muncul pesan merah `Username wajib diisi.` |  |  |
| 3.6 | Username mengandung spasi/huruf besar | Username: Karen Natalia | Muncul pesan bahwa username harus huruf kecil, boleh angka, tanpa spasi |  |  |
| 3.7 | Username kurang dari 5 karakter | Username: kar | Muncul pesan validasi minimal username |  |  |
| 3.8 | Username ganda | Username sudah terdaftar | Muncul pesan merah `Username sudah terdaftar.` |  |  |
| 3.9 | Password kosong | Password tidak diisi | Muncul pesan merah `Password wajib diisi.` |  |  |
| 3.10 | Password kurang dari 6 karakter | Password: 123 | Muncul pesan merah `Password tidak boleh kurang dari 6 karakter.` |  |  |
| 3.11 | User group kosong | User Group tidak dipilih | Muncul pesan merah `User group wajib dipilih.` |  |  |

## E. Test Case Master Data Kategori Aset Tetap

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 4 | Membuka menu Kategori Aset Tetap | Klik menu `Kategori Aset Tetap` | Sistem menampilkan daftar kategori aset tetap |  |  |
| 4.1 | Tambah kategori normal | Kelompok aset, umur ekonomis, tarif penyusutan, keterangan diisi valid | Data kategori aset berhasil disimpan |  |  |
| 4.2 | Kelompok aset kosong | Kelompok aset tidak dipilih | Muncul pesan merah `Kelompok aset tetap wajib dipilih.` |  |  |
| 4.3 | Kelompok aset ganda | Kelompok aset yang sama sudah ada | Muncul pesan merah `Kelompok aset sudah terdaftar.` |  |  |
| 4.4 | Umur ekonomis kosong | Umur ekonomis tidak diisi | Sistem menampilkan pesan wajib isi |  |  |
| 4.5 | Tarif penyusutan kosong | Tarif tidak diisi | Sistem menampilkan pesan wajib isi |  |  |

## F. Test Case Master Data Barang Kantor

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 5 | Membuka menu Barang Kantor | Klik menu `Barang Kantor` | Sistem menampilkan daftar barang kantor |  |  |
| 5.1 | Tambah aset normal | Kategori: Aset Tetap; Status: Siap Digunakan; Tanggal Diterima valid; Jenis Aset; Kategori Aset; Nama Barang; Stok; Satuan; Nilai Residu; Harga Perolehan; Status Barang | Data aset berhasil disimpan dan penyusutan aset otomatis terbentuk |  |  |
| 5.2 | Tambah BHP normal | Kategori: BHP; Jenis BHP; Nama Barang; Stok; Satuan; Status Barang | Data BHP berhasil disimpan |  |  |
| 5.3 | Kategori barang kosong | Kategori Barang tidak dipilih | Muncul pesan merah `Kategori barang wajib dipilih.` |  |  |
| 5.4 | Nama barang kosong | Nama Barang tidak diisi | Muncul pesan merah `Nama barang wajib diisi.` |  |  |
| 5.5 | Nama barang terlalu pendek | Nama Barang: ab | Muncul pesan minimal 3 karakter |  |  |
| 5.6 | Nama barang terlalu panjang | Nama Barang lebih dari 25 karakter | Muncul pesan maksimal 25 karakter |  |  |
| 5.7 | Nama BHP ganda | Nama BHP yang sama sudah ada | Muncul pesan merah `Nama barang BHP sudah ada dan tidak boleh duplikat.` |  |  |
| 5.8 | Stok kosong | Stok tidak diisi | Muncul pesan merah `Stok wajib diisi.` |  |  |
| 5.9 | Stok 0 atau minus | Stok: 0 / -1 | Muncul pesan merah `Stok harus lebih dari 0.` |  |  |
| 5.10 | Satuan kosong | Satuan tidak dipilih | Muncul pesan merah `Satuan wajib dipilih.` |  |  |
| 5.11 | Jenis aset kosong | Kategori aset, Jenis Aset tidak dipilih | Muncul pesan merah `Jenis aset wajib dipilih.` |  |  |
| 5.12 | Kategori aset kosong | Aset dipilih, kategori aset tidak dipilih | Muncul pesan merah `Kategori aset wajib dipilih.` |  |  |
| 5.13 | Nilai residu kosong | Nilai Residu tidak diisi | Muncul pesan merah `Nilai residu wajib diisi.` |  |  |
| 5.14 | Nilai residu minus | Nilai Residu: -1 | Muncul pesan nilai residu tidak boleh kurang dari 0 |  |  |
| 5.15 | Nilai residu lebih besar dari harga perolehan | Nilai Residu: 5.000.000; Harga Perolehan: 1.000.000 | Muncul pesan merah `Nilai residu tidak boleh melebihi harga perolehan.` |  |  |
| 5.16 | Harga perolehan kosong | Harga Perolehan tidak diisi | Muncul pesan merah `Nilai perolehan wajib diisi.` |  |  |
| 5.17 | Harga perolehan 0 atau minus | Harga Perolehan: 0 / -1 | Muncul pesan merah bahwa nilai perolehan harus lebih dari 0 |  |  |
| 5.17.1 | Harga perolehan di bawah ribuan | Harga Perolehan: 999 | Muncul pesan merah bahwa nilai perolehan minimal Rp1.000 dan data tidak disimpan |  |  |
| 5.18 | Harga perolehan melebihi batas | Harga Perolehan: 100.000.001 | Muncul pesan tidak boleh lebih dari Rp100.000.000 |  |  |
| 5.19 | Tanggal diterima lebih awal dari pembelian perolehan | Ubah tanggal diterima sebelum tanggal pembelian | Muncul pesan tanggal diterima tidak boleh lebih awal dari tanggal pembelian |  |  |
| 5.20 | Status penggunaan belum siap lalu ubah sekali ke siap | Status awal Belum Siap Digunakan, edit menjadi Siap Digunakan dan isi tanggal diterima | Status berubah ke Siap Digunakan dan setelah itu terkunci/tidak bisa diubah lagi |  |  |
| 5.21 | Scan barcode | Scan QR barang | Sistem membuka halaman detail barang di Filament |  |  |
| 5.22 | Kembali dari detail barang | Klik `Kembali ke Barang Kantor` | Sistem kembali ke daftar Barang Kantor, bukan ke login |  |  |

## G. Test Case Pendapatan Hibah

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 6 | Membuka menu Pendapatan Hibah | Klik menu `Pendapatan Hibah` | Sistem menampilkan daftar pendapatan hibah |  |  |
| 6.1 | Tambah hibah uang normal | Tanggal hibah valid; Sumber Hibah; Jenis Hibah: Hibah Uang; Akun Bank Hibah tersedia; Akun Pendapatan Hibah tersedia; Nilai Hibah: 10.000.000 | Data tersimpan dan jurnal pendapatan hibah otomatis terbentuk |  |  |
| 6.2 | Tambah hibah barang normal | Tanggal hibah valid; Sumber Hibah; Jenis Hibah: Hibah Barang; Nilai Hibah valid | Data tersimpan sesuai jenis hibah |  |  |
| 6.3 | Tanggal hibah kosong | Tanggal tidak diisi | Muncul pesan merah `Tanggal hibah wajib diisi.` |  |  |
| 6.4 | Tanggal hibah melebihi hari ini | Tanggal hibah di masa depan | Muncul pesan merah `Tanggal hibah tidak boleh melebihi hari ini.` |  |  |
| 6.5 | Sumber hibah kosong | Sumber Hibah tidak diisi | Muncul pesan merah `Sumber hibah wajib diisi.` |  |  |
| 6.6 | Sumber hibah terlalu pendek | Sumber Hibah: ab | Muncul pesan minimal 3 karakter |  |  |
| 6.7 | Nilai hibah kosong | Nilai Hibah tidak diisi | Muncul pesan merah `Nilai hibah wajib diisi.` |  |  |
| 6.8 | Nilai hibah 0 atau minus | Nilai Hibah: 0 / -1 | Muncul pesan merah `Nilai hibah harus lebih dari 0.` |  |  |
| 6.9 | Akun COA belum dibuat | Akun Kas Bank Hibah/Pendapatan Donasi Hibah belum ada di COA | Sistem menampilkan keterangan `Tambahkan akun COA`, tidak menyebabkan error database |  |  |
| 6.10 | Jurnal pendapatan hibah balance | Simpan Pendapatan Hibah nilai 10.000.000 | Jurnal Umum debit dan kredit masing-masing 10.000.000 |  |  |

## H. Test Case Pengisian Kas Kecil

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 7 | Membuka menu Pengisian Kas Kecil | Klik menu `Pengisian Kas Kecil` | Sistem menampilkan daftar pengisian kas kecil |  |  |
| 7.1 | Tambah pengisian kas kecil normal | Tanggal valid; Akun Kas Kecil tersedia; Akun Sumber Dana tersedia; Nominal: 1.000.000; Keterangan | Data tersimpan dan jurnal pengisian kas kecil terbentuk |  |  |
| 7.2 | Tanggal kosong | Tanggal tidak diisi | Muncul pesan merah `Tanggal wajib diisi.` |  |  |
| 7.3 | Tanggal melebihi hari ini | Tanggal di masa depan | Muncul pesan merah `Tanggal tidak boleh melebihi hari ini.` |  |  |
| 7.4 | Nominal kosong | Nominal tidak diisi | Muncul pesan merah `Nominal wajib diisi.` |  |  |
| 7.5 | Nominal 0 atau minus | Nominal: 0 / -1 | Muncul pesan merah `Nominal harus lebih dari 0.` |  |  |
| 7.6 | COA belum tersedia | Kas Kecil/Kas Pengeluaran Institusi belum dibuat di COA | Sistem menampilkan keterangan `Tambahkan akun COA`, tidak error database |  |  |
| 7.7 | Jurnal pengisian kas kecil balance | Simpan nominal 1.000.000 | Jurnal debit Kas Kecil 1.000.000 dan kredit Kas Pengeluaran Institusi 1.000.000 |  |  |
| 7.8 | Aksi hapus tidak tersedia | Buka tabel pengisian kas kecil | Tombol `Hapus` tidak tampil |  |  |

## I. Test Case Perolehan Barang

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 8 | Membuka menu Perolehan Barang | Klik menu `Perolehan Barang` | Sistem menampilkan daftar perolehan barang |  |  |
| 8.1 | Tambah perolehan pembelian normal | Sumber: Pembelian; Nomor otomatis PRL-PB; Tanggal Pembelian valid; Status Penggunaan; Foto Nota; Detail barang; Diskon; Biaya Lainnya; Klik Hitung Alokasi; Simpan | Data perolehan tersimpan, barang kantor terbentuk, jurnal perolehan terbentuk |  |  |
| 8.2 | Tambah perolehan hibah uang normal | Sumber: Hibah Uang; Nomor otomatis PRL-HU; Tanggal Pembelian valid; Sumber Hibah tersedia; Detail aset; Hitung Nilai Hibah; Simpan | Data perolehan tersimpan dan mengurangi sisa dana hibah |  |  |
| 8.3 | Tambah perolehan hibah barang normal | Sumber: Hibah Barang; Nomor otomatis PRL-HB; Tanggal Diterima valid; Sumber Hibah; Bukti Dokumen; Detail aset; Hitung Nilai Hibah; Simpan | Data hibah barang tersimpan, aset otomatis siap digunakan |  |  |
| 8.4 | Sumber perolehan kosong | Sumber tidak dipilih | Muncul pesan merah `Kolom sumber perolehan wajib dipilih.` |  |  |
| 8.5 | Nomor perolehan kosong | Nomor dikosongkan secara paksa | Muncul pesan merah `Kolom nomor perolehan wajib diisi.` |  |  |
| 8.6 | Tanggal pembelian kosong | Tanggal tidak diisi | Muncul pesan merah `Kolom tanggal pembelian wajib diisi.` |  |  |
| 8.7 | Tanggal pembelian di luar tahun berjalan | Isi tanggal sebelum awal tahun berjalan | Muncul pesan bahwa tanggal hanya boleh tahun berjalan |  |  |
| 8.8 | Tanggal pembelian melebihi hari ini | Isi tanggal masa depan | Muncul pesan tanggal tidak boleh melebihi hari ini |  |  |
| 8.9 | Tanggal diterima lebih awal dari tanggal pembelian | Tanggal Pembelian 10/03/2026, Tanggal Diterima 09/03/2026 | Muncul pesan `Tanggal Diterima tidak boleh lebih awal dari tanggal pembelian.` |  |  |
| 8.10 | Foto nota kosong untuk pembelian | Sumber Pembelian, foto nota tidak diupload | Muncul pesan merah `Kolom foto nota wajib diupload.` |  |  |
| 8.11 | Bukti dokumen hibah kosong | Sumber Hibah/Hibah Uang, bukti tidak diupload | Muncul pesan merah `Kolom bukti dokumen hibah wajib diupload.` |  |  |
| 8.12 | Nama/sumber hibah kosong | Hibah Barang, Sumber Hibah tidak diisi | Muncul pesan merah `Kolom sumber hibah wajib diisi.` |  |  |
| 8.13 | Sumber hibah tanggalnya setelah tanggal pembelian | Tanggal Pembelian 01/01/2026, pilih hibah tanggal 01/02/2026 | Sumber hibah tidak muncul atau muncul pesan bahwa tanggal hibah harus sama/sebelum tanggal pembelian |  |  |
| 8.14 | Kategori barang kosong | Detail kategori barang tidak dipilih | Muncul pesan merah `Kolom kategori barang wajib dipilih.` |  |  |
| 8.15 | Nama barang aset kosong | Detail aset, Nama Barang kosong | Muncul pesan merah `Kolom nama barang wajib diisi.` |  |  |
| 8.16 | Jenis aset kosong | Detail aset, Jenis Aset tidak dipilih | Muncul pesan merah `Kolom jenis barang wajib dipilih.` |  |  |
| 8.17 | Kategori aset tetap kosong | Detail aset, Kategori Aset Tetap tidak dipilih | Muncul pesan merah `Kolom kategori aset tetap wajib dipilih.` |  |  |
| 8.18 | BHP tidak memilih nama barang | Detail BHP, Nama Barang BHP kosong | Muncul pesan merah `Kolom nama barang BHP wajib dipilih.` |  |  |
| 8.19 | Satuan perolehan kosong | Satuan Perolehan tidak dipilih | Muncul pesan merah `Kolom satuan perolehan wajib dipilih.` |  |  |
| 8.20 | Jumlah kosong | Jumlah tidak diisi | Muncul pesan merah `Kolom jumlah wajib diisi.` |  |  |
| 8.21 | Jumlah 0 atau minus | Jumlah: 0 / -1 | Muncul pesan merah `Jumlah harus lebih dari 0.` |  |  |
| 8.22 | Harga satuan kosong | Pembelian, Harga Satuan kosong | Muncul pesan merah `Kolom harga satuan wajib diisi.` |  |  |
| 8.23 | Harga satuan 0 atau minus | Harga Satuan: 0 / -1 | Muncul pesan merah `Harga satuan harus lebih dari 0.` |  |  |
| 8.23.1 | Harga satuan di bawah ribuan | Harga Satuan: 999 | Muncul pesan merah bahwa harga satuan minimal Rp1.000 dan data tidak disimpan |  |  |
| 8.24 | Harga perolehan kosong | Harga Perolehan tidak diisi | Muncul pesan merah `Kolom harga perolehan wajib diisi.` |  |  |
| 8.25 | Harga perolehan 0 atau minus | Harga Perolehan: 0 / -1 | Muncul pesan merah `Harga perolehan harus lebih dari 0.` |  |  |
| 8.25.1 | Harga perolehan di bawah ribuan | Harga Perolehan: 999 | Muncul pesan merah bahwa harga perolehan minimal Rp1.000 dan data tidak disimpan |  |  |
| 8.26 | Nilai residu lebih besar dari harga perolehan | Nilai Residu 2.000.000, Harga Perolehan 1.000.000 | Muncul pesan merah `Nilai residu tidak boleh melebihi harga perolehan.` |  |  |
| 8.27 | Diskon kosong saat hitung alokasi | Diskon dikosongkan, klik Hitung Alokasi | Muncul pesan merah `Diskon tidak boleh kosong.` |  |  |
| 8.28 | Diskon 0 | Diskon: 0; Biaya Lainnya: 0; klik Hitung Alokasi | Sistem menerima nilai 0 dan menghitung alokasi |  |  |
| 8.29 | Biaya lainnya kosong saat hitung alokasi | Biaya Lainnya dikosongkan, klik Hitung Alokasi | Muncul pesan merah `Biaya lainnya tidak boleh kosong.` |  |  |
| 8.30 | Biaya lainnya 0 | Biaya Lainnya: 0; klik Hitung Alokasi | Sistem menerima nilai 0 dan menghitung alokasi |  |  |
| 8.31 | Diskon lebih besar/sama subtotal + biaya | Subtotal 100.000; Diskon 100.000; Biaya 0 | Muncul pesan merah `Diskon tidak boleh lebih besar atau sama dengan subtotal barang ditambah biaya lainnya.` |  |  |
| 8.32 | Saldo kas kecil tidak cukup | Grand Total lebih besar dari saldo kas kecil | Muncul pesan merah `Saldo Kas Kecil tidak mencukupi...` dan data tidak disimpan |  |  |
| 8.33 | Hitung alokasi pembelian | Input contoh: beberapa barang, diskon, biaya lainnya | Sistem menghitung Total Harga, Alokasi Diskon, Alokasi Biaya Lainnya, Harga Perolehan, Total Harga Perolehan, dan Grand Total |  |  |
| 8.34 | Grand total sama dengan total detail | Setelah hitung alokasi | Grand Total sama dengan jumlah Total Harga Perolehan semua detail |  |  |
| 8.35 | Jurnal perolehan balance | Simpan perolehan pembelian Grand Total 135.631 | Total debit jurnal = 135.631 dan total kredit jurnal = 135.631 |  |  |
| 8.36 | Buku besar sesuai jurnal perolehan | Buka Buku Besar akun terkait perolehan | Nominal debit/kredit Buku Besar sama dengan Jurnal Umum dan Grand Total transaksi |  |  |
| 8.37 | Nomor perolehan berurutan lintas sumber | Input Pembelian, Hibah Uang, Hibah Barang | Nomor mengikuti urutan global: PRL-PB-0001, PRL-HU-0002, PRL-HB-0003 |  |  |
| 8.38 | Satuan BHP mengikuti master barang | Buat BHP satuan Kotak lalu pilih di perolehan | Satuan Perolehan otomatis Kotak dan tidak berubah ke satuan lain |  |  |

## J. Test Case Penyusutan Aset Tetap

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 9 | Membuka menu Penyusutan | Klik menu `Penyusutan Aset Tetap` | Sistem menampilkan daftar aset penyusutan |  |  |
| 9.1 | Penyusutan otomatis dari aset siap digunakan | Tambah aset siap digunakan di Barang Kantor | Data penyusutan otomatis muncul |  |  |
| 9.2 | Aset belum siap digunakan | Tambah aset status Belum Siap Digunakan | Status penyusutan belum aktif/belum waktunya sesuai kondisi |  |  |
| 9.3 | Cut-off tanggal diterima <= 15 | Tanggal diterima 10/03/2026 | Periode awal penyusutan Maret 2026 |  |  |
| 9.4 | Cut-off tanggal diterima > 15 | Tanggal diterima 16/03/2026 | Periode awal penyusutan April 2026 |  |  |
| 9.5 | Proses akhir periode normal | Pilih bulan/tahun yang wajib disusutkan, klik Proses Akhir Periode | Sistem membuat detail penyusutan dan jurnal penyusutan |  |  |
| 9.6 | Proses periode belum waktunya | Pilih periode sebelum bulan mulai penyusutan | Sistem melewati aset dan tidak membuat jurnal |  |  |
| 9.7 | Proses periode yang sudah pernah diposting | Proses bulan yang sama dua kali | Sistem tidak membuat duplikasi detail/jurnal penyusutan |  |  |
| 9.8 | Keterangan kelengkapan lengkap | Semua bulan wajib sampai periode berjalan sudah disusutkan | Kolom Keterangan Kelengkapan menampilkan `Lengkap` |  |  |
| 9.9 | Keterangan kelengkapan bolong | Bulan wajib tertentu belum disusutkan | Kolom Keterangan Kelengkapan menampilkan `Bolong: Nama Bulan Tahun` |  |  |
| 9.10 | Keterangan sebelum wajib susut | Aset belum masuk bulan mulai penyusutan | Keterangan bukan `Lengkap`; status periode menunjukkan `Belum Waktunya` |  |  |
| 9.11 | Kartu penyusutan | Klik tombol `Kartu` | Sistem menampilkan kartu aset di dalam Filament |  |  |
| 9.12 | Kembali dari kartu penyusutan dari Barang Kantor | Buka kartu dari detail barang, klik Kembali | Sistem kembali ke Barang Kantor |  |  |
| 9.13 | Jurnal penyusutan balance | Proses penyusutan bulanan | Jurnal debit Beban Penyusutan = kredit Akumulasi Penyusutan |  |  |

## K. Test Case Peminjaman Barang

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 10 | Anggota membuka menu Peminjaman | Login anggota, klik Peminjaman | Sistem menampilkan form/daftar peminjaman anggota |  |  |
| 10.1 | Pengajuan peminjaman normal | Pilih barang tersedia; isi jumlah; tanggal; bukti jika diperlukan | Pengajuan tersimpan dengan status menunggu/disesuaikan sistem |  |  |
| 10.2 | Barang kosong | Barang tidak dipilih | Muncul pesan wajib pilih barang |  |  |
| 10.3 | Jumlah kosong | Jumlah tidak diisi | Muncul pesan wajib isi jumlah |  |  |
| 10.4 | Jumlah 0 atau minus | Jumlah: 0 / -1 | Sistem menolak karena jumlah harus lebih dari 0 |  |  |
| 10.5 | Jumlah melebihi stok | Ajukan jumlah lebih dari stok | Sistem menolak/menampilkan stok tidak cukup |  |  |
| 10.6 | Admin menyetujui peminjaman | Admin klik Setujui | Status berubah disetujui dan stok/ketersediaan barang berkurang/terkunci |  |  |
| 10.7 | Admin menolak peminjaman | Admin klik Tolak | Status berubah ditolak dan stok tidak berubah |  |  |
| 10.8 | Pengembalian barang | Admin/verifikator memproses kembali | Stok/ketersediaan barang kembali sesuai jumlah |  |  |

## L. Test Case Pemakaian BHP

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 11 | Anggota membuka menu Pemakaian BHP | Login anggota, klik Pemakaian BHP | Sistem menampilkan pengajuan pemakaian BHP |  |  |
| 11.1 | Pengajuan pemakaian normal | Pilih BHP stok tersedia; jumlah valid | Pengajuan tersimpan |  |  |
| 11.2 | Satuan pemakaian mengikuti barang | Barang satuan Kotak dipilih | Satuan pemakaian otomatis Kotak dan tidak bisa diubah ke Pcs |  |  |
| 11.3 | Barang kosong | Barang tidak dipilih | Muncul pesan wajib pilih barang |  |  |
| 11.4 | Jumlah kosong | Jumlah tidak diisi | Muncul pesan wajib isi jumlah |  |  |
| 11.5 | Jumlah 0 atau minus | Jumlah: 0 / -1 | Sistem menolak jumlah |  |  |
| 11.6 | Jumlah melebihi stok | Jumlah lebih besar dari stok | Sistem menolak karena stok tidak cukup |  |  |
| 11.7 | Admin setujui pemakaian | Admin klik Setujui | Status disetujui dan stok BHP berkurang |  |  |
| 11.8 | Admin tolak pemakaian | Admin klik Tolak | Status ditolak dan stok tidak berubah |  |  |

## M. Test Case Pengajuan Pembelian Barang

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 12 | Anggota membuka menu Pembelian Barang | Login anggota, klik Pembelian Barang | Sistem menampilkan pengajuan pembelian |  |  |
| 12.1 | Pengajuan pembelian normal | Nama barang, jumlah, perkiraan harga, alasan/keterangan diisi | Pengajuan tersimpan |  |  |
| 12.2 | Nama barang kosong | Nama barang tidak diisi | Muncul pesan wajib isi nama barang |  |  |
| 12.3 | Jumlah kosong | Jumlah tidak diisi | Muncul pesan wajib isi jumlah |  |  |
| 12.4 | Jumlah 0 atau minus | Jumlah: 0 / -1 | Sistem menolak jumlah |  |  |
| 12.5 | Perkiraan harga minus | Perkiraan Harga: -1 | Sistem menolak nominal minus |  |  |
| 12.6 | Admin setujui pengajuan | Admin klik Setujui | Status pengajuan berubah disetujui |  |  |
| 12.7 | Admin tolak pengajuan | Admin klik Tolak | Status pengajuan berubah ditolak |  |  |

## N. Test Case Jurnal Umum

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 13 | Membuka menu Jurnal Umum | Klik menu Jurnal Umum | Sistem menampilkan daftar jurnal otomatis dari transaksi |  |  |
| 13.1 | Jurnal Perolehan Pembelian | Simpan perolehan pembelian | Jurnal berisi debit akun aset/BHP dan kredit Kas Kecil |  |  |
| 13.2 | Jurnal Hibah Barang | Simpan perolehan hibah barang | Jurnal berisi debit akun aset dan kredit Penerimaan/Pendapatan Hibah Barang sesuai COA |  |  |
| 13.3 | Jurnal Hibah Uang | Simpan perolehan dari hibah uang | Jurnal berisi debit aset/BHP dan kredit Kas Bank Hibah |  |  |
| 13.4 | Jurnal Pendapatan Hibah | Simpan pendapatan hibah | Jurnal debit Kas Bank Hibah dan kredit Pendapatan Donasi Hibah |  |  |
| 13.5 | Jurnal Pengisian Kas Kecil | Simpan pengisian kas kecil | Jurnal debit Kas Kecil dan kredit Kas Pengeluaran Institusi |  |  |
| 13.6 | Jurnal Penyusutan | Proses akhir periode penyusutan | Jurnal debit Beban Penyusutan dan kredit Akumulasi Penyusutan |  |  |
| 13.7 | Validasi balance | Cek setiap nomor bukti jurnal | Total debit harus sama dengan total kredit |  |  |
| 13.8 | Validasi nominal perolehan | Cek jurnal PRL tertentu | Nominal jurnal sama dengan Grand Total/Total Nilai Hibah perolehan |  |  |

## O. Test Case Buku Besar

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 14 | Membuka menu Buku Besar | Klik menu Buku Besar | Sistem menampilkan buku besar per akun |  |  |
| 14.1 | Filter akun | Pilih akun tertentu | Sistem menampilkan transaksi akun tersebut saja |  |  |
| 14.2 | Urutan akun | Lihat daftar akun/filter | Akun tampil sesuai urutan nomor akun |  |  |
| 14.3 | Saldo normal debit | Pilih akun normal debit | Saldo bertambah jika debit, berkurang jika kredit |  |  |
| 14.4 | Saldo normal kredit | Pilih akun normal kredit | Saldo bertambah jika kredit, berkurang jika debit |  |  |
| 14.5 | Kas Pengeluaran Institusi normal kredit | Pilih akun Kas Pengeluaran Institusi | Kredit pengisian kas kecil menambah saldo sesuai saldo normal kredit |  |  |
| 14.6 | Nominal sesuai jurnal | Bandingkan Buku Besar dengan Jurnal Umum | Nominal debit/kredit sama dengan Jurnal Umum |  |  |
| 14.7 | PDF Buku Besar | Cetak/Export PDF Buku Besar | PDF tampil dengan data sesuai filter dan nomor akun |  |  |

## P. Test Case Laporan dan Detail

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 15 | Detail Barang dari tabel | Klik Detail pada Barang Kantor | Sistem membuka detail barang di dalam Filament |  |  |
| 15.1 | Detail dari scan barcode | Scan QR barang | Sistem membuka detail barang di dalam Filament |  |  |
| 15.2 | Detail penyusutan dari detail barang | Klik Kartu Penyusutan | Sistem membuka kartu penyusutan tanpa kembali ke login |  |  |
| 15.3 | Kembali dari detail barang | Klik Kembali ke Barang Kantor | Sistem kembali ke menu Barang Kantor |  |  |
| 15.4 | Kembali dari kartu penyusutan | Klik Kembali | Jika dibuka dari Barang Kantor, kembali ke Barang Kantor; jika dari Penyusutan, kembali ke Penyusutan |  |  |
| 15.5 | Laporan Jurnal Umum | Buka/cetak laporan jurnal | Data laporan sama dengan data jurnal di sistem |  |  |
| 15.6 | Laporan Buku Besar | Buka/cetak laporan buku besar | Data laporan sama dengan buku besar di sistem |  |  |
| 15.7 | Laporan Kartu Penyusutan | Cetak kartu penyusutan | PDF kartu menampilkan detail aset dan riwayat penyusutan |  |  |

## Q. Test Case Navigasi dan Form Belum Disimpan

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 16 | Pindah menu saat form belum disimpan | Isi form tambah, klik menu lain | Sistem menampilkan popup `Data belum disimpan` |  |  |
| 16.1 | Klik Batal pada popup | Klik Batal | Sistem tetap di halaman form |  |  |
| 16.2 | Klik Keluar/Lanjutkan | Klik Keluar/Lanjutkan | Sistem pindah ke menu tujuan |  |  |
| 16.3 | Tombol back setelah pindah fitur | Dashboard -> Tambah COA -> Perolehan -> Back | Sistem tidak kembali ke Tambah COA, kembali sesuai alur yang benar |  |  |
| 16.4 | Tombol back dari create resource | Perolehan -> Tambah Perolehan -> Back | Sistem kembali ke daftar Perolehan |  |  |

## R. Test Case Integrasi Perolehan, Jurnal Umum, dan Buku Besar

| No | Test Case Name | Input Data / Langkah | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| 17 | Perolehan pembelian dengan pembulatan | Input barang: Total Harga, Diskon, Biaya Lainnya yang menghasilkan pembulatan alokasi | Grand Total tetap sama dengan total harga perolehan detail |  |  |
| 17.1 | Jurnal dari grand total | Simpan transaksi perolehan | Nominal kredit Kas Kecil mengambil Grand Total perolehan |  |  |
| 17.2 | Debit jurnal sama dengan kredit | Cek detail jurnal transaksi | Total debit sama dengan total kredit setelah penyesuaian pembulatan |  |  |
| 17.3 | Buku besar sama dengan jurnal | Buka Buku Besar akun yang terlibat | Nominal buku besar sama dengan nominal jurnal umum |  |  |
| 17.4 | Perolehan hibah uang tidak melebihi sisa hibah | Sisa Hibah 5.000.000, input aset 6.000.000 | Sistem menolak dan menampilkan sisa dana hibah |  |  |
| 17.5 | Perolehan pembelian melebihi saldo kas kecil | Saldo Kas Kecil 1.000.000, Grand Total 2.000.000 | Sistem menolak dan meminta pengisian kas kecil |  |  |

## S. Ringkasan Data Uji yang Disarankan

Gunakan data uji berikut agar test case mudah dilakukan:

| Jenis Data | Contoh Data |
| --- | --- |
| Admin | username `admin`, password valid |
| Anggota | username `anggota1`, password valid |
| COA Harta | Kas Kecil, Kas Bank Hibah, Kas Pengeluaran Institusi, Sarana Pendidikan Laboratorium, Inventaris Kantor, Kendaraan Bermotor, Akumulasi Penyusutan |
| COA Beban | Beban ATK Operasional, BPP Inventaris Kantor, Beban Penyusutan |
| COA Pendapatan | Penerimaan Hibah Barang, Pendapatan Donasi Hibah |
| Kategori Aset | Kelompok 1, umur ekonomis 4 tahun |
| BHP | Pulpen satuan Kotak, Kwitansi satuan Pcs |
| Aset | Motor, Portable Monitor, Charging Station Portable |
| Pendapatan Hibah | PDH dengan nilai 10.000.000 dan tanggal sebelum perolehan |
| Pengisian Kas Kecil | Nominal 1.000.000 atau lebih |
