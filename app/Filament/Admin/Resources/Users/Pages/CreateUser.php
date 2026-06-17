<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected ?bool $hasUnsavedDataChangesAlert = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['remember_token'] = Str::random(60);

        return $data;
    }

    protected function hasUnsavedDataChangesAlert(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Tambah User';
    }

    public function getCreateButtonLabel(): string
    {
        return 'Tambah User';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah User';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Batal')
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');
    }
}
