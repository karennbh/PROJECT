<?php

namespace App\Filament\Admin\Resources\PeminjamanBarangs;

use App\Filament\Admin\Resources\PeminjamanBarangs\Pages\ListPeminjamanBarangs;
use App\Filament\Admin\Resources\PeminjamanBarangs\Schemas\PeminjamanBarangInfolist;
use App\Filament\Admin\Resources\PeminjamanBarangs\Tables\PeminjamanBarangsTable;
use App\Models\PeminjamanBarang;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PeminjamanBarangResource extends Resource
{
    protected static ?string $model = PeminjamanBarang::class;

    protected static ?string $navigationLabel = 'Peminjaman Barang Kantor';
    protected static ?string $modelLabel = 'Peminjaman Barang Kantor';
    protected static ?string $pluralModelLabel = 'Peminjaman Barang Kantor';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengajuan';

    protected static ?string $recordTitleAttribute = 'judul_peminjaman';

    public static function table(Table $table): Table
    {
        return PeminjamanBarangsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PeminjamanBarangInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPeminjamanBarangs::route('/'),
        ];
    }
}
