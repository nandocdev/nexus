<?php
namespace App\Controllers;
// app/Controllers/HomeController.php
use Nexus\Modules\Http\Controller;

class HomeController extends Controller {
    public function index() {
        $this->view('home', ['title' => 'Welcome', 'layout' => 'layouts/app']);
    }
}