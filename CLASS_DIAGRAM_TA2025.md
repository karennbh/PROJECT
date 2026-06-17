# Class Diagram - Sistem TA2025
## Struktur Models dan Relationships

---

## 📊 Full System Class Diagram

```mermaid
classDiagram
    class User {
        -int id_user
        -string name
        -string username
        -string password
        -string user_group (admin|anggota)
        +pengajuanPembelianBarangs()
        +peminjamanBarangs()
        +pemakaianBHPs()
    }

    class BarangKantor {
        -string kode_barang
        -string kategori_barang (aset|bhp)
        -string barcode
        -string nama_barang
        -int stok
        -string satuan
        -string jenis_aset
        -string jenis_bhp
        -int kategori_aset_id
        -int umur_ekonomis
        -int nilai_residu
        -string status_penggunaan
        -date tanggal_diterima
        -int harga_perolehan
        -string status_barang
        -string status_pinjam
        +kategoriAset()
        +penyusutan()
        +perolehanDetail()
        +peminjamanBarangs()
        +pemakaianBHPs()
    }

    class KategoriAsetTetap {
        -int id_kategori
        -string nama_kategori
        -int umur_ekonomis_tahun
        -int persentase_penyusutan
        -string keterangan
        +barangKantors()
    }

    class Coa {
        -string kode_akun
        -string nama_akun
        -string header_akun
        -string saldo
        -int jumlah_saldo
        +jurnalDetails()
        +pengisianKasKecilsSebagaiKasKecil()
        +pengisianKasKecilsSebagaiSumberDana()
        +pendapatanHibahsSebagaiBank()
        +pendapatanHibahsSebagaiPendapatan()
    }

    class PerolehanBarang {
        -string id_perolehan_barang
        -string sumber_perolehan (pembelian|hibah|hibah_uang)
        -date tanggal_pembelian
        -string status_penggunaan
        -date tanggal_diterima
        -string nama_pemberi_hibah
        -string foto_nota
        -string bukti_dokumen_hibah
        -int subtotal_barang
        -int diskon_total
        -int biaya_lainnya_total
        -int grand_total
        -int total_nilai_hibah
        -string pendapatan_hibah_id
        +details()
        +jurnalUmum()
        +generateId()
        +allocateBarang()
    }

    class PerolehanBarangDetail {
        -int id_detail
        -string perolehan_barang_id
        -string nama_barang
        -string kategori_barang
        -int qty
        -int harga_satuan
        -int diskon_satuan
        -int biaya_lainnya_per_item
        -int harga_perolehan
        -string barang_kode
        +perolehanBarang()
        +barangKantor()
    }

    class JurnalUmum {
        -int id_jurnal_umum
        -string reff_perolehan_barang
        -string reff_penyusutan
        -string reff_pengisian_kas_kecil
        -string reff_pendapatan_hibah
        -date tanggal
        -string deskripsi
        -string tipe_transaksi
        +details()
        +perolehanBarang()
        +penyusutan()
        +penyusutanDetails()
        +pengisianKasKecil()
        +pendapatanHibah()
    }

    class JurnalDetail {
        -int id_jurnal_detail
        -int id_jurnal_umum
        -string kode_akun
        -string tipe (debit|kredit)
        -int nominal
        +jurnalUmum()
        +coa()
    }

    class PenyusutanAsetTetap {
        -string id_penyusutan
        -string kode_barang
        -string nama_aset
        -string status_penggunaan
        -date tanggal_diterima
        -int harga_perolehan
        -int nilai_residu
        -int umur_ekonomis_tahun
        -int beban_penyusutan_bulanan
        -int total_biaya_penyusutan
        -string status_penyusutan
        +barangKantor()
        +details()
        +jurnalUmum()
        +calculateDepreciation()
        +processMonth()
    }

    class PenyusutanDetail {
        -int id_detail
        -string id_penyusutan
        -int id_jurnal_umum
        -string periode
        -int beban_penyusutan
        -int akumulasi_penyusutan
        -int nilai_buku
        +penyusutan()
        +jurnalUmum()
    }

    class PeminjamanBarang {
        -int id_peminjaman
        -int user_id
        -string kode_barang
        -string kategori_barang
        -string nama_barang
        -date tanggal_pinjam
        -date tanggal_pengembalian
        -string alasan_peminjaman
        -string bukti_peminjaman
        -string bukti_pengembalian
        -int jumlah_pinjam
        -string status_pinjam (pending|disetujui|expired|kembali|ditolak)
        +barang()
        +user()
        +getIsTerlambatAttribute()
        +markAsApproved()
        +markAsReturned()
        +markAsRejected()
    }

    class PemakaianBHP {
        -int id_pemakaian
        -int user_id
        -string kode_barang
        -string kategori_barang
        -string nama_barang
        -date tanggal_pemakaian
        -int jumlah_dipakai
        -string tujuan_pemakaian
        -string bukti_pemakaian
        +barang()
        +user()
        +recordConsumption()
    }

    class PengajuanPembelianBarang {
        -int id_pengajuan
        -int user_id
        -string nama_barang_diajukan
        -string spesifikasi_barang
        -int estimasi_harga
        -string alasan_pengajuan
        -string prioritas
        -date tanggal_diperlukan
        -string status_pengajuan
        +user()
        +requestClarification()
        +approve()
        +reject()
    }

    class PengisianKasKecil {
        -string no_transaksi
        -date tanggal
        -string akun_kas_kecil
        -string akun_sumber_dana
        -int nominal
        -string bukti
        -string keterangan
        +jurnalUmum()
        +coaKasKecil()
        +coaSumberDana()
        +generateNo()
    }

    class PendapatanHibah {
        -string no_hibah
        -date tanggal_hibah
        -string sumber_hibah
        -string jenis_hibah
        -string akun_bank_hibah
        -string akun_pendapatan_hibah
        -int nilai_hibah
        -string keterangan
        +jurnalUmum()
        +coaBank()
        +coaPendapatan()
        +generateNoHibah()
    }

    class BukuBesar {
        -int id
        -string kode_akun
        -date periode_awal
        -date periode_akhir
        -int saldo_awal
        -int total_debit
        -int total_kredit
        -int saldo_akhir
        +calculateBalance()
        +generateReport()
    }

    %% Relationships
    User "1" -- "*" PeminjamanBarang : creates
    User "1" -- "*" PemakaianBHP : creates
    User "1" -- "*" PengajuanPembelianBarang : creates

    BarangKantor "*" -- "1" KategoriAsetTetap : hasCategory
    BarangKantor "1" -- "*" PenyusutanAsetTetap : hasDepreciation
    BarangKantor "1" -- "*" PeminjamanBarang : canBeBorrowed
    BarangKantor "1" -- "*" PemakaiaanBHP : canBeConsumed
    BarangKantor "1" -- "*" PerolehanBarangDetail : relatedTo

    PerolehanBarang "1" -- "*" PerolehanBarangDetail : contains
    PerolehanBarang "1" -- "1" JurnalUmum : createsJournal
    PerolehanBarang "*" -- "1" PendapatanHibah : optionalRef

    JurnalUmum "1" -- "*" JurnalDetail : contains
    JurnalUmum "1" -- "1" PerolehanBarang : referencesOptional
    JurnalUmum "1" -- "1" PenyusutanAsetTetap : referencesOptional
    JurnalUmum "1" -- "1" PengisianKasKecil : referencesOptional
    JurnalUmum "1" -- "1" PendapatanHibah : referencesOptional

    JurnalDetail "*" -- "1" Coa : debitOrKredit
    JurnalDetail "*" -- "1" JurnalUmum : partOf

    PenyusutanAsetTetap "1" -- "*" PenyusutanDetail : monthlyRecords
    PenyusutanAsetTetap "1" -- "1" JurnalUmum : createsJournal
    PenyusutanAsetTetap "*" -- "1" BarangKantor : depreciateBagg

    PengisianKasKecil "*" -- "1" Coa : usesKasKecil
    PengisianKasKecil "*" -- "1" Coa : usesSumberDana
    PengisianKasKecil "1" -- "1" JurnalUmum : createsJournal

    PendapatanHibah "*" -- "1" Coa : usesBank
    PendapatanHibah "*" -- "1" Coa : usesPendapatan
    PendapatanHibah "1" -- "1" JurnalUmum : createsJournal

    BukuBesar "*" -- "1" Coa : tracksAccount
```

---

## 🔗 Relationship Patterns

### **One-to-Many Relationships**
| Parent | Child | Relasi | Deskripsi |
|--------|-------|--------|-----------|
| User | PeminjamanBarang | `hasMany()` | User bisa pinjam banyak barang |
| User | PemakaianBHP | `hasMany()` | User bisa pakai banyak BHP |
| User | PengajuanPembelianBarang | `hasMany()` | User bisa ajukan banyak pembelian |
| PerolehanBarang | PerolehanBarangDetail | `hasMany()` | 1 perolehan punya banyak detail |
| JurnalUmum | JurnalDetail | `hasMany()` | 1 jurnal punya banyak detail entries |
| PenyusutanAsetTetap | PenyusutanDetail | `hasMany()` | 1 aset punya banyak bulan penyusutan |
| BarangKantor | PeminjamanBarang | `hasMany()` | 1 barang bisa dipinjam berkali-kali |
| BarangKantor | PemakaianBHP | `hasMany()` | 1 BHP bisa dipakai berkali-kali |

### **Many-to-One Relationships**
| Child | Parent | Relasi | Deskripsi |
|-------|--------|--------|-----------|
| BarangKantor | KategoriAsetTetap | `belongsTo()` | Barang punya 1 kategori |
| PeminjamanBarang | BarangKantor | `belongsTo()` | Peminjaman merujuk 1 barang |
| PeminjamanBarang | User | `belongsTo()` | Peminjaman milik 1 user |
| PemakaianBHP | BarangKantor | `belongsTo()` | Pemakaian merujuk 1 barang |
| JurnalDetail | Coa | `belongsTo()` | Detail merujuk 1 akun COA |
| PenyusutanDetail | PenyusutanAsetTetap | `belongsTo()` | Detail merujuk 1 penyusutan |

### **Polymorphic Relationships**
| Referrer | Target | Deskripsi |
|----------|--------|-----------|
| JurnalUmum | PerolehanBarang | Optional ref via `reff_perolehan_barang` |
| JurnalUmum | PenyusutanAsetTetap | Optional ref via `reff_penyusutan` |
| JurnalUmum | PengisianKasKecil | Optional ref via `reff_pengisian_kas_kecil` |
| JurnalUmum | PendapatanHibah | Optional ref via `reff_pendapatan_hibah` |

---

## 🏗️ Core Domain Models

### **Master Data Layer**
- **User**: Authentication, roles, user management
- **Coa**: Chart of Accounts for financial transactions
- **KategoriAsetTetap**: Asset categorization with depreciation rules
- **BarangKantor**: Inventory master data (aset + BHP)

### **Transaction Layer**
- **PerolehanBarang + PerolehanBarangDetail**: Procurement (3 sumber)
- **PengisianKasKecil**: Cash entry to petty cash
- **PendapatanHibah**: Donation/grant income
- **PeminjamanBarang**: Asset borrowing
- **PemakaianBHP**: Consumption of materials

### **Financial Layer**
- **JurnalUmum + JurnalDetail**: General journal entries
- **PenyusutanAsetTetap + PenyusutanDetail**: Depreciation tracking
- **BukuBesar**: General ledger reports

### **Request/Workflow Layer**
- **PengajuanPembelianBarang**: Purchase requests (approval workflow)

---

## 📋 Key Model Attributes

### **BarangKantor Statuses**
```
status_penggunaan:
  - 'belum_siap_digunakan'  (new item, not ready)
  - 'siap_digunakan'        (ready for use)

status_barang:
  - 'Aktif'      (in use)
  - 'Tidak Aktif' (not in use)

status_pinjam:
  - 'Tersedia'              (available)
  - 'Sedang Dipinjam'       (borrowed)
  - 'Telah Didistribusikan' (distributed)
  - 'Tidak untuk Dipinjamkan' (not for loan)

kategori_barang:
  - 'aset'  (fixed asset)
  - 'bhp'   (consumable material)
```

### **PerolehanBarang Sumber**
```
sumber_perolehan:
  - 'pembelian'       (purchase)
  - 'hibah'           (donation - legacy)
  - 'hibah_barang'    (goods donation)
  - 'hibah_uang'      (cash donation)
```

### **PeminjamanBarang Statuses**
```
status_pinjam:
  - 'pending'                    (waiting approval)
  - 'disetujui'                  (approved, borrowed)
  - 'menunggu_verifikasi_pengembalian' (return pending)
  - 'kembali'                    (returned)
  - 'ditolak'                    (rejected)
  - 'expired'                    (overdue)
```

### **JurnalUmum Tipe Transaksi**
```
tipe_transaksi:
  - 'perolehan_barang'      (procurement)
  - 'penyusutan'            (depreciation)
  - 'pengisian_kas_kecil'   (cash entry)
  - 'pendapatan_hibah'      (donation income)
  - 'pembelian_barang'      (purchase - legacy)
```

---

## 🔄 Data Flow

```
User Input
    ↓
Request/Transaction Models
    ├─ PeminjamanBarang → Update BarangKantor.stok
    ├─ PemakaianBHP → Update BarangKantor.stok
    ├─ PengajuanPembelianBarang → Pending request
    ├─ PerolehanBarang → Allocate BarangKantor
    ├─ PengisianKasKecil → Update Coa balance
    └─ PendapatanHibah → Update Coa balance
    ↓
Auto Journal Creation (via Event Listeners)
    ├─ Create JurnalUmum
    ├─ Create JurnalDetail (debit/kredit pairs)
    └─ Reference back to transaction
    ↓
Financial Reporting
    ├─ BukuBesar (per COA account)
    ├─ Depreciation tracking via PenyusutanDetail
    └─ Balance calculations
```

---

## 💡 Design Patterns

### **1. Auto-Journal Creation**
- Every transaction (PerolehanBarang, Penyusutan, Kas Kecil, Hibah) auto-creates JurnalUmum entries
- Ensures double-entry bookkeeping
- JurnalUmum has polymorphic references to transactions

### **2. Event-Driven Updates**
```php
// When PerolehanBarang is created:
// ├─ Generate barcode for aset
// ├─ Create BarangKantor records
// ├─ Create PenyusutanAsetTetap
// └─ Create JurnalUmum entries

// When BarangKantor status changes:
// └─ Update related PenyusutanAsetTetap
```

### **3. Inventory Management**
- BarangKantor.stok tracks availability
- PeminjamanBarang.markAsBorrowed() reduces stok
- PemakaianBHP reduces stok (consumption)
- PerolehanBarang allocation increases stok

### **4. Financial Accuracy**
- Every JurnalUmum must have balanced JurnalDetail (debit = kredit)
- All monetary fields are integers (in cents or rupiah)
- Coa.jumlah_saldo auto-calculated from JurnalDetail

### **5. Cascading Deletes**
```php
// Delete PerolehanBarang → Delete PerolehanBarangDetail
// Delete JurnalUmum → Delete JurnalDetail
// Delete PenyusutanAsetTetap → Delete PenyusutanDetail → Update JurnalUmum
```

---

## 🔐 Key Constraints

| Constraint | Implementation | Purpose |
|-----------|----------------|---------|
| **Barcode Uniqueness** | `barcode` unique index | Prevent duplicate codes |
| **Kode Barang PK** | `kode_barang` PRIMARY KEY | Ensure unique inventory codes |
| **Jurnal Balance** | Debit = Kredit validation | Financial accuracy |
| **Stok Non-Negative** | `stok >= 0` constraint | Prevent over-allocation |
| **COA Existence** | FK to `coa.kode_akun` | Ensure valid accounts |
| **User Role** | `user_group` IN (admin, anggota) | Authorization |
| **Date Consistency** | `tanggal_diterima` <= `tanggal_pinjam` | Logical order |

---

## 📖 Inheritance Hierarchy

TA2025 uses **Eloquent Models** (no explicit inheritance in code), but conceptually:

```
Eloquent\Model (Laravel base)
    ├─ User (Authentication)
    ├─ BarangKantor (Master Data)
    ├─ KategoriAsetTetap (Master Data)
    ├─ Coa (Master Data)
    ├─ PerolehanBarang (Transaction)
    │   └─ PerolehanBarangDetail (Detail)
    ├─ PeminjamanBarang (Transaction)
    ├─ PemakaianBHP (Transaction)
    ├─ PengajuanPembelianBarang (Workflow)
    ├─ PengisianKasKecil (Transaction)
    ├─ PendapatanHibah (Transaction)
    ├─ JurnalUmum (Financial)
    │   └─ JurnalDetail (Detail)
    ├─ PenyusutanAsetTetap (Financial)
    │   └─ PenyusutanDetail (Detail)
    └─ BukuBesar (Reporting)
```

All models follow Eloquent conventions with model-specific primary keys and timestamping.

