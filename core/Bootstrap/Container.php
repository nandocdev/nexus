<?php
namespace Nexus\Bootstrap;
// app/Core/Container.php
class Container {
    private $bindings = [];
    private $instances = [];
    
    public function bind($abstract, $concrete = null) {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        
        $this->bindings[$abstract] = $concrete;
    }
    
    public function make($abstract) {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        if (!isset($this->bindings[$abstract])) {
            $reflection = new \ReflectionClass($abstract);
            if ($reflection->isInstantiable()) {
                $instance = new $abstract();
                $this->instances[$abstract] = $instance;
                return $instance;
            } else {
                throw new \Exception("Cannot instantiate $abstract");
            }
        }
        
        $concrete = $this->bindings[$abstract];
        
        if ($concrete instanceof \Closure) {
            $instance = $concrete();
        } elseif (is_string($concrete)) {
            $instance = new $concrete();
        } else {
            throw new \Exception("Invalid binding for $abstract");
        }
        
        $this->instances[$abstract] = $instance;
        return $instance;
    }
}