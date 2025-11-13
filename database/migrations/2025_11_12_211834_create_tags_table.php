<?php
namespace Migrations;

use Nexus\Modules\Database\Migration;

class CreateTagsTableMigration extends Migration {
    public function up() {
        $this->createTable('tags', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'name' => 'VARCHAR(255) NOT NULL UNIQUE',
            'slug' => 'VARCHAR(255) NOT NULL UNIQUE',
            'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);
    }

    public function down() {
        $this->dropTable('tags');
    }
}
