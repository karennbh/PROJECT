<?php

namespace App\Filament\Admin\Resources\PendapatanHibahs\Schemas;

use App\Filament\Admin\Resources\PendapatanHibahs\PendapatanHibahResource;
use App\Models\PendapatanHibah;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class PendapatanHibahForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['novalidate' => true])
            ->components([
                TextInput::make('no_hibah')
                    ->label('Nomor Hibah')
                    ->readOnly()
                    ->dehydrated()
                    ->default(fn () => PendapatanHibah::generateNoHibah()),

                DatePicker::make('tanggal_hibah')
                    ->label('Tanggal Hibah')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->minDate(today()->startOfYear())
                    ->maxDate(today())
                    ->required()
                    ->rules(['date', 'after_or_equal:' . today()->startOfYear()->toDateString(), 'before_or_equal:today'])
                    ->validationMessages([
                        'required' => 'Tanggal hibah wajib diisi.',
                        'after_or_equal' => 'Tanggal hibah hanya boleh diisi pada tahun berjalan.',
                        'before_or_equal' => 'Tanggal hibah tidak boleh melebihi hari ini.',
                    ]),

                TextInput::make('sumber_hibah')
                    ->label('Sumber Hibah')
                    ->required()
                    ->rule('min:3')
                    ->rule('max:50')
                    ->validationMessages([
                        'required' => 'Sumber hibah wajib diisi.',
                        'min' => 'Sumber hibah minimal 3 karakter.',
                        'max' => 'Sumber hibah maksimal 50 karakter.',
                    ]),

                TextInput::make('jenis_hibah_label')
                    ->label('Jenis Hibah')
                    ->formatStateUsing(fn () => 'Hibah Uang')
                    ->afterStateHydrated(fn (callable $set) => $set('jenis_hibah_label', 'Hibah Uang'))
                    ->readOnly()
                    ->dehydrated(false),

                Hidden::make('jenis_hibah')
                    ->default('hibah_uang')
                    ->dehydrated(),

                TextInput::make('akun_bank_hibah_label')
                    ->label('Akun Bank Hibah')
                    ->formatStateUsing(fn () => PendapatanHibahResource::coaDisplayName('Kas Bank Hibah'))
                    ->afterStateHydrated(fn (callable $set) => $set('akun_bank_hibah_label', PendapatanHibahResource::coaDisplayName('Kas Bank Hibah')))
                    ->helperText(fn () => PendapatanHibahResource::coaExists('Kas Bank Hibah') ? null : 'Tambahkan akun COA terlebih dahulu.')
                    ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! PendapatanHibahResource::coaExists('Kas Bank Hibah')) {
                            $fail('Tambahkan akun COA Kas Bank Hibah terlebih dahulu.');
                        }
                    })
                    ->readOnly()
                    ->dehydrated(false),

                Hidden::make('akun_bank_hibah')
                    ->default(fn () => PendapatanHibahResource::coaCode('Kas Bank Hibah'))
                    ->dehydrateStateUsing(fn ($state) => $state ?: PendapatanHibahResource::coaCode('Kas Bank Hibah'))
                    ->required()
                    ->validationMessages([
                        'required' => 'Tambahkan akun COA Kas Bank Hibah terlebih dahulu.',
                    ])
                    ->dehydrated(),

                TextInput::make('akun_pendapatan_hibah_label')
                    ->label('Akun Pendapatan Hibah')
                    ->formatStateUsing(fn () => PendapatanHibahResource::coaDisplayName('Pendapatan Donasi Hibah'))
                    ->afterStateHydrated(fn (callable $set) => $set('akun_pendapatan_hibah_label', PendapatanHibahResource::coaDisplayName('Pendapatan Donasi Hibah')))
                    ->helperText(fn () => PendapatanHibahResource::coaExists('Pendapatan Donasi Hibah') ? null : 'Tambahkan akun COA terlebih dahulu.')
                    ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! PendapatanHibahResource::coaExists('Pendapatan Donasi Hibah')) {
                            $fail('Tambahkan akun COA Pendapatan Donasi Hibah terlebih dahulu.');
                        }
                    })
                    ->readOnly()
                    ->dehydrated(false),

                Hidden::make('akun_pendapatan_hibah')
                    ->default(fn () => PendapatanHibahResource::coaCode('Pendapatan Donasi Hibah'))
                    ->dehydrateStateUsing(fn ($state) => $state ?: PendapatanHibahResource::coaCode('Pendapatan Donasi Hibah'))
                    ->required()
                    ->validationMessages([
                        'required' => 'Tambahkan akun COA Pendapatan Donasi Hibah terlebih dahulu.',
                    ])
                    ->dehydrated(),

                TextInput::make('nilai_hibah')
                    ->label('Nilai Hibah')
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => PendapatanHibahResource::normalizeRupiah($state))
                    ->afterStateHydrated(fn ($state, callable $set) => $set('nilai_hibah', PendapatanHibahResource::formatRupiah($state)))
                    ->extraInputAttributes(['style' => 'text-align: right'])
                    ->rule(function () {
                        return function (string $attribute, mixed $value, \Closure $fail): void {
                            $nominal = PendapatanHibahResource::normalizeRupiah($value);
                            if (str_contains((string) $value, '-') || $nominal <= 0) {
                                $fail('Nilai hibah harus lebih dari 0.');
                            } elseif ($nominal < 100000) {
                                $fail('Nilai hibah minimal Rp 100.000.');
                            }
                        };
                    })
                    ->validationMessages([
                        'required' => 'Nilai hibah wajib diisi.',
                    ]),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
