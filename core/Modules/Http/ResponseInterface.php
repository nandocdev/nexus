<?php
namespace Nexus\Modules\Http;

interface ResponseInterface {
    /**
     * Set the response content.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Get the response content.
     *
     * @return mixed
     */
    public function getContent();

    /**
     * Set the response status code.
     *
     * @param  int  $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode);

    /**
     * Get the response status code.
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Set a header.
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function header($key, $value);

    /**
     * Set multiple headers.
     *
     * @param  array  $headers
     * @return $this
     */
    public function withHeaders(array $headers);

    /**
     * Get all headers.
     *
     * @return array
     */
    public function getHeaders();

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
    public function cookie($name, $value, $minutes = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true);

    /**
     * Get the response cookies.
     *
     * @return array
     */
    public function getCookies();

    /**
     * Send the response.
     *
     * @return void
     */
    public function send();
}