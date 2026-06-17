<x-filament-widgets::widget>
    <div class="blue-barang-kantor-widget">
        @once
            <style>
                .blue-barang-kantor-widget .fi-section {
                    border: 1px solid #7dd3fc !important;
                    border-radius: 24px !important;
                    overflow: hidden !important;
                    background: #ffffff !important;
                    box-shadow: 0 8px 24px rgba(2, 132, 199, 0.10) !important;
                }

                .blue-barang-kantor-widget .fi-section-header {
                    background: #ffffff !important;
                    border-bottom: 1px solid #d7eefc !important;
                    padding: 1.25rem 1.5rem !important;
                }

                .blue-barang-kantor-widget .fi-section-header-heading {
                    color: #0b4f7c !important;
                    font-weight: 700 !important;
                    font-size: 1.15rem !important;
                }

                .blue-barang-kantor-widget .fi-ta-header,
                .blue-barang-kantor-widget .fi-ta-header-ctn,
                .blue-barang-kantor-widget .fi-ta-header-toolbar,
                .blue-barang-kantor-widget .fi-ta-toolbar,
                .blue-barang-kantor-widget .fi-section-content {
                    background: #dff4ff !important;
                }

                .blue-barang-kantor-widget table thead tr {
                    background: linear-gradient(90deg, #30b8f4 0%, #1fa7ee 100%) !important;
                }

                .blue-barang-kantor-widget table thead th {
                    background: transparent !important;
                    color: #ffffff !important;
                    font-weight: 700 !important;
                    border-bottom: none !important;
                    padding-top: 14px !important;
                    padding-bottom: 14px !important;
                }

                .blue-barang-kantor-widget table thead th * {
                    color: #ffffff !important;
                }

                .blue-barang-kantor-widget table tbody tr:nth-child(even) {
                    background: #f4fbff !important;
                }

                .blue-barang-kantor-widget table tbody tr:hover {
                    background: #e9f8ff !important;
                }

                .blue-barang-kantor-widget table tbody td {
                    border-bottom: 1px solid #d9eef9 !important;
                    vertical-align: middle !important;
                }

                .blue-barang-kantor-widget .fi-input-wrp {
                    border: 1px solid #8bd8ff !important;
                    border-radius: 14px !important;
                    background: #ffffff !important;
                }

                .blue-barang-kantor-widget .fi-badge {
                    border-radius: 999px !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-section {
                    background: #102236 !important;
                    border-color: rgba(103, 232, 249, 0.28) !important;
                    box-shadow: 0 8px 24px rgba(2, 12, 27, 0.32) !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-section-header {
                    background: #123041 !important;
                    border-bottom-color: rgba(103, 232, 249, 0.16) !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-section-header-heading,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-ta-header-heading {
                    color: #effbff !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-ta-header,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-ta-header-ctn,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-ta-header-toolbar,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-ta-toolbar,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-section-content {
                    background: #123041 !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr:nth-child(odd) {
                    background: #141f37 !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr:nth-child(even) {
                    background: #182338 !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr:hover,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr[aria-selected="true"],
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr[data-selected="true"],
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr.fi-active {
                    background: #18324a !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody td,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody td *,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody td span,
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody td div {
                    color: #eefbff !important;
                    border-bottom-color: rgba(103, 232, 249, 0.14) !important;
                    background: transparent !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr .fi-badge {
                    color: #dffcff !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-input-wrp {
                    background: #112332 !important;
                    border-color: rgba(125, 211, 252, 0.26) !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody tr[class*="bg-"],
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget table tbody td[class*="bg-"],
                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .fi-ta-record {
                    background: transparent !important;
                }
            </style>
        @endonce

        <x-filament::section :heading="$this->headingLabel()">
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
