<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian Barang</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #0c4a6e; font-size: 18px; }
        .header h3 { margin: 5px 0; color: #0ea5e9; font-size: 14px; text-transform: uppercase; }
        .header p { margin: 4px 0 0; color: #64748b; }

        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th { background-color: #29b6e8; color: white; padding: 8px; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #eee; vertical-align: top; }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            background: #eff6ff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>CoE SMART EV</h2>
        <h3>Laporan Pembelian Barang</h3>
        <p>Periode: {{ $periodeLabel }}</p>
        <p>Status: {{ $statusLabel }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 16%;">Nama Pemohon</th>
                <th style="width: 18%;">Nama Barang</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 10%;">Harga</th>
                <th style="width: 8%;">Jumlah</th>
                <th style="width: 12%;">Total</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                @php
                    $kategoriLabel = $record->kategori_barang === 'aset' ? 'Aset' : 'BHP';
                @endphp
                <tr>
                    <td class="text-center">{{ $record->id_pembelian_barang_kantor }}</td>
                    <td class="text-left">{{ $record->user->name ?? '-' }}</td>
                    <td class="text-left">{{ $record->nama_barang }}</td>
                    <td class="text-center">{{ $kategoriLabel }}</td>
                    <td class="text-right">Rp {{ number_format((int) $record->perkiraan_harga, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format((int) $record->jumlah, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format((int) $record->sub_total, 0, ',', '.') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($record->tanggal_pengajuan)->format('d/m/Y') }}</td>
                    <td class="text-center"><span class="badge">{{ ucfirst($record->status) }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
