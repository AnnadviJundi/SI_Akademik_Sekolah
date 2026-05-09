<?php

namespace App\Services;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class PembayaranReviewService
{
    public function verify(Pembayaran $pembayaran): Pembayaran
    {
        return $this->updateStatus($pembayaran, 'Lunas');
    }

    public function reject(Pembayaran $pembayaran): Pembayaran
    {
        return $this->updateStatus($pembayaran, 'Ditolak');
    }

    private function updateStatus(Pembayaran $pembayaran, string $status): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran, $status): Pembayaran {
            $pembayaran->update([
                'status' => $status,
            ]);

            return $pembayaran->fresh(['siswa', 'semester', 'verifiedByUser', 'latestBukti']);
        });
    }
}
