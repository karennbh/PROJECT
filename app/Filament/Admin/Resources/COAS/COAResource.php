<?php

namespace App\Filament\Admin\Resources\COAS;

use App\Filament\Admin\Resources\COAS\Pages\CreateCOA;
use App\Filament\Admin\Resources\COAS\Pages\EditCOA;
use App\Filament\Admin\Resources\COAS\Pages\ListCOAS;
use App\Filament\Admin\Resources\COAS\Schemas\COAForm;
use App\Filament\Admin\Resources\COAS\Tables\COASTable;
use App\Models\Coa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class COAResource extends Resource
{
    protected static ?string $model = Coa::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'COA';
    protected static ?string $pluralModelLabel = 'COA';
    protected static ?string $modelLabel = 'COA';
    protected static ?string $recordTitleAttribute = 'nama_akun';
    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return COAForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return COASTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCOAS::route('/'),
            'create' => CreateCoa::route('/create'),
            'edit' => EditCoa::route('/{record}/edit'),
        ];
    }
}
