<?php
namespace Nexus\Modules\Exception;

use Nexus\Modules\Logging\Logger;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        $context = $this->buildExceptionContext($exception);
        Logger::error('Exception occurred', $context);

        // Here you could add additional reporting mechanisms
        // like sending emails, Slack notifications, etc.
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  \Throwable  $exception
     * @return bool
     */
    protected function shouldntReport(Throwable $exception)
    {
        foreach ($this->dontReport as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build context array for exception logging.
     *
     * @param  \Throwable  $exception
     * @return array
     */
    protected function buildExceptionContext(Throwable $exception)
    {
        return [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Throwable  $exception
     * @return mixed
     */
    public function render(Throwable $exception)
    {
        // Check if it's an AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            return $this->renderJsonResponse($exception);
        }

        return $this->renderHtmlResponse($exception);
    }

    /**
     * Render JSON response for AJAX requests.
     *
     * @param  \Throwable  $exception
     * @return string
     */
    protected function renderJsonResponse(Throwable $exception)
    {
        $statusCode = $this->getHttpStatusCode($exception);

        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'error' => true,
            'message' => $this->getDisplayMessage($exception),
            'code' => $exception->getCode(),
        ];

        // Add debug information in development mode
        if ($this->isDebugMode()) {
            $response['debug'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        return json_encode($response);
    }

    /**
     * Render HTML response for regular requests.
     *
     * @param  \Throwable  $exception
     * @return string
     */
    protected function renderHtmlResponse(Throwable $exception)
    {
        $statusCode = $this->getHttpStatusCode($exception);

        http_response_code($statusCode);

        if ($this->isDebugMode()) {
            return $this->renderDebugPage($exception);
        }

        return $this->renderErrorPage($statusCode);
    }

    /**
     * Render debug page with detailed exception information.
     *
     * @param  \Throwable  $exception
     * @return string
     */
    protected function renderDebugPage(Throwable $exception)
    {
        $title = 'Exception Occurred';
        $message = htmlspecialchars($exception->getMessage());
        $file = htmlspecialchars($exception->getFile());
        $line = $exception->getLine();
        $trace = htmlspecialchars($exception->getTraceAsString());

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #e74c3c; color: white; padding: 15px; margin: -20px -20px 20px -20px; border-radius: 5px 5px 0 0; }
        .error-details { background: #f8f9fa; padding: 15px; border-left: 4px solid #e74c3c; margin: 15px 0; }
        .stack-trace { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 3px; font-family: monospace; white-space: pre-wrap; overflow-x: auto; }
        .file-info { font-weight: bold; color: #e74c3c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$title}</h1>
        </div>
        <div class="error-details">
            <p><strong>Message:</strong> {$message}</p>
            <p><span class="file-info">File:</span> {$file}:{$line}</p>
        </div>
        <h3>Stack Trace:</h3>
        <div class="stack-trace">{$trace}</div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render generic error page.
     *
     * @param  int  $statusCode
     * @return string
     */
    protected function renderErrorPage($statusCode)
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Page Not Found',
            500 => 'Internal Server Error',
        ];

        $message = $messages[$statusCode] ?? 'An error occurred';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$statusCode} - {$message}</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error-code { font-size: 72px; color: #e74c3c; margin: 0; }
        .error-message { font-size: 24px; color: #333; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error-code">{$statusCode}</h1>
        <p class="error-message">{$message}</p>
        <p>We apologize for the inconvenience. Please try again later.</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get appropriate HTTP status code for exception.
     *
     * @param  \Throwable  $exception
     * @return int
     */
    protected function getHttpStatusCode(Throwable $exception)
    {
        // Map common exceptions to HTTP status codes
        $exceptionMap = [
            \InvalidArgumentException::class => 400,
            \Nexus\Modules\Auth\AuthenticationException::class => 401,
            \Nexus\Modules\Auth\AuthorizationException::class => 403,
            \Nexus\Modules\Http\RouteNotFoundException::class => 404,
            \Nexus\Modules\Validation\ValidationException::class => 422,
        ];

        foreach ($exceptionMap as $exceptionClass => $statusCode) {
            if ($exception instanceof $exceptionClass) {
                return $statusCode;
            }
        }

        return 500; // Internal Server Error as default
    }

    /**
     * Get display message for exception.
     *
     * @param  \Throwable  $exception
     * @return string
     */
    protected function getDisplayMessage(Throwable $exception)
    {
        // In production, don't show detailed error messages
        if (!$this->isDebugMode()) {
            return 'An error occurred while processing your request.';
        }

        return $exception->getMessage();
    }

    /**
     * Check if debug mode is enabled.
     *
     * @return bool
     */
    protected function isDebugMode()
    {
        return \Nexus\Modules\Config\Config::get('DEBUG', false) ||
               \Nexus\Modules\Config\Config::get('APP_DEBUG', false) ||
               getenv('APP_DEBUG') === 'true';
    }
}