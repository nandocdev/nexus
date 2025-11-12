<?php
namespace App\Controllers;

use Nexus\Modules\Http\Controller;
use App\Models\User;

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
}