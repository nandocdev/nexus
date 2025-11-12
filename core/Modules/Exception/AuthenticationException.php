<?php
namespace Nexus\Modules\Exception;

class AuthenticationException extends \Exception
{
    protected $redirectTo;

    public function __construct($message = 'Unauthenticated.', \Throwable $previous = null, $redirectTo = null)
    {
        $this->redirectTo = $redirectTo;

        parent::__construct($message, 401, $previous);
    }

    public function getRedirectTo()
    {
        return $this->redirectTo;
    }
}