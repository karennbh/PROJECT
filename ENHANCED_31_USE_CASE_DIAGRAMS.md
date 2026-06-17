# Enhanced 31 Use Case Sequence Diagrams - Sistem TA2025
## Dengan Detail Lengkap, Validasi, Auto-Processing, dan Approval Workflow

---

## UC-MASTER-BARANG-001: Admin Create/Edit Barang Kantor (Enhanced)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant Filament
    participant Event as Event Handler
    participant Model as BarangKantor<br/>Model
    participant Barcode as Barcode<br/>Generator
    participant Penyusutan as Penyusutan<br/>Generator
    participant DB as Database
    participant Notification as Notification

    Admin->>Browser: Akses /admin/barang-kantors
    Browser->>Filament: GET /admin/barang-kantors
    Filament->>DB: Fetch barang list
    DB->>Filament: Return list
    Filament->>Browser: Display table
    Browser->>Admin: Show barang list

    Admin->>Browser: Click create/edit
    Filament->>DB: Load kategori, satuan options
    Filament->>Browser: Show form
    Browser->>Admin: Display form

    Admin->>Browser: Fill:<br/>- Kategori (aset/bhp)<br/>- Nama barang<br/>- Stok<br/>- Satuan<br/>- Harga perolehan<br/>- Tanggal diterima<br/>- Status penggunaan<br/>- Jenis aset/BHP

    Admin->>Browser: Submit form
    Browser->>Filament: POST data
    Filament->>Model: Create/Update BarangKantor
    
    Model->>Model: Validate required fields
    alt Missing required fields
        Model->>Browser: Validation error
        Browser->>Admin: Show field errors
    else All fields valid
        Model->>Model: Check business rules
        alt kategori = aset AND status = siap_digunakan AND tanggal_diterima empty
            Model->>Browser: Error: Aset siap harus punya tanggal_diterima
            Browser->>Admin: Show error
        else kategori = bhp AND stok < 0
            Model->>Browser: Error: Stok BHP tidak boleh negatif
            Browser->>Admin: Show error
        else All rules pass
            Model->>DB: Save barang record
            DB->>Model: Return barang ID
            
            alt kategori = aset (new record only)
                Event->>Barcode: Trigger model event 'created'
                Barcode->>Barcode: Generate barcode format
                Note over Barcode: Format: BRG-XXXXX<br/>Using sequence number
                Barcode->>DB: Update barcode field
                DB->>Barcode: Confirm
                
                Event->>Penyusutan: Auto-create penyusutan
                Penyusutan->>Penyusutan: Load KategoriAset data
                Penyusutan->>Penyusutan: Calculate:<br/>- umur_ekonomis<br/>- persentase penyusutan<br/>- tanggal mulai
                Penyusutan->>DB: Create PenyusutanAsetTetap record
                DB->>Penyusutan: Confirm
                
                Event->>Barcode: Generate QR code
                Barcode->>Barcode: Create URL:<br/>https://quickchart.io/qr?text=...
                Barcode->>Model: Set QR URL attribute
            end
            
            Event->>Event: Trigger model event 'saved'
            Event->>Model: syncAssetStatuses()
            Model->>Model: Sync status to penyusutan if aset
            alt kategori = aset
                Model->>DB: Update PenyusutanAsetTetap.status
                DB->>Model: Confirm
            end
            
            Model->>Notification: Send creation notification
            Notification->>Notification: Build message
            Note over Notification: Barang '[nama]' telah dibuat<br/>Barcode: [code]<br/>Kategori: [kategori]
            Notification->>Browser: Toast/success message
            
            Model->>Browser: Success response + redirect
            Browser->>Admin: Show success message + redirect to detail
        end
    end
```

---

## UC-TRANS-PEROLEHAN-001: Admin Create Perolehan Barang (Unified)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant Filament
    participant Page as Create Page
    participant Model as PerolehanBarang
    participant Allocator as PerolehanBarang<br/>Allocator
    participant KasKecil as KasKecilBalance
    participant Journal as Journal<br/>Generator
    participant DB as Database
    participant Notification

    Admin->>Browser: Akses /admin/perolehan-barangs
    Browser->>Filament: GET list
    Filament->>DB: Fetch perolehan list
    Filament->>Browser: Display table
    Browser->>Admin: Show perolehan

    Admin->>Browser: Click create
    Page->>Model: generatePerolehanId()
    Note over Model: Generate:<br/>PRL-PB-0001 (Pembelian)<br/>PRL-HU-0002 (Hibah Uang)<br/>PRL-HB-0003 (Hibah Barang)
    Page->>DB: Load COA, hibah, kategori options
    Page->>Browser: Show form with ID & dropdowns
    Browser->>Admin: Display form

    Admin->>Browser: Select sumber perolehan
    
    alt sumber = Pembelian
        Browser->>Filament: Update form for pembelian
        Filament->>Browser: Show purchase-specific fields
        Admin->>Browser: Fill:<br/>- Tanggal pembelian<br/>- Tanggal diterima<br/>- Items (nama, kategori, qty, harga, diskon, biaya)
        
    else sumber = Hibah Uang
        Browser->>Filament: Update form for hibah uang
        Filament->>DB: Load hibah dengan sisa
        Filament->>Browser: Show hibah dropdown
        Admin->>Browser: Select hibah + fill items
        
    else sumber = Hibah Barang
        Browser->>Filament: Update form for hibah barang
        Filament->>Browser: Show hibah barang fields
        Admin->>Browser: Fill:<br/>- Sumber hibah (nama pemberi)<br/>- Tanggal diterima<br/>- Items
    end

    Admin->>Browser: Fill items table (multiple rows)
    Admin->>Browser: Click "Hitung Alokasi" button
    Browser->>Allocator: POST calculate allocation
    
    Allocator->>Allocator: For each item:<br/>- total_harga = qty * harga_satuan<br/>- Calculate discount allocation<br/>- Calculate other cost allocation<br/>- Compute harga_perolehan
    
    alt sumber = Pembelian
        Allocator->>Allocator: total_harga_perolehan = sum
        Allocator->>KasKecil: Check available balance
        KasKecil->>DB: Query COA 'Kas Kecil'
        DB->>KasKecil: Return saldo + jurnal
        KasKecil->>KasKecil: Calculate:<br/>saldo_awal + debit - kredit
        alt Kas kecil insufficient
            KasKecil->>Browser: Error: Not enough kas kecil
            Browser->>Admin: Show error + required balance
        else Kas cukup
            Allocator->>Browser: Return calculated allocation
        end
    else sumber = Hibah Uang
        Allocator->>Allocator: total <= sisa hibah?
        alt Total > sisa
            Allocator->>Browser: Error: Exceeds hibah balance
            Browser->>Admin: Show error
        else Valid
            Allocator->>Browser: Return allocation
        end
    else sumber = Hibah Barang
        Allocator->>Browser: Return allocation
    end

    Admin->>Browser: Review calculated allocation
    Admin->>Browser: Submit form
    Browser->>Filament: POST perolehan data
    Filament->>Model: Create PerolehanBarang
    
    Model->>Model: Validate all items
    alt Invalid items
        Model->>Browser: Validation error
        Browser->>Admin: Show error
    else Valid
        Model->>DB: Save PerolehanBarang record
        DB->>Model: Return ID
        
        Model->>DB: Save PerolehanBarangDetail records
        DB->>Model: Confirm
        
        loop For each detail item
            Model->>Allocator: Allocate barang
            
            alt item kategori = aset
                Allocator->>Allocator: Generate aset codes
                Note over Allocator: ASET-00001, ASET-00002, etc
                loop Create qty records
                    Allocator->>DB: Create BarangKantor (aset)
                    Allocator->>DB: Auto-create PenyusutanAsetTetap
                    DB->>Allocator: Confirm
                end
                
            else item kategori = bhp
                Allocator->>DB: Find/Create BarangKantor (BHP)
                alt BHP exists
                    Allocator->>DB: UPDATE stok += qty
                else BHP tidak ada
                    Allocator->>Allocator: Generate code BHP-00001
                    Allocator->>DB: CREATE new BarangKantor (BHP)
                end
                DB->>Allocator: Confirm
            end
            
            Model->>DB: Update PerolehanBarangDetail.barang allocation
        end
        
        Model->>Journal: syncJournalUmum()
        Journal->>Journal: Determine debit/kredit accounts:<br/>- Debit: Based on jenis (aset/bhp)<br/>- Kredit: Based on sumber
        
        Note over Journal: Debit accounts:<br/>- Aset Sarana Pendidikan<br/>- Aset Inventaris Kantor<br/>- Aset Kendaraan<br/>- BHP ATK<br/>- BHP Inventaris<br/><br/>Kredit accounts:<br/>- Kas Kecil (Pembelian)<br/>- Kas Bank Hibah (Hibah Uang)<br/>- Penerimaan Hibah Barang (Hibah Barang)
        
        Journal->>DB: Delete old jurnal (if edit)
        Journal->>DB: Create JurnalUmum record
        Journal->>DB: Create JurnalDetail records (debit)
        Journal->>DB: Create JurnalDetail records (kredit)
        DB->>Journal: Confirm all saved
        
        Model->>Notification: Send notification
        Notification->>Browser: Success message + details
        Browser->>Admin: Perolehan berhasil dicatat!<br/>Items allocated: [n] aset, [n] BHP<br/>Jurnal created
    end
```

---

## UC-TRANS-PENYUSUTAN-001: Admin Process End-of-Period Penyusutan (Enhanced)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant Page as ListPenyusutans
    participant Model as Penyusutan
    participant Calculator as Depreciation<br/>Calculator
    participant Journal as Journal<br/>Generator
    participant DB as Database
    participant Validator as Validation<br/>Handler

    Admin->>Browser: Access /admin/penyusutans
    Browser->>Page: GET /admin/penyusutans
    Page->>DB: Fetch penyusutan list with status
    Page->>Browser: Display table + period selector
    Browser->>Admin: Show penyusutan list + period form

    Admin->>Browser: Select bulan & tahun
    Admin->>Browser: Click "Proses Akhir Periode"
    Browser->>Page: POST process request
    
    Page->>Validator: Validate period
    alt Period invalid
        Validator->>Browser: Error: Invalid period
        Browser->>Admin: Show error
    else Period valid
        Page->>Model: Load all penyusutan to process
        
        Model->>DB: Query penyusutan WHERE
        Note over DB: - status_barang = 'Aktif'<br/>- status_penggunaan = 'siap_digunakan'<br/>- tanggal_diterima IS NOT NULL<br/>- periode belum diproses<br/>- in umur ekonomis
        
        DB->>Model: Return qualified penyusutan
        
        Model->>Model: Determine start month
        Note over Model: calculateBulanMulai():<br/>- If tgl diterima 1-15: start this month<br/>- If tgl diterima > 15: start next month
        
        loop For each penyusutan
            Model->>Validator: Validate penyusutan eligibility
            
            alt Not ready yet
                Validator->>Model: Mark: "Belum Waktunya"
                Model->>Model: Skip to next
                
            else Already complete
                Validator->>Model: Mark: "Lengkap"
                Model->>Model: Skip to next
                
            else Period already processed
                Validator->>Model: Mark: "Sudah diproses"
                Model->>Model: Skip to next
                
            else Missing data
                Validator->>Model: Mark: "Belum Siap Digunakan"
                Model->>Model: Skip to next
                
            else Ready to process
                Model->>Calculator: Calculate depreciation
                
                Calculator->>Calculator: Get kategori data
                Calculator->>Calculator: Compute beban_bulanan:<br/>= (harga_perolehan - nilai_residu)<br/>  / (umur_ekonomis_tahun * 12)
                
                Calculator->>Calculator: Calculate akumulasi:<br/>= previous_akumulasi + beban_bulanan
                
                Calculator->>Calculator: Calculate nilai_buku:<br/>= harga_perolehan - akumulasi
                
                Calculator->>Journal: Create jurnal entry
                
                Journal->>Journal: Create JurnalUmum:<br/>- tipe_transaksi = 'penyusutan'<br/>- penyusutan_id = [id]
                
                Journal->>DB: Save JurnalUmum
                DB->>Journal: Return jurnal_id
                
                Journal->>DB: Create JurnalDetail (Debit)
                Note over DB: Akun 5611104 - Beban Penyusutan<br/>Nominal = beban_bulanan
                DB->>Journal: Confirm
                
                Journal->>DB: Create JurnalDetail (Kredit)
                Note over DB: Akun 1264101 - Akumulasi Penyusutan<br/>Nominal = beban_bulanan
                DB->>Journal: Confirm
                
                Journal->>Model: Return jurnal created
                
                Model->>DB: Create PenyusutanDetail
                Note over DB: periode<br/>beban_penyusutan<br/>akumulasi<br/>nilai_buku<br/>jurnal_id
                DB->>Model: Confirm
                
                Model->>Model: Update status to 'processed'
            end
        end
        
        Page->>Page: Generate summary
        Note over Page: - Total processed<br/>- Total skipped (reasons)<br/>- Total jurnal created
        
        Page->>Browser: Success message + summary
        Browser->>Admin: Periode posting berhasil!<br/>- Processed: [n]<br/>- Belum Siap: [n]<br/>- Sudah Lengkap: [n]<br/>- Sudah Diproses: [n]
    end
```

---

## UC-USER-PINJAM-002: Anggota Request Peminjaman (Enhanced)

```mermaid
sequenceDiagram
    actor Anggota
    participant Browser
    participant Laravel
    participant Ctrl as Peminjaman<br/>Controller
    participant Model as Peminjaman<br/>Barang
    participant BarangModel as BarangKantor
    participant Policy as Permission<br/>Policy
    participant DB as Database
    participant Notification

    Anggota->>Browser: Access /peminjaman
    Browser->>Laravel: GET /peminjaman
    Laravel->>Ctrl: index()
    
    Ctrl->>BarangModel: Fetch borrowable barang
    BarangModel->>DB: Query WHERE<br/>- kategori_barang = 'aset'<br/>- status_penggunaan = 'active'<br/>- status_pinjam = 'available'<br/>- isSiapPakai() = true
    
    DB->>BarangModel: Return available barang
    Ctrl->>Browser: Load form with barang list
    Browser->>Anggota: Display peminjaman form

    Anggota->>Browser: Select barang
    Browser->>Ctrl: Load barang details
    alt Barang out of stock
        Browser->>Anggota: Disable "Pinjam" button
        Anggota->>Anggota: Cannot proceed
    else Barang available
        Browser->>Anggota: Show available qty
    end

    Anggota->>Browser: Fill form:<br/>- Barang<br/>- Qty peminjaman<br/>- Tujuan/riwayat<br/>- Kebutuhan<br/>- Perkiraan tanggal kembali (optional)
    
    Anggota->>Browser: Validate before submit
    Browser->>Browser: Check:<br/>- Qty <= available stok<br/>- Tujuan not empty<br/>- Qty > 0

    Anggota->>Browser: Submit form
    Browser->>Laravel: POST /peminjaman
    Laravel->>Ctrl: store(request)
    
    Ctrl->>Ctrl: Validate input
    alt Missing required fields
        Ctrl->>Browser: Validation error
        Browser->>Anggota: Show form errors
    else Invalid qty
        Ctrl->>Browser: Error: Invalid quantity
        Browser->>Anggota: Show error
    else All valid
        Ctrl->>Model: Create PeminjamanBarang instance
        Model->>Model: Set initial values
        Note over Model: - user_id = auth()->id()<br/>- barang_id = [selected]<br/>- qty_pinjam = [qty]<br/>- tujuan = [tujuan]<br/>- status = 'borrowed'<br/>- tanggal_pinjam = now()
        
        Model->>Model: Validate business logic
        alt Qty > available stok
            Model->>Browser: Error: Stock not enough
            Browser->>Anggota: Show error + available qty
        else User has unpaid debt
            Model->>Browser: Error: Unpaid peminjaman exists
            Browser->>Anggota: Show error + list unpaid
        else Barang tidak bisa dipinjam (status)
            Model->>Browser: Error: Item not available for borrowing
            Browser->>Anggota: Show error
        else All valid
            Model->>DB: Save peminjaman record
            DB->>Model: Return ID
            
            Model->>BarangModel: markAsBorrowed(qty)
            BarangModel->>BarangModel: Update stok
            Note over BarangModel: stok -= qty_pinjam<br/>status_pinjam = 'borrowed'
            BarangModel->>DB: Update barang_kantors
            DB->>BarangModel: Confirm
            
            Model->>Notification: Create notification
            Notification->>Notification: Build message
            Note over Notification: Peminjaman berhasil!<br/>- Barang: [nama]<br/>- Qty: [qty]<br/>- Tujuan: [tujuan]<br/>- Tanggal kembali: [expected date]
            Notification->>Browser: Toast success
            
            Model->>Notification: Notify admin
            Notification->>Notification: Send admin alert<br/>"Peminjaman baru dari [anggota]<br/>Barang: [nama]<br/>Qty: [qty]"
            
            Model->>Browser: Success response + redirect
            Browser->>Anggota: Peminjaman recorded!<br/>Redirect to riwayat
        end
    end
```

---

## UC-USER-PINJAM-ADMIN-APPROVAL: Admin Review Peminjaman (NEW)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant Filament
    participant Model as Peminjaman<br/>Barang
    participant Notification as Notification<br/>Service
    participant DB as Database

    Note over Browser: Optional workflow:<br/>Jika sistem memerlukan approval sebelum peminjaman final

    Admin->>Browser: Access /admin/peminjaman-barangs
    Browser->>Filament: GET /admin/peminjaman-barangs
    Filament->>DB: Fetch peminjaman with status
    DB->>Filament: Return list (borrowed, pending_approval, returned, dll)
    Filament->>Browser: Display table
    Browser->>Admin: Show peminjaman list

    Admin->>Browser: Click peminjaman untuk review
    Filament->>DB: Load peminjaman detail + barang + user
    DB->>Filament: Return detail
    Filament->>Browser: Show peminjaman detail
    Browser->>Admin: Display:<br/>- Anggota<br/>- Barang<br/>- Qty<br/>- Tujuan<br/>- Tanggal pinjam<br/>- Status

    Admin->>Browser: Review details
    
    alt Admin setuju
        Admin->>Browser: Click "Approve"
        Browser->>Filament: Submit approval
        Filament->>Model: Update status
        Model->>DB: Set status = 'approved'
        DB->>Model: Confirm
        
        Model->>Notification: Send user notification
        Notification->>Browser: "Peminjaman disetujui"
        
        Model->>Browser: Success
        Browser->>Admin: Peminjaman approved!
        
    else Admin tolak
        Admin->>Browser: Click "Reject"
        Browser->>Browser: Show rejection reason form
        Admin->>Browser: Input alasan penolakan
        
        Browser->>Filament: Submit rejection
        Filament->>Model: Update peminjaman
        Model->>BarangModel as BarangKantor: Reverse borrowing
        
        BarangModel->>BarangModel: markAsReturned(qty)
        BarangModel->>DB: Restore stok
        DB->>BarangModel: Confirm
        
        Model->>DB: Update peminjaman
        Note over DB: status = 'rejected'<br/>rejection_reason = [reason]
        DB->>Model: Confirm
        
        Model->>Notification: Send rejection notification
        Notification->>Browser: "Peminjaman ditolak: [reason]"
        
        Model->>Browser: Success
        Browser->>Admin: Peminjaman rejected + restored stok
    end
```

---

## UC-USER-PENGAJUAN-ADMIN-APPROVAL: Admin Approve Pengajuan Pembelian (NEW)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant Filament
    participant Model as Pengajuan<br/>Pembelian
    participant Journal as Journal
    participant Notification
    participant Email as Email Service
    participant DB as Database

    Admin->>Browser: Access /admin/pengajuan-pembelian-barangs
    Browser->>Filament: GET /admin/pengajuan-pembelian-barangs
    Filament->>DB: Fetch pengajuan with status
    DB->>Filament: Return (pending, approved, rejected, purchased)
    Filament->>Browser: Display table with filters
    Browser->>Admin: Show pengajuan list

    Admin->>Browser: Click pengajuan untuk review
    Filament->>DB: Load pengajuan detail + user + attachment
    DB->>Filament: Return detail
    Filament->>Browser: Show pengajuan detail
    Browser->>Admin: Display:<br/>- Pengaju (anggota)<br/>- Barang diajukan<br/>- Spesifikasi<br/>- Qty<br/>- Estimasi harga<br/>- Alasan<br/>- Prioritas<br/>- Tanggal diperlukan

    Admin->>Browser: Review pengajuan
    
    alt Admin setuju
        Admin->>Browser: Click "Approve"
        Browser->>Browser: Show approval form
        Admin->>Browser: Fill:<br/>- Catatan approval<br/>- Status (Approved/Pending Purchase)
        
        Browser->>Filament: Submit approval
        Filament->>Model: Update pengajuan
        
        Model->>DB: Update pengajuan
        Note over DB: status = 'approved'<br/>approved_by = admin_id<br/>approved_at = now()<br/>approval_note = [note]
        DB->>Model: Confirm
        
        Model->>Journal: Create optional jurnal
        alt Create as pending order
            Journal->>DB: Create memo/pending entry
        end
        
        Model->>Notification: Send notification to user
        Notification->>Notification: Build message
        Note over Notification: Pengajuan disetujui!<br/>- Barang: [nama]<br/>- Status: [approved/pending]<br/>- Catatan: [note]
        Notification->>Browser: Display message
        
        Model->>Email: Send email to user
        Email->>Email: Template: approval_notification<br/>With all details + next steps
        Email->>Email: Send email
        
        Model->>Browser: Success response
        Browser->>Admin: Pengajuan approved!
        
    else Admin tolak
        Admin->>Browser: Click "Reject"
        Browser->>Browser: Show rejection form
        Admin->>Browser: Input alasan penolakan
        
        Browser->>Filament: Submit rejection
        Filament->>Model: Update pengajuan
        
        Model->>DB: Update pengajuan
        Note over DB: status = 'rejected'<br/>rejection_reason = [reason]<br/>rejected_by = admin_id<br/>rejected_at = now()
        DB->>Model: Confirm
        
        Model->>Notification: Send rejection notification
        Notification->>Notification: Build message
        Note over Notification: Pengajuan ditolak<br/>Alasan: [reason]<br/>Hubungi admin untuk clarification
        
        Model->>Email: Send rejection email
        Email->>Email: Template: rejection_notification
        Email->>Email: Send email
        
        Model->>Browser: Success
        Browser->>Admin: Pengajuan rejected + user notified
        
    else Admin needs clarification
        Admin->>Browser: Click "Request Info"
        Browser->>Browser: Show clarification form
        Admin->>Browser: Input pertanyaan
        
        Browser->>Filament: Submit
        Filament->>Model: Update status
        
        Model->>DB: Set status = 'pending_clarification'
        Model->>Notification: Send clarification request
        Notification->>Email: Send email
        Email->>Email: Template: clarification_needed
        Email->>Email: Send with questions
        
        Browser->>Admin: Clarification request sent!
    end
```

---

## UC-TRANS-HIBAH-001: Admin Create Pendapatan Hibah (Enhanced)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant Filament
    participant Model as Pendapatan<br/>Hibah
    participant CoaModel as Coa Model
    participant Journal as Journal<br/>Generator
    participant Validator as Validator
    participant DB as Database
    participant Notification

    Admin->>Browser: Access /admin/pendapatan-hibahs
    Browser->>Filament: GET /admin/pendapatan-hibahs
    Filament->>DB: Fetch hibah list
    Filament->>Browser: Display table
    Browser->>Admin: Show hibah list

    Admin->>Browser: Click create
    Filament->>DB: Load COA list (filter for hibah-related)
    Filament->>Browser: Show form
    Browser->>Admin: Display form

    Admin->>Browser: Fill form:<br/>- Tanggal hibah<br/>- Sumber hibah<br/>- Jenis hibah<br/>- Akun bank hibah (COA)<br/>- Akun pendapatan hibah (COA)<br/>- Nilai hibah<br/>- Keterangan

    Admin->>Browser: Submit
    Browser->>Filament: POST data
    Filament->>Model: Create PendapatanHibah
    
    Model->>Validator: Validate input
    alt Missing required fields
        Validator->>Browser: Validation error
        Browser->>Admin: Show field errors
    else Invalid amount
        Validator->>Browser: Error: Nilai hibah harus > 0
        Browser->>Admin: Show error
    else All valid
        Model->>Validator: Check COA exists
        alt COA kas bank tidak ada
            CoaModel->>DB: Query COA by kode
            alt Not found
                CoaModel->>Validator: Not found
                Validator->>Browser: Error: COA "[kode]" not found
                Browser->>Admin: Show error + suggest: "Tambahkan akun COA"
            else Found
                CoaModel->>Validator: Confirm
            end
        else COA pendapatan tidak ada
            CoaModel->>DB: Query COA
            alt Not found
                Validator->>Browser: Error: COA pendapatan not found
                Browser->>Admin: Show error
            end
        else Both COA exist
            Model->>Model: generateNoHibah()
            Note over Model: Generate: PDH-0001, PDH-0002, etc
            
            Model->>DB: Save hibah record
            DB->>Model: Return ID
            
            Model->>Journal: syncJournalUmum()
            Journal->>Journal: Build jurnal entries
            Note over Journal: Entry 1 (Debit):<br/>- akun = akun_bank_hibah<br/>- nominal = nilai_hibah<br/>- type = debit<br/><br/>Entry 2 (Kredit):<br/>- akun = akun_pendapatan_hibah<br/>- nominal = nilai_hibah<br/>- type = kredit
            
            Journal->>Validator: Validate debit = kredit
            alt Balance not equal
                Validator->>Browser: Error: Debit-kredit tidak seimbang
                Browser->>Admin: Show error
            else Balanced
                Journal->>DB: Delete old jurnal (if edit)
                Journal->>DB: Create JurnalUmum record
                Journal->>DB: Create JurnalDetail (debit)
                Journal->>DB: Create JurnalDetail (kredit)
                DB->>Journal: Confirm all
                
                Model->>Model: syncPendingTransactionJournals()
                Note over Model: Cek apakah ada perolehan yang menunggu akun ini
                
                Model->>Notification: Send success notification
                Notification->>Browser: "Hibah PDH-[no] dicatat<br/>Nilai: Rp [amount]<br/>Jurnal: [jurnal_id] berhasil dibuat"
                
                Model->>Browser: Success response
                Browser->>Admin: Hibah recorded!<br/>Jurnal otomatis dibuat
            end
        end
    end
```

---

## UC-TRANS-KAS-001: Admin Create Pengisian Kas Kecil (Enhanced)

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant Filament
    participant Model as Pengisian<br/>Kas Kecil
    participant Journal
    participant Validator
    participant DB as Database
    participant Notification

    Admin->>Browser: Access /admin/pengisian-kas-kecils
    Browser->>Filament: GET /admin/pengisian-kas-kecils
    Filament->>DB: Fetch list
    Filament->>Browser: Display table + summary (current kasir balance)
    Browser->>Admin: Show list + balance info

    Admin->>Browser: Click create
    Filament->>DB: Load COA for selection
    Filament->>DB: Get current Kas Kecil balance (for reference)
    Filament->>Browser: Show form
    Browser->>Admin: Display form

    Admin->>Browser: Fill form:<br/>- Tanggal<br/>- Akun kas kecil (COA)<br/>- Akun sumber dana (COA)<br/>- Nominal pengisian<br/>- Bukti file/reference<br/>- Keterangan

    Admin->>Browser: Submit
    Browser->>Filament: POST data
    Filament->>Model: Create PengisianKasKecil
    
    Model->>Validator: Validate
    alt Missing required
        Validator->>Browser: Error
        Browser->>Admin: Show error
    else Nominal <= 0
        Validator->>Browser: Error: Nominal harus > 0
        Browser->>Admin: Show error
    else All valid
        Model->>Model: generateNoTransaksi()
        Note over Model: Generate: PKK-0001, PKK-0002, etc
        
        Model->>DB: Save pengisian record
        DB->>Model: Confirm
        
        Model->>Journal: syncJournalUmum()
        Journal->>Journal: Create entries:<br/>1. Debit: Kas Kecil<br/>2. Kredit: Kas Pengeluaran Institusi
        
        Journal->>Validator: Check balance
        Journal->>Journal: Build jurnal
        Journal->>DB: Save JurnalUmum + JurnalDetail
        DB->>Journal: Confirm
        
        Model->>Notification: Send notification
        Notification->>Browser: "Pengisian Kas Kecil PKK-[no]<br/>Nominal: Rp [amount]<br/>Saldo baru: Rp [new balance]"
        
        Model->>Browser: Success
        Browser->>Admin: Pengisian kas kecil recorded!
    end
```

---

## 📊 Summary Enhancement

| Fitur | Implementasi |
|-------|--------------|
| **Detail Validasi** | ✅ Input validation + business logic checks |
| **Error Handling** | ✅ Setiap step memiliki error path |
| **Auto-Processing** | ✅ Barcode gen, jurnal creation, allocator |
| **Approval Workflow** | ✅ User approval + notification |
| **Admin Approval** | ✅ Peminjaman + Pengajuan |
| **Notification System** | ✅ Toast, email, admin alert |
| **Unified Perolehan** | ✅ 3 sumber dalam 1 UC |
| **Debit-Kredit Balance** | ✅ Validation di setiap jurnal |
| **Stok Management** | ✅ Realtime availability check |
| **Period Processing** | ✅ Eligibility validation |

---

## 🎯 Key Enhancements

1. **Barcode & QR Generation**: Automatic dengan format sequence
2. **Auto Journal Creation**: Semua transaksi membuat jurnal terverifikasi
3. **Allocator Logic**: Smart allocation dengan cost distribution
4. **Kas Kecil Validation**: Check balance sebelum perolehan
5. **Approval Workflow**: User + Admin approval dengan notification
6. **Period Processing**: Automatic eligibility checking untuk penyusutan
7. **Error Handling**: Comprehensive validation di setiap tahap
8. **Notification**: Toast + Email + Admin Alert
9. **Debit-Kredit Balance**: Verification di jurnal creation
10. **Unified Operations**: Perolehan 3 sumber dalam 1 use case

