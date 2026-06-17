<?php

namespace App\Filament\Admin\Resources\Penyusutans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use App\Models\PenyusutanAsetTetap;
use App\Models\BarangKantor;

class PenyusutanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('id_penyusutan')
                ->label('ID Penyusutan')
                ->default(function () {
                    $lastId = PenyusutanAsetTetap::query()
                        ->where('id_penyusutan', 'like', PenyusutanAsetTetap::ID_PREFIX . '-%')
                        ->orderByDesc('id_penyusutan')
                        ->value('id_penyusutan');

                    return PenyusutanAsetTetap::formatIdNumber(PenyusutanAsetTetap::extractNumericPart($lastId) + 1);
                })
                ->readOnly()
                ->dehydrated(false),

            // Pilih aset yang belum pernah disusutkan
            Select::make('kode_barang')
                ->label('Kode Aset')
                ->options(function ($get, $record) {
                    $dipakai = PenyusutanAsetTetap::pluck('kode_barang')->toArray();

                    // Kalau edit, tetap ijinkan record-nya sendiri
                    if ($record) {
                        $dipakai = array_diff($dipakai, [$record->kode_barang]);
                    }

                    return BarangKantor::where('kategori_barang', 'aset')
                        ->where('status_penggunaan', BarangKantor::STATUS_SIAP_DIGUNAKAN)
                        ->whereNotNull('tanggal_diterima')
                        ->whereNotIn('kode_barang', $dipakai)
                        ->orderBy('kode_barang')
                        ->pluck('kode_barang', 'kode_barang');
                })
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $aset = BarangKantor::find($state);

                    if (! $aset) {
                        $set('tanggal_diterima', null);
                        $set('harga_perolehan', null);
                        $set('nilai_residu', null);
                        $set('umur_ekonomis_tahun', null);
                        $set('beban_penyusutan_bulanan', null);
                        return;
                    }

                    $set('tanggal_diterima', optional($aset->tanggal_diterima)->format('Y-m-d'));
                    $set('status_penggunaan', $aset->status_penggunaan);
                    $set('harga_perolehan', $aset->harga_perolehan);
                    $set('nilai_residu', $aset->nilai_residu);
                    $set('umur_ekonomis_tahun', $aset->umur_ekonomis);

                    // Hitung beban per bulan
                    $totalBulan = max((int) $aset->umur_ekonomis * 12, 1);
                    $beban = ($aset->harga_perolehan - (int) $aset->nilai_residu) / $totalBulan;

                    $set('beban_penyusutan_bulanan', (int) round($beban));
                }),

            DatePicker::make('tanggal_diterima')
                ->label('Tanggal Diterima')
                ->required()
                ->native(false)
                ->readOnly(),

            TextInput::make('status_penggunaan')
                ->hidden()
                ->dehydrated(),

            // ini hanya untuk state sementara, nilai final sudah diset dari model
            TextInput::make('harga_perolehan')
                ->hidden()
                ->dehydrated(false),

            TextInput::make('nilai_residu')
                ->hidden()
                ->dehydrated(false),

            TextInput::make('umur_ekonomis_tahun')
                ->hidden()
                ->dehydrated(false),

            TextInput::make('beban_penyusutan_bulanan')
                ->hidden()
                ->dehydrated(false),
        ]);
    }
}
