<?php
namespace App\Seeders;
// app/Seeders/UserSeeder.php
use App\Core\Seeder;
use App\Models\User;

class UserSeeder extends Seeder {
    public function run() {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password'],
            ['name' => 'User', 'email' => 'user@example.com', 'password' => 'password']
        ];
        
        foreach ($users as $userData) {
            $user = new User();
            $user->create($userData);
        }
    }
}