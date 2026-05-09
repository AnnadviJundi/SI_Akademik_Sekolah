@php
    $record = $getRecord();
    $proof = $record?->latestBukti;
    $proofPath = $proof?->file_path;
    $proofType = strtolower((string) ($proof?->file_type ?? ''));
    $isImage = in_array($proofType, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
@endphp

@if (! $proofPath)
    <div>-</div>
@else
    <div style="display: flex; flex-direction: column; gap: 12px;">
        @if ($isImage)
            <img
                src="{{ asset('storage/' . $proofPath) }}"
                alt="Bukti pembayaran"
                style="max-width: 320px; border-radius: 12px; border: 1px solid #d1d5db;"
            >
        @else
            <div style="display: inline-flex; width: fit-content; border-radius: 9999px; background: #e5e7eb; padding: 4px 10px; font-size: 12px;">
                File {{ strtoupper($proofType) }}
            </div>
        @endif

        <div>
            <a
                href="{{ asset('storage/' . $proofPath) }}"
                download
                style="display: inline-flex; align-items: center; border-radius: 10px; background: #111827; color: #fff; padding: 8px 14px; text-decoration: none;"
            >
                Download Bukti
            </a>
        </div>
    </div>
@endif
