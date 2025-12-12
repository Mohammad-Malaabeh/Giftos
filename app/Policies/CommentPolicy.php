<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('comments.view');
    }

    public function view(User $user, Comment $comment): bool
    {
        // Users can view their own comments or if they have permission
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->hasPermission('comments.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('comments.create');
    }

    public function update(User $user, Comment $comment): bool
    {
        // Users can update their own comments or if they have permission
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->hasPermission('comments.update');
    }

    public function delete(User $user, Comment $comment): bool
    {
        // Users can delete their own comments or if they have permission
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->hasPermission('comments.delete');
    }

    public function approve(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comments.approve');
    }

    public function moderate(User $user): bool
    {
        return $user->hasPermission('comments.moderate');
    }

    public function reply(User $user, Comment $comment): bool
    {
        // Users can reply to comments if they can create comments
        return $this->create($user);
    }

    public function bulkModerate(User $user): bool
    {
        return $user->hasPermission('comments.bulk');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('comments.manage');
    }
}
