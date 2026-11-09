<?php

namespace App\Policies;

use App\Models\JobPost;
use App\Models\User;

class JobPostPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'employer';
    }

    public function update(User $user, JobPost $jobPost): bool
    {
        return $user->role === 'employer'
        && $user->id === $jobPost->user_id;
    }

    public function delete(User $user, JobPost $jobPost): bool
    {
        return $user->role === 'employer'
        && $user->id === $jobPost->user_id;
    }
}