<!DOCTYPE html>
<html>
<head>
    <title>Laporan Jurnal Umum</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #0c4a6e; font-size: 18px; }
        .header h3 { margin: 5px 0; color: #0ea5e9; font-size: 14px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background-color: #29b6e8; color: white; padding: 8px; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #eee; }
        
        /* Style Tabel Kedua (Overview) */
        .table-overview th { background-color: #f9fafb; color: #4b5563; text-align: left; }
        .badge { background-color: #ecfdf5; color: #065f46; padding: 3px 10px; border-radius: 12px; font-size: 9px; font-weight: bold; border: 1px solid #d1fae5; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>CoE SMART EV</h2>
        <h3>LAPORAN JURNAL UMUM</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($periode_awal)->translatedFormat('F Y') }} - {{ \Carbon\Carbon::parse($periode_akhir)->translatedFormat('F Y') }}</p>
    </div>

    <table>
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
            @foreach ($jurnals as $jurnal)
                @php
                    // Logika Nomor Bukti Dinamis (Reff)
                    $nomorBukti = $jurnal->reff_transaksi;
                @endphp

                @foreach ($jurnal->details as $detail)
                <tr>
                    <td class="text-center">
                        @if ($loop->first) {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }} @endif
                    </td>
                    <td class="text-center">{{ $detail->coa?->kode_akun ?? $detail->kode_akun }}</td>
                    <td>
                        @if($detail->nominal_debit > 0)
                            {{ $detail->coa->nama_akun ?? '-' }}
                        @else
                            <span style="padding-left: 20px;">{{ $detail->coa->nama_akun ?? '-' }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($loop->first) {{ $nomorBukti }} @endif
                    </td>
                    <td class="text-right">
                        {{ $detail->nominal_debit > 0 ? 'Rp' . number_format($detail->nominal_debit, 0, ',', '.') : '' }}
                    </td>
                    <td class="text-right">
                        {{ $detail->nominal_kredit > 0 ? 'Rp' . number_format($detail->nominal_kredit, 0, ',', '.') : '' }}
                    </td>
                </tr>
                @endforeach
            @endforeach
            
            <tr style="background-color: #f9fafb;">
                <td colspan="4" class="text-right font-bold">TOTAL</td>
                <td class="text-right font-bold">Rp{{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp{{ number_format($totalKredit, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <hr style="border: 1px solid #eee; margin-bottom: 20px;">

    <table class="table-overview">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nomor Bukti</th>
                <th>Deskripsi</th>
                <th>Tipe Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daftarTransaksi as $item)
            @php
                $buktiTransaksi = $item->reff_transaksi;
            @endphp
            <tr>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $buktiTransaksi ?? '-' }}</td>
                <td>{{ $item->deskripsi ?? 'Pembelian (-)' }}</td>
                <td class="text-center">
                    <span class="badge">{{ strtoupper(str_replace('_', ' ', $item->tipe_transaksi)) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>

