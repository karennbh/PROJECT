<?php

namespace App\Filament\Admin\Resources\Penyusutans;

use App\Filament\Admin\Resources\Penyusutans\Pages\ListPenyusutans;
use App\Filament\Admin\Resources\Penyusutans\Pages\PenyusutanKartuPage;
use App\Filament\Admin\Resources\Penyusutans\Schemas\PenyusutanForm;
use App\Filament\Admin\Resources\Penyusutans\Tables\PenyusutansTable;
// use App\Models\BarangKantor;
use App\Models\PenyusutanAsetTetap;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PenyusutanResource extends Resource
{
    protected static ?string $model = PenyusutanAsetTetap::class;
    // protected static ?string $model = BarangKantor::class;
    protected static ?string $navigationLabel = 'Penyusutan Aset Tetap';
    protected static ?string $pluralModelLabel = 'Penyusutan Aset Tetap';
    protected static ?string $modelLabel = 'Penyusutan Aset Tetap';
    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-down';

    protected static ?string $recordTitleAttribute = 'id_penyusutan';

    public static function form(Schema $schema): Schema
    {
        return PenyusutanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenyusutansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPenyusutans::route('/'),
            // route kartu: butuh {record} supaya id aset kebawa di URL
            'kartu' => PenyusutanKartuPage::route('/{record}/kartu'),
        ];
    }
}
