<?php

namespace App\Filament\Admin\Resources\Penyusutans\Pages;

use App\Filament\Admin\Resources\Penyusutans\PenyusutanResource;
use App\Models\JurnalDetail;
use App\Models\JurnalUmum;
use App\Models\PenyusutanAsetTetap;
use App\Models\PenyusutanDetail;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListPenyusutans extends ListRecords
{
    protected static string $resource = PenyusutanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('prosesPenyusutan')
                ->label('Proses Akhir Periode')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Proses Penyusutan')
                ->modalDescription('Proses ini akan membuat jurnal dan detail penyusutan untuk semua periode yang belum diposting sampai periode yang dipilih.')
                ->form([
                    Select::make('tahun')
                        ->label('Tahun')
                        ->options(
                            collect(range(2021, now()->year))
                                ->mapWithKeys(fn ($y) => [$y => $y])
                                ->toArray()
                        )
                        ->default((int) now()->year)
                        ->required()
                        ->live(),

                    Select::make('bulan')
                        ->label('Periode (Bulan)')
                        ->options([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                            4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ])
                        ->default((int) now()->month)
                        ->required()
                        ->live(),
                ])
                ->action(function (array $data): void {
                    $bulan = (int) $data['bulan'];
                    $tahun = (int) $data['tahun'];

                    $targetStart = Carbon::create($tahun, $bulan, 1)->startOfMonth();
                    $targetEnd = Carbon::create($tahun, $bulan, 1)->endOfMonth();

                    if ($targetStart->gt(now()->startOfMonth())) {
                        Notification::make()
                            ->title('Tidak Dapat Diproses')
                            ->warning()
                            ->body('Tidak dapat memproses periode masa depan.')
                            ->send();

                        return;
                    }

                    if (! $this->periodeSudahBolehDiposting($targetEnd)) {
                        Notification::make()
                            ->title('Belum Waktunya')
                            ->warning()
                            ->body(
                                "Penyusutan periode {$targetStart->translatedFormat('F Y')} "
                                . "hanya dapat dilakukan pada atau setelah {$targetEnd->translatedFormat('d F Y')}."
                            )
                            ->send();

                        return;
                    }

                    $this->postingPeriode($bulan, $tahun);
                }),
        ];
    }

    protected function periodeSudahBolehDiposting(Carbon $targetEnd): bool
    {
        return now()->gte($targetEnd->copy()->startOfDay());
    }

    protected function postingPeriode(int $bulan, int $tahun): void
    {
        $targetEnd = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $assets = PenyusutanAsetTetap::query()
            ->with('barangKantor')
            ->get();

        if ($assets->isEmpty()) {
            Notification::make()
                ->title('Info')
                ->warning()
                ->body('Tidak ada data penyusutan.')
                ->send();

            return;
        }

        $totalDiposting = 0;
        $totalSudahAda = 0;
        $totalDilewati = 0;

        DB::beginTransaction();

        try {
            foreach ($assets as $asset) {
                if (! $asset->isAktif()) {
                    $totalDilewati++;
                    continue;
                }

                $mulai = $asset->bulanMulaiPenyusutan();

                if ($targetEnd->lt($mulai)) {
                    $totalDilewati++;
                    continue;
                }

                $akhirValid = $asset->bulanAkhirUmurEkonomis();

                if ($targetEnd->copy()->startOfMonth()->gt($akhirValid)) {
                    $periodeAkhir = $akhirValid->copy();
                } else {
                    $periodeAkhir = $targetEnd->copy();
                }

                $cursor = $mulai->copy()->endOfMonth();
                $dipostingUntukAset = 0;

                while ($cursor->lte($periodeAkhir)) {
                    $exists = PenyusutanDetail::where('penyusutan_id', $asset->id_penyusutan)
                        ->where('periode', $cursor->toDateString())
                        ->exists();

                    if ($exists) {
                        $totalSudahAda++;
                        $cursor->addMonthNoOverflow()->endOfMonth();
                        continue;
                    }

                    $this->buatDetailDanJurnalPenyusutan($asset, $cursor);
                    $dipostingUntukAset++;
                    $cursor->addMonthNoOverflow()->endOfMonth();
                }

                if ($dipostingUntukAset > 0) {
                    $this->hitungUlangAkumulasiPenyusutan($asset);
                    $totalDiposting += $dipostingUntukAset;
                }
            }

            DB::commit();

            if ($totalDiposting === 0) {
                Notification::make()
                    ->title('Tidak Ada Periode Baru')
                    ->warning()
                    ->body('Tidak ada periode penyusutan baru yang perlu diposting.')
                    ->send();

                return;
            }

            $pesan = "Berhasil membuat {$totalDiposting} detail penyusutan sampai {$targetEnd->translatedFormat('F Y')}.";

            if ($totalSudahAda > 0) {
                $pesan .= " {$totalSudahAda} periode sudah pernah diposting dan dilewati.";
            }

            if ($totalDilewati > 0) {
                $pesan .= " {$totalDilewati} aset dilewati (belum siap digunakan / non-aktif / di luar periode penyusutan).";
            }

            Notification::make()
                ->title('Proses Penyusutan Selesai')
                ->success()
                ->body($pesan)
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('Gagal')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    private function buatDetailDanJurnalPenyusutan(PenyusutanAsetTetap $asset, Carbon $periode): void
    {
        $bebanFinal = (float) $asset->beban_penyusutan_bulanan;

        $ju = JurnalUmum::create([
            'reff_penyusutan' => $asset->id_penyusutan,
            'tanggal' => $periode->toDateString(),
            'deskripsi' => 'Penyusutan: '
                . ($asset->nama_aset ?? $asset->kode_barang ?? $asset->id_penyusutan)
                . ' (' . $periode->copy()->startOfMonth()->translatedFormat('M Y') . ')',
            'tipe_transaksi' => 'penyusutan',
        ]);

        JurnalDetail::create([
            'id_jurnal_umum' => $ju->id_jurnal_umum,
            'kode_akun' => '5611104',
            'nominal_debit' => (int) round($bebanFinal),
            'nominal_kredit' => 0,
            'keterangan' => 'Beban Penyusutan',
        ]);

        JurnalDetail::create([
            'id_jurnal_umum' => $ju->id_jurnal_umum,
            'kode_akun' => '1264101',
            'nominal_debit' => 0,
            'nominal_kredit' => (int) round($bebanFinal),
            'keterangan' => 'Akumulasi Penyusutan',
        ]);

        PenyusutanDetail::create([
            'penyusutan_id' => $asset->id_penyusutan,
            'periode' => $periode->toDateString(),
            'beban_penyusutan_bulanan' => $bebanFinal,
            'akumulasi' => 0,
            'nilai_buku' => (int) $asset->harga_perolehan,
            'jurnal_umum_id' => $ju->id_jurnal_umum,
        ]);
    }

    private function hitungUlangAkumulasiPenyusutan(PenyusutanAsetTetap $asset): void
    {
        $akumulasi = 0;

        $asset->details()
            ->get()
            ->each(function (PenyusutanDetail $detail) use ($asset, &$akumulasi): void {
                $akumulasi += (float) $detail->beban_penyusutan_bulanan;

                $detail->forceFill([
                    'akumulasi' => (int) round($akumulasi),
                    'nilai_buku' => max(
                        (int) round((float) $asset->harga_perolehan - $akumulasi),
                        (int) $asset->nilai_residu
                    ),
                ])->saveQuietly();
            });
    }
}
