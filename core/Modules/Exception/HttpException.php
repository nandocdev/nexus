<?php
namespace Nexus\Modules\Exception;

class HttpException extends \Exception
{
    protected $statusCode;
    protected $headers;

    public function __construct($statusCode = 500, $message = null, \Throwable $previous = null, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message ?? 'HTTP ' . $statusCode . ' Error', $statusCode, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}