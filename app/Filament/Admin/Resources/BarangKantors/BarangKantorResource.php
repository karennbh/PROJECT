<?php

namespace App\Filament\Admin\Resources\BarangKantors;

use App\Filament\Admin\Resources\BarangKantors\Pages\CreateBarangKantor;
use App\Filament\Admin\Resources\BarangKantors\Pages\EditBarangKantor;
use App\Filament\Admin\Resources\BarangKantors\Pages\ListBarangKantors;
use App\Filament\Admin\Resources\BarangKantors\Pages\ViewBarangKantor;
// use App\Filament\Admin\Resources\BarangKantors\Pages\ScanBarangKantor;
use App\Filament\Admin\Resources\BarangKantors\Schemas\BarangKantorForm;
use App\Filament\Admin\Resources\BarangKantors\Schemas\BarangKantorInfolist;
use App\Filament\Admin\Resources\BarangKantors\Tables\BarangKantorsTable;
use App\Models\BarangKantor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BarangKantorResource extends Resource
{
    private const MIN_HARGA_PEROLEHAN = 1000;
    private const MAX_HARGA_PEROLEHAN = 100000000;

    protected static ?string $model = BarangKantor::class;

    protected static ?string $navigationLabel = 'Barang Kantor';
    protected static ?string $modelLabel = 'Barang Kantor';
    protected static ?string $pluralModelLabel = 'Barang Kantor';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 4;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $recordTitleAttribute = 'nama_barang';

    public static function form(Schema $schema): Schema
    {
        return BarangKantorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BarangKantorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BarangKantorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'kode_barang',
            'nama_barang',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBarangKantors::route('/'),
            'create' => CreateBarangKantor::route('/create'),
            'scan'   => \App\Filament\Admin\Resources\BarangKantors\Pages\ScanBarangKantor::route('/scan'),
            'view' => ViewBarangKantor::route('/{record}'),
            'edit' => EditBarangKantor::route('/{record}/edit'),
        ];
    }

    public static function normalizeRupiah(mixed $state): int
    {
        if ($state === null || $state === '') {
            return 0;
        }

        $state = trim((string) $state);

        if (preg_match('/^\d+\.\d{1,2}$/', $state) === 1) {
            return (int) round((float) $state);
        }

        $numeric = preg_replace('/[^0-9]/', '', $state);

        return (int) ($numeric ?: 0);
    }

    public static function formatRupiah(mixed $state): string
    {
        return number_format(self::normalizeRupiah($state), 0, ',', '.');
    }

    public static function nonNegativeNominalRule(string $fieldLabel): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($fieldLabel): void {
            if ($value === null || $value === '') {
                return;
            }

            if (str_contains((string) $value, '-')) {
                $fail($fieldLabel . ' tidak boleh input kurang dari 0.');
            }
        };
    }

    public static function positiveNominalRangeRule(string $fieldLabel): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($fieldLabel): void {
            if ($value === null || $value === '') {
                return;
            }

            if (str_contains((string) $value, '-')) {
                $fail($fieldLabel . ' harus lebih dari 0.');

                return;
            }

            $nominal = self::normalizeRupiah($value);

            if ($nominal <= 0) {
                $fail($fieldLabel . ' harus lebih dari 0.');

                return;
            }

            if ($nominal < self::MIN_HARGA_PEROLEHAN) {
                $fail($fieldLabel . ' minimal Rp 1.000.');

                return;
            }

            if ($nominal > self::MAX_HARGA_PEROLEHAN) {
                $fail($fieldLabel . ' tidak boleh lebih dari Rp 100.000.000.');
            }
        };
    }

    public static function residualNotGreaterThanAcquisitionRule(callable $get): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
            if ($value === null || $value === '') {
                return;
            }

            $nilaiResidu = self::normalizeRupiah($value);
            $hargaPerolehan = self::normalizeRupiah($get('harga_perolehan'));

            if ($hargaPerolehan > 0 && $nilaiResidu > $hargaPerolehan) {
                $fail('Nilai residu tidak boleh melebihi harga perolehan.');
            }
        };
    }

    public static function isStatusPenggunaanLocked(?BarangKantor $record): bool
    {
        return $record?->status_penggunaan === BarangKantor::STATUS_SIAP_DIGUNAKAN
            && filled($record?->tanggal_diterima);
    }
}
