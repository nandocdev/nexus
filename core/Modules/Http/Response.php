<?php
namespace Nexus\Modules\Http;

class Response implements ResponseInterface
{
    /**
     * The response content.
     *
     * @var mixed
     */
    protected $content;

    /**
     * The response status code.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * The response headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * The response cookies.
     *
     * @var array
     */
    protected $cookies;

    /**
     * Create a new response instance.
     *
     * @param  mixed  $content
     * @param  int  $statusCode
     * @param  array  $headers
     * @return void
     */
    public function __construct($content = '', $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->cookies = [];
    }

    /**
     * Create a response instance.
     *
     * @param  mixed  $content
     * @param  int  $statusCode
     * @param  array  $headers
     * @return static
     */
    public static function make($content = '', $statusCode = 200, array $headers = [])
    {
        return new static($content, $statusCode, $headers);
    }

    /**
     * Create a JSON response.
     *
     * @param  mixed  $data
     * @param  int  $statusCode
     * @param  array  $headers
     * @return static
     */
    public static function json($data, $statusCode = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';

        return new static(json_encode($data), $statusCode, $headers);
    }

    /**
     * Create a redirect response.
     *
     * @param  string  $url
     * @param  int  $statusCode
     * @return static
     */
    public static function redirect($url, $statusCode = 302)
    {
        return new static('', $statusCode, ['Location' => $url]);
    }

    /**
     * Set the response content.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the response content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the response status code.
     *
     * @param  int  $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Get the response status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set a header.
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function header($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Set multiple headers.
     *
     * @param  array  $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Get all headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set a cookie.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int  $minutes
     * @param  string  $path
     * @param  string  $domain
     * @param  bool  $secure
     * @param  bool  $httpOnly
     * @return $this
     */
    public function cookie($name, $value, $minutes = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true)
    {
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'expire' => $minutes > 0 ? time() + ($minutes * 60) : 0,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
        ];

        return $this;
    }

    /**
     * Get the response cookies.
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Send the response.
     *
     * @return void
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendCookies();
        $this->sendContent();
    }

    /**
     * Send the headers.
     *
     * @return void
     */
    protected function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }
    }

    /**
     * Send the cookies.
     *
     * @return void
     */
    protected function sendCookies()
    {
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }
    }

    /**
     * Send the content.
     *
     * @return void
     */
    protected function sendContent()
    {
        echo $this->content;
    }

    /**
     * Get the status text for a status code.
     *
     * @param  int  $code
     * @return string
     */
    public static function getStatusText($code)
    {
        $statusTexts = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Too Early',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];

        return $statusTexts[$code] ?? 'Unknown Status';
    }
}