<x-filament-widgets::widget>
    <x-filament::section>
        <div>
            <style>
                .bb-wrap {
                    width: 100%;
                    max-width: 100%;
                    overflow: hidden;
                    font-size: 14px;
                }
                
                /* ===== FILTER CARD ===== */
                .bb-filter {
                    background: linear-gradient(135deg, #eef9fd, #d0f0fa);
                    border: 1px solid #7dd9f0;
                    border-radius: 12px;
                    padding: 16px 20px;
                    margin-bottom: 24px;
                    box-shadow: 0 2px 8px rgba(41,182,232,0.10);
                }
                .bb-filter label { font-weight: 700; font-size: 13px; color: #0c4a6e; white-space: nowrap; }
                .bb-filter-row {
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    flex-wrap: wrap;
                }
                .bb-filter-field {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .bb-month-input {
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                }
                .bb-filter input, .bb-filter select {
                    border: 1px solid #7dd9f0;
                    border-radius: 10px;
                    padding: 0.7rem 2.75rem 0.7rem 0.9rem;
                    font-size: 13px;
                    background: #fff;
                    color: #1f2937;
                    outline: none;
                    transition: border-color .2s, box-shadow .2s, transform .2s;
                    box-shadow: 0 4px 14px rgba(41, 182, 232, 0.10);
                }
                .bb-filter input[type="month"] {
                    width: 150px;
                    min-width: 150px;
                    max-width: 150px;
                }
                .bb-filter select {
                    min-width: 220px;
                }
                .bb-filter input[type="month"]::-webkit-calendar-picker-indicator {
                    opacity: 0;
                    cursor: pointer;
                    position: absolute;
                    inset: 0;
                    width: 100%;
                    height: 100%;
                }
                .bb-month-icon {
                    position: absolute;
                    right: 0.9rem;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 1rem;
                    height: 1rem;
                    color: #0f4c6b;
                    pointer-events: none;
                    opacity: 0.9;
                }
                .bb-filter input:hover,
                .bb-filter select:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 8px 18px rgba(41, 182, 232, 0.14);
                }
                
                /* ===== TOMBOL DENGAN ANIMASI GLOW ===== */
                .bb-btn, .bb-btn-print {
                    background: #0ea5e9 !important;
                    background-image: none !important;
                    background-color: #0ea5e9 !important;
                    color: #fff !important;
                    padding: 7px 22px;
                    border-radius: 10px;
                    font-size: 13px;
                    font-weight: 700;
                    border: none;
                    cursor: pointer;
                    min-width: 140px; 
                    height: 38px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    text-decoration: none;
                    transition: all 0.3s ease-in-out;
                    box-shadow: 0 4px 10px rgba(14, 165, 233, .25) !important;
                }

                .bb-btn:hover, .bb-btn-print:hover {
                    transform: translateY(-2px);
                    background: #0284c7 !important;
                    background-image: none !important;
                    background-color: #0284c7 !important;
                    box-shadow: 0 8px 18px rgba(14, 165, 233, .35) !important;
                }

                /* Styling Header & Tabel */
                .bb-header { text-align: center; margin-bottom: 25px; }
                .bb-header h2 { font-weight: 800; font-size: 22px; color: #0c4a6e; margin: 0; }
                .bb-header h3 { font-weight: 700; font-size: 18px; color: #0ea5e9; margin: 5px 0; text-transform: uppercase; }
                
                .bb-ledger-card {
                    width: 100%;
                    max-width: 100%;
                    margin-bottom: 30px;
                    overflow: hidden;
                    border: 1px solid #cfe8f3;
                    border-radius: 8px;
                    background: #fff;
                }
                .bb-ledger-scroll {
                    width: 100%;
                    max-width: 100%;
                    overflow-x: auto;
                    overflow-y: hidden;
                    -webkit-overflow-scrolling: touch;
                    overscroll-behavior-x: contain;
                }
                .bb-ledger-inner {
                    min-width: 1180px;
                }
                .bb-akun-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    background: #f0faff;
                    padding: 12px 20px;
                    border: 0;
                    border-bottom: 0;
                    gap: 18px;
                }
                .bb-akun-container > div {
                    min-width: 0;
                    overflow-wrap: anywhere;
                }
                .bb-akun-label { font-weight: 700; color: #111; }

                .ledger-table {
                    width: 100%;
                    border-collapse: collapse;
                    table-layout: fixed;
                    font-size: 13.5px;
                    margin-bottom: 0;
                    border: 0;
                    border-top: 0;
                    box-shadow: none;
                }
                .ledger-table col.col-tanggal { width: 11%; }
                .ledger-table col.col-kode { width: 11%; }
                .ledger-table col.col-keterangan { width: 18%; }
                .ledger-table col.col-reff { width: 14%; }
                .ledger-table col.col-debit { width: 15%; }
                .ledger-table col.col-kredit { width: 15%; }
                .ledger-table col.col-saldo { width: 16%; }
                .ledger-table th {
                    background: linear-gradient(135deg, #29b6e8, #0ea5e9);
                    color: #fff !important;
                    font-weight: 700;
                    padding: 11px 14px;
                    border: 1px solid #7dd9f0;
                    text-align: center;
                }
                .ledger-table td {
                    padding: 9px 14px;
                    border: 1px solid #cfe8f3;
                    background: #fff;
                    vertical-align: middle;
                    overflow-wrap: anywhere;
                }
                .ledger-table th:nth-child(5),
                .ledger-table th:nth-child(6),
                .ledger-table th:nth-child(7),
                .ledger-table td:nth-child(5),
                .ledger-table td:nth-child(6),
                .ledger-table td:nth-child(7) {
                    white-space: nowrap;
                    overflow-wrap: normal;
                    word-break: keep-all;
                }
                .ledger-table tbody tr:first-child td {
                    border-top: 1px solid #cfe8f3;
                }
                .ledger-table tfoot td {
                    border: 1px solid #cfe8f3;
                    background: #fff;
                }
                .ledger-table tfoot .row-saldo td {
                    border-bottom: 1px solid #cfe8f3 !important;
                }
                .ledger-table tfoot tr:last-child td {
                    border-bottom: 1px solid #cfe8f3;
                }
                .saldo-cell { color: #000000 !important; font-weight: 700; }
                .row-bold-text { font-weight: 800 !important; color: #111 !important; }
                .row-saldo td { background: #fff; }
                .no-data-row td {
                    background: linear-gradient(90deg, #f8fcff, #eef9fd) !important;
                    color: #426579;
                    padding: 18px 14px;
                    text-align: center;
                }
                .no-data-state {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    font-weight: 700;
                    color: #0f4c6b;
                    letter-spacing: .2px;
                }
                .no-data-state svg {
                    width: 18px;
                    height: 18px;
                    color: #29b6e8;
                }
                .no-data-state span {
                    color: #64748b;
                    font-weight: 600;
                }
                .text-right { text-align: right !important; }
                .text-center { text-align: center !important; }
                .text-left { text-align: left !important; }

                :is(html[data-theme="dark"], html.dark, .dark) .bb-filter {
                    background: linear-gradient(135deg, #123041, #0f2232);
                    border-color: rgba(125, 211, 252, 0.22);
                    box-shadow: 0 2px 12px rgba(2, 12, 27, 0.28);
                }
                :is(html[data-theme="dark"], html.dark, .dark) .bb-filter label {
                    color: #effbff;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .bb-filter input,
                :is(html[data-theme="dark"], html.dark, .dark) .bb-filter select {
                    background: #112332;
                    border-color: rgba(125, 211, 252, 0.24);
                    color: #effbff;
                    box-shadow: 0 6px 16px rgba(2, 12, 27, 0.22);
                }
                :is(html[data-theme="dark"], html.dark, .dark) .bb-month-icon {
                    color: #dff7ff;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .bb-header h2 {
                    color: #eefbff;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .bb-header h3 {
                    color: #67d5ff;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .bb-header p {
                    color: #a5eaff;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .bb-akun-container {
                    background: linear-gradient(135deg, #123041, #18324a);
                    border-color: rgba(125, 211, 252, 0.20);
                    border-bottom: 0;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .bb-ledger-card {
                    background: #111f31;
                    border-color: rgba(125, 211, 252, 0.20);
                }
                :is(html[data-theme="dark"], html.dark, .dark) .bb-akun-container,
                :is(html[data-theme="dark"], html.dark, .dark) .bb-akun-container * {
                    color: #eefbff !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .bb-akun-label {
                    color: #7fe3ff !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table {
                    box-shadow: 0 2px 12px rgba(2, 12, 27, 0.24);
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table {
                    box-shadow: 0 2px 12px rgba(2, 12, 27, 0.24), inset 0 -1px 0 rgba(125, 211, 252, 0.10);
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table td {
                    color: #effbff !important;
                    border-color: rgba(125, 211, 252, 0.10) !important;
                    background: transparent !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr:nth-child(odd) {
                    background: #141f37 !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr:nth-child(even) {
                    background: #182338 !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr:hover,
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr[aria-selected="true"],
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr[data-selected="true"],
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr.fi-active {
                    background: #18324a !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr,
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr td,
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tbody tr td *,
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tfoot tr td,
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tfoot tr td * {
                    color: #eefbff !important;
                    background: transparent !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tfoot tr {
                    background: linear-gradient(90deg, #123041, #18324a) !important;
                    border-top-color: rgba(125, 211, 252, 0.24) !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ledger-table tfoot tr:last-child td {
                    border-bottom-color: rgba(125, 211, 252, 0.10) !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .saldo-cell,
                :is(html[data-theme="dark"], html.dark, .dark) .row-bold-text {
                    color: #7fe3ff !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .no-data-row td {
                    background: linear-gradient(90deg, #111f31, #142a3f) !important;
                    color: #b0d7e5 !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .no-data-state {
                    color: #e5f9ff !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .no-data-state span {
                    color: #a9d8e8 !important;
                }

                @media (max-width: 768px) {
                    .bb-wrap {
                        font-size: 13px;
                    }
                    .bb-ledger-inner {
                        min-width: 1080px;
                    }
                    .bb-akun-container {
                        align-items: flex-start;
                        padding: 10px 12px;
                        font-size: 13px;
                    }
                    .ledger-table {
                        font-size: 13px;
                    }
                    .ledger-table th,
                    .ledger-table td {
                        padding: 8px 10px;
                    }
                    .bb-filter-field {
                        width: 100%;
                        justify-content: space-between;
                    }
                    .bb-filter input[type="month"],
                    .bb-filter select {
                        width: 100%;
                        min-width: 0;
                        max-width: 200px;
                    }
                }

                @media print {
                    .bb-filter {
                        display: none !important;
                    }

                    .bb-wrap {
                        overflow: visible;
                        font-size: 10.5px;
                    }

                    .bb-header {
                        text-align: center;
                        border-bottom: 2px solid #111827;
                        padding-bottom: 10px;
                        margin-bottom: 14px;
                    }

                    .bb-header h2,
                    .bb-header h3,
                    .bb-header p {
                        color: #111827 !important;
                    }

                    .bb-ledger-card {
                        border-color: #d1d5db;
                        border-radius: 0;
                        break-inside: avoid;
                    }

                    .bb-ledger-scroll {
                        overflow: visible;
                    }

                    .bb-ledger-inner {
                        min-width: 0;
                    }

                    .bb-akun-container {
                        background: #f3f4f6 !important;
                        border-bottom: 1px solid #d1d5db;
                        color: #111827 !important;
                    }

                    .ledger-table {
                        font-size: 9.5px;
                        table-layout: fixed;
                    }

                    .ledger-table th {
                        background: #e5e7eb !important;
                        color: #111827 !important;
                        border-color: #d1d5db;
                        padding: 6px;
                    }

                    .ledger-table td {
                        border-color: #e5e7eb;
                        padding: 5px;
                        background: #fff !important;
                        color: #111827 !important;
                    }

                    .saldo-cell,
                    .row-bold-text {
                        color: #111827 !important;
                    }
                }
            </style>

            <div class="bb-wrap" id="printable-area">
                <div class="bb-filter">
                    <form wire:submit.prevent="filter">
                        <div class="bb-filter-row">
                            <div class="bb-filter-field">
                                <label>Periode Awal:</label>
                                <div class="bb-month-input">
                                    <input type="month" wire:model.live="periode_awal" value="{{ $periode_awal ?: now()->format('Y-m') }}">
                                    <svg class="bb-month-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-13 9h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2Z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bb-filter-field">
                                <label>Periode Akhir:</label>
                                <div class="bb-month-input">
                                    <input type="month" wire:model.live="periode_akhir" min="{{ $periode_awal ?: now()->format('Y-m') }}" value="{{ $periode_akhir ?: now()->format('Y-m') }}">
                                    <svg class="bb-month-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-13 9h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2Z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bb-filter-field">
                                <label>Akun COA:</label>
                                <select wire:model="coa_id">
                                    <option value="">-- Semua Akun --</option>
                                    @foreach (\App\Models\Coa::orderBy('nama_akun')->orderBy('kode_akun')->get() as $akun)
                                        <option value="{{ $akun->kode_akun }}">{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="bb-btn" style="background: #0ea5e9 !important; background-image: none !important; background-color: #0ea5e9 !important; box-shadow: 0 4px 10px rgba(14, 165, 233, .25) !important;">Filter</button>
                            <button type="button" wire:click="cetakLaporan" wire:loading.attr="disabled" class="bb-btn-print" style="background: #0ea5e9 !important; background-image: none !important; background-color: #0ea5e9 !important; box-shadow: 0 4px 10px rgba(14, 165, 233, .25) !important;">
                                <span wire:loading.remove>Cetak Buku Besar</span>
                                <span wire:loading>Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bb-header">
                    <h2>CoE SMART EV</h2>
                    <h3>LAPORAN BUKU BESAR</h3>
                    @if($periode_awal && $periode_akhir)
                        <p>Periode: {{ \Carbon\Carbon::parse($periode_awal)->translatedFormat('F Y') }} - {{ \Carbon\Carbon::parse($periode_akhir)->translatedFormat('F Y') }}</p>
                    @endif
                </div>

                @php
                    $displayCoas = $coa_id 
                        ? \App\Models\Coa::whereKey($coa_id)->get() 
                        : \App\Models\Coa::whereIn('kode_akun', array_keys($saldoAwal))->orderBy('kode_akun')->get();
                @endphp

                @forelse ($displayCoas as $index => $currentCoa)
                    @php
                        $hasData = false;
                        foreach($jurnals as $jurnal) {
                            if($jurnal->details->where('kode_akun', $currentCoa->kode_akun)->count() > 0) {
                                $hasData = true;
                                break;
                            }
                        }
                        
                        $isNormalKredit = \App\Filament\Admin\Resources\BukuBesars\Widgets\BukuBesarTableOverview::isNormalKredit($currentCoa);
                    @endphp

                    <div class="{{ $index > 0 ? 'mt-8' : '' }} bb-ledger-card">
                        <div class="bb-ledger-scroll">
                            <div class="bb-ledger-inner">
                                <div class="bb-akun-container">
                                    <div><span class="bb-akun-label">Nama Akun:</span> {{ $currentCoa->nama_akun }}</div>
                                    <div><span class="bb-akun-label">Nomor Akun:</span> {{ $currentCoa->kode_akun }}</div>
                                </div>

                                <table class="ledger-table">
                                    <colgroup>
                                        <col class="col-tanggal">
                                        <col class="col-kode">
                                        <col class="col-keterangan">
                                        <col class="col-reff">
                                        <col class="col-debit">
                                        <col class="col-kredit">
                                        <col class="col-saldo">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Kode Akun</th> {{-- Header Diganti --}}
                                            <th>Keterangan</th>
                                            <th>Reff</th> {{-- Header Diganti --}}
                                            <th>Debit</th>
                                            <th>Kredit</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $runningSaldo = $saldoAwal[$currentCoa->kode_akun] ?? 0; @endphp
                                        
                                        <tr class="row-saldo">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right row-bold-text">Saldo Awal</td>
                                            <td class="text-right saldo-cell">Rp{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                                        </tr>

                                        @if(!$hasData)
                                            <tr class="no-data-row">
                                                <td colspan="7">
                                                    <div class="no-data-state">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" />
                                                        </svg>
                                                        Tidak ada transaksi <span>pada periode ini</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                                @foreach ($jurnals as $jurnal)
                                                @foreach ($jurnal->details->where('kode_akun', $currentCoa->kode_akun) as $detail)
                                                    @php
                                                        $ledgerRows = \App\Filament\Admin\Resources\BukuBesars\Widgets\BukuBesarTableOverview::ledgerRowsForDetail($jurnal, $detail);
                                                    @endphp
                                                    @foreach ($ledgerRows as $ledgerRow)
                                                        @php
                                                            $debit  = (int) $ledgerRow['debit'];
                                                            $kredit = (int) $ledgerRow['kredit'];
                                                            $lawan = $ledgerRow['lawan'];

                                                            if($isNormalKredit) {
                                                                $runningSaldo += ($kredit - $debit);
                                                            } else {
                                                                $runningSaldo += ($debit - $kredit);
                                                            }
                                                        @endphp
                                                    <tr>
                                                        <td class="text-center">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                                                        {{-- Kolom Kode Akun (Diisi kode akun lawan) --}}
                                                        <td class="text-center">{{ $lawan?->coa?->kode_akun ?? $lawan?->kode_akun ?? '-' }}</td>
                                                        {{-- Kolom Keterangan (Diisi nama akun lawan) --}}
                                                        <td class="text-left">{{ $lawan?->coa?->nama_akun ?? '-' }}</td>
                                                        {{-- Kolom Reff (Diisi nomor bukti transaksi) --}}
                                                        <td class="text-center">{{ $jurnal->reff_transaksi }}</td>
                                                        
                                                        <td class="text-right">{{ $debit > 0 ? 'Rp' . number_format($debit,0,',','.') : '-' }}</td>
                                                        <td class="text-right">{{ $kredit > 0 ? 'Rp' . number_format($kredit,0,',','.') : '-' }}</td>
                                                        <td class="text-right saldo-cell">Rp{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                                                    </tr>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr class="row-saldo">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right row-bold-text">Saldo Akhir</td>
                                            <td class="text-right saldo-cell">Rp{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center" style="padding: 40px; color: #9ca3af;">Data tidak ditemukan.</div>
                @endforelse
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>


