<?php
namespace App\Models;

use Nexus\Modules\Database\Model;

class Comment extends Model
{
    protected $table = 'comments';
    protected $fillable = ['content', 'user_id', 'post_id'];
    protected $hidden = [];

    /**
     * Get the user that owns the comment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that owns the comment
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}