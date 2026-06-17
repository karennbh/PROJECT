<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable(),

                TextColumn::make('user_group')
                    ->label('User Group')
                    ->badge() 
                    ->color(fn (string $state) => match ($state) {
                        'admin'   => 'primary', // biru
                        'anggota' => 'success', // hijau
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Lihat ' . $record->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->fillForm(fn ($record): array => [
                        'name' => $record->name ?: '-',
                        'username' => $record->username ?: '-',
                        'user_group' => ucfirst((string) ($record->user_group ?: '-')),
                        'created_at' => $record->created_at?->format('d/m/Y H:i') ?: '-',
                    ])
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')->label('Nama'),
                                TextInput::make('username')->label('Username'),
                                TextInput::make('user_group')->label('User Group'),
                                // TextInput::make('created_at')->label('Tanggal Dibuat'),
                            ]),
                    ])
                    ->disabledForm(),
                DeleteAction::make(),
            ])
            ->recordAction('lihat')
            ->recordUrl(null)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Tambahkan pengguna agar akses aplikasi dapat dikelola.');
    }
}
