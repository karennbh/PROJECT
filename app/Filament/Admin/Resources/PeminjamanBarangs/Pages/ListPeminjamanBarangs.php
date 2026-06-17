<?php

namespace App\Filament\Admin\Resources\PeminjamanBarangs\Pages;

use App\Filament\Admin\Resources\PeminjamanBarangs\PeminjamanBarangResource;
use App\Models\PeminjamanBarang;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPeminjamanBarangs extends ListRecords
{
    protected static string $resource = PeminjamanBarangResource::class;

    public function mount(): void
    {
        PeminjamanBarang::expirePendingOverdue();

        parent::mount();
    }

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
                    Select::make('status_pinjam')
                        ->label('Status')
                        ->placeholder('Semua status')
                        ->options([
                            'pending' => 'Pending',
                            'expired' => 'Expired',
                            'disetujui' => 'Sedang Dipinjam',
                            'menunggu_verifikasi_pengembalian' => 'Menunggu Verifikasi Admin',
                            'kembali' => 'Sudah Dikembalikan',
                            'ditolak' => 'Ditolak',
                        ]),
                ])
                ->action(function (array $data) {
                    $tanggalAwal = Carbon::parse($data['tanggal_awal'])->startOfDay();
                    $tanggalAkhir = Carbon::parse($data['tanggal_akhir'])->endOfDay();

                    if ($tanggalAkhir->lt($tanggalAwal)) {
                        $tanggalAkhir = $tanggalAwal->copy()->endOfDay();
                    }

                    $query = PeminjamanBarang::query()
                        ->with(['user', 'barang'])
                        ->whereBetween('tanggal_pinjam', [$tanggalAwal->toDateString(), $tanggalAkhir->toDateString()]);

                    if (! blank($data['status_pinjam'] ?? null)) {
                        $query->where('status_pinjam', $data['status_pinjam']);
                    }

                    $records = $query
                        ->orderBy('tanggal_pinjam', 'asc')
                        ->orderBy('id_peminjaman', 'asc')
                        ->get();

                    if ($records->isEmpty()) {
                        Notification::make()
                            ->title('Tidak Ada Data')
                            ->body('Tidak ada data peminjaman barang kantor pada periode tersebut.')
                            ->warning()
                            ->send();

                        return null;
                    }

                    $periodeLabel = $tanggalAwal->translatedFormat('d F Y') . ' - ' . $tanggalAkhir->translatedFormat('d F Y');
                    $statusLabel = match ($data['status_pinjam'] ?? null) {
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                        'disetujui' => 'Sedang Dipinjam',
                        'menunggu_verifikasi_pengembalian' => 'Menunggu Verifikasi Admin',
                        'kembali' => 'Sudah Dikembalikan',
                        'ditolak' => 'Ditolak',
                        default => 'Semua Status',
                    };

                    $pdf = Pdf::loadView('filament.admin.resources.peminjaman-barangs.laporan-pdf', [
                        'records' => $records,
                        'periodeLabel' => $periodeLabel,
                        'statusLabel' => $statusLabel,
                    ])->setPaper('a4', 'portrait');

                    $filename = 'Laporan Peminjaman Barang Kantor_' . $tanggalAwal->format('d-m-Y') . '_' . $tanggalAkhir->format('d-m-Y') . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),
        ];
    }
}
