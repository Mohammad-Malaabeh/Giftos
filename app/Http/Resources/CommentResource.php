<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'excerpt' => str_limit(strip_tags($this->content), 100),
            'likes_count' => $this->likes_count,
            'is_liked' => $this->when(auth()->check(), $this->is_liked),
            'replies_count' => $this->when($this->relationLoaded('replies'), $this->replies->count()),
            'is_approved' => $this->is_approved,
            'approved_at' => $this->when($this->approved_at, $this->approved_at),
            
            // User information
            'user' => $this->when($this->relationLoaded('user'), function () {
                return new UserResource($this->user);
            }),
            'user_id' => $this->user_id,
            
            // Parent/Reply information
            'is_reply' => $this->isReply(),
            'is_top_level' => $this->isTopLevel(),
            'parent_id' => $this->when($this->parent_id, $this->parent_id),
            'parent' => $this->when($this->relationLoaded('parent'), function () {
                return new CommentResource($this->parent);
            }),
            'replies' => $this->when($this->relationLoaded('replies'), function () {
                return CommentResource::collection($this->replies);
            }),
            
            // Approval information
            'approved_by' => $this->when($this->relationLoaded('approvedBy'), function () {
                return new UserResource($this->approvedBy);
            }),
            
            // Polymorphic relationship
            'commentable_type' => $this->commentable_type,
            'commentable_id' => $this->commentable_id,
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'formatted_created_at' => $this->formatted_created_at,
            
            // Permissions
            'can_edit' => $this->when(auth()->check(), function () {
                return $this->canBeEditedBy(auth()->user());
            }),
            'can_delete' => $this->when(auth()->check(), function () {
                return $this->canBeDeletedBy(auth()->user());
            }),
            
            // API URLs
            'api_url' => route('api.comments.show', $this->id),
            'like_url' => route('api.comments.like', $this->id),
            'approve_url' => route('api.comments.approve', $this->id),
        ];
    }
}
