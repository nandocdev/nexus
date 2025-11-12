<?php
namespace App\Migrations;
// app/Migrations/CreateUsersTable.php
use Nexus\Modules\Database\Migration;

class CreateUsersTable extends Migration {
    public function up() {
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->query($sql);
    }
    
    public function down() {
        $this->db->query("DROP TABLE users");
    }
}