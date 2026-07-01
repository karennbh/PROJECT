<?php

namespace Tests\Feature;

use App\Models\PengajuanPembelianBarang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PembelianBarangValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->actingAs(User::factory()->create([
            'user_group' => 'anggota',
        ]));
    }

    public function test_pembelian_barang_menolak_harga_melebihi_batas_int(): void
    {
        $response = $this->post(route('pembelian.store'), $this->validPayload([
            'items' => [[
                'nama_barang' => 'Laptop Kantor',
                'jumlah' => 1,
                'kategori_barang' => 'aset',
                'perkiraan_harga' => 99999999999,
                'link_barang' => null,
            ]],
        ]));

        $response->assertSessionHasErrors('items.0.perkiraan_harga');
        $this->assertDatabaseCount(PengajuanPembelianBarang::class, 0);
    }

    public function test_pembelian_barang_menolak_subtotal_melebihi_batas_int(): void
    {
        $response = $this->post(route('pembelian.store'), $this->validPayload([
            'items' => [[
                'nama_barang' => 'Laptop Kantor',
                'jumlah' => 2,
                'kategori_barang' => 'aset',
                'perkiraan_harga' => 1500000000,
                'link_barang' => null,
            ]],
        ]));

        $response->assertSessionHasErrors('items.0.perkiraan_harga');
        $this->assertDatabaseCount(PengajuanPembelianBarang::class, 0);
    }

    private function validPayload(array $overrides = []): array
    {
        $payload = [
            'alasan' => 'Dibutuhkan untuk operasional kantor.',
            'bukti_pendukung' => UploadedFile::fake()->createWithContent(
                'bukti.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
            'items' => [[
                'nama_barang' => 'Laptop Kantor',
                'jumlah' => 1,
                'kategori_barang' => 'aset',
                'perkiraan_harga' => 1000000,
                'link_barang' => null,
            ]],
        ];

        return array_replace_recursive($payload, $overrides);
    }
}
