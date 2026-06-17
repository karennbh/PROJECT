# Mermaid Sequence Diagram - Sistem TA2025

## Deskripsi Sistem TA2025

Sistem TA2025 adalah aplikasi manajemen aset dan keuangan berbasis Laravel dengan Filament Admin Panel. Sistem ini digunakan untuk:

1. **Manajemen Master Data**: User, COA (Chart of Accounts), Kategori Aset, Barang Kantor
2. **Transaksi Admin**: Pendapatan Hibah, Pengisian Kas Kecil, Perolehan Barang, Penyusutan Aset
3. **Operasi Anggota/User**: Peminjaman Barang, Pemakaian BHP, Pengajuan Pembelian
4. **Laporan Akuntansi**: Buku Besar, Jurnal Umum, Penyusutan Aset Tetap
5. **Audit Trail**: Riwayat aktivitas semua transaksi

---

## Diagram Sequence Lengkap Sistem

```mermaid
sequenceDiagram
    actor User
    participant Browser
    participant Laravel as Laravel<br/>Application
    participant Auth as Auth<br/>Controller
    participant DB as Database
    participant Session as Session<br/>Handler
    participant Filament as Filament<br/>Admin Panel
    participant Model as Model &<br/>Business Logic
    participant Journal as Journal<br/>Generator
    participant Response as View/<br/>Response

    %% ==================== AUTHENTICATION FLOW ====================
    rect rgb(200, 220, 255)
        Note over User,Response: AUTHENTICATION & LOGIN FLOW
        
        User->>Browser: Akses aplikasi (/)
        Browser->>Laravel: GET /
        Laravel->>Auth: Check user session
        alt User belum login
            Auth->>Response: Redirect ke /login
            Response->>Browser: Display login form
            Browser->>User: Show login page
        else User sudah login
            Auth->>Auth: Check user_group
            alt Admin
                Auth->>Response: Redirect ke /admin
            else Anggota
                Auth->>Response: Redirect ke /dashboard
            end
            Response->>Browser: Redirect response
        end
        
        User->>Browser: Input username & password
        Browser->>Laravel: POST /login
        Laravel->>Auth: Process login
        Auth->>DB: Validasi username & password (User model)
        alt Login berhasil
            DB->>Auth: Return user data
            Auth->>Session: Regenerate session
            Session->>DB: Store last_activity_at timestamp
            Auth->>Response: Return redirect + user_id + user_group
            Response->>Browser: Redirect ke dashboard/admin
            Browser->>User: Login successful
        else Login gagal
            DB->>Auth: User not found or invalid password
            Auth->>Response: Return error message
            Response->>Browser: Show error
            Browser->>User: Display error
        end
    end

    %% ==================== SESSION MANAGEMENT ====================
    rect rgb(220, 255, 220)
        Note over User,Response: SESSION & IDLE TIMEOUT MANAGEMENT
        
        Note over Browser: Session lifetime: 45 menit (dari .env)
        Browser->>Laravel: Periodic session check
        Laravel->>Session: Check last_activity_at
        alt Activity detected
            Session->>DB: Update last_activity_at
            Session->>Response: Session valid
        else Timeout (45 menit idle)
            Session->>Response: Session expired
            Response->>Browser: Redirect ke /login
            Browser->>User: Session expired message
        end
    end

    %% ==================== ADMIN MASTER DATA FLOW ====================
    rect rgb(255, 230, 200)
        Note over User,Response: ADMIN: INPUT MASTER DATA
        
        User->>Browser: Access /admin
        Browser->>Filament: Load admin panel
        Filament->>DB: Fetch existing resources
        
        Note over Filament: 📊 Master Data Resources:<br/>Users, COA, Kategori Aset,<br/>Barang Kantor
        
        User->>Filament: Navigate to Barang Kantor
        Filament->>DB: List barang kantor
        DB->>Filament: Return barang list
        Filament->>Browser: Display table
        Browser->>User: Show barang list
        
        User->>Filament: Create/Edit barang kantor
        Filament->>Browser: Show form
        Browser->>User: Input barang details
        
        User->>Browser: Submit form
        Browser->>Filament: POST data
        Filament->>Model: Instantiate BarangKantor model
        Model->>Model: Trigger validation
        
        alt Valid data
            Model->>DB: Save barang kantor
            DB->>Model: Confirm save
            
            alt Barang baru (kategori_barang = aset)
                Model->>Model: Generate barcode otomatis (BRG-000001)
                Model->>DB: Create penyusutan record otomatis
                Model->>Model: Generate QR code URL
                DB->>Model: Confirm auto-generated data
            end
            
            Model->>Response: Success response
            Response->>Browser: Show success message + redirect
            Browser->>User: Data saved successfully
        else Invalid data
            Model->>Response: Validation error
            Response->>Browser: Show form with errors
            Browser->>User: Display validation errors
        end
    end

    %% ==================== ACCOUNTING SETUP FLOW ====================
    rect rgb(255, 200, 200)
        Note over User,Response: ADMIN: SETUP ACCOUNTING (COA)
        
        User->>Filament: Create COA (Chart of Accounts)
        Filament->>Browser: Show COA form
        Browser->>User: Input akun details
        
        User->>Browser: Submit
        Browser->>Filament: POST COA data
        Filament->>Model: Instantiate Coa model
        Model->>DB: Save COA
        DB->>Model: Confirm save
        
        Model->>Model: syncPendingTransactionJournals()
        Note over Model: Check pending journals:<br/>- Perolehan Barang<br/>- Pendapatan Hibah<br/>- Pengisian Kas Kecil
        
        alt Pending journals found
            Model->>Journal: Create jurnal for each transaction
            Journal->>DB: Save jurnal records
            DB->>Journal: Confirm save
            Journal->>Model: Return success
        end
        
        Model->>Response: Success
        Response->>Browser: Show success message
        Browser->>User: COA created & journals synced
    end

    %% ==================== TRANSACTION ENTRY FLOW ====================
    rect rgb(200, 255, 200)
        Note over User,Response: ADMIN: INPUT TRANSACTIONS
        
        Note over Filament: 📝 Transaction Types:<br/>Pendapatan Hibah, Pengisian Kas Kecil,<br/>Perolehan Barang, Penyusutan Aset
        
        User->>Filament: Create Pendapatan Hibah
        Filament->>Browser: Show form
        Browser->>User: Input hibah details<br/>(sumber, tanggal, nilai)
        
        User->>Browser: Select COA (Kas Bank Hibah)
        alt COA tersedia
            Browser->>Filament: Load COA options
        else COA belum ada
            Filament->>Response: Show "Tambahkan akun COA" button
            Response->>Browser: Display add COA option
        end
        
        User->>Browser: Submit hibah
        Browser->>Filament: POST data
        Filament->>Model: Instantiate PendapatanHibah
        Model->>DB: Save hibah
        DB->>Model: Confirm
        
        Model->>Model: generateNoHibah()
        Note over Model: Generate: PDH-0001
        
        Model->>Journal: syncJurnalUmum()
        Journal->>Journal: Create two journal entries
        Note over Journal: Debit: Kas Bank Hibah<br/>Kredit: Pendapatan Donasi Hibah
        
        Journal->>DB: Save jurnal entries
        DB->>Journal: Confirm
        
        Journal->>Model: Return success
        Model->>Response: Success response
        Response->>Browser: Show success + redirect
        Browser->>User: Transaction recorded & journals created
    end

    %% ==================== PEROLEHAN BARANG FLOW ====================
    rect rgb(200, 220, 255)
        Note over User,Response: ADMIN: PEROLEHAN BARANG (ASSET PROCUREMENT)
        
        User->>Filament: Create Perolehan Barang
        Filament->>Browser: Show form
        Browser->>User: Select source<br/>(Pembelian/Hibah Uang/Hibah Barang)
        
        User->>Browser: Select Pembelian
        Browser->>Filament: Update form
        Filament->>Model: Get Perolehan ID<br/>generatePerolehanId()
        Note over Model: Generate ID format:<br/>PRL-PB-0001 (Pembelian)<br/>PRL-HU-0002 (Hibah Uang)<br/>PRL-HB-0003 (Hibah Barang)
        Model->>Browser: Display ID + form
        
        User->>Browser: Input barang details<br/>(tanggal, kategori, harga, etc)
        Browser->>User: Show barang input table
        
        User->>Browser: Add multiple barang rows
        Browser->>Filament: Submit perolehan
        Filament->>Model: Instantiate PerolehanBarang
        
        Model->>Model: Validate all details
        alt Aset (kategori_barang = aset)
            Model->>DB: Create detail record
            Model->>Model: Allocate barang dari detail
            Note over Model: PerolehanBarangAllocator:<br/>Create BarangKantor record
            Model->>DB: Create BarangKantor
        else BHP (kategori_barang = bhp)
            Model->>DB: Create/update BarangKantor<br/>(increment stok)
        end
        
        Model->>Journal: syncJurnalUmum()
        Journal->>Journal: Create multi-entry journal
        Note over Journal: Debit: Aset/BHP account<br/>Kredit: Kas/Hutang/Donasi
        Journal->>DB: Save jurnal entries
        
        Model->>Response: Success
        Response->>Browser: Show success
        Browser->>User: Perolehan recorded & items allocated
    end

    %% ==================== USER PEMINJAMAN BARANG ====================
    rect rgb(255, 240, 200)
        Note over User,Response: USER/ANGGOTA: PEMINJAMAN BARANG
        
        User->>Browser: Access /peminjaman
        Browser->>Laravel: GET /peminjaman
        Laravel->>Model: Fetch available barang
        Note over Model: scopeBorrowableForPeminjaman():<br/>Filter barang yang bisa dipinjam
        Model->>DB: Query barang kantor
        DB->>Model: Return borrowable items
        
        Model->>Response: Pass data to view
        Response->>Browser: Display available barang
        Browser->>User: Show list of items to borrow
        
        User->>Browser: Select barang & input qty
        Browser->>Filament: Show form
        Browser->>User: Input riwayat/tujuan peminjaman
        
        User->>Browser: Submit peminjaman
        Browser->>Laravel: POST /peminjaman
        Laravel->>Model: Create PeminjamanBarang
        
        alt Validation passed
            Model->>DB: Save peminjaman record
            DB->>Model: Confirm
            
            Model->>Model: markAsBorrowed()
            Note over Model: Reduce stok quantity<br/>Change status to borrowed
            
            Model->>DB: Update BarangKantor
            DB->>Model: Confirm
            
            Model->>Response: Success
            Response->>Browser: Show confirmation
            Browser->>User: Peminjaman recorded!
        else Validation failed
            Model->>Response: Error message
            Response->>Browser: Display error
            Browser->>User: Show error details
        end
    end

    %% ==================== USER PEMINJAMAN PENGEMBALIAN ====================
    rect rgb(240, 255, 200)
        Note over User,Response: USER/ANGGOTA: PENGEMBALIAN BARANG
        
        User->>Browser: Access /peminjaman/riwayat
        Browser->>Laravel: GET /peminjaman/riwayat
        Laravel->>Model: Fetch user peminjaman history
        Model->>DB: Query PeminjamanBarang records
        DB->>Model: Return borrowing history
        
        Model->>Response: Pass data to view
        Response->>Browser: Display peminjaman list
        Browser->>User: Show items to return
        
        User->>Browser: Click kembalikan
        Browser->>Laravel: PATCH /peminjaman/kembalikan/{id}
        Laravel->>Model: Load PeminjamanBarang
        
        Model->>Model: markAsReturned()
        Note over Model: Increase stok quantity<br/>Change status to available
        
        Model->>DB: Update BarangKantor + Peminjaman
        DB->>Model: Confirm
        
        Model->>Response: Success
        Response->>Browser: Show confirmation
        Browser->>User: Item returned successfully
    end

    %% ==================== USER PEMAKAIAN BHP ====================
    rect rgb(200, 255, 255)
        Note over User,Response: USER/ANGGOTA: PEMAKAIAN BHP
        
        User->>Browser: Access /pemakaian
        Browser->>Laravel: GET /pemakaian
        Laravel->>Model: Fetch BHP items
        Note over Model: Filter barang where<br/>kategori_barang = 'bhp'
        Model->>DB: Query BHP items
        DB->>Model: Return BHP list
        
        Model->>Response: Pass to view
        Response->>Browser: Display BHP list
        Browser->>User: Show consumables
        
        User->>Browser: Select BHP & input qty
        Browser->>User: Show usage details form
        
        User->>Browser: Submit pemakaian
        Browser->>Laravel: POST /pemakaian
        Laravel->>Model: Create PemakaianBHP
        
        alt Validation passed
            Model->>Model: Validate stok available
            alt Stok cukup
                Model->>DB: Save PemakaianBHP
                DB->>Model: Confirm
                
                Model->>Model: Reduce stok
                Model->>DB: Update BarangKantor (stok)
                DB->>Model: Confirm
                
                Model->>Response: Success
                Response->>Browser: Show confirmation
                Browser->>User: Usage recorded & stok updated
            else Stok tidak cukup
                Model->>Response: Error insufficient stok
                Response->>Browser: Show error
                Browser->>User: Not enough stock
            end
        else Validation failed
            Model->>Response: Validation error
            Response->>Browser: Show form errors
            Browser->>User: Display errors
        end
    end

    %% ==================== APPROVAL & PENGAJUAN FLOW ====================
    rect rgb(255, 200, 255)
        Note over User,Response: USER/ADMIN: PENGAJUAN PEMBELIAN BARANG
        
        User->>Browser: Access /pembelian
        Browser->>Laravel: GET /pembelian
        Laravel->>Model: Fetch purchase requests
        Model->>DB: Query PengajuanPembelianBarang
        DB->>Model: Return requests
        
        Model->>Response: Pass to view
        Response->>Browser: Display requests
        Browser->>User: Show purchase proposals
        
        User->>Browser: Submit pengajuan pembelian
        Browser->>Laravel: POST /pembelian
        Laravel->>Model: Create PengajuanPembelianBarang
        
        Model->>DB: Save pengajuan
        DB->>Model: Confirm
        
        Model->>Response: Success
        Response->>Browser: Confirmation
        Browser->>User: Request submitted
        
        par Admin approval process
            Admin->>Filament: Access Pengajuan Resources
            Filament->>DB: Load pending requests
            DB->>Filament: Return requests
            
            Admin->>Filament: Review & approve/reject
            Filament->>Model: Update status
            Model->>DB: Save status change
            DB->>Model: Confirm
            
            Model->>Journal: If approved - syncJurnalUmum()
            Journal->>DB: Create accounting entries
            
            Filament->>Response: Success
        end
        
        Note over User: Notification/Email sent to user
    end

    %% ==================== REPORTING & ACCOUNTING ====================
    rect rgb(220, 220, 220)
        Note over User,Response: REPORTING & ACCOUNTING VIEWS
        
        Admin->>Filament: Access Buku Besar (General Ledger)
        Filament->>DB: Query JurnalDetail records
        Note over DB: Join with COA for<br/>account names & balances
        DB->>Filament: Return journal details
        Filament->>Response: Render general ledger view
        Response->>Browser: Display ledger
        Browser->>Admin: Show account balances
        
        Admin->>Filament: Access Jurnal Umum (Journal)
        Filament->>DB: Query JurnalUmum records
        DB->>Filament: Return journal entries
        Filament->>Response: Render journal view
        Response->>Browser: Display transactions
        Browser->>Admin: Show all journal entries
        
        Admin->>Filament: Access Penyusutan Aset Tetap
        Filament->>DB: Query PenyusutanAsetTetap
        Note over DB: Linked to BarangKantor<br/>& KategoriAsetTetap
        DB->>Filament: Return depreciation data
        Filament->>Response: Render depreciation report
        Response->>Browser: Display depreciation schedule
        Browser->>Admin: Show asset depreciation
    end

    %% ==================== ACTIVITY LOGGING ====================
    rect rgb(200, 200, 255)
        Note over User,Response: AUDIT TRAIL & ACTIVITY LOGGING
        
        Note over Database: All transaction changes logged<br/>via Laravel audit system
        
        User->>Browser: Access /riwayat (Activity History)
        Browser->>Laravel: GET /riwayat
        Laravel->>Model: Fetch user activities
        Model->>DB: Query activity/audit logs
        DB->>Model: Return user's activities
        
        Model->>Response: Pass to view
        Response->>Browser: Display activity timeline
        Browser->>User: Show riwayat aktivitas
    end
```

---

## Ringkasan Komponen Utama

### 🔐 Authentication & Authorization
- **File**: `app/Http/Controllers/AuthController.php`, `app/Filament/Auth/Login.php`
- **Flow**: Login → Validasi → Session creation → Role-based redirect
- **Session Timeout**: 45 menit idle timeout

### 📊 Master Data (Admin Panel)
- **Users**: `app/Filament/Admin/Resources/Users/UserResource.php`
- **COA (Akun Akuntansi)**: `app/Models/coa.php`
- **Kategori Aset**: `app/Models/KategoriAsetTetap.php`
- **Barang Kantor**: `app/Models/BarangKantor.php` (Aset & BHP)
  - Auto-generate barcode & penyusutan untuk aset
  - QR code integration dari QuickChart

### 💰 Transaksi Admin
- **Pendapatan Hibah**: Uang masuk dari donasi → Auto-create jurnal
- **Pengisian Kas Kecil**: Refill kas kecil → Auto-create jurnal
- **Perolehan Barang**: Input aset/BHP baru (3 sumber: pembelian, hibah uang, hibah barang)
  - Auto allocate barang dari detail
  - Auto-create multi-entry jurnal
- **Penyusutan Aset Tetap**: Calculate & record depreciation

### 👤 Operasi User/Anggota
- **Peminjaman Barang**: Request → Approve → Return (stok otomatis berkurang/bertambah)
- **Pemakaian BHP**: Request usage → Record → Stok berkurang
- **Pengajuan Pembelian**: Submit proposal → Admin review → Approval
- **Riwayat Aktivitas**: Audit trail semua transaksi user

### 📈 Laporan & Akuntansi
- **Buku Besar**: Account balances dari JurnalDetail
- **Jurnal Umum**: All journal entries
- **Penyusutan Aset**: Depreciation schedule

---

## Key Features Sistem

1. **Automatic Journal Generation**
   - Setiap transaksi otomatis membuat jurnal akuntansi
   - Debit-kredit balance dijamin

2. **Barcode & QR Code Integration**
   - Auto-generate barcode untuk setiap barang aset
   - QR code untuk scanning di halaman detail

3. **Multi-tab Session Management**
   - SessionStorage token untuk multi-tab authentication
   - Deteksi jika login di tab berbeda dengan akun berbeda

4. **Role-Based Access Control (RBAC)**
   - Admin → Filament Admin Panel + Master Data + Reports
   - Anggota → User Dashboard + Request/Approval Workflow

5. **Automatic Calculation**
   - Stok otomatis berkurang saat peminjaman
   - Stok otomatis bertambah saat pengembalian
   - Penyusutan aset tetap otomatis

6. **Idle Timeout Security**
   - 45 menit idle → Auto logout
   - Server-side + client-side validation

---

## Teknologi Stack

- **Framework**: Laravel 11 + Filament Admin Panel
- **ORM**: Eloquent
- **Database**: MySQL
- **Frontend**: Blade Templates + Livewire
- **Barcode**: QuickChart QR, Html5Qrcode scanner
- **Authentication**: Laravel Authentication
- **Validation**: Form Request + Model validation

---

## Catatan Penting

- **Pending Journals**: Jika COA belum ada, sistem mencatat journal sebagai "pending" sampai akun dibuat
- **Stok Tracking**: Diimplementasikan via model methods `markAsBorrowed()` & `markAsReturned()`
- **Journal Sync**: Model `Coa` otomatis sync pending journals saat COA baru dibuat
- **Barcode Format**: `BRG-000001`, `PDH-0001`, `PKK-0001`, `PRL-PB-0001`, dll

