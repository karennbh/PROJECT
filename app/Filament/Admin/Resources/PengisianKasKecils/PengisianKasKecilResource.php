<?php

namespace App\Filament\Admin\Resources\PengisianKasKecils;

use App\Filament\Admin\Resources\PengisianKasKecils\Pages\CreatePengisianKasKecil;
use App\Filament\Admin\Resources\PengisianKasKecils\Pages\EditPengisianKasKecil;
use App\Filament\Admin\Resources\PengisianKasKecils\Pages\ListPengisianKasKecils;
use App\Filament\Admin\Resources\PengisianKasKecils\Schemas\PengisianKasKecilForm;
use App\Filament\Admin\Resources\PengisianKasKecils\Tables\PengisianKasKecilsTable;
use App\Models\Coa;
use App\Models\PengisianKasKecil;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PengisianKasKecilResource extends Resource
{
    protected static ?string $model = PengisianKasKecil::class;

    protected static ?string $navigationLabel = 'Pengisian Kas Kecil';
    protected static ?string $modelLabel = 'Pengisian Kas Kecil';
    protected static ?string $pluralModelLabel = 'Pengisian Kas Kecil';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'no_transaksi';

    public static function form(Schema $schema): Schema
    {
        return PengisianKasKecilForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengisianKasKecilsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengisianKasKecils::route('/'),
            'create' => CreatePengisianKasKecil::route('/create'),
            'edit' => EditPengisianKasKecil::route('/{record}/edit'),
        ];
    }

    public static function normalizeRupiah(mixed $state): int
    {
        if ($state === null || $state === '') {
            return 0;
        }

        $numeric = preg_replace('/[^0-9]/', '', (string) $state);

        return (int) ($numeric ?: 0);
    }

    public static function formatRupiah(mixed $state): string
    {
        return number_format(self::normalizeRupiah($state), 0, ',', '.');
    }

    public static function kasKecilCode(): ?string
    {
        return Coa::query()->where('nama_akun', 'Kas Kecil')->value('kode_akun');
    }

    public static function kasPengeluaranInstitusiCode(): ?string
    {
        return Coa::query()->where('nama_akun', 'Kas Pengeluaran Institusi')->value('kode_akun');
    }

    public static function coaDisplayName(string $name): string
    {
        return self::coaExists($name) ? $name : 'Tambahkan akun COA';
    }

    public static function coaExists(string $name): bool
    {
        return Coa::query()->where('nama_akun', $name)->exists();
    }
}
