<?php

namespace App\Filament\Admin\Resources\PengisianKasKecils\Schemas;

use App\Filament\Admin\Resources\PengisianKasKecils\PengisianKasKecilResource;
use App\Models\PengisianKasKecil;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class PengisianKasKecilForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['novalidate' => true])
            ->components([
                TextInput::make('no_transaksi')
                    ->label('No Transaksi')
                    ->readOnly()
                    ->dehydrated()
                    ->default(fn () => PengisianKasKecil::generateNoTransaksi(now()->toDateString())),

                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->minDate(today()->startOfYear())
                    ->maxDate(today())
                    ->required()
                    ->rules(['date', 'after_or_equal:' . today()->startOfYear()->toDateString(), 'before_or_equal:today'])
                    ->validationMessages([
                        'required' => 'Tanggal wajib diisi.',
                        'after_or_equal' => 'Tanggal hanya boleh diisi pada tahun berjalan.',
                        'before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, string $operation): void {
                        if ($operation === 'create') {
                            $set('no_transaksi', PengisianKasKecil::generateNoTransaksi($state ?: now()->toDateString()));
                        }
                    }),

                TextInput::make('akun_kas_kecil_label')
                    ->label('Akun Kas Kecil')
                    ->formatStateUsing(fn () => PengisianKasKecilResource::coaDisplayName('Kas Kecil'))
                    ->afterStateHydrated(fn (callable $set) => $set('akun_kas_kecil_label', PengisianKasKecilResource::coaDisplayName('Kas Kecil')))
                    ->helperText(fn () => PengisianKasKecilResource::coaExists('Kas Kecil') ? null : 'Tambahkan akun COA terlebih dahulu.')
                    ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! PengisianKasKecilResource::coaExists('Kas Kecil')) {
                            $fail('Tambahkan akun COA Kas Kecil terlebih dahulu.');
                        }
                    })
                    ->readOnly()
                    ->dehydrated(false),

                Hidden::make('akun_kas_kecil')
                    ->default(fn () => PengisianKasKecilResource::kasKecilCode())
                    ->dehydrateStateUsing(fn ($state) => $state ?: PengisianKasKecilResource::kasKecilCode())
                    ->required()
                    ->validationMessages([
                        'required' => 'Tambahkan akun COA Kas Kecil terlebih dahulu.',
                    ])
                    ->dehydrated(),

                TextInput::make('akun_sumber_dana_label')
                    ->label('Akun Sumber Dana')
                    ->formatStateUsing(fn () => PengisianKasKecilResource::coaDisplayName('Kas Pengeluaran Institusi'))
                    ->afterStateHydrated(fn (callable $set) => $set('akun_sumber_dana_label', PengisianKasKecilResource::coaDisplayName('Kas Pengeluaran Institusi')))
                    ->helperText(fn () => PengisianKasKecilResource::coaExists('Kas Pengeluaran Institusi') ? null : 'Tambahkan akun COA terlebih dahulu.')
                    ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! PengisianKasKecilResource::coaExists('Kas Pengeluaran Institusi')) {
                            $fail('Tambahkan akun COA Kas Pengeluaran Institusi terlebih dahulu.');
                        }
                    })
                    ->readOnly()
                    ->dehydrated(false),

                Hidden::make('akun_sumber_dana')
                    ->default(fn () => PengisianKasKecilResource::kasPengeluaranInstitusiCode())
                    ->dehydrateStateUsing(fn ($state) => $state ?: PengisianKasKecilResource::kasPengeluaranInstitusiCode())
                    ->required()
                    ->validationMessages([
                        'required' => 'Tambahkan akun COA Kas Pengeluaran Institusi terlebih dahulu.',
                    ])
                    ->dehydrated(),

                TextInput::make('nominal')
                    ->label('Nominal')
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => PengisianKasKecilResource::normalizeRupiah($state))
                    ->afterStateHydrated(fn ($state, callable $set) => $set('nominal', PengisianKasKecilResource::formatRupiah($state)))
                    ->extraInputAttributes(['style' => 'text-align: right'])
                    ->rule(function () {
                        return function (string $attribute, mixed $value, \Closure $fail): void {
                            $nominal = PengisianKasKecilResource::normalizeRupiah($value);

                            if (str_contains((string) $value, '-') || $nominal <= 0) {
                                $fail('Nominal harus lebih dari 0.');
                            }
                        };
                    })
                    ->validationMessages([
                        'required' => 'Nominal wajib diisi.',
                    ]),

                FileUpload::make('bukti')
                    ->label('Bukti')
                    ->disk('public')
                    ->directory('bukti-pengisian-kas-kecil')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'])
                    ->columnSpanFull(),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->default('Pengisian kas kecil dari hasil pengajuan ke pusat')
                    ->rows(3)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
