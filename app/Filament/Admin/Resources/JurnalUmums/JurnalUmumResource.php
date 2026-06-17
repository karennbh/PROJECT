<?php

namespace App\Filament\Admin\Resources\JurnalUmums;

use App\Filament\Admin\Resources\JurnalUmums\Pages\ListJurnalUmums;
use App\Filament\Admin\Resources\JurnalUmums\Schemas\JurnalUmumForm;
use App\Filament\Admin\Resources\JurnalUmums\Schemas\JurnalUmumInfolist;
use App\Filament\Admin\Resources\JurnalUmums\Tables\JurnalUmumsTable;
use App\Models\JurnalUmum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class JurnalUmumResource extends Resource
{
    protected static ?string $model = JurnalUmum::class;
    protected static ?string $navigationLabel = 'Jurnal Umum';
    protected static ?string $modelLabel = 'Jurnal Umum';
    protected static ?string $pluralModelLabel = 'Jurnal Umum';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $recordTitleAttribute = 'reff_perolehan_barang';

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return JurnalUmumForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JurnalUmumsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JurnalUmumInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function isGloballySearchable(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJurnalUmums::route('/'),
        ];
    }
}

