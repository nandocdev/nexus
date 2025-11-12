<?php
namespace App\Migrations;

use Nexus\Modules\Database\Migration;

class CreateCommentsTableMigration extends Migration
{
    public function up()
    {
        $this->createTable('comments', [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'content' => 'TEXT NOT NULL',
            'user_id' => 'INT NOT NULL',
            'post_id' => 'INT NOT NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE',
            'FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE'
        ]);
    }

    public function down()
    {
        $this->dropTable('comments');
    }
}
