<?php
namespace Nexus\Modules\Database;
// app/Core/Migration.php
abstract class Migration {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    abstract public function up();
    abstract public function down();
}