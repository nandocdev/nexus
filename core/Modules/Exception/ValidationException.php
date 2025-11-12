<?php
namespace Nexus\Modules\Exception;

class ValidationException extends \Exception
{
    protected $errors;
    protected $inputs;

    public function __construct($message = 'Validation failed', array $errors = [], array $inputs = [], \Throwable $previous = null)
    {
        $this->errors = $errors;
        $this->inputs = $inputs;

        parent::__construct($message, 422, $previous);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getInputs()
    {
        return $this->inputs;
    }

    public function errors()
    {
        return $this->errors;
    }
}