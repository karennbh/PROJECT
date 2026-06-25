<?php

namespace App\Filament\Admin\Resources\Pemakaianbhps;

use App\Filament\Admin\Resources\Pemakaianbhps\Pages\ListPemakaianbhps;
use App\Filament\Admin\Resources\Pemakaianbhps\Schemas\PemakaianbhpInfolist;
use App\Filament\Admin\Resources\Pemakaianbhps\Tables\PemakaianbhpsTable;
use App\Models\PemakaianBHP;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PemakaianbhpResource extends Resource
{
    protected static ?string $model = PemakaianBHP::class;
    protected static ?string $navigationLabel = 'Pemakaian BHP';
    protected static ?string $modelLabel = 'Pemakaian BHP';
    protected static ?string $pluralModelLabel = 'Pemakaian BHP';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-minus-circle';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengajuan';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'kode_barang';
    
    public static function table(Table $table): Table
    {
        return PemakaianbhpsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PemakaianbhpInfolist::configure($schema);
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
            'index' => ListPemakaianbhps::route('/'),
        ];
    }
}
