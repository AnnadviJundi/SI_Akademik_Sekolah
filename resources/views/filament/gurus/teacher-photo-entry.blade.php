@php
    $record = $getRecord();
    $photoPath = $record?->foto_path;
@endphp

@if (! $photoPath)
    <div>-</div>
@else
    <div style="display: flex; flex-direction: column; gap: 12px;">
        <img
            src="{{ asset('storage/' . $photoPath) }}"
            alt="Foto guru {{ $record?->nama }}"
            style="max-width: 220px; border-radius: 16px; border: 1px solid #d1d5db; object-fit: cover;"
        >
    </div>
@endif
