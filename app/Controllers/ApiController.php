<?php
namespace App\Controllers;

use Nexus\Modules\Http\Controller;
use Nexus\Modules\Http\ApiResource;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Tag;

class ApiController extends Controller {
    public function index(Request $request) {
        $users = User::all();
        return ApiResource::collection($users, 'Users retrieved successfully');
    }

    public function store(Request $request) {
        $data = $request->isJson() ? $request->json() : $request->all();

        $user = User::create($data);

        if ($user) {
            return ApiResource::resource($user, 'User created successfully', 201);
        } else {
            return ApiResource::error('Failed to create user', 500);
        }
    }

    /**
     * Get all users with their posts (advanced ORM demo)
     */
    public function users(Request $request) {
        try {
            // Usar query builder avanzado con relaciones
            $users = User::query()
                ->with(['posts' => function ($query) {
                    $query->where('published', true)->limit(5);
                }])
                ->active()
                ->get();

            return ApiResource::collection($users, 'Users with posts retrieved successfully');
        } catch (\Exception $e) {
            return ApiResource::error('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user with relationships
     */
    public function user(Request $request, $id) {
        try {
            $user = User::find($id);

            if (!$user) {
                return ApiResource::notFound('User');
            }

            // Cargar relaciones ansiosamente
            $user->load(['posts.comments', 'posts.tags']);

            return ApiResource::resource($user, 'User retrieved successfully');
        } catch (\Nexus\Modules\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return ApiResource::error('Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new post
     */
    public function createPost(Request $request) {
        try {
            validate($request->all(), [
                'title' => 'required|min:5|max:255',
                'content' => 'required|min:10',
                'user_id' => 'required|numeric',
                'tags' => 'array'
            ]);

            // Crear el post
            $post = Post::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'user_id' => $request->input('user_id'),
                'published' => true
            ]);

            return ApiResource::resource($post, 'Post created successfully', 201);
        } catch (\Nexus\Modules\Exception\ValidationException $e) {
            return ApiResource::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResource::error('Failed to create post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Raw SQL query example
     */
    public function stats(Request $request) {
        try {
            // Consulta SQL cruda para estadísticas
            $stats = User::raw("
                SELECT
                    COUNT(DISTINCT u.id) as total_users,
                    COUNT(DISTINCT p.id) as total_posts,
                    COUNT(DISTINCT c.id) as total_comments
                FROM users u
                LEFT JOIN posts p ON u.id = p.user_id
                LEFT JOIN comments c ON u.id = c.user_id
            ")->fetch(PDO::FETCH_ASSOC);

            return ApiResource::resource($stats, 'Statistics retrieved successfully');
        } catch (\Exception $e) {
            return ApiResource::error('Failed to retrieve statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Complex query with joins
     */
    public function popularPosts(Request $request) {
        try {
            // Posts populares con información del autor y conteo de comentarios
            $posts = Post::query()
                ->select('posts.*', 'users.name as author_name', 'COUNT(comments.id) as comments_count')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->leftJoin('comments', 'posts.id', '=', 'comments.post_id')
                ->where('posts.published', true)
                ->groupBy('posts.id', 'users.name')
                ->orderBy('comments_count', 'DESC')
                ->limit(10)
                ->get();

            return ApiResource::collection($posts, 'Popular posts retrieved successfully');
        } catch (\Exception $e) {
            return ApiResource::error('Failed to retrieve popular posts: ' . $e->getMessage(), 500);
        }
    }
}