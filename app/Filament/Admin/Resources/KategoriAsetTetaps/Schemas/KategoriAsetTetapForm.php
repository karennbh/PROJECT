<?php

namespace App\Filament\Admin\Resources\KategoriAsetTetaps\Schemas;

use App\Models\KategoriAsetTetap;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class KategoriAsetTetapForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes([
                'novalidate' => true,
            ])
            ->components([

            TextInput::make('id_kategori_aset')
                ->label('ID Kategori Aset Tetap')
                ->readOnly()
                ->default(function () {
                    $count = KategoriAsetTetap::count() + 1;
                    return 'KTGRAST' . str_pad($count, 4, '0', STR_PAD_LEFT);
                })
                ->dehydrated(true)
                ->visible(fn ($context) => $context === 'create'),

            Select::make('nama_kategori_aset')
                ->label('Kelompok Aset Tetap')
                ->options([
                    'Kelompok 1' => 'Kelompok 1',
                    'Kelompok 2' => 'Kelompok 2',
                    'Kelompok 3' => 'Kelompok 3',
                    'Kelompok 4' => 'Kelompok 4',
                ])
                ->markAsRequired()
                ->rule('required')
                ->rule(Rule::in(['Kelompok 1', 'Kelompok 2', 'Kelompok 3', 'Kelompok 4']))
                ->rule(fn ($record) =>
                    Rule::unique(KategoriAsetTetap::class, 'nama_kategori_aset')
                        ->ignore($record?->id_kategori_aset, 'id_kategori_aset')
                )
                ->validationMessages([
                    'required' => 'Kelompok aset tetap wajib dipilih.',
                    'in' => 'Kelompok aset tetap harus dipilih dari opsi yang tersedia.',
                    'unique'   => 'Kelompok aset sudah terdaftar.',
                ])
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set): void {
                    if ($state === 'Kelompok 1') {
                        $set('keterangan', 'Sarana Pendidikan Laboratorium dan Inventaris Kantor.');
                    }
                }),

            TextInput::make('umur_ekonomis')
                ->label('Umur Ekonomis (Tahun)')
                ->numeric()
                ->live()
                ->rule('required')
                ->rule('min:5')
                ->rule(fn ($record) =>
                    Rule::unique(KategoriAsetTetap::class, 'umur_ekonomis')
                        ->ignore($record?->id_kategori_aset, 'id_kategori_aset')
                )
                ->dehydrated()
                ->afterStateHydrated(fn ($state, callable $set) => $set('tarif_penyusutan', self::hitungTarifPenyusutan($state)))
                ->afterStateUpdated(fn ($state, callable $set) => $set('tarif_penyusutan', self::hitungTarifPenyusutan($state)))
                ->validationMessages([
                    'required' => 'Umur ekonomis wajib diisi.',
                    'min' => 'Umur ekonomis tidak boleh kurang dari Kelompok 1, minimal 5 tahun.',
                    'numeric' => 'Umur ekonomis harus berupa angka.',
                    'unique' => 'Umur ekonomis tersebut sudah digunakan oleh kelompok aset lain.',
                ]),

            TextInput::make('tarif_penyusutan')
                ->label('Tarif Penyusutan (%)')
                ->numeric()
                ->suffix('%')
                ->rule('required')
                ->rule('min:0')
                ->disabled()
                ->dehydrated()
                ->validationMessages([
                    'required' => 'Tarif penyusutan wajib diisi.',
                    'min' => 'Tarif penyusutan tidak boleh kurang dari 0.',
                    'numeric' => 'Tarif penyusutan harus berupa angka.',
                ]),

            Textarea::make('keterangan')
                ->label('Keterangan')
                ->rule('max:255')
                ->validationMessages([
                    'max' => 'Keterangan maksimal 255 karakter.',
                ])
                ->maxLength(255)
                ->rows(4)
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    private static function hitungTarifPenyusutan(mixed $umurEkonomis): ?float
    {
        $umur = (int) $umurEkonomis;

        return $umur > 0 ? round(100 / $umur, 2) : null;
    }
}
