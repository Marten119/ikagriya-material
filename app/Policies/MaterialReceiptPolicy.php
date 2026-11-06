<?php

namespace App\Policies;

use App\Models\MaterialReceipt;
use App\Models\User;

class MaterialReceiptPolicy
{
    // Admin boleh mengubah semua transaksi; staff hanya transaksi yang ia catat sendiri.
    public function update(User $user, MaterialReceipt $receipt): bool
    {
        return $user->isAdmin() || $receipt->created_by === $user->id;
    }

    // Hapus hanya admin.
    public function delete(User $user, MaterialReceipt $receipt): bool
    {
        return $user->isAdmin();
    }
}
