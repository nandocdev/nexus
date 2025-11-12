<?php
namespace App\Core;
// app/Core/Seeder.php
abstract class Seeder {
    protected $db;
    
    public function __construct() {
        $this->db = \Nexus\Modules\Database\Database::getInstance();
    }
    
    abstract public function run();
}