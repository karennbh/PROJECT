<?php

namespace App\Filament\Admin\Resources\PendapatanHibahs;

use App\Filament\Admin\Resources\PendapatanHibahs\Pages\CreatePendapatanHibah;
use App\Filament\Admin\Resources\PendapatanHibahs\Pages\EditPendapatanHibah;
use App\Filament\Admin\Resources\PendapatanHibahs\Pages\ListPendapatanHibahs;
use App\Filament\Admin\Resources\PendapatanHibahs\Schemas\PendapatanHibahForm;
use App\Filament\Admin\Resources\PendapatanHibahs\Tables\PendapatanHibahsTable;
use App\Models\Coa;
use App\Models\PendapatanHibah;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PendapatanHibahResource extends Resource
{
    protected static ?string $model = PendapatanHibah::class;

    protected static ?string $navigationLabel = 'Pendapatan Hibah';
    protected static ?string $modelLabel = 'Pendapatan Hibah';
    protected static ?string $pluralModelLabel = 'Pendapatan Hibah';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';
    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'no_hibah';

    public static function form(Schema $schema): Schema
    {
        return PendapatanHibahForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PendapatanHibahsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPendapatanHibahs::route('/'),
            'create' => CreatePendapatanHibah::route('/create'),
            'edit' => EditPendapatanHibah::route('/{record}/edit'),
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

    public static function coaDisplayName(string $name): string
    {
        return self::coaExists($name) ? $name : 'Tambahkan akun COA';
    }

    public static function coaExists(string $name): bool
    {
        return Coa::query()->where('nama_akun', $name)->exists();
    }

    public static function coaCode(string $name): ?string
    {
        return Coa::query()->where('nama_akun', $name)->value('kode_akun');
    }
}
