<?php
namespace Nexus\Modules\Http;

interface RequestInterface
{
    /**
     * Get the request method.
     *
     * @return string
     */
    public function method();

    /**
     * Get the request URI.
     *
     * @return string
     */
    public function uri();

    /**
     * Get the request path.
     *
     * @return string
     */
    public function path();

    /**
     * Get a query parameter.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function query($key = null, $default = null);

    /**
     * Get an input parameter.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function input($key = null, $default = null);

    /**
     * Get all input data.
     *
     * @return array
     */
    public function all();

    /**
     * Get a header value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function header($key, $default = null);

    /**
     * Get all headers.
     *
     * @return array
     */
    public function headers();

    /**
     * Get the request content type.
     *
     * @return string|null
     */
    public function contentType();

    /**
     * Check if the request is AJAX.
     *
     * @return bool
     */
    public function ajax();

    /**
     * Check if the request is JSON.
     *
     * @return bool
     */
    public function isJson();

    /**
     * Get the JSON payload.
     *
     * @return array|null
     */
    public function json();

    /**
     * Get the request body content.
     *
     * @return string
     */
    public function getContent();

    /**
     * Get a cookie value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function cookie($key, $default = null);

    /**
     * Get a session value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function session($key = null, $default = null);

    /**
     * Get an uploaded file.
     *
     * @param  string  $key
     * @return mixed
     */
    public function file($key);

    /**
     * Get the user agent.
     *
     * @return string|null
     */
    public function userAgent();

    /**
     * Get the IP address.
     *
     * @return string|null
     */
    public function ip();

    /**
     * Check if the request is secure (HTTPS).
     *
     * @return bool
     */
    public function secure();

    /**
     * Get the host.
     *
     * @return string
     */
    public function host();

    /**
     * Get the full URL.
     *
     * @return string
     */
    public function fullUrl();
}