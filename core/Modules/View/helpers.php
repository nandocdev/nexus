<?php
/**
 * Global view helpers for Nexus Framework
 *
 * These functions are automatically available in all views
 */

if (!function_exists('url')) {
    /**
     * Generate a URL for the application.
     *
     * @param  string  $path
     * @return string
     */
    function url($path = '') {
        return \Nexus\Modules\View\ViewHelpers::url($path);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL for the application.
     *
     * @param  string  $path
     * @return string
     */
    function asset($path) {
        return \Nexus\Modules\View\ViewHelpers::asset($path);
    }
}

if (!function_exists('route')) {
    /**
     * Generate a route URL.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @return string
     */
    function route($name, $parameters = []) {
        return \Nexus\Modules\View\ViewHelpers::route($name, $parameters);
    }
}

if (!function_exists('csrf')) {
    /**
     * Generate a CSRF token field.
     *
     * @return string
     */
    function csrf() {
        return \Nexus\Modules\View\ViewHelpers::csrf();
    }
}

if (!function_exists('method')) {
    /**
     * Generate a form method spoofing input.
     *
     * @param  string  $method
     * @return string
     */
    function method($method) {
        return \Nexus\Modules\View\ViewHelpers::method($method);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities.
     *
     * @param  string  $value
     * @return string
     */
    function e($value) {
        return \Nexus\Modules\View\ViewHelpers::e($value);
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function old($key, $default = null) {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    /**
     * Get session value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function session($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('auth')) {
    /**
     * Get the authenticated user.
     *
     * @return mixed
     */
    function auth() {
        return \Nexus\Modules\Auth\Auth::user();
    }
}

if (!function_exists('guest')) {
    /**
     * Check if user is not authenticated.
     *
     * @return bool
     */
    function guest() {
        return !\Nexus\Modules\Auth\Auth::check();
    }
}