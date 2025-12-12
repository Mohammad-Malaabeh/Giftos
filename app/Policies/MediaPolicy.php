<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Media;
use Illuminate\Auth\Access\Response;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('media.view');
    }

    public function view(User $user, Media $media): bool
    {
        // Users can view their own media or if they have permission
        if ($media->mediable_id === $user->id && $media->mediable_type === User::class) {
            return true;
        }

        return $user->hasPermission('media.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('media.upload');
    }

    public function update(User $user, Media $media): bool
    {
        // Users can update their own media or if they have permission
        if ($media->mediable_id === $user->id && $media->mediable_type === User::class) {
            return true;
        }

        return $user->hasPermission('media.update');
    }

    public function delete(User $user, Media $media): bool
    {
        // Users can delete their own media or if they have permission
        if ($media->mediable_id === $user->id && $media->mediable_type === User::class) {
            return true;
        }

        return $user->hasPermission('media.delete');
    }

    public function download(User $user, Media $media): bool
    {
        // Users can download their own media or if they have permission
        if ($media->mediable_id === $user->id && $media->mediable_type === User::class) {
            return true;
        }

        return $user->hasPermission('media.download');
    }

    public function bulkUpload(User $user): bool
    {
        return $user->hasPermission('media.bulk');
    }

    public function bulkDelete(User $user): bool
    {
        return $user->hasPermission('media.bulk');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('media.manage');
    }
}
