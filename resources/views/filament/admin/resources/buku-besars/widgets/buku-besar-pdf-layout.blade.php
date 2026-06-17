<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Besar - CoE SMART EV</title>
    <style>
        /* Pengaturan Kertas A4 Portrait */
        @page {
            size: a4 portrait;
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Header Laporan */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #29b6e8;
            padding-bottom: 10px;
        }
        .header h2 { 
            margin: 0; 
            color: #0c4a6e; 
            font-size: 16pt;
            text-transform: uppercase;
        }
        .header h3 { 
            margin: 5px 0; 
            color: #0ea5e9; 
            font-size: 13pt;
            text-transform: uppercase;
        }
        .header p { 
            margin: 0; 
            font-size: 10pt; 
            color: #444; 
        }

        /* Box Info Akun */
        .akun-box {
            background-color: #f0faff;
            padding: 8px 12px;
            border: 1px solid #cfe8f3;
            border-bottom: 0;
            border-radius: 4px 4px 0 0;
            margin-bottom: 0;
            overflow: hidden;
        }
        .akun-box table { margin-bottom: 0; border: none; }
        .akun-box td { border: none; padding: 0; color: #111; font-weight: bold; }

        /* Tabel Utama */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            table-layout: fixed;
            border: 1px solid #cfe8f3;
            border-top: 0;
            box-shadow: inset 0 -1px 0 #cfe8f3;
        }
        col.col-tanggal { width: 14%; }
        col.col-kode { width: 13%; }
        col.col-keterangan { width: 20%; }
        col.col-reff { width: 17%; }
        col.col-debit { width: 9%; }
        col.col-kredit { width: 12%; }
        col.col-saldo { width: 15%; }
        th {
            background-color: #29b6e8;
            color: white;
            padding: 8px 4px;
            border: 1px solid #7dd9f0;
            font-size: 8.5pt;
            text-transform: uppercase;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #cfe8f3;
            word-wrap: break-word;
            background: #fff;
        }
        tbody tr:last-child td {
            border-bottom: 1px solid #cfe8f3;
        }
        .row-saldo-akhir td {
            border-bottom: 1px solid #cfe8f3;
        }

        .row-saldo-awal {
            background-color: #ffffff;
            font-weight: bold;
        }
        .row-saldo-akhir {
            background-color: #ffffff;
            font-weight: bold;
        }
        .saldo-pekat {
            color: #000000 !important;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>CoE SMART EV</h2>
        <h3>LAPORAN BUKU BESAR</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($periode_awal)->translatedFormat('F Y') }} - {{ \Carbon\Carbon::parse($periode_akhir)->translatedFormat('F Y') }}</p>
    </div>

    @foreach ($displayCoas as $index => $coa)
        @php
            $isNormalKredit = \App\Filament\Admin\Resources\BukuBesars\Widgets\BukuBesarTableOverview::isNormalKredit($coa);
        @endphp

        <div class="akun-box">
            <table style="width: 100%;">
                <tr>
                    <td align="left">Nama Akun: <span style="font-weight: normal;">{{ $coa->nama_akun }}</span></td>
                    <td align="right">Nomor Akun: <span style="font-weight: normal;">{{ $coa->kode_akun }}</span></td>
                </tr>
            </table>
        </div>

        <table>
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
                @php 
                    $runningSaldo = $saldoAwal[$coa->kode_akun] ?? 0;
                @endphp
                
                <tr class="row-saldo-awal">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">Saldo Awal</td>
                    <td class="text-right saldo-pekat">Rp{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                </tr>

                @foreach ($jurnals as $jurnal)
                    @foreach ($jurnal->details->where('kode_akun', $coa->kode_akun) as $detail)
                        @php
                            $ledgerRows = \App\Filament\Admin\Resources\BukuBesars\Widgets\BukuBesarTableOverview::ledgerRowsForDetail($jurnal, $detail);
                        @endphp
                        @foreach ($ledgerRows as $ledgerRow)
                            @php
                                $d = (int) $ledgerRow['debit'];
                                $k = (int) $ledgerRow['kredit'];
                                $lawan = $ledgerRow['lawan'];

                                if($isNormalKredit) {
                                    $runningSaldo += ($k - $d);
                                } else {
                                    $runningSaldo += ($d - $k);
                                }
                            @endphp
                        <tr>
                            <td class="text-center">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/y') }}</td>
                            {{-- Kolom Kode Akun (Isi Kode Akun Lawan) --}}
                            <td class="text-center">{{ $lawan?->coa?->kode_akun ?? $lawan?->kode_akun ?? '-' }}</td>
                            {{-- Kolom Keterangan (Isi Nama Akun Lawan) --}}
                            <td style="font-size: 8pt;">{{ $lawan?->coa?->nama_akun ?? '-' }}</td>
                            {{-- Kolom Reff (Isi No Bukti) --}}
                            <td class="text-center" style="font-size: 8pt;">{{ $jurnal->reff_transaksi }}</td>
                            
                            <td class="text-right">{{ $d > 0 ? 'Rp' . number_format($d, 0, ',', '.') : '-' }}</td>
                            <td class="text-right">{{ $k > 0 ? 'Rp' . number_format($k, 0, ',', '.') : '-' }}</td>
                            <td class="text-right saldo-pekat">Rp{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                @endforeach

                <tr class="row-saldo-akhir">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">Saldo Akhir</td>
                    <td class="text-right saldo-pekat">Rp{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>


