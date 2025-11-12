<?php
namespace App\Controllers;
// app/Controllers/UserController.php
use Nexus\Modules\Http\Controller;
use Nexus\Modules\Validation\Validator;
use App\Models\User;

class UserController extends Controller {
    public function index() {
        $users = User::all();
        $this->view('users.index', ['users' => $users, 'layout' => 'layouts.app']);
    }
    
    public function create() {
        $this->view('users.create', ['layout' => 'layouts.app']);
    }
    
    public function show($id) {
        $user = User::find($id);
        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }
        $this->view('users.show', ['user' => $user, 'layout' => 'layouts.app']);
    }
    
    public function edit($id) {
        $user = User::find($id);
        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }
        $this->view('users.edit', ['user' => $user, 'layout' => 'layouts.app']);
    }
    
    public function store() {
        $data = $_POST;
        
        $validator = new Validator($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        if (!$validator->validate()) {
            $this->view('users.create', ['errors' => $validator->errors(), 'old' => $data, 'layout' => 'layouts.app']);
            return;
        }
        
        User::create($data);
        
        $this->redirect('/users');
    }
    
    public function update($id) {
        $data = $_POST;
        $user = User::find($id);
        
        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }
        
        $validator = new Validator($data, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email'
        ]);
        
        if (!$validator->validate()) {
            $this->view('users.edit', ['user' => $user, 'errors' => $validator->errors(), 'old' => $data, 'layout' => 'layouts.app']);
            return;
        }
        
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