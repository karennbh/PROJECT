<x-filament::page>
    @php
        $assetInfo = $this->getAssetInformation();
        $rows = $this->getKartuRows();
        $periodeLabel = $this->mode === 'tahun' ? 'Per Tahun' : 'Per Bulan';
    @endphp

    <style>
        .kartu-aset-shell {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .kartu-aset-header {
            background: #fff;
            border: 1px solid #d9eef9;
            border-radius: 24px;
            padding: 1.75rem;
            box-shadow: 0 10px 28px rgba(14, 165, 233, 0.08);
        }

        .kartu-aset-brand {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .kartu-aset-brand h2 {
            margin: 0;
            color: #0c4a6e;
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .kartu-aset-brand h3 {
            margin: 0.25rem 0 0;
            color: #0ea5e9;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .kartu-aset-brand p {
            margin: 0.35rem 0 0;
            color: #64748b;
            font-size: 0.95rem;
        }

        .kartu-aset-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem 2rem;
        }

        .kartu-aset-meta-item {
            display: grid;
            grid-template-columns: 205px 16px 1fr;
            gap: 0.35rem;
            align-items: center;
        }

        .kartu-aset-meta-label {
            color: #475569;
            font-weight: 600;
            white-space: nowrap;
        }

        .kartu-aset-meta-value {
            color: #0f172a;
            font-weight: 700;
        }

        .kartu-aset-table-wrap {
            background: #fff;
            border: 1px solid #d9eef9;
            border-radius: 24px;
            padding: 1.25rem;
            box-shadow: 0 10px 28px rgba(14, 165, 233, 0.08);
        }

        .kartu-aset-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 18px;
        }

        .kartu-aset-table thead tr {
            background: linear-gradient(90deg, #30b8f4 0%, #1fa7ee 100%);
        }

        .kartu-aset-table th {
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 14px 12px;
            border: 1px solid #8dd8fb;
        }

        .kartu-aset-table td {
            padding: 12px;
            border: 1px solid #dbe7f0;
            color: #0f172a;
        }

        .kartu-aset-table tbody tr:nth-child(even) {
            background: #f8fbff;
        }

        .kartu-aset-table tbody tr:hover {
            background: #eef8ff;
        }

        .kartu-aset-note {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.35rem;
        }

        .text-right {
            text-align: right;
        }

        @media (max-width: 1024px) {
            .kartu-aset-meta {
                grid-template-columns: 1fr;
            }

            .kartu-aset-meta-item {
                grid-template-columns: 205px 16px 1fr;
            }
        }

        @media (max-width: 640px) {
            .kartu-aset-meta-item {
                grid-template-columns: minmax(170px, 1fr) 16px minmax(0, 1fr);
            }

            .kartu-aset-meta-label {
                white-space: normal;
            }
        }

        @media print {
            .kartu-aset-shell {
                gap: 1rem;
            }

            .kartu-aset-header,
            .kartu-aset-table-wrap {
                border: 1px solid #d1d5db;
                border-radius: 0;
                box-shadow: none;
                break-inside: avoid;
            }

            .kartu-aset-brand {
                text-align: center;
                margin-bottom: 1rem;
            }

            .kartu-aset-brand h2,
            .kartu-aset-brand h3,
            .kartu-aset-brand p {
                color: #111827;
            }

            .kartu-aset-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.65rem 1.25rem;
            }

            .kartu-aset-meta-item {
                grid-template-columns: 205px 16px 1fr;
            }

            .kartu-aset-table {
                font-size: 11px;
                border-radius: 0;
            }

            .kartu-aset-table thead tr {
                background: #e5e7eb !important;
            }

            .kartu-aset-table th {
                color: #111827;
                border-color: #d1d5db;
                padding: 8px;
            }

            .kartu-aset-table td {
                border-color: #e5e7eb;
                padding: 8px;
            }
        }
    </style>

    <div class="kartu-aset-shell">
        <section class="kartu-aset-header">
            <div class="kartu-aset-brand">
                <h2>CoE SMART EV</h2>
                <h3>Kartu Penyusutan Aset</h3>
                <p>{{ $assetInfo['nama_aset'] }} - {{ $assetInfo['kode_aset'] }} | {{ $periodeLabel }}</p>
            </div>

            <div class="kartu-aset-meta">
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Nama Aset</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['nama_aset'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Kode Aset</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['kode_aset'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Tanggal Diterima</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['tanggal_diterima'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Harga Perolehan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['harga_perolehan'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Umur Ekonomis / Tahun</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['umur_ekonomis_tahun'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Umur Ekonomis / Bulan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['umur_ekonomis_bulan'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Nilai Sisa</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['nilai_sisa'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Metode Penyusutan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['metode_penyusutan'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Penyusutan per Bulan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['penyusutan_per_bulan'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Penyusutan per Tahun</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['penyusutan_per_tahun'] }}</div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Total Biaya Penyusutan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value">{{ $assetInfo['total_biaya_penyusutan'] }}</div>
                </div>
            </div>
        </section>

        <section class="kartu-aset-table-wrap">
            <table class="kartu-aset-table">
                <thead>
                    <tr>
                        <th>{{ $this->mode === 'tahun' ? 'Tahun' : 'Tanggal / Periode' }}</th>
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
                                    <div class="kartu-aset-note">{{ $row['periode_detail'] }}</div>
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
        </section>
    </div>
</x-filament::page>
