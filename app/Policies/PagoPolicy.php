<?php

namespace App\Policies;

use App\Models\Pago;
use App\Models\User;

class PagoPolicy
{
    /**
     * Anular un pago es una operación financiera sensible.
     */
    public function anular(User $user, Pago $pago): bool
    {
        return in_array($user->role, ['admin', 'gerente']);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pago $pago): bool
    {
        return true;
    }
}