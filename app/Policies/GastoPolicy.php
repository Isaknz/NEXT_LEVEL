<?php

namespace App\Policies;

use App\Models\Gasto;
use App\Models\User;

class GastoPolicy
{
    /**
     * Anular un gasto es una operación financiera sensible.
     */
    public function anular(User $user, Gasto $gasto): bool
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

    public function update(User $user, Gasto $gasto): bool
    {
        return true;
    }

    public function view(User $user, Gasto $gasto): bool
    {
        return true;
    }
}