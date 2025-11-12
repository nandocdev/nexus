<?php
namespace App\Models;
// app/Models/User.php
use Nexus\Modules\Database\Model;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    /**
     * Get the posts for the user
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the comments for the user
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for users by email domain
     */
    public function scopeByEmailDomain($query, $domain)
    {
        return $query->where('email', 'LIKE', "%@{$domain}");
    }

    public function setPasswordAttribute($value) {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->attributes['password']);
    }
}