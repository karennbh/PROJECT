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

    public function test_pembelian_barang_menerima_harga_11_digit(): void
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

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas(PengajuanPembelianBarang::class, [
            'nama_barang' => 'Laptop Kantor',
            'jumlah' => 1,
            'perkiraan_harga' => '99999999999.00',
            'sub_total' => '99999999999.00',
        ]);
    }

    public function test_pembelian_barang_menerima_subtotal_di_atas_batas_int(): void
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

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas(PengajuanPembelianBarang::class, [
            'nama_barang' => 'Laptop Kantor',
            'jumlah' => 2,
            'perkiraan_harga' => '1500000000.00',
            'sub_total' => '3000000000.00',
        ]);
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
