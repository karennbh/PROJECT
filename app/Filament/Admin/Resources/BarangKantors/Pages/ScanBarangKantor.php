<?php

namespace App\Filament\Admin\Resources\BarangKantors\Pages;

use App\Filament\Admin\Resources\BarangKantors\BarangKantorResource;
use App\Models\BarangKantor;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class ScanBarangKantor extends Page
{
    protected static string $resource = BarangKantorResource::class;

    protected string $view = 'filament.admin.resources.barang-kantors.pages.scan-barang-kantor';

    public ?string $barcode = null;

    public function submit(): void
    {
        $keyword = trim((string) $this->barcode);

        if ($keyword === '') {
            Notification::make()
                ->title('Input pencarian kosong')
                ->danger()
                ->send();
            return;
        }

        if (filter_var($keyword, FILTER_VALIDATE_URL)) {
            $path = parse_url($keyword, PHP_URL_PATH) ?: '';
            $keyword = trim((string) basename($path));
        }

        $barang = BarangKantor::query()
            ->where(function (Builder $query) use ($keyword): void {
                $query->where('barcode', $keyword)
                    ->orWhere('kode_barang', $keyword)
                    ->orWhere('nama_barang', $keyword)
                    ->orWhere('nama_barang', 'like', '%' . $keyword . '%');
            })
            ->orderByRaw('CASE WHEN barcode = ? THEN 1 WHEN kode_barang = ? THEN 2 WHEN nama_barang = ? THEN 3 ELSE 4 END', [
                $keyword,
                $keyword,
                $keyword,
            ])
            ->orderBy('nama_barang')
            ->first();

        if (! $barang) {
            Notification::make()
                ->title("Barang tidak ditemukan: {$keyword}")
                ->danger()
                ->send();
            return;
        }

        $this->redirect(BarangKantorResource::getUrl('view', [
            'record' => $barang,
        ]));
    }
}
