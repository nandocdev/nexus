<?php

if (!function_exists('logger')) {
    /**
     * Get the logger instance.
     *
     * @return \Nexus\Modules\Logging\Logger
     */
    function logger() {
        static $logger;
        if (!$logger) {
            $logger = new \Nexus\Modules\Logging\Logger();
        }
        return $logger;
    }
}

if (!function_exists('report')) {
    /**
     * Report an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    function report(\Throwable $exception) {
        $handler = new \Nexus\Modules\Exception\Handler();
        $handler->report($exception);
    }
}

if (!function_exists('abort')) {
    /**
     * Throw an HTTP exception.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  array  $headers
     * @return void
     */
    function abort($code = 404, $message = '', array $headers = []) {
        throw new \Nexus\Modules\Exception\HttpException($code, $message, null, $headers);
    }
}

if (!function_exists('validate')) {
    /**
     * Validate data and throw exception on failure.
     *
     * @param  array  $data
     * @param  array  $rules
     * @return \Nexus\Modules\Validation\Validator
     */
    function validate(array $data, array $rules) {
        $validator = new \Nexus\Modules\Validation\Validator($data, $rules);
        $validator->validateOrFail();
        return $validator;
    }
}