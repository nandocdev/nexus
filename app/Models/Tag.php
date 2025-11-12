<?php
namespace App\Models;

use Nexus\Modules\Database\Model;

class Tag extends Model
{
    protected $table = 'tags';
    protected $fillable = ['name', 'slug'];
    protected $hidden = [];

    /**
     * Get the posts for the tag (many-to-many)
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tags');
    }

    /**
     * Mutator for slug
     */
    public function setNameAttribute($value)
    {
        $this->attributes['slug'] = strtolower(str_replace(' ', '-', $value));
        return $value;
    }
}