<x-filament-widgets::widget>
    <div class="blue-barang-kantor-widget">
        @once
            <style>
                .blue-barang-kantor-widget .bhp-nested-table {
                    border: 1px solid #d7eefc;
                    border-radius: 18px;
                    overflow: hidden;
                    background: #dff4ff;
                    box-shadow: none;
                }

                .blue-barang-kantor-widget .bhp-nested-table__heading {
                    padding: 1.25rem 1.5rem;
                    background: #dff4ff;
                    border-bottom: 1px solid #d7eefc;
                    color: #0b4f7c;
                    font-size: 1.15rem;
                    font-weight: 700;
                }

                .blue-barang-kantor-widget .bhp-nested-table__heading::before {
                    content: none;
                }

                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-header,
                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-header-ctn,
                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-header-toolbar,
                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-toolbar {
                    background: #dff4ff !important;
                }

                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta {
                    background: #dff4ff !important;
                }

                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-header-heading {
                    display: none !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .bhp-nested-table {
                    background: #102236;
                    border-color: rgba(103, 232, 249, 0.28);
                    box-shadow: none;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .bhp-nested-table__heading {
                    background: #123041;
                    border-bottom-color: rgba(103, 232, 249, 0.18);
                    color: #effbff;
                }
            </style>
        @endonce

        <x-filament::section :heading="$this->headingLabel()">
            <div class="space-y-6">
                @livewire(\App\Filament\Admin\Resources\BarangKantors\Widgets\BhpAtkOperasionalTable::class)
                @livewire(\App\Filament\Admin\Resources\BarangKantors\Widgets\BhpInventarisKantorTable::class)
            </div>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
