<?php
namespace App\Controllers;
// app/Controllers/AuthController.php
use Nexus\Modules\Http\Controller;
use Nexus\Modules\Auth\Auth;
use Nexus\Modules\Validation\Validator;

class AuthController extends Controller {
    public function loginForm() {
        $this->view('auth/login', ['layout' => 'layouts/auth']);
    }
    
    public function login() {
        $data = $_POST;
        
        $validator = new Validator($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!$validator->validate()) {
            $this->view('auth/login', ['errors' => $validator->errors(), 'old' => $data, 'layout' => 'layouts/auth']);
            return;
        }
        
        if (Auth::attempt($data)) {
            $this->redirect('/users');
        } else {
            $this->view('auth/login', ['error' => 'Invalid credentials', 'old' => $data, 'layout' => 'layouts/auth']);
        }
    }
    
    public function logout() {
        Auth::logout();
        $this->redirect('/login');
    }
}