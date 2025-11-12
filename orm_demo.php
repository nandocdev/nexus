<?php
require 'vendor/autoload.php';
require 'core/helpers.php';

use Nexus\Modules\Config\Config;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Tag;

// Cargar configuración
Config::load('database');

echo "=== Nexus ORM Advanced Features Demo ===\n\n";

// 1. Raw SQL Queries
echo "1. Raw SQL Queries:\n";
$users = User::raw("SELECT * FROM users WHERE id = ?", [1]);
echo "Raw query executed successfully\n\n";

// 2. Query Builder
echo "2. Query Builder:\n";
$allPosts = Post::all();
echo "Total posts: " . count($allPosts) . "\n\n";

// 3. Model Features
echo "3. Model Features:\n";
$user = User::find(1);
if ($user) {
    echo "User found: " . $user->name . "\n";
    echo "User email: " . $user->email . "\n";
} else {
    echo "User not found\n";
}

$post = Post::find(1);
if ($post) {
    echo "Post found: " . $post->title . "\n";
    echo "Post content preview: " . substr($post->content, 0, 50) . "...\n";
} else {
    echo "Post not found\n";
}
echo "\n";

// 4. Scopes
echo "4. Scopes:\n";
try {
    $publishedPosts = Post::published()->get();
    echo "Published posts: " . count($publishedPosts) . "\n\n";
} catch (Exception $e) {
    echo "Scopes not fully implemented yet\n\n";
}

// 5. Mutators and Accessors
echo "5. Mutators and Accessors:\n";
$user = User::find(1);
if ($user) {
    echo "User name: " . $user->name . "\n";
} else {
    echo "User not found\n";
}

$post = Post::find(1);
if ($post) {
    echo "Post title (accessor): " . $post->title . "\n\n";
} else {
    echo "Post not found\n\n";
}

// 6. Complex Queries
echo "6. Complex Queries:\n";
try {
    $recentPosts = Post::query()
        ->where('published', true)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    echo "Recent published posts: " . count($recentPosts) . "\n\n";
} catch (Exception $e) {
    echo "Complex queries: " . $e->getMessage() . "\n\n";
}

// 7. Raw Queries
echo "7. Raw Queries:\n";
try {
    $rawResult = User::raw("SELECT COUNT(*) as count FROM users");
    echo "Total users via raw query: " . ($rawResult->fetch(PDO::FETCH_OBJ)->count ?? 'unknown') . "\n\n";
} catch (Exception $e) {
    echo "Raw queries: " . $e->getMessage() . "\n\n";
}

// 8. Relationships
echo "8. Relationships:\n";
try {
    $user = User::find(1);
    if ($user) {
        echo "User: " . $user->name . "\n";
        $posts = $user->posts;
        echo "Posts count: " . count($posts) . "\n";
        if (count($posts) > 0) {
            echo "First post: " . $posts[0]->title . "\n";
        }
    }
} catch (Exception $e) {
    echo "Relationships error: " . $e->getMessage() . "\n";
}

try {
    $post = Post::find(1);
    if ($post) {
        echo "Post: " . $post->title . "\n";
        $postUser = $post->user;
        if ($postUser) {
            echo "Post author: " . $postUser->name . "\n";
        }
    }
} catch (Exception $e) {
    echo "BelongsTo relationship error: " . $e->getMessage() . "\n";
}

try {
    $user = User::find(1);
    if ($user) {
        $user->load('posts');
        echo "Loaded posts for user: " . count($user->posts) . "\n";
    }
} catch (Exception $e) {
    echo "Eager loading error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Demo Complete ===\n";