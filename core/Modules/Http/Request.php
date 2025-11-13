<?php
namespace Nexus\Modules\Http;

class Request implements RequestInterface {
    /**
     * The request method.
     *
     * @var string
     */
    protected $method;

    /**
     * The request URI.
     *
     * @var string
     */
    protected $uri;

    /**
     * The request path.
     *
     * @var string
     */
    protected $path;

    /**
     * The query parameters.
     *
     * @var array
     */
    protected $query;

    /**
     * The POST parameters.
     *
     * @var array
     */
    protected $post;

    /**
     * The request headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * The request body.
     *
     * @var string|null
     */
    protected $body;

    /**
     * The uploaded files.
     *
     * @var array
     */
    protected $files;

    /**
     * The server parameters.
     *
     * @var array
     */
    protected $server;

    /**
     * The cookies.
     *
     * @var array
     */
    protected $cookies;

    /**
     * The session data.
     *
     * @var array
     */
    protected $session;

    /**
     * Create a new request instance.
     *
     * @param  array  $query
     * @param  array  $post
     * @param  array  $server
     * @param  array  $cookies
     * @param  array  $files
     * @param  array|null  $session
     * @return void
     */
    public function __construct(array $query = [], array $post = [], array $server = [], array $cookies = [], array $files = [], array $session = null) {
        $this->query = $query ?: $_GET;
        $this->post = $post ?: $_POST;
        $this->server = $server ?: $_SERVER;
        $this->cookies = $cookies ?: $_COOKIE;
        $this->files = $files ?: $_FILES;
        $this->session = $session ?: ($_SESSION ?? []);

        $this->method = $this->server['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->server['REQUEST_URI'] ?? '/';
        $this->path = parse_url($this->uri, PHP_URL_PATH) ?: '/';

        // Handle method spoofing
        if ($this->method === 'POST' && isset($this->post['_method'])) {
            $this->method = strtoupper($this->post['_method']);
        }
    }

    /**
     * Create a request from the current PHP globals.
     *
     * @return static
     */
    public static function capture() {
        return new static($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES, $_SESSION ?? []);
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function method() {
        return $this->method;
    }

    /**
     * Check if the request method is the given method.
     *
     * @param  string  $method
     * @return bool
     */
    public function isMethod($method) {
        return strtoupper($this->method) === strtoupper($method);
    }

    /**
     * Get the request URI.
     *
     * @return string
     */
    public function uri() {
        return $this->uri;
    }

    /**
     * Get the request path.
     *
     * @return string
     */
    public function path() {
        return $this->path;
    }

    /**
     * Get the query string.
     *
     * @return string
     */
    public function queryString() {
        return $this->server['QUERY_STRING'] ?? '';
    }

    /**
     * Get a query parameter.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function query($key = null, $default = null) {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    /**
     * Get a POST parameter.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function input($key = null, $default = null) {
        if ($key === null) {
            return array_merge($this->query, $this->post);
        }

        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get all input data.
     *
     * @return array
     */
    public function all() {
        return array_merge($this->query, $this->post);
    }

    /**
     * Get only the specified input keys.
     *
     * @param  array  $keys
     * @return array
     */
    public function only(array $keys) {
        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $this->input($key);
        }

        return $results;
    }

    /**
     * Get all input except the specified keys.
     *
     * @param  array  $keys
     * @return array
     */
    public function except(array $keys) {
        $keys = array_flip($keys);

        return array_diff_key($this->all(), $keys);
    }

    /**
     * Check if the request has a given input key.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key) {
        return isset($this->post[$key]) || isset($this->query[$key]);
    }

    /**
     * Get a header value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function header($key, $default = null) {
        $headers = $this->headers();

        $key = str_replace('_', '-', strtolower($key));
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

        return $headers[$key] ?? $default;
    }

    /**
     * Get all headers.
     *
     * @return array
     */
    public function headers() {
        if ($this->headers) {
            return $this->headers;
        }

        $this->headers = [];

        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $this->headers[$key] = $value;
            }
        }

        return $this->headers;
    }

    /**
     * Get the request content type.
     *
     * @return string|null
     */
    public function contentType() {
        return $this->header('Content-Type') ?: $this->header('content-type');
    }

    /**
     * Check if the request is AJAX.
     *
     * @return bool
     */
    public function ajax() {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Check if the request is JSON.
     *
     * @return bool
     */
    public function isJson() {
        return strpos($this->contentType() ?? '', 'application/json') === 0;
    }

    /**
     * Get the JSON payload.
     *
     * @return array|null
     */
    public function json() {
        if (!$this->isJson()) {
            return null;
        }

        $body = $this->getContent();

        return json_decode($body, true);
    }

    /**
     * Get the request body content.
     *
     * @return string
     */
    public function getContent() {
        if ($this->body === null) {
            $this->body = file_get_contents('php://input');
        }

        return $this->body;
    }

    /**
     * Get a cookie value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function cookie($key, $default = null) {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get all cookies.
     *
     * @return array
     */
    public function cookies() {
        return $this->cookies;
    }

    /**
     * Get a session value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function session($key = null, $default = null) {
        if ($key === null) {
            return $this->session;
        }

        return $this->session[$key] ?? $default;
    }

    /**
     * Get an uploaded file.
     *
     * @param  string  $key
     * @return mixed
     */
    public function file($key) {
        return $this->files[$key] ?? null;
    }

    /**
     * Get all uploaded files.
     *
     * @return array
     */
    public function files() {
        return $this->files;
    }

    /**
     * Get the user agent.
     *
     * @return string|null
     */
    public function userAgent() {
        return $this->header('User-Agent');
    }

    /**
     * Get the IP address.
     *
     * @return string|null
     */
    public function ip() {
        return $this->server['REMOTE_ADDR'] ??
            $this->server['HTTP_X_FORWARDED_FOR'] ??
            $this->server['HTTP_CLIENT_IP'] ?? null;
    }

    /**
     * Check if the request is secure (HTTPS).
     *
     * @return bool
     */
    public function secure() {
        return isset($this->server['HTTPS']) && $this->server['HTTPS'] === 'on';
    }

    /**
     * Get the host.
     *
     * @return string
     */
    public function host() {
        return $this->server['HTTP_HOST'] ?? 'localhost';
    }

    /**
     * Get the full URL.
     *
     * @return string
     */
    public function fullUrl() {
        $scheme = $this->secure() ? 'https' : 'http';
        $host = $this->host();

        return $scheme . '://' . $host . $this->uri;
    }
}