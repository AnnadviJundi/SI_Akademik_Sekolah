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
            alt="Foto siswa {{ $record?->nama }}"
            style="width: min(100%, 360px); max-width: 360px; border-radius: 18px; border: 1px solid #d1d5db; object-fit: cover;"
        >

        <div>
            <a
                href="{{ asset('storage/' . $photoPath) }}"
                download
                style="display: inline-flex; align-items: center; border-radius: 10px; background: #111827; color: #fff; padding: 8px 14px; text-decoration: none;"
            >
                Download Foto
            </a>
        </div>
    </div>
@endif
