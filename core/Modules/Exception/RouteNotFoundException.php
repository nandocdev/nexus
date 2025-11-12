<?php
namespace Nexus\Modules\Exception;

class RouteNotFoundException extends \Exception
{
    protected $method;
    protected $uri;

    public function __construct($message = 'Route not found', $method = null, $uri = null, \Throwable $previous = null)
    {
        $this->method = $method;
        $this->uri = $uri;

        parent::__construct($message, 404, $previous);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }
}