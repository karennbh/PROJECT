<?php

namespace App\Filament\Admin\Resources\PengajuanPembelianBarangs;

use App\Filament\Admin\Resources\PengajuanPembelianBarangs\Pages\ListPengajuanPembelianBarangs;
use App\Filament\Admin\Resources\PengajuanPembelianBarangs\Schemas\PengajuanPembelianBarangInfolist;
use App\Filament\Admin\Resources\PengajuanPembelianBarangs\Tables\PengajuanPembelianBarangsTable;
use App\Models\PengajuanPembelianBarang;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PengajuanPembelianBarangResource extends Resource
{
    protected static ?string $model = PengajuanPembelianBarang::class;

    protected static ?string $navigationLabel = 'Pembelian Barang Kantor';
    protected static ?string $modelLabel = 'Pembelian Barang Kantor';
    protected static ?string $pluralModelLabel = 'Pembelian Barang Kantor';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengajuan';
    protected static ?string $recordTitleAttribute = 'id_pembelian_barang_kantor';

    public static function table(Table $table): Table
    {
        return PengajuanPembelianBarangsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PengajuanPembelianBarangInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
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
            'index' => ListPengajuanPembelianBarangs::route('/'),
        ];
    }
}
