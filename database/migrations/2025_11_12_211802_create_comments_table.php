<?php
namespace Migrations;

use Nexus\Modules\Database\Migration;

class CreateCommentsTableMigration extends Migration {
    public function up() {
        $this->createTable('comments', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'content' => 'TEXT NOT NULL',
            'user_id' => 'INTEGER NOT NULL',
            'post_id' => 'INTEGER NOT NULL',
            'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE',
            'FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE'
        ]);
    }

    public function down() {
        $this->dropTable('comments');
    }
}
