<x-filament::page>
    <div class="mb-4 w-full text-right">
        <x-filament::button
            tag="a"
            href="{{ \App\Filament\Admin\Resources\BarangKantors\BarangKantorResource::getUrl('index') }}"
            color="primary"
            icon="heroicon-o-arrow-left"
        >
            Kembali
        </x-filament::button>
    </div>

    <x-filament::section>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="text-lg font-bold">Scan atau Cari Barang</div>

                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model.defer="barcode"
                        placeholder="Scan QR, lalu cari dengan kode barang atau nama barang"
                    />
                </x-filament::input.wrapper>

                <div class="flex gap-2 flex-wrap">
                    <x-filament::button type="button" color="primary" onclick="startScanPreferBack()">
                        Mulai Scan
                    </x-filament::button>

                    <x-filament::button type="button" color="danger" onclick="stopScan()">
                        Stop
                    </x-filament::button>

                    <x-filament::button type="button" color="gray" wire:click="submit">
                        Cari Manual
                    </x-filament::button>
                </div>

                <div class="text-sm text-gray-500">
                    Scan QR barang atau cari manual dengan kode dan nama barang. Sistem akan langsung membuka halaman detail barang yang selalu update dari database.
                </div>

                <div id="scan-status" class="text-sm text-gray-500"></div>
            </div>

            <div>
                <div class="text-lg font-bold mb-3">Kamera</div>
                <div id="reader" style="width:100%; max-width:520px;"></div>
            </div>
        </div>
    </x-filament::section>

    <script src="https://unpkg.com/html5-qrcode" defer></script>

    <script>
        let html5QrCode = null;
        let cameraList = [];
        let currentCameraIndex = 0;

        function setStatus(msg) {
            const el = document.getElementById('scan-status');
            if (el) el.innerText = msg;
        }

        function pickBackCameraIndex(cameras) {
            // coba pilih kamera belakang dari label (kalau label tersedia)
            const keywords = ['back', 'rear', 'environment', 'belakang'];
            const idx = cameras.findIndex(c => {
                const label = (c.label || '').toLowerCase();
                return keywords.some(k => label.includes(k));
            });

            return idx >= 0 ? idx : 0; // fallback kamera pertama
        }

        async function initCameras() {
            cameraList = await Html5Qrcode.getCameras();
            if (!cameraList || cameraList.length === 0) {
                setStatus('Kamera tidak ditemukan.');
                return false;
            }
            return true;
        }

        async function startWithCameraIndex(index) {
            setStatus('Membuka kamera...');

            // stop dulu kalau sedang jalan
            await stopScan(true);

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            currentCameraIndex = Math.max(0, Math.min(index, cameraList.length - 1));
            const cameraId = cameraList[currentCameraIndex].id;

            const config = { fps: 12, qrbox: { width: 240, height: 240 } };

            try {
                setStatus('Scan aktif. Arahkan kamera ke QR barang.');

                await html5QrCode.start(
                    cameraId,
                    config,
                    (decodedText) => {
                        // isi state Livewire, lalu submit
                        @this.set('barcode', decodedText);
                        @this.call('submit');
                    },
                    () => {}
                );
            } catch (err) {
                setStatus('Gagal membuka kamera: ' + err);
            }
        }

        async function startScanPreferBack() {
            if (!window.Html5Qrcode) {
                setStatus('Library scanner belum siap, coba lagi...');
                return;
            }
            const ok = await initCameras();
            if (!ok) return;

            const backIdx = pickBackCameraIndex(cameraList);
            await startWithCameraIndex(backIdx);
        }

        async function stopScan(silent = false) {
            if (!html5QrCode) return;

            try {
                const isScanning = html5QrCode.getState && html5QrCode.getState() === Html5QrcodeScannerState.SCANNING;
                if (isScanning) {
                    await html5QrCode.stop();
                } else {
                    // kalau state API beda versi, tetap coba stop
                    await html5QrCode.stop().catch(() => {});
                }

                html5QrCode.clear();
                // jangan set null supaya bisa start cepat lagi
                if (!silent) setStatus('Scan dihentikan.');
            } catch (e) {
                if (!silent) setStatus('Scan dihentikan.');
            }
        }
    </script>
</x-filament::page>
