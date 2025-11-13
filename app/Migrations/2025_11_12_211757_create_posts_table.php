<?php
namespace App\Migrations;

use Nexus\Modules\Database\Migration;

class CreatePostsTableMigration extends Migration {
    public function up() {
        $this->createTable('posts', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'title' => 'VARCHAR(255) NOT NULL',
            'content' => 'TEXT NOT NULL',
            'user_id' => 'INTEGER NOT NULL',
            'published' => 'INTEGER DEFAULT 0',
            'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE'
        ]);
    }

    public function down() {
        $this->dropTable('posts');
    }
}
