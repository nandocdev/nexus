<?php
namespace App\Controllers;
// app/Controllers/HomeController.php
use Nexus\Modules\Http\Controller;
use Nexus\Modules\Http\Request;

class HomeController extends Controller {
    public function index(Request $request) {
        echo $this->view('home', ['title' => 'Welcome', 'layout' => 'layouts/app']);
    }
}