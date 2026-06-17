<?php

namespace App\Filament\Admin\Resources\KategoriAsetTetaps;

use App\Filament\Admin\Resources\KategoriAsetTetaps\Pages\CreateKategoriAsetTetap;
use App\Filament\Admin\Resources\KategoriAsetTetaps\Pages\EditKategoriAsetTetap;
use App\Filament\Admin\Resources\KategoriAsetTetaps\Pages\ListKategoriAsetTetaps;
use App\Filament\Admin\Resources\KategoriAsetTetaps\Schemas\KategoriAsetTetapForm;
use App\Filament\Admin\Resources\KategoriAsetTetaps\Tables\KategoriAsetTetapsTable;
use App\Models\KategoriAsetTetap;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class KategoriAsetTetapResource extends Resource
{
    protected static ?string $model = KategoriAsetTetap::class;

    protected static ?string $navigationLabel = 'Kategori Aset Tetap';
    protected static ?string $modelLabel = 'Kategori Aset Tetap';
    protected static ?string $pluralModelLabel = 'Kategori Aset Tetap';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $recordTitleAttribute = 'nama_kategori_aset';

    public static function form(Schema $schema): Schema
    {
        return KategoriAsetTetapForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KategoriAsetTetapsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function canCreate(): bool
    {
        // Tombol 'Create' hanya muncul jika jumlah data kurang dari 4
        return KategoriAsetTetap::count() < 4;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKategoriAsetTetaps::route('/'),
            'create' => CreateKategoriAsetTetap::route('/create'),
            'edit' => EditKategoriAsetTetap::route('/{record}/edit'),
        ];
    }
}
