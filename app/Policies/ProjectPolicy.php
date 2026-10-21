<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $this->canManage($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->canManage($user, $project);
    }

    protected function canManage(User $user, Project $project): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($project->owner_id === $user->id) {
            return true;
        }

        $role = $project->members()->where('users.id', $user->id)->value('project_user.role');

        return $role === 'manager';
    }
}
