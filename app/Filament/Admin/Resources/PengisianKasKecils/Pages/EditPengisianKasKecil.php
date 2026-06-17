<?php

namespace App\Filament\Admin\Resources\PengisianKasKecils\Pages;

use App\Filament\Admin\Resources\PengisianKasKecils\PengisianKasKecilResource;
use App\Models\Coa;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditPengisianKasKecil extends EditRecord
{
    protected static string $resource = PengisianKasKecilResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->validateRequiredCoa($data);
    }

    private function validateRequiredCoa(array $data): array
    {
        $data['akun_kas_kecil'] = $data['akun_kas_kecil']
            ?: Coa::query()->where('nama_akun', 'Kas Kecil')->value('kode_akun');
        $data['akun_sumber_dana'] = $data['akun_sumber_dana']
            ?: Coa::query()->where('nama_akun', 'Kas Pengeluaran Institusi')->value('kode_akun');

        $errors = [];

        if (blank($data['akun_kas_kecil'])) {
            $errors['data.akun_kas_kecil'] = 'Akun Kas Kecil belum tersedia di COA. Silakan buat akun Kas Kecil terlebih dahulu.';
        }

        if (blank($data['akun_sumber_dana'])) {
            $errors['data.akun_sumber_dana'] = 'Akun Kas Pengeluaran Institusi belum tersedia di COA. Silakan buat akun Kas Pengeluaran Institusi terlebih dahulu.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }
}
