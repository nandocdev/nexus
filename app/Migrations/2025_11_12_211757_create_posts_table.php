<?php
namespace App\Migrations;

use Nexus\Modules\Database\Migration;

class CreatePostsTableMigration extends Migration
{
    public function up()
    {
        $this->createTable('posts', [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title' => 'VARCHAR(255) NOT NULL',
            'content' => 'TEXT NOT NULL',
            'user_id' => 'INT NOT NULL',
            'published' => 'BOOLEAN DEFAULT FALSE',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE'
        ]);
    }

    public function down()
    {
        $this->dropTable('posts');
    }
}
