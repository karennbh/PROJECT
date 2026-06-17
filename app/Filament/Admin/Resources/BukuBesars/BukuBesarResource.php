<?php

namespace App\Filament\Admin\Resources\BukuBesars;

use App\Filament\Admin\Resources\BukuBesars\Pages\ListBukuBesars;
use App\Filament\Admin\Resources\BukuBesars\Tables\BukuBesarsTable;
use App\Models\BukuBesar;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BukuBesarResource extends Resource
{
    protected static ?string $model = BukuBesar::class;
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static ?string $modelLabel = 'Buku Besar';
    protected static ?string $pluralModelLabel = 'Buku Besar';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'tanggal';
    public static function isGloballySearchable(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return BukuBesarsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBukuBesars::route('/'),
        ];
    }
}
