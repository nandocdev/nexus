<?php
namespace App\Controllers;
// app/Controllers/UserController.php
use Nexus\Modules\Http\Controller;
use Nexus\Modules\Http\Request;
use Nexus\Modules\Validation\Validator;
use App\Models\User;

class UserController extends Controller {
    public function index(Request $request) {
        $users = User::all();
        $this->view('users.index', ['users' => $users, 'layout' => 'layouts/app']);
    }

    public function create(Request $request) {
        $this->view('users.create', ['layout' => 'layouts/app']);
    }

    public function show(Request $request, $id) {
        $user = User::find($id);
        if (!$user) {
            abort(404, 'User not found');
        }
        $this->view('users.show', ['user' => $user, 'layout' => 'layouts/app']);
    }

    public function edit(Request $request, $id) {
        $user = User::find($id);
        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }
        $this->view('users.edit', ['user' => $user, 'layout' => 'layouts/app']);
    }

    public function store(Request $request) {
        $data = $request->all();

        // Usar el helper validate que lanza ValidationException automáticamente
        validate($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        User::create($data);
        $this->redirect('/users');
    }

    public function update(Request $request, $id) {
        $data = $request->all();
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

    public function delete(Request $request, $id) {
        $user = User::find($id);
        if ($user) {
            $user->delete($id);
        }
        $this->redirect('/users');
    }
}