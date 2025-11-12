<?php
namespace App\Models;

use Nexus\Modules\Database\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'user_id'];
    protected $hidden = [];

    /**
     * Get the user that owns the post
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the post
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the tags for the post (many-to-many)
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    /**
     * Scope for published posts
     */
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    /**
     * Scope for posts by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessor for title
     */
    public function getTitleAttribute($value)
    {
        return ucwords($value);
    }

    /**
     * Mutator for content
     */
    public function setContentAttribute($value)
    {
        return trim($value);
    }
}