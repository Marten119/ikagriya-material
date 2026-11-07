<?php

namespace App\Policies;

use App\Models\MaterialReceipt;
use App\Models\User;

class MaterialReceiptPolicy
{
    // Admin: semua transaksi. Staff: hanya transaksi buatannya sendiri DAN
    // hanya jika cluster transaksi itu masih ditetapkan untuk akunnya.
    public function update(User $user, MaterialReceipt $receipt): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $receipt->created_by === $user->id
            && in_array($receipt->cluster_id, $user->assignedClusterIds(), true);
    }

    // Hapus hanya admin.
    public function delete(User $user, MaterialReceipt $receipt): bool
    {
        return $user->isAdmin();
    }
}
