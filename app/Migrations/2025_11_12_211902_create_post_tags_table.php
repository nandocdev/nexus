<?php
namespace App\Migrations;

use Nexus\Modules\Database\Migration;

class CreatePostTagsTableMigration extends Migration
{
    public function up()
    {
        $this->createTable('post_tags', [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'post_id' => 'INT NOT NULL',
            'tag_id' => 'INT NOT NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE',
            'FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE',
            'UNIQUE KEY unique_post_tag (post_id, tag_id)'
        ]);
    }

    public function down()
    {
        $this->dropTable('post_tags');
    }
}
