<?php
namespace App\Migrations;

use Nexus\Modules\Database\Migration;

class CreatePostTagsTableMigration extends Migration {
    public function up() {
        $this->createTable('post_tags', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'post_id' => 'INTEGER NOT NULL',
            'tag_id' => 'INTEGER NOT NULL',
            'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE',
            'FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE',
            'UNIQUE(post_id, tag_id)'
        ]);
    }

    public function down() {
        $this->dropTable('post_tags');
    }
}
