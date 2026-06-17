<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Penyusutan Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #0c4a6e; font-size: 18px; }
        .header h3 { margin: 5px 0; color: #0ea5e9; font-size: 14px; text-transform: uppercase; }
        .header p { margin: 4px 0 0; color: #64748b; }

        .info-table, .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .info-table td {
            padding: 5px 6px;
            vertical-align: top;
        }

        .info-label {
            width: 180px;
            color: #4b5563;
            font-weight: bold;
            white-space: nowrap;
        }

        .colon {
            width: 12px;
            text-align: center;
        }

        .info-value {
            font-weight: bold;
            color: #111827;
        }

        .main-table th {
            background-color: #29b6e8;
            color: white;
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .main-table td {
            padding: 7px;
            border: 1px solid #eee;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .note { font-size: 10px; color: #6b7280; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CoE SMART EV</h2>
        <h3>Kartu Penyusutan Aset</h3>
        <p>{{ $assetInfo['nama_aset'] }} - {{ $assetInfo['kode_aset'] }}</p>
        <p>{{ $mode === 'tahun' ? 'Tampilan Per Tahun' : 'Tampilan Per Bulan' }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Nama Aset</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['nama_aset'] }}</td>
            <td class="info-label">Kode Aset</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['kode_aset'] }}</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal Diterima</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['tanggal_diterima'] }}</td>
            <td class="info-label">Harga Perolehan</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['harga_perolehan'] }}</td>
        </tr>
        <tr>
            <td class="info-label">Umur Ekonomis / Tahun</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['umur_ekonomis_tahun'] }}</td>
            <td class="info-label">Umur Ekonomis / Bulan</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['umur_ekonomis_bulan'] }}</td>
        </tr>
        <tr>
            <td class="info-label">Nilai Sisa</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['nilai_sisa'] }}</td>
            <td class="info-label">Metode Penyusutan</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['metode_penyusutan'] }}</td>
        </tr>
        <tr>
            <td class="info-label">Penyusutan per Bulan</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['penyusutan_per_bulan'] }}</td>
            <td class="info-label">Penyusutan per Tahun</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['penyusutan_per_tahun'] }}</td>
        </tr>
        <tr>
            <td class="info-label">Total Biaya Penyusutan</td>
            <td class="colon">:</td>
            <td class="info-value">{{ $assetInfo['total_biaya_penyusutan'] }}</td>
            <td class="info-label"></td>
            <td class="colon"></td>
            <td class="info-value"></td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th>{{ $mode === 'tahun' ? 'Tahun' : 'Tanggal / Periode' }}</th>
                <th>Keterangan</th>
                <th>Harga Perolehan</th>
                <th>Penyusutan</th>
                <th>Akumulasi Penyusutan</th>
                <th>Nilai Buku</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>
                        <div>{{ $row['periode'] }}</div>
                        @if (!empty($row['periode_detail']))
                            <div class="note">{{ $row['periode_detail'] }}</div>
                        @endif
                    </td>
                    <td>{{ $row['keterangan'] }}</td>
                    <td class="text-right">{{ $row['harga_perolehan'] }}</td>
                    <td class="text-right">{{ $row['penyusutan'] }}</td>
                    <td class="text-right">{{ $row['akumulasi'] }}</td>
                    <td class="text-right">{{ $row['nilai_buku'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
