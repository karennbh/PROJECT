<?php

namespace App\Filament\Admin\Resources\PerolehanBarangs;

use App\Filament\Admin\Resources\PerolehanBarangs\Pages\CreatePerolehanBarang;
use App\Filament\Admin\Resources\PerolehanBarangs\Pages\EditPerolehanBarang;
use App\Filament\Admin\Resources\PerolehanBarangs\Pages\ListPerolehanBarangs;
use App\Filament\Admin\Resources\PerolehanBarangs\Schemas\PerolehanBarangForm;
use App\Filament\Admin\Resources\PerolehanBarangs\Tables\PerolehanBarangsTable;
use App\Models\BarangKantor;
use App\Models\PendapatanHibah;
use App\Models\PerolehanBarang;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class PerolehanBarangResource extends Resource
{
    protected static ?string $model = PerolehanBarang::class;

    protected static ?string $navigationLabel = 'Perolehan Barang';
    protected static ?string $pluralModelLabel = 'Perolehan Barang';
    protected static ?string $modelLabel = 'Perolehan Barang';
    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $recordTitleAttribute = 'id_perolehan_barang';

    public static function form(Schema $schema): Schema
    {
        return PerolehanBarangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerolehanBarangsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPerolehanBarangs::route('/'),
            'create' => CreatePerolehanBarang::route('/create'),
            'edit' => EditPerolehanBarang::route('/{record}/edit'),
        ];
    }

    public static function generatePerolehanId(string $source): string
    {
        $prefix = match ($source) {
            PerolehanBarang::SUMBER_HIBAH => 'PRL-HB',
            PerolehanBarang::SUMBER_HIBAH_UANG => 'PRL-HU',
            default => 'PRL-PB',
        };
        $lastNumber = PerolehanBarang::query()
            ->pluck('id_perolehan_barang')
            ->map(fn (string $id): int => (int) preg_replace('/\D+/', '', $id))
            ->max();

        $next = ((int) $lastNumber) + 1;

        return $prefix . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function isPembelianSource(mixed $source): bool
    {
        return ($source ?? PerolehanBarang::SUMBER_PEMBELIAN) === PerolehanBarang::SUMBER_PEMBELIAN;
    }

    public static function isHibahSource(mixed $source): bool
    {
        return in_array($source, [PerolehanBarang::SUMBER_HIBAH_LEGACY, PerolehanBarang::SUMBER_HIBAH, PerolehanBarang::SUMBER_HIBAH_UANG], true);
    }

    public static function isHibahUangSource(mixed $source): bool
    {
        return $source === PerolehanBarang::SUMBER_HIBAH_UANG;
    }

    public static function sumberPerolehanOptions(): array
    {
        return [
            PerolehanBarang::SUMBER_PEMBELIAN => 'Pembelian',
            PerolehanBarang::SUMBER_HIBAH => 'Hibah Barang',
            PerolehanBarang::SUMBER_HIBAH_UANG => 'Hibah Uang',
        ];
    }

    public static function statusPenggunaanOptions(): array
    {
        return [
            BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN => 'Belum Siap Digunakan',
            BarangKantor::STATUS_SIAP_DIGUNAKAN => 'Siap Digunakan',
        ];
    }

    public static function kategoriBarangOptions(): array
    {
        return [
            'aset' => 'Aset Tetap',
            'bhp' => 'Barang Habis Pakai',
        ];
    }

    public static function jenisAsetOptions(): array
    {
        return [
            'sarana_pendidikan_laboratorium' => 'Sarana Pendidikan Laboratorium',
            'inventaris_kantor' => 'Inventaris Kantor',
            'kendaraan' => 'Kendaraan',
        ];
    }

    public static function jenisBhpOptions(): array
    {
        return [
            BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR => 'ATK Operasional Kantor',
            BarangKantor::JENIS_BHP_INVENTARIS_KANTOR => 'BPP Inventaris Kantor',
        ];
    }

    public static function satuanBarangOptions(): array
    {
        return [
            'Pcs' => 'Pcs',
            'Unit' => 'Unit',
            'Pack' => 'Pack',
            'Kotak' => 'Kotak',
            'Rim' => 'Rim',
        ];
    }

    public static function statusPenggunaanLabel(?string $status): string
    {
        return match ($status) {
            BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN => 'Belum Siap Digunakan',
            BarangKantor::STATUS_SIAP_DIGUNAKAN => 'Siap Digunakan',
            default => '-',
        };
    }

    public static function fillPendapatanHibahInfo(mixed $noHibah, Set $set): void
    {
        $hibah = $noHibah ? PendapatanHibah::query()->find($noHibah) : null;

        $set('nama_pemberi_hibah', $hibah?->sumber_hibah);
        $set('nilai_pengakuan_pendapatan_hibah_uang', $hibah?->nilai_hibah ?? 0);
        $set('total_pendapatan_hibah_display', $hibah ? self::formatRupiah($hibah->nilai_hibah) : null);
    }

    public static function pendapatanHibahOptions(mixed $tanggalPembelian, ?PerolehanBarang $record = null): array
    {
        $tanggal = self::parseTanggalPerolehan($tanggalPembelian);

        if (! $tanggal) {
            return [];
        }

        return PendapatanHibah::query()
            ->whereDate('tanggal_hibah', '<=', $tanggal->toDateString())
            ->orderByDesc('tanggal_hibah')
            ->get()
            ->filter(fn (PendapatanHibah $hibah) => $hibah->sisa > 0 || $hibah->no_hibah === $record?->pendapatan_hibah_id)
            ->mapWithKeys(fn (PendapatanHibah $hibah) => [
                $hibah->no_hibah => "{$hibah->sumber_hibah} - {$hibah->no_hibah}",
            ])
            ->all();
    }

    public static function isPendapatanHibahAvailableForTanggal(mixed $noHibah, mixed $tanggalPembelian): bool
    {
        if (blank($noHibah)) {
            return true;
        }

        $tanggal = self::parseTanggalPerolehan($tanggalPembelian);

        if (! $tanggal) {
            return false;
        }

        $hibah = PendapatanHibah::query()->find($noHibah);

        if (! $hibah?->tanggal_hibah) {
            return false;
        }

        return Carbon::parse($hibah->tanggal_hibah)->startOfDay()->lte($tanggal);
    }

    public static function parseTanggalPerolehan(mixed $tanggal): ?Carbon
    {
        if (blank($tanggal)) {
            return null;
        }

        try {
            return Carbon::parse($tanggal)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function formatRupiah(mixed $state): string
    {
        $numeric = preg_replace('/[^0-9]/', '', (string) ($state ?? ''));

        return number_format((int) ($numeric ?: 0), 0, ',', '.');
    }
}
