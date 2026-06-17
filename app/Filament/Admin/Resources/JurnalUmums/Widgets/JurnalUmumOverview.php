<?php

namespace App\Filament\Admin\Resources\JurnalUmums\Widgets;

use Filament\Widgets\Widget;
use App\Models\JurnalUmum;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan DomPDF sudah terinstal

class JurnalUmumOverview extends Widget
{
    protected string $view = 'filament.admin.resources.jurnal-umums.widgets.jurnal-umum-overview';

    public ?string $periode_awal = null;
    public ?string $periode_akhir = null;

    public $jurnals;
    public float|int $totalDebit = 0;
    public float|int $totalKredit = 0;

    protected int|string|array $columnSpan = 'full';

    public function mount(): void
    {
        $this->periode_awal = now()->format('Y-m');
        $this->periode_akhir = now()->format('Y-m');
        $this->reloadData();
    }

    public function updatedPeriodeAwal($value)
    {
        if ($this->periode_akhir && Carbon::parse($this->periode_akhir)->lt(Carbon::parse($value))) {
            $this->periode_akhir = $value;
        }
    }

    public function filterJurnal(): void
    {
        if (Carbon::parse($this->periode_akhir)->lt(Carbon::parse($this->periode_awal))) {
            $this->periode_akhir = $this->periode_awal;
        }
        $this->reloadData();
    }

    public function reloadData(): void
    {
        if (!$this->periode_awal || !$this->periode_akhir) {
            $this->jurnals = collect();
            $this->totalDebit = 0;
            $this->totalKredit = 0;
            return;
        }

        $query = JurnalUmum::with('details.coa')
            ->whereBetween('tanggal', [
                Carbon::parse($this->periode_awal)->startOfMonth()->format('Y-m-d'),
                Carbon::parse($this->periode_akhir)->endOfMonth()->format('Y-m-d')
            ]);

        $this->jurnals = $query->orderBy('tanggal', 'asc')->get();
        $this->totalDebit  = $this->jurnals->flatMap->details->sum('nominal_debit');
        $this->totalKredit = $this->jurnals->flatMap->details->sum('nominal_kredit');
    }

    // FUNGSI CETAK BARU
    public function cetakJurnal()
{
    $this->reloadData();

    $pAwal = Carbon::parse($this->periode_awal)->translatedFormat('F Y');
    $pAkhir = Carbon::parse($this->periode_akhir)->translatedFormat('F Y');
    $periodeStr = ($pAwal == $pAkhir) ? $pAwal : "{$pAwal} - {$pAkhir}";
    
    // Ambil data untuk tabel kedua (Daftar Transaksi)
    // Kita ambil model JurnalUmum yang sama tapi tanpa detail coa untuk ringkasan di bawah
    $daftarTransaksi = JurnalUmum::whereBetween('tanggal', [
                            Carbon::parse($this->periode_awal)->startOfMonth()->format('Y-m-d'),
                            Carbon::parse($this->periode_akhir)->endOfMonth()->format('Y-m-d')
                        ])
                        ->orderBy('tanggal', 'asc')
                        ->get();

    $data = [
        'jurnals' => $this->jurnals,
        'daftarTransaksi' => $daftarTransaksi, // Data untuk tabel bawah
        'totalDebit' => $this->totalDebit,
        'totalKredit' => $this->totalKredit,
        'periode_awal' => $this->periode_awal,
        'periode_akhir' => $this->periode_akhir,
    ];

    $filename = "Jurnal Umum_{$periodeStr}.pdf";

    $pdf = Pdf::loadView('filament.admin.resources.jurnal-umums.widgets.jurnal-umum-pdf', $data)
                  ->setPaper('a4', 'portrait');

    return response()->streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename);
}

    public function getViewData(): array
    {
        return [
            'jurnals'       => $this->jurnals ?? collect(),
            'periode_awal'  => $this->periode_awal,
            'periode_akhir' => $this->periode_akhir,
            'totalDebit'    => $this->totalDebit,
            'totalKredit'   => $this->totalKredit,
        ];
    }
}