<?php

namespace App\Support;

class PerolehanBarangAllocator
{
    public static function allocate(array $data): array
    {
        $details = collect($data['details'] ?? [])
            ->map(function (array $detail) {
                $jumlah = max(1, self::normalizeNumber($detail['jumlah_perolehan'] ?? 0));
                $hargaAwal = self::normalizeNumber($detail['harga_satuan'] ?? $detail['harga_perolehan'] ?? 0);
                $subtotalAwal = $jumlah * $hargaAwal;

                return array_merge($detail, [
                    'jumlah_perolehan' => $jumlah,
                    'harga_satuan' => $hargaAwal,
                    'total_harga' => $subtotalAwal,
                ]);
            })
            ->values();

        if ($details->isEmpty()) {
            $data['details'] = [];
            $data['subtotal_barang'] = 0;
            $data['diskon_total'] = self::normalizeNumber($data['diskon_total'] ?? 0);
            $data['biaya_lainnya_total'] = self::normalizeNumber($data['biaya_lainnya_total'] ?? 0);
            $data['grand_total'] = 0;

            return $data;
        }

        $subtotals = $details->pluck('total_harga')->map(fn ($value) => (int) $value)->all();
        $subtotalBarang = array_sum($subtotals);
        $diskonTotal = self::normalizeNumber($data['diskon_total'] ?? 0);
        $biayaLainnyaTotal = self::normalizeNumber($data['biaya_lainnya_total'] ?? 0);

        $updatedDetails = $details->map(function (array $detail) use ($subtotalBarang, $diskonTotal, $biayaLainnyaTotal) {
            $subtotalAwal = (int) $detail['total_harga'];
            $jumlah = max(1, (int) $detail['jumlah_perolehan']);
            $alokasiDiskonRaw = $subtotalBarang > 0
                ? ($diskonTotal * $subtotalAwal) / $subtotalBarang
                : 0;
            $alokasiBiayaLainnyaRaw = $subtotalBarang > 0
                ? ($biayaLainnyaTotal * $subtotalAwal) / $subtotalBarang
                : 0;
            $alokasiDiskonItem = (int) round($alokasiDiskonRaw, 0, PHP_ROUND_HALF_UP);
            $alokasiBiayaLainnyaItem = (int) round($alokasiBiayaLainnyaRaw, 0, PHP_ROUND_HALF_UP);

            $totalFinal = (int) round(
                $subtotalAwal - $alokasiDiskonRaw + $alokasiBiayaLainnyaRaw,
                0,
                PHP_ROUND_HALF_UP
            );

            $persentase = $subtotalBarang > 0
                ? round(($subtotalAwal / $subtotalBarang) * 100, 4)
                : 0;

            return array_merge($detail, [
                'persentase_subtotal' => $persentase,
                'alokasi_diskon' => $alokasiDiskonItem,
                'alokasi_biaya_lainnya' => $alokasiBiayaLainnyaItem,
                'harga_perolehan' => (int) round($totalFinal / $jumlah),
                'total_harga_perolehan' => $totalFinal,
            ]);
        })->all();

        $data['details'] = $updatedDetails;
        $data['subtotal_barang'] = $subtotalBarang;
        $data['diskon_total'] = $diskonTotal;
        $data['biaya_lainnya_total'] = $biayaLainnyaTotal;
        $data['grand_total'] = (int) collect($updatedDetails)->sum('total_harga_perolehan');

        return $data;
    }

    public static function calculationSignature(array $data): string
    {
        $details = collect($data['details'] ?? [])
            ->map(fn (array $detail) => [
                'kategori_barang' => $detail['kategori_barang'] ?? null,
                'nama_barang' => $detail['nama_barang'] ?? null,
                'jenis_aset' => $detail['jenis_aset'] ?? null,
                'jenis_bhp' => $detail['jenis_bhp'] ?? null,
                'kategori_aset_id' => $detail['kategori_aset_id'] ?? null,
                'kode_barang' => $detail['kode_barang'] ?? null,
                'umur_ekonomis' => self::normalizeNumber($detail['umur_ekonomis'] ?? 0),
                'nilai_residu' => self::normalizeNumber($detail['nilai_residu'] ?? 0),
                'jumlah_perolehan' => self::normalizeNumber($detail['jumlah_perolehan'] ?? 0),
                'harga_satuan' => self::normalizeNumber($detail['harga_satuan'] ?? 0),
            ])
            ->values()
            ->all();

        return sha1((string) json_encode([
            'details' => $details,
            'diskon_total' => self::normalizeNumber($data['diskon_total'] ?? 0),
            'biaya_lainnya_total' => self::normalizeNumber($data['biaya_lainnya_total'] ?? 0),
        ]));
    }

    public static function normalizeNumber(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        $value = trim((string) $value);

        if (preg_match('/^\d+\.\d{1,2}$/', $value) === 1) {
            return (int) round((float) $value);
        }

        $normalized = preg_replace('/[^0-9]/', '', $value);

        return (int) ($normalized ?: 0);
    }

    public static function normalizeSignedNumber(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        $value = trim((string) $value);
        $isNegative = str_starts_with($value, '-');
        $normalized = preg_replace('/[^0-9]/', '', $value);
        $number = (int) ($normalized ?: 0);

        return $isNegative ? -$number : $number;
    }

    /**
     * @param  array<int, int>  $weights
     * @return array<int, int>
     */
    private static function distributeAmount(int $amount, array $weights): array
    {
        $count = count($weights);

        if ($count === 0 || $amount === 0) {
            return array_fill(0, $count, 0);
        }

        $totalWeight = array_sum($weights);

        if ($totalWeight <= 0) {
            $base = intdiv($amount, $count);
            $remainder = $amount - ($base * $count);
            $result = array_fill(0, $count, $base);

            for ($i = 0; $i < $remainder; $i++) {
                $result[$i]++;
            }

            return $result;
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

    private static function distributeSignedAmount(int $amount, array $weights): array
    {
        if ($amount === 0) {
            return array_fill(0, count($weights), 0);
        }

        $sign = $amount < 0 ? -1 : 1;

        return array_map(
            fn (int $value): int => $value * $sign,
            self::distributeAmount(abs($amount), $weights),
        );
    }
}
