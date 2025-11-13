<?php
namespace Migrations;

use Nexus\Modules\Database\Migration;

class CreateUsersTableMigration extends Migration {
    public function up() {
        $this->createTable('users', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'name' => 'VARCHAR(255) NOT NULL',
            'email' => 'VARCHAR(255) UNIQUE NOT NULL',
            'password' => 'VARCHAR(255) NOT NULL',
            'active' => 'INTEGER DEFAULT 1',
            'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);
    }

    public function down() {
        $this->dropTable('users');
    }
}