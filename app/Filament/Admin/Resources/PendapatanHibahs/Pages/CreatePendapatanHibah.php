<?php

namespace App\Filament\Admin\Resources\PendapatanHibahs\Pages;

use App\Filament\Admin\Resources\PendapatanHibahs\PendapatanHibahResource;
use App\Models\Coa;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePendapatanHibah extends CreateRecord
{
    protected static string $resource = PendapatanHibahResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->validateRequiredCoa($data);
    }

    private function validateRequiredCoa(array $data): array
    {
        $data['akun_bank_hibah'] = $data['akun_bank_hibah']
            ?: Coa::query()->where('nama_akun', 'Kas Bank Hibah')->value('kode_akun');
        $data['akun_pendapatan_hibah'] = $data['akun_pendapatan_hibah']
            ?: Coa::query()->where('nama_akun', 'Pendapatan Donasi Hibah')->value('kode_akun');

        $errors = [];

        if (blank($data['akun_bank_hibah'])) {
            $errors['data.akun_bank_hibah'] = 'Akun Kas Bank Hibah belum tersedia di COA. Silakan buat akun Kas Bank Hibah terlebih dahulu.';
        }

        if (blank($data['akun_pendapatan_hibah'])) {
            $errors['data.akun_pendapatan_hibah'] = 'Akun Pendapatan Donasi Hibah belum tersedia di COA. Silakan buat akun Pendapatan Donasi Hibah terlebih dahulu.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }
}
