<?php

namespace App\Filament\Admin\Resources\PengajuanPembelianBarangs\Pages;

use App\Filament\Admin\Resources\PengajuanPembelianBarangs\PengajuanPembelianBarangResource;
use App\Models\PengajuanPembelianBarang;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanPembelianBarangs extends ListRecords
{
    protected static string $resource = PengajuanPembelianBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->form([
                    DatePicker::make('tanggal_awal')
                        ->label('Tanggal Awal')
                        ->default(now()->startOfMonth())
                        ->required()
                        ->live(),
                    DatePicker::make('tanggal_akhir')
                        ->label('Tanggal Akhir')
                        ->default(now()->endOfMonth())
                        ->minDate(fn (callable $get) => $get('tanggal_awal'))
                        ->required(),
                    Select::make('status')
                        ->label('Status')
                        ->placeholder('Semua status')
                        ->options([
                            'pending' => 'Pending',
                            'disetujui' => 'Disetujui',
                            'ditolak' => 'Ditolak',
                        ]),
                ])
                ->action(function (array $data) {
                    $tanggalAwal = Carbon::parse($data['tanggal_awal'])->startOfDay();
                    $tanggalAkhir = Carbon::parse($data['tanggal_akhir'])->endOfDay();

                    if ($tanggalAkhir->lt($tanggalAwal)) {
                        $tanggalAkhir = $tanggalAwal->copy()->endOfDay();
                    }

                    $query = PengajuanPembelianBarang::query()
                        ->with('user')
                        ->whereBetween('tanggal_pengajuan', [$tanggalAwal->toDateString(), $tanggalAkhir->toDateString()]);

                    if (! blank($data['status'] ?? null)) {
                        $query->where('status', $data['status']);
                    }

                    $records = $query
                        ->orderBy('tanggal_pengajuan', 'asc')
                        ->orderBy('id_pembelian_barang_kantor', 'asc')
                        ->get();

                    if ($records->isEmpty()) {
                        Notification::make()
                            ->title('Tidak Ada Data')
                            ->body('Tidak ada data pembelian barang pada periode tersebut.')
                            ->warning()
                            ->send();

                        return null;
                    }

                    $periodeLabel = $tanggalAwal->translatedFormat('d F Y') . ' - ' . $tanggalAkhir->translatedFormat('d F Y');
                    $statusLabel = match ($data['status'] ?? null) {
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        default => 'Semua Status',
                    };

                    $pdf = Pdf::loadView('filament.admin.resources.pembelian-barangs.laporan-pdf', [
                        'records' => $records,
                        'periodeLabel' => $periodeLabel,
                        'statusLabel' => $statusLabel,
                    ])->setPaper('a4', 'portrait');

                    $filename = 'Laporan Pembelian Barang_' . $tanggalAwal->format('d-m-Y') . '_' . $tanggalAkhir->format('d-m-Y') . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),
        ];
    }
}
