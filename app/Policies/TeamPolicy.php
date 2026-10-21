<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Team $team): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Team $team): bool
    {
        return $this->canManage($user, $team);
    }

    public function delete(User $user, Team $team): bool
    {
        return $this->canManage($user, $team);
    }

    protected function canManage(User $user, Team $team): bool
    {
        return $team->owner_id === $user->id || $user->role === UserRole::Admin;
    }
}
