@php
    $record = $getRecord();
@endphp

@if ($record?->barcode)
    <a
        href="{{ $record->barcode_target_url }}"
        target="_blank"
        rel="noopener noreferrer"
        style="display:flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none;color:inherit"
    >
        <img
            src="{{ $record->barcode_qr_image_url }}"
            alt="QR {{ $record->barcode }}"
            style="width:90px;height:90px;border-radius:12px;border:1px solid #dbeafe;padding:6px;background:#fff"
        />
        <div style="font-size:10px;text-align:center;line-height:1.35">
            {{ $record->kode_barang }}
        </div>
    </a>
@else
    <div>-</div>
@endif
