<?php

namespace App\Filament\Admin\Resources\Penyusutans\Pages;

use App\Filament\Admin\Resources\Penyusutans\PenyusutanResource;
use App\Filament\Admin\Resources\BarangKantors\BarangKantorResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class PenyusutanKartuPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PenyusutanResource::class;

    public string $mode = 'bulan';

    public function getView(): string
    {
        return 'filament.admin.resources.penyusutans.kartu';
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();

        return 'Kartu Penyusutan Aset - ' . ($record?->nama_aset ?? 'N/A') . ' - ' . ($record?->kode_barang ?? 'N/A');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mode')
                ->label('Tampilan')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('warning')
                ->form([
                    Select::make('mode')
                        ->label('Tampilkan')
                        ->options([
                            'bulan' => 'Per Bulan',
                            'tahun' => 'Per Tahun',
                        ])
                        ->default($this->mode)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->mode = $data['mode'] ?? 'bulan';
                }),

            Actions\Action::make('back')
                ->label('Kembali')
                ->url(fn (): string => request()->query('from') === 'barang-kantor'
                    ? BarangKantorResource::getUrl('index')
                    : PenyusutanResource::getUrl())
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),

            Actions\Action::make('print')
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->action(fn () => $this->cetakKartu()),
        ];
    }

    public function getAssetInformation(): array
    {
        $record = $this->getRecord();
        $aset = $record->barangKantor;

        $harga = (int) $record->harga_perolehan;
        $nilaiResidu = (int) ($record->nilai_residu ?? 0);
        $umurTahun = max((int) $record->umur_ekonomis_tahun, 1);
        $umurBulan = $umurTahun * 12;
        $penyusutanPerBulan = ($harga - $nilaiResidu) / max($umurBulan, 1);
        $penyusutanPerTahun = $penyusutanPerBulan * 12;
        $totalBiayaPenyusutan = (float) ($record->total_biaya_penyusutan ?? 0);

        return [
            'nama_aset' => $record->nama_aset ?? $aset?->nama_barang ?? '-',
            'kode_aset' => $record->kode_barang ?? $aset?->kode_barang ?? '-',
            'tanggal_diterima' => $record->tanggal_diterima
                ? Carbon::parse($record->tanggal_diterima)->translatedFormat('d F Y')
                : '-',
            'harga_perolehan' => 'Rp ' . number_format($harga, 0, ',', '.'),
            'umur_ekonomis_tahun' => $umurTahun . ' Tahun',
            'umur_ekonomis_bulan' => $umurBulan . ' Bulan',
            'nilai_sisa' => 'Rp ' . number_format($nilaiResidu, 0, ',', '.'),
            'metode_penyusutan' => 'Garis Lurus',
            'penyusutan_per_bulan' => 'Rp ' . number_format($penyusutanPerBulan, 0, ',', '.'),
            'penyusutan_per_tahun' => 'Rp ' . number_format($penyusutanPerTahun, 0, ',', '.'),
            'total_biaya_penyusutan' => 'Rp ' . number_format($totalBiayaPenyusutan, 0, ',', '.'),
            'periode_awal' => $record->tanggal_diterima
                ? $record->bulanMulaiPenyusutan()->translatedFormat('F Y')
                : '-',
            'periode_akhir' => $record->details()->exists()
                ? Carbon::parse($record->details()->latest('periode')->value('periode'))->translatedFormat('F Y')
                : ($record->tanggal_diterima ? $record->bulanMulaiPenyusutan()->translatedFormat('F Y') : '-'),
        ];
    }

    public function getKartuRows(): array
    {
        $record = $this->getRecord();
        $harga = (int) $record->harga_perolehan;
        $tanggalPerolehan = $record->tanggal_diterima
            ? Carbon::parse($record->tanggal_diterima)
            : now();

        $rows = [[
            'periode' => $tanggalPerolehan->translatedFormat('d M Y'),
            'keterangan' => 'Pembelian Aset',
            'harga_perolehan' => 'Rp ' . number_format($harga, 0, ',', '.'),
            'penyusutan' => '-',
            'akumulasi' => '-',
            'nilai_buku' => 'Rp ' . number_format($harga, 0, ',', '.'),
            'is_header' => true,
        ]];

        $details = $record->details()
            ->orderBy('periode', 'asc')
            ->get();

        if ($this->mode === 'tahun') {
            $grouped = $details->groupBy(fn ($detail) => Carbon::parse($detail->periode)->year);

            foreach ($grouped as $year => $group) {
                $first = $group->first();
                $last = $group->last();
                $jumlahBulan = $group->count();

                $rows[] = [
                    'periode' => (string) $year,
                    'keterangan' => 'Penyusutan (' . $jumlahBulan . ' bulan)',
                    'harga_perolehan' => '-',
                    'penyusutan' => 'Rp ' . number_format((float) $group->sum('beban_penyusutan_bulanan'), 0, ',', '.'),
                    'akumulasi' => 'Rp ' . number_format((float) $last->akumulasi, 0, ',', '.'),
                    'nilai_buku' => 'Rp ' . number_format((float) $last->nilai_buku, 0, ',', '.'),
                    'is_header' => false,
                    'periode_detail' => Carbon::parse($first->periode)->translatedFormat('F Y') . ' s/d ' . Carbon::parse($last->periode)->translatedFormat('F Y'),
                ];
            }

            return $rows;
        }

        foreach ($details as $detail) {
            $rows[] = [
                'periode' => Carbon::parse($detail->periode)->translatedFormat('F Y'),
                'keterangan' => 'Penyusutan',
                'harga_perolehan' => '-',
                'penyusutan' => 'Rp ' . number_format((float) $detail->beban_penyusutan_bulanan, 0, ',', '.'),
                'akumulasi' => 'Rp ' . number_format((float) $detail->akumulasi, 0, ',', '.'),
                'nilai_buku' => 'Rp ' . number_format((float) $detail->nilai_buku, 0, ',', '.'),
                'is_header' => false,
            ];
        }

        return $rows;
    }

    public function cetakKartu()
    {
        $record = $this->getRecord();
        $assetInfo = $this->getAssetInformation();
        $rows = $this->getKartuRows();

        $filename = 'Kartu Penyusutan Aset- ' . Str::slug($record->nama_aset ?? 'Tanpa Nama', ' ') . '- ' . ($record->kode_barang ?? 'Tanpa Kode') . '.pdf';

        $pdf = Pdf::loadView('filament.admin.resources.penyusutans.kartu-pdf', [
            'record' => $record,
            'assetInfo' => $assetInfo,
            'rows' => $rows,
            'mode' => $this->mode,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
}
