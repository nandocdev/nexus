<?php
namespace App\Controllers;
// app/Controllers/UserController.php
use Nexus\Modules\Http\Controller;
use Nexus\Modules\Validation\Validator;
use App\Models\User;

class UserController extends Controller {
    public function index() {
        $users = User::all();
        $this->view('users.index', ['users' => $users, 'layout' => 'layouts/app']);
    }

    public function create() {
        $this->view('users.create', ['layout' => 'layouts/app']);
    }

    public function show($id) {
        $user = User::find($id);
        if (!$user) {
            abort(404, 'User not found');
        }
        $this->view('users.show', ['user' => $user, 'layout' => 'layouts/app']);
    }

    public function edit($id) {
        $user = User::find($id);
        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }
        $this->view('users.edit', ['user' => $user, 'layout' => 'layouts/app']);
    }

    public function store() {
        $data = $_POST;

        // Usar el helper validate que lanza ValidationException automáticamente
        validate($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        User::create($data);
        $this->redirect('/users');
    }

    public function update($id) {
        $data = $_POST;
        $user = User::find($id);

        if (!$user) {
            abort(404, 'User not found');
        }

        validate($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email'
        ]);

        $user->update($id, $data);
        $this->redirect('/users/' . $id);
    }

    public function delete($id) {
        $user = User::find($id);
        if ($user) {
            $user->delete($id);
        }
        $this->redirect('/users');
    }
}