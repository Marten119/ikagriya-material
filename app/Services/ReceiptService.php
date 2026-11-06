<?php

namespace App\Services;

use App\Models\MaterialReceipt;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ReceiptService
{
    private const FIELDS = [
        'transaction_date', 'cluster_id', 'material_id', 'volume', 'unit_id',
        'usage_per_unit', 'usage_m2', 'notes',
    ];

    /**
     * Simpan transaksi baru. Mengembalikan [transaksi, apakahBaruDibuat].
     * Token form yang sama tidak pernah membuat transaksi kedua.
     */
    public function create(array $data, User $user): array
    {
        $payload = Arr::only($data, self::FIELDS);
        $token = $data['submit_token'];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                $receipt = DB::transaction(function () use ($payload, $token, $user) {
                    $receipt = MaterialReceipt::create($payload + [
                        'transaction_number' => $this->nextNumber(),
                        'submit_token' => $token,
                        'created_by' => $user->id,
                    ]);
                    ActivityLogger::log('create', 'material_receipt', $receipt->id, null, $this->snapshot($receipt->refresh()));

                    return $receipt;
                });

                return [$receipt, true];
            } catch (UniqueConstraintViolationException $e) {
                $existing = MaterialReceipt::where('submit_token', $token)->first();
                if ($existing) {
                    return [$existing, false]; // kirim ganda: transaksi sudah ada
                }
                // selain itu: nomor transaksi bentrok dengan input bersamaan, ulangi
            }
        }

        throw new \RuntimeException('Gagal membuat nomor transaksi yang unik.');
    }

    /** Mengembalikan true jika ada data yang berubah. */
    public function update(MaterialReceipt $receipt, array $data): bool
    {
        return DB::transaction(function () use ($receipt, $data) {
            $old = $this->snapshot($receipt);
            $receipt->update(Arr::only($data, self::FIELDS));
            $receipt->refresh();
            $new = $this->snapshot($receipt);

            if ($old === $new) {
                return false;
            }

            ActivityLogger::log('update', 'material_receipt', $receipt->id, $old, $new);

            return true;
        });
    }

    public function delete(MaterialReceipt $receipt): void
    {
        DB::transaction(function () use ($receipt) {
            $old = $this->snapshot($receipt);
            $receipt->delete();
            ActivityLogger::log('delete', 'material_receipt', $receipt->id, $old, null);
        });
    }

    /** Format: MM-YYYYMMDD-NNNN, urutan di-reset tiap hari pencatatan. */
    private function nextNumber(): string
    {
        $prefix = 'MM-'.now()->format('Ymd').'-';

        $last = MaterialReceipt::where('transaction_number', 'like', $prefix.'%')
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function snapshot(MaterialReceipt $r): array
    {
        return [
            'transaction_number' => $r->transaction_number,
            'transaction_date' => $r->transaction_date->toDateString(),
            'cluster_id' => $r->cluster_id,
            'material_id' => $r->material_id,
            'volume' => (string) $r->volume,
            'unit_id' => $r->unit_id,
            'usage_per_unit' => (string) $r->usage_per_unit,
            'usage_m2' => (string) $r->usage_m2,
            'notes' => $r->notes,
        ];
    }
}
