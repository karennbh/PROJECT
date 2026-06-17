<?php

namespace App\Filament\Admin\Resources\COAS\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\COA;
use Illuminate\Validation\Rule;

class COAForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes([
                'novalidate' => true,
            ])
            ->components([

            Select::make('header_akun')
                ->label('Header Akun')
                ->options([
                    'Harta' => 'Harta',
                    'Pendapatan' => 'Pendapatan',
                    'Beban' => 'Beban',
                ])
                ->markAsRequired()
                ->rule('required')
                ->rule(Rule::in(['Harta', 'Pendapatan', 'Beban']))
                ->reactive()
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('nama_akun', null);
                    $set('kode_akun', null);
                    $set('saldo', self::saldoNormalForHeader($state));
                })
                ->validationMessages([
                    'required' => 'Header akun wajib dipilih.',
                    'in' => 'Header akun harus dipilih dari opsi yang tersedia.',
                ]),

            Select::make('nama_akun')
                ->label('Nama Akun')
                ->markAsRequired()
                ->rule('required')
                ->rule(Rule::in([
                    'Kas Kecil',
                    'Kas Bank Hibah',
                    'Inventaris Kantor',
                    'Sarana Pendidikan Laboratorium',
                    'Kendaraan Bermotor',
                    'Akumulasi Penyusutan',
                    'Kas Pengeluaran Institusi',
                    'Beban ATK Operasional',
                    'BPP Inventaris Kantor',
                    'Beban Penyusutan',
                    'Penerimaan Hibah Barang',
                    'Pendapatan Donasi Hibah',
                ]))
                ->reactive()
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->options(function ($get) {
                    $options = match ($get('header_akun')) {
                        'Harta' => [
                            'Kas Kecil' => 'Kas Kecil',
                            'Kas Bank Hibah' => 'Kas Bank Hibah',
                            'Inventaris Kantor' => 'Inventaris Kantor',
                            'Sarana Pendidikan Laboratorium' => 'Sarana Pendidikan Laboratorium',
                            'Kendaraan Bermotor' => 'Kendaraan Bermotor',
                            'Akumulasi Penyusutan' => 'Akumulasi Penyusutan',
                            'Kas Pengeluaran Institusi' => 'Kas Pengeluaran Institusi',
                        ],
                        'Beban' => [
                            'Beban ATK Operasional' => 'Beban ATK Operasional',
                            'BPP Inventaris Kantor' => 'BPP Inventaris Kantor',
                            'Beban Penyusutan' => 'Beban Penyusutan',
                        ],
                        'Pendapatan' => [
                            'Penerimaan Hibah Barang' => 'Penerimaan Hibah Barang',
                            'Pendapatan Donasi Hibah' => 'Pendapatan Donasi Hibah',
                        ],
                        default => [],
                    };

                    asort($options, SORT_NATURAL | SORT_FLAG_CASE);

                    return $options;
                })
                ->disableOptionWhen(function (string $value, callable $get, ?COA $record = null): bool {
                    if (! $get('header_akun')) {
                        return false;
                    }

                    return COA::query()
                        ->where('header_akun', $get('header_akun'))
                        ->where('nama_akun', $value)
                        ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                        ->exists();
                })
                ->helperText('Akun yang sudah dibuat tidak bisa dipilih lagi.')

                // VALIDASI HANYA SAAT CREATE
                ->rule(function (string $operation, callable $get) {
                    if ($operation === 'edit') {
                        return null; // edit → lewati validasi
                    }

                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                        if (!$get('header_akun')) return;

                        $exists = COA::where('header_akun', $get('header_akun'))
                            ->where('nama_akun', $value)
                            ->exists();

                        if ($exists) {
                            $fail("Nama akun sudah digunakan pada header {$get('header_akun')}.");
                        }
                    };
                })
                ->validationMessages([
                    'required' => 'Nama akun wajib dipilih.',
                    'in' => 'Nama akun harus dipilih dari opsi yang tersedia.',
                ])

                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $map = [
                        'Harta|Kas Kecil' => ['1111202', 'Debit'],
                        'Harta|Kas Bank Hibah' => ['1112106', 'Debit'],
                        'Harta|Inventaris Kantor' => ['1234101', 'Debit'],
                        'Harta|Sarana Pendidikan Laboratorium' => ['1233101', 'Debit'],
                        'Harta|Kendaraan Bermotor' => ['1237101', 'Debit'],
                        'Harta|Akumulasi Penyusutan' => ['1264101', 'Kredit'],
                        'Harta|Kas Pengeluaran Institusi' => ['1112105', 'Debit'],
                        'Beban|Beban ATK Operasional' => ['5314102', 'Debit'],
                        'Beban|BPP Inventaris Kantor' => ['5611103', 'Debit'],
                        'Beban|Beban Penyusutan' => ['5611104', 'Debit'],
                        'Pendapatan|Penerimaan Hibah Barang' => ['4151102', 'Kredit'],
                        'Pendapatan|Pendapatan Donasi Hibah' => ['4950013', 'Kredit'],
                    ];

                    $key = ($get('header_akun') ?? '') . '|' . $state;

                    if (isset($map[$key])) {
                        [$kode, $saldo] = $map[$key];
                        $set('kode_akun', $kode);
                        $set('saldo', $saldo);
                    } else {
                        $set('saldo', self::saldoNormalForHeader($get('header_akun')));
                    }
                }),

            TextInput::make('kode_akun')
                ->label('Kode Akun')
                ->markAsRequired()
                ->rule('required')
                ->readOnly()
                ->disabled(fn (string $operation) => $operation === 'edit')
                ->validationMessages([
                    'required' => 'Kode akun wajib diisi.',
                ]),

            TextInput::make('saldo')
                ->label('Saldo Normal')
                ->afterStateHydrated(fn ($state, callable $set) => $set('saldo', filled($state) ? ucfirst((string) $state) : null))
                ->dehydrateStateUsing(fn ($state) => strtolower((string) $state))
                ->markAsRequired()
                ->rule('required')
                ->readOnly()
                ->dehydrated()
                ->validationMessages([
                    'required' => 'Saldo normal wajib dipilih.',
                ]),

            TextInput::make('jumlah_saldo')
                ->label('Jumlah Saldo (Rp)')
                ->prefix('Rp')
                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                ->markAsRequired()
                ->rule('required')
                ->rule(function () {
                    return function (string $attribute, $value, \Closure $fail) {
                        if (str_contains((string) $value, '-')) {
                            $fail('Jumlah saldo tidak boleh kurang dari 0.');

                            return;
                        }

                        $nominal = self::normalizeRupiah($value);

                        if ($nominal > 0 && $nominal < 1000) {
                            $fail('Jumlah saldo harus dalam nominal ribuan, minimal Rp 1.000.');
                        }
                    };
                })
                ->dehydrateStateUsing(fn ($state) => self::normalizeRupiah($state))
                ->afterStateHydrated(function ($state, callable $set) {
                    if (blank($state)) {
                        $set('jumlah_saldo', '0');

                        return;
                    }

                    $set('jumlah_saldo', self::formatRupiah($state));
                })
                ->default(0)
                ->extraInputAttributes(['style' => 'text-align: right'])
                ->validationMessages([
                    'required' => 'Jumlah saldo wajib diisi.',
                ]),
        ]);
    }

    private static function normalizeRupiah(mixed $state): int
    {
        if ($state === null || $state === '') {
            return 0;
        }

        $state = trim((string) $state);

        if (preg_match('/^\d+\.\d{1,2}$/', $state) === 1) {
            return (int) round((float) $state);
        }

        $numeric = preg_replace('/[^0-9]/', '', $state);

        return (int) ($numeric ?: 0);
    }

    private static function formatRupiah(mixed $state): string
    {
        return number_format(self::normalizeRupiah($state), 0, ',', '.');
    }

    private static function saldoNormalForHeader(?string $headerAkun): ?string
    {
        return match ($headerAkun) {
            'Harta', 'Beban' => 'Debit',
            'Pendapatan' => 'Kredit',
            default => null,
        };
    }
}
