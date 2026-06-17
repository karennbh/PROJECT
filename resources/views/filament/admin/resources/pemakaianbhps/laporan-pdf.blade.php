<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pemakaian BHP</title>
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
        <h3>Laporan Pemakaian BHP</h3>
        <p>Periode: {{ $periodeLabel }}</p>
        <p>Status: {{ $statusLabel }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 18%;">Nama Pengguna</th>
                <th style="width: 22%;">Nama Barang BHP</th>
                <th style="width: 10%;">Jumlah</th>
                <th style="width: 10%;">Stok</th>
                <th style="width: 14%;">Tanggal Penggunaan</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 18%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                <tr>
                    <td class="text-center">{{ $record->id_pemakaian }}</td>
                    <td class="text-left">{{ $record->user->name ?? '-' }}</td>
                    <td class="text-left">{{ $record->barang->nama_barang ?? '-' }}</td>
                    <td class="text-center">{{ number_format((int) $record->jumlah, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format((int) ($record->barang->stok ?? 0), 0, ',', '.') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($record->tanggal_pemakaian)->format('d/m/Y') }}</td>
                    <td class="text-center"><span class="badge">{{ ucfirst($record->status) }}</span></td>
                    <td class="text-left">{{ $record->alasan_kebutuhan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
