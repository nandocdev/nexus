<?php
namespace App\Migrations;

use Nexus\Modules\Database\Migration;

class CreateTagsTableMigration extends Migration
{
    public function up()
    {
        $this->createTable('tags', [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(255) NOT NULL UNIQUE',
            'slug' => 'VARCHAR(255) NOT NULL UNIQUE',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
    }

    public function down()
    {
        $this->dropTable('tags');
    }
}
