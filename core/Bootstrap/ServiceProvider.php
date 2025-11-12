<?php
namespace Nexus\Bootstrap;

abstract class ServiceProvider {
    protected $container;
    
    public function __construct() {
        $this->container = null; // Se setea desde Application
    }
    
    public function setContainer($container) {
        $this->container = $container;
    }
    
    abstract public function register();
    
    public function boot() {
        // Método opcional para inicialización
    }
}