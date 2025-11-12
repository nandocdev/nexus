<?php
namespace App\Controllers;

use Nexus\Modules\Http\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Tag;

class ApiController extends Controller {
    public function index() {
        $users = User::all();
        $this->json($users);
    }

    public function store() {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $user = User::create($data);

        if ($user) {
            $this->json(['success' => true, 'user' => User::find($user)], 201);
        } else {
            $this->json(['error' => 'Failed to create user'], 500);
        }
    }

    /**
     * Get all users with their posts (advanced ORM demo)
     */
    public function users() {
        try {
            // Usar query builder avanzado con relaciones
            $users = User::query()
                ->with(['posts' => function($query) {
                    $query->where('published', true)->limit(5);
                }])
                ->active()
                ->get();

            $this->json([
                'success' => true,
                'data' => $users,
                'count' => count($users)
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user with relationships
     */
    public function user($id) {
        try {
            $user = User::find($id);

            if (!$user) {
                abort(404, 'User not found');
            }

            // Cargar relaciones ansiosamente
            $user->load(['posts.comments', 'posts.tags']);

            $this->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Nexus\Modules\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new post
     */
    public function createPost() {
        try {
            validate($_POST, [
                'title' => 'required|min:5|max:255',
                'content' => 'required|min:10',
                'user_id' => 'required|numeric',
                'tags' => 'array'
            ]);

            // Crear el post
            $post = Post::create([
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'user_id' => $_POST['user_id'],
                'published' => true
            ]);

            $this->json([
                'success' => true,
                'data' => $post,
                'message' => 'Post created successfully'
            ], 201);
        } catch (\Nexus\Modules\Exception\ValidationException $e) {
            $this->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Raw SQL query example
     */
    public function stats() {
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

            $this->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complex query with joins
     */
    public function popularPosts() {
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

            $this->json([
                'success' => true,
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}