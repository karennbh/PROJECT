<?php

namespace App\Filament\Admin\Resources\BukuBesars\Widgets;

use Filament\Widgets\Widget;
use App\Models\JurnalUmum;
use App\Models\Coa;
use App\Models\JurnalDetail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BukuBesarTableOverview extends Widget
{
    protected string $view = 'filament.admin.resources.buku-besars.widgets.buku-besar-overview';

    public $periode_awal;
    public $periode_akhir;
    public $coa_id = ""; 

    public $saldoAwal = []; 
    public $jurnals;

    protected int|string|array $columnSpan = 'full';

    public function mount(): void
    {
        $this->ensureDefaultPeriode();
        $this->jurnals = collect();
        $this->filter();
    }

    public function hydrate(): void
    {
        $this->ensureDefaultPeriode();
    }

    public function filter(): void
    {
        $this->ensureDefaultPeriode();

        $this->validate([
            'periode_awal' => 'required',
            'periode_akhir' => 'required',
        ]);

        $awal  = Carbon::parse($this->periode_awal)->startOfMonth();
        $akhir = Carbon::parse($this->periode_akhir)->endOfMonth();

        $queryCoa = Coa::query();
        if ($this->coa_id) {
            $queryCoa->whereKey($this->coa_id);
        }
        $daftarCoa = $queryCoa
            ->orderBy('nama_akun')
            ->orderBy('kode_akun')
            ->get();

        $this->saldoAwal = [];
        $kodeAkuns = $daftarCoa->pluck('kode_akun')->toArray();

        foreach ($daftarCoa as $coa) {
            $kodeAkun = $coa->kode_akun;
            
            $isNormalKredit = self::isNormalKredit($coa);

            $saldo = (int) ($coa->jumlah_saldo ?? 0);
            
            $transaksiLalu = \App\Models\JurnalDetail::whereHas('jurnalUmum', function($q) use ($awal) {
                    $q->where('tanggal', '<', $awal);
                })
                ->where('kode_akun', $kodeAkun)
                ->get();

            foreach ($transaksiLalu as $d) {
                if ($isNormalKredit) {
                    // Jika normal Kredit: Saldo + Kredit - Debit
                    $saldo += ($d->nominal_kredit - $d->nominal_debit);
                } else {
                    // Jika normal Debit: Saldo + Debit - Kredit
                    $saldo += ($d->nominal_debit - $d->nominal_kredit);
                }
            }
            
            // Simpan hasil akhir akumulasi masa lalu sebagai Saldo Awal periode ini
            $this->saldoAwal[$kodeAkun] = $saldo;
        }

        $this->jurnals = JurnalUmum::whereBetween('tanggal', [$awal, $akhir])
            ->whereHas('details', fn($q) => $q->whereIn('kode_akun', $kodeAkuns))
            ->with(['details.coa'])
            ->orderBy('tanggal')
            ->orderBy('id_jurnal_umum')
            ->get();
    }

    public function updatedPeriodeAwal($value)
    {
        if (Carbon::parse($value)->gt(Carbon::parse($this->periode_akhir))) {
            $this->periode_akhir = $value;
        }
    }

    public function cetakLaporan()
    {
        $this->filter();

        $namaAkun = 'Semua Akun';
        if ($this->coa_id) {
            $coa = Coa::find($this->coa_id);
            $namaAkun = $coa ? $coa->nama_akun : 'Semua Akun';
        }

        $pAwal = Carbon::parse($this->periode_awal)->translatedFormat('F Y');
        $pAkhir = Carbon::parse($this->periode_akhir)->translatedFormat('F Y');
        $periodeStr = ($pAwal == $pAkhir) ? $pAwal : "{$pAwal} - {$pAkhir}";
        
        $filename = "Buku Besar_{$periodeStr}_{$namaAkun}.pdf";

        $displayCoas = $this->coa_id 
            ? Coa::whereKey($this->coa_id)->get() 
            : Coa::whereIn('kode_akun', array_keys($this->saldoAwal))
                ->orderBy('kode_akun')
                ->get();

        $data = [
            'jurnals' => $this->jurnals,
            'saldoAwal' => $this->saldoAwal,
            'periode_awal' => $this->periode_awal,
            'periode_akhir' => $this->periode_akhir,
            'displayCoas' => $displayCoas,
        ];

        $pdf = Pdf::loadView('filament.admin.resources.buku-besars.widgets.buku-besar-pdf-layout', $data)
                  ->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function getViewData(): array
    {
        $this->ensureDefaultPeriode();

        return [
            'jurnals'       => $this->jurnals,
            'saldoAwal'     => $this->saldoAwal,
            'periode_awal'  => $this->periode_awal,
            'periode_akhir' => $this->periode_akhir,
            'coa_id'        => $this->coa_id,
        ];
    }

    public static function ledgerRowsForDetail(JurnalUmum $jurnal, JurnalDetail $detail): array
    {
        $debit = (int) $detail->nominal_debit;
        $kredit = (int) $detail->nominal_kredit;
        $isDebit = $debit > 0;
        $amount = $isDebit ? $debit : $kredit;

        if ($amount <= 0) {
            return [];
        }

        $opposites = $jurnal->details
            ->filter(fn (JurnalDetail $item): bool => $isDebit
                ? (int) $item->nominal_kredit > 0
                : (int) $item->nominal_debit > 0)
            ->values();

        if ($opposites->isEmpty()) {
            return [[
                'lawan' => null,
                'debit' => $debit,
                'kredit' => $kredit,
            ]];
        }

        if ($opposites->count() === 1) {
            return [[
                'lawan' => $opposites->first(),
                'debit' => $debit,
                'kredit' => $kredit,
            ]];
        }

        $weights = $opposites
            ->map(fn (JurnalDetail $item): int => $isDebit
                ? (int) $item->nominal_kredit
                : (int) $item->nominal_debit)
            ->all();

        $allocated = self::distributeAmount($amount, $weights);

        return $opposites
            ->map(fn (JurnalDetail $opposite, int $index): array => [
                'lawan' => $opposite,
                'debit' => $isDebit ? ($allocated[$index] ?? 0) : 0,
                'kredit' => $isDebit ? 0 : ($allocated[$index] ?? 0),
            ])
            ->filter(fn (array $row): bool => ((int) $row['debit'] + (int) $row['kredit']) > 0)
            ->values()
            ->all();
    }

    public static function isNormalKredit(Coa $coa): bool
    {
        $saldoNormal = strtolower((string) $coa->saldo);

        if ($saldoNormal === 'kredit') {
            return true;
        }

        if ($saldoNormal === 'debit') {
            return false;
        }

        $kodeAkun = (string) $coa->kode_akun;

        return str_starts_with($kodeAkun, '2')
            || str_starts_with($kodeAkun, '3')
            || str_starts_with($kodeAkun, '4')
            || str_contains(strtolower((string) $coa->nama_akun), 'akumulasi');
    }

    private function ensureDefaultPeriode(): void
    {
        $currentMonth = now()->format('Y-m');

        $this->periode_awal = filled($this->periode_awal) ? $this->periode_awal : $currentMonth;
        $this->periode_akhir = filled($this->periode_akhir) ? $this->periode_akhir : $currentMonth;
    }

    private static function distributeAmount(int $amount, array $weights): array
    {
        $count = count($weights);

        if ($count === 0) {
            return [];
        }

        $totalWeight = array_sum($weights);

        if ($amount === 0 || $totalWeight <= 0) {
            return array_fill(0, $count, 0);
        }

        $result = [];
        $fractions = [];
        $allocated = 0;

        foreach ($weights as $index => $weight) {
            $raw = ($amount * $weight) / $totalWeight;
            $floor = (int) floor($raw);

            $result[$index] = $floor;
            $fractions[$index] = $raw - $floor;
            $allocated += $floor;
        }

        $remainder = $amount - $allocated;
        arsort($fractions);

        foreach (array_keys($fractions) as $index) {
            if ($remainder <= 0) {
                break;
            }

            $result[$index]++;
            $remainder--;
        }

        ksort($result);

        return array_values($result);
    }
}
