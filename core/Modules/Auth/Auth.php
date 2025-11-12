<?php
namespace Nexus\Modules\Auth;
// app/Core/Auth.php
class Auth {
    protected static $userModel = 'App\\Models\\User';
    
    public static function attempt($credentials) {
        $userModel = self::$userModel;
        $users = $userModel::where('email', $credentials['email']);
        $user = !empty($users) ? $users[0] : null;
        
        if ($user && password_verify($credentials['password'], $user->password)) {
            Session::set('user_id', $user->id);
            Session::set('user', $user);
            return true;
        }
        
        return false;
    }
    
    public static function check() {
        return Session::has('user_id');
    }
    
    public static function user() {
        return Session::get('user');
    }
    
    public static function logout() {
        Session::remove('user_id');
        Session::remove('user');
        Session::destroy();
    }
    
    public static function id() {
        return Session::get('user_id');
    }
}