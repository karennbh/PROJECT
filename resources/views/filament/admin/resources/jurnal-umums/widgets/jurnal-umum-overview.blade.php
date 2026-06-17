<x-filament-widgets::widget>
    <x-filament::section>
        <div class="overflow-x-auto">

            <style>
                .ju-wrap { font-size: 14px; }

                /* ===== FILTER CARD ===== */
                .ju-filter {
                    background: linear-gradient(135deg, #eef9fd, #d0f0fa);
                    border: 1px solid #7dd9f0;
                    border-radius: 12px;
                    padding: 16px 20px;
                    margin-bottom: 24px;
                    box-shadow: 0 2px 8px rgba(41,182,232,0.10);
                }
                .ju-filter label { font-weight: 700; font-size: 13px; color: #0c4a6e; white-space: nowrap; }
                .ju-filter-row {
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    flex-wrap: wrap;
                }
                .ju-filter-field {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .ju-month-input {
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                }
                .ju-filter input {
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
                .ju-filter input[type="month"] {
                    width: 150px;
                    min-width: 150px;
                    max-width: 150px;
                }
                .ju-filter input[type="month"]::-webkit-calendar-picker-indicator {
                    opacity: 0;
                    cursor: pointer;
                    position: absolute;
                    inset: 0;
                    width: 100%;
                    height: 100%;
                }
                .ju-month-icon {
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
                .ju-filter input:focus { border-color: #29b6e8; box-shadow: 0 0 0 2px rgba(41,182,232,.20); }
                .ju-filter input:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 8px 18px rgba(41, 182, 232, 0.14);
                }
                
                /* ===== TOMBOL (SAMA UKURAN & GAYA) ===== */
                .ju-btn, .ju-btn-print {
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
                    box-shadow: 0 4px 10px rgba(14, 165, 233, .25) !important;
                    transition: all 0.2s ease;
                    letter-spacing: .3px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 140px; 
                    height: 38px;
                    text-decoration: none;
                }
                .ju-btn:hover, .ju-btn-print:hover { 
                    transform: translateY(-1px);
                    background: #0284c7 !important;
                    background-image: none !important;
                    background-color: #0284c7 !important;
                    box-shadow: 0 8px 18px rgba(14, 165, 233, .35) !important;
                }

                /* ===== HEADER LAPORAN ===== */
                .ju-header {
                    text-align: center;
                    margin-bottom: 16px;
                    padding: 18px 0 10px;
                    border-bottom: 3px solid #29b6e8;
                }
                .ju-header h2 { font-weight: 800; font-size: 18px; margin: 0; color: #0c4a6e; letter-spacing: .5px; }
                .ju-header h3 { font-weight: 700; font-size: 15px; margin: 2px 0 0; color: #0ea5e9; }
                .ju-header p  { font-weight: 600; font-size: 13px; margin: 4px 0 0; color: #29b6e8; }

                /* ===== TABLE ===== */
                .jurnal-table { width: 100%; border-collapse: collapse; font-size: 13.5px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
                .jurnal-table th {
                    background: linear-gradient(135deg, #29b6e8, #0ea5e9);
                    color: #fff;
                    font-weight: 700;
                    padding: 11px 14px;
                    text-align: center;
                    border: none;
                    letter-spacing: .3px;
                }
                .jurnal-table td { padding: 9px 14px; border-bottom: 1px solid #e5e7eb; border-right: 1px solid #f3f4f6; }
                .jurnal-table tbody tr:hover { background-color: #eef9fd; transition: background .15s; }
                .jurnal-table tbody tr:nth-child(even) { background-color: #fafafa; }

                .row-total { background: linear-gradient(90deg, #d0f0fa, #eef9fd) !important; }
                .total-cell { color: #0ea5e9; font-weight: 700; }

                .text-center-cell { text-align: center !important; }
                .text-right-cell  { text-align: right !important; }
                .text-left-cell   { text-align: left !important; }
                .font-bold-custom { font-weight: 700 !important; }
                .empty-row td { text-align: center; color: #9ca3af; padding: 24px; font-style: italic; }

                :is(html[data-theme="dark"], html.dark, .dark) .ju-filter {
                    background: linear-gradient(135deg, #123041, #0f2232);
                    border-color: rgba(125, 211, 252, 0.22);
                    box-shadow: 0 2px 12px rgba(2, 12, 27, 0.28);
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ju-filter label { color: #effbff; }
                :is(html[data-theme="dark"], html.dark, .dark) .ju-filter input {
                    background: #112332;
                    border-color: rgba(125, 211, 252, 0.24);
                    color: #effbff;
                    box-shadow: 0 6px 16px rgba(2, 12, 27, 0.22);
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ju-month-icon {
                    color: #dff7ff;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .ju-header {
                    border-bottom-color: #30b8f4;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .ju-header h2 { color: #eefbff; }
                :is(html[data-theme="dark"], html.dark, .dark) .ju-header h3 { color: #67d5ff; }
                :is(html[data-theme="dark"], html.dark, .dark) .ju-header p { color: #a5eaff; }

                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table {
                    box-shadow: 0 2px 12px rgba(2, 12, 27, 0.24);
                }
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table td {
                    color: #effbff;
                    border-bottom-color: rgba(125, 211, 252, 0.10);
                    border-right-color: rgba(125, 211, 252, 0.08);
                    background: transparent !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr:nth-child(odd) {
                    background: #141f37;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr:nth-child(even) {
                    background: #182338;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr:hover,
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr[aria-selected="true"],
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr[data-selected="true"],
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr.fi-active {
                    background: #18324a;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr,
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr td,
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr td *,
                :is(html[data-theme="dark"], html.dark, .dark) .jurnal-table tbody tr span {
                    color: #eefbff !important;
                    background: transparent !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .row-total {
                    background: linear-gradient(90deg, #123041, #18324a) !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .row-total td,
                :is(html[data-theme="dark"], html.dark, .dark) .total-cell,
                :is(html[data-theme="dark"], html.dark, .dark) .font-bold-custom {
                    color: #7fe3ff !important;
                }
                :is(html[data-theme="dark"], html.dark, .dark) .empty-row td {
                    color: #b0d7e5;
                }

                @media (max-width: 768px) {
                    .ju-filter-field {
                        width: 100%;
                        justify-content: space-between;
                    }
                    .ju-filter input[type="month"] {
                        width: 100%;
                        min-width: 0;
                        max-width: 170px;
                    }
                }

                @media print {
                    .ju-filter {
                        display: none !important;
                    }

                    .ju-wrap {
                        font-size: 11px;
                    }

                    .ju-header {
                        text-align: center;
                        border-bottom: 2px solid #111827;
                        padding: 0 0 10px;
                        margin-bottom: 14px;
                    }

                    .ju-header h2,
                    .ju-header h3,
                    .ju-header p {
                        color: #111827 !important;
                    }

                    .jurnal-table {
                        box-shadow: none;
                        border-radius: 0;
                        font-size: 10.5px;
                    }

                    .jurnal-table th {
                        background: #e5e7eb !important;
                        color: #111827 !important;
                        border: 1px solid #d1d5db;
                        padding: 7px;
                    }

                    .jurnal-table td {
                        border: 1px solid #e5e7eb;
                        padding: 6px;
                    }

                    .row-total {
                        background: #f3f4f6 !important;
                    }

                    .total-cell,
                    .font-bold-custom {
                        color: #111827 !important;
                    }
                }
            </style>

            <div class="ju-wrap">

                {{-- FILTER SECTION --}}
                <div class="ju-filter">
                    <form wire:submit.prevent="filterJurnal">
                        <div class="ju-filter-row">
                            <div class="ju-filter-field">
                                <label>Periode Awal:</label>
                                <div class="ju-month-input">
                                    <input type="month" wire:model.live="periode_awal">
                                    <svg class="ju-month-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-13 9h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2Z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ju-filter-field">
                                <label>Periode Akhir:</label>
                                <div class="ju-month-input">
                                    <input type="month" wire:model="periode_akhir" min="{{ $periode_awal }}">
                                    <svg class="ju-month-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-13 9h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2Z" />
                                    </svg>
                                </div>
                            </div>
                            
                            <button type="submit" class="ju-btn" style="background: #0ea5e9 !important; background-image: none !important; background-color: #0ea5e9 !important; box-shadow: 0 4px 10px rgba(14, 165, 233, .25) !important;">Filter</button>
                            <button type="button" wire:click="cetakJurnal" wire:loading.attr="disabled" class="ju-btn-print" style="background: #0ea5e9 !important; background-image: none !important; background-color: #0ea5e9 !important; box-shadow: 0 4px 10px rgba(14, 165, 233, .25) !important;">
                                <span wire:loading.remove>Cetak Jurnal Umum</span>
                                <span wire:loading>Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- HEADER LAPORAN --}}
                <div class="ju-header">
                    <h2>CoE SMART EV</h2>
                    <h3>Jurnal Umum</h3>
                    @if($periode_awal && $periode_akhir)
                        <p>Periode {{ \Carbon\Carbon::parse($periode_awal)->translatedFormat('F Y') }} s/d {{ \Carbon\Carbon::parse($periode_akhir)->translatedFormat('F Y') }}</p>
                    @endif
                </div>

                {{-- TABEL --}}
                <table class="jurnal-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kode Akun</th>
                            <th>Keterangan</th>
                            <th>Reff</th>
                            <th>Debit (Rp)</th>
                            <th>Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurnals as $jurnal)

                            @php
                                $nomorBukti = $jurnal->reff_transaksi;
                            @endphp

                            @foreach ($jurnal->details as $detail)
                                <tr>
                                    <td class="text-center-cell">
                                        @if ($loop->first)
                                            {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}
                                        @endif
                                    </td>

                                    <td class="text-center-cell">{{ $detail->coa?->kode_akun ?? $detail->kode_akun }}</td>

                                    <td class="text-left-cell">
                                        @if($detail->nominal_debit > 0)
                                            {{ $detail->coa->nama_akun ?? '-' }}
                                        @else
                                            <span style="margin-left:25px;">{{ $detail->coa->nama_akun ?? '-' }}</span>
                                        @endif
                                    </td>

                                    <td class="text-center-cell">
                                        @if ($loop->first)
                                            {{ $nomorBukti }}
                                        @endif
                                    </td>

                                    <td class="text-right-cell">
                                        {{ $detail->nominal_debit > 0 ? 'Rp' . number_format($detail->nominal_debit, 0, ',', '.') : '' }}
                                    </td>

                                    <td class="text-right-cell">
                                        {{ $detail->nominal_kredit > 0 ? 'Rp' . number_format($detail->nominal_kredit, 0, ',', '.') : '' }}
                                    </td>
                                </tr>
                            @endforeach

                        @empty
                            <tr class="empty-row">
                                <td colspan="6">Tidak ada data untuk periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="row-total">
                            <td colspan="4" class="text-right-cell font-bold-custom" style="color:#0ea5e9;">Total</td>
                            <td class="text-right-cell total-cell">Rp{{ number_format($totalDebit, 0, ',', '.') }}</td>
                            <td class="text-right-cell total-cell">Rp{{ number_format($totalKredit, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

