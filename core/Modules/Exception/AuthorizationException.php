<?php
namespace Nexus\Modules\Exception;

class AuthorizationException extends \Exception
{
    public function __construct($message = 'This action is unauthorized.', \Throwable $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}