<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\PerolehanBarangs\Pages\CreatePerolehanBarang;
use App\Models\KategoriAsetTetap;
use App\Models\PerolehanBarang;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PerolehanBarangValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private KategoriAsetTetap $kategoriAset;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'user_group' => 'admin',
        ]);

        $this->kategoriAset = KategoriAsetTetap::query()->create([
            'nama_kategori_aset' => 'Kelompok 1',
            'umur_ekonomis' => 5,
            'tarif_penyusutan' => 20,
            'keterangan' => 'Dipakai untuk tes validasi perolehan.',
        ]);

        $this->actingAs($this->admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        app()->setLocale('id');
    }

    public function test_perolehan_barang_menolak_field_wajib_yang_kosong(): void
    {
        Livewire::test(CreatePerolehanBarang::class)
            ->fillForm([
                'sumber_perolehan' => PerolehanBarang::SUMBER_PEMBELIAN,
                'tanggal_pembelian' => null,
                'foto_nota' => null,
                'details' => [[
                    'kategori_barang' => 'aset',
                    'nama_barang' => '',
                    'jenis_aset' => null,
                    'kategori_aset_id' => null,
                    'jumlah_perolehan' => null,
                    'harga_satuan' => '',
                ]],
            ])
            ->call('create')
            ->assertHasFormErrors([
                'tanggal_pembelian' => 'required',
                'foto_nota' => 'required',
                'details.0.nama_barang' => 'required',
                'details.0.jenis_aset' => 'required',
                'details.0.kategori_aset_id' => 'required',
                'details.0.jumlah_perolehan' => 'required',
                'details.0.harga_satuan' => 'required',
            ]);
    }

    public function test_perolehan_barang_menolak_nilai_minus_satu(): void
    {
        Livewire::test(CreatePerolehanBarang::class)
            ->fillForm($this->validPembelianData([
                'details' => [[
                    'kategori_barang' => 'aset',
                    'nama_barang' => 'Laptop Validasi',
                    'jenis_aset' => 'inventaris_kantor',
                    'kategori_aset_id' => $this->kategoriAset->getKey(),
                    'umur_ekonomis' => 5,
                    'nilai_residu' => '0',
                    'satuan_perolehan' => 'Unit',
                    'jumlah_perolehan' => -1,
                    'harga_satuan' => '-1',
                ]],
                'diskon_total' => '-1',
            ]))
            ->call('create')
            ->assertHasFormErrors([
                'details.0.jumlah_perolehan',
                'details.0.harga_satuan',
                'diskon_total',
            ]);
    }

    public function test_pesan_validasi_required_perolehan_menggunakan_bahasa_indonesia(): void
    {
        $livewire = Livewire::test(CreatePerolehanBarang::class);
        $fields = $livewire->instance()->form->getFlatFields(withHidden: true, withAbsoluteKeys: true);

        $fotoNotaField = $this->findFieldByName($fields, 'foto_nota');
        $kategoriBarangField = $this->findFieldByName($fields, 'kategori_barang_input');

        $this->assertSame('Kolom foto nota wajib diupload.', $fotoNotaField->getValidationMessages()['required'] ?? null);
        $this->assertSame('Kolom kategori barang wajib dipilih.', $kategoriBarangField->getValidationMessages()['required'] ?? null);
    }

    public function test_pesan_validasi_required_hibah_menggunakan_bahasa_indonesia(): void
    {
        $livewire = Livewire::test(CreatePerolehanBarang::class)
            ->fillForm([
                'sumber_perolehan' => PerolehanBarang::SUMBER_HIBAH,
            ]);

        $fields = $livewire->instance()->form->getFlatFields(withHidden: true, withAbsoluteKeys: true);

        $buktiDokumenHibahField = $this->findFieldByName($fields, 'bukti_dokumen_hibah');
        $namaPemberiHibahField = $this->findFieldByName($fields, 'nama_pemberi_hibah');

        $this->assertSame('Kolom bukti dokumen hibah wajib diupload.', $buktiDokumenHibahField->getValidationMessages()['required'] ?? null);
        $this->assertSame('Kolom sumber hibah wajib diisi.', $namaPemberiHibahField->getValidationMessages()['required'] ?? null);

        $livewire->fillForm([
                'tanggal_pembelian' => '2026-04-20',
                'bukti_dokumen_hibah' => null,
                'nama_pemberi_hibah' => '',
                'details' => [[
                    'kategori_barang' => 'aset',
                    'nama_barang' => 'Barang Hibah',
                    'jenis_aset' => 'inventaris_kantor',
                    'kategori_aset_id' => $this->kategoriAset->getKey(),
                    'satuan_perolehan' => 'Unit',
                    'jumlah_perolehan' => -1,
                    'harga_perolehan' => '-1',
                ]],
            ])
            ->call('create')
            ->assertHasFormErrors([
                'bukti_dokumen_hibah' => 'required',
                'nama_pemberi_hibah' => 'required',
                'details.0.jumlah_perolehan',
                'details.0.harga_perolehan',
            ]);
    }

    public function test_form_perolehan_mematikan_validasi_bawaan_browser(): void
    {
        Livewire::test(CreatePerolehanBarang::class)
            ->assertSeeHtml('novalidate="novalidate"', false);
    }

    private function validPembelianData(array $overrides = []): array
    {
        $data = [
            'sumber_perolehan' => PerolehanBarang::SUMBER_PEMBELIAN,
            'tanggal_pembelian' => '2026-04-20',
            'foto_nota' => UploadedFile::fake()->create('nota.jpg', 100, 'image/jpeg'),
            'keterangan' => 'Tes validasi perolehan barang',
            'details' => [[
                'kategori_barang' => 'aset',
                'nama_barang' => 'Laptop Validasi',
                'jenis_aset' => 'inventaris_kantor',
                'kategori_aset_id' => $this->kategoriAset->getKey(),
                'umur_ekonomis' => 5,
                'nilai_residu' => '0',
                'satuan_perolehan' => 'Unit',
                'jumlah_perolehan' => 1,
                'harga_satuan' => '1000000',
                'harga_perolehan' => '1000000',
            ]],
            'diskon_total' => '0',
            'biaya_lainnya_total' => '0',
        ];

        return array_replace_recursive($data, $overrides);
    }

    /**
     * @param  array<string, Field>  $fields
     */
    private function findFieldByName(array $fields, string $name): Field
    {
        foreach ($fields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        $this->fail("Field dengan nama [{$name}] tidak ditemukan pada schema.");
    }
}
