<?php
namespace App\Controllers;
// app/Controllers/HomeController.php
use Nexus\Modules\Http\Controller;

class HomeController extends Controller {
    public function index() {
        echo $this->view('home', ['title' => 'Welcome', 'layout' => 'layouts/app']);
    }
}