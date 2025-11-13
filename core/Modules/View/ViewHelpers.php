<?php
namespace Nexus\Modules\View;

class ViewHelpers {
    /**
     * Generate a URL for the application.
     *
     * @param  string  $path
     * @return string
     */
    public static function url($path = '') {
        $baseUrl = getenv('APP_URL') ?: 'http://localhost:8080';
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Generate an asset URL for the application.
     *
     * @param  string  $path
     * @return string
     */
    public static function asset($path) {
        return self::url('assets/' . ltrim($path, '/'));
    }

    /**
     * Generate a route URL (simplified version).
     *
     * @param  string  $name
     * @param  array  $parameters
     * @return string
     */
    public static function route($name, $parameters = []) {
        // This is a simplified version. In a real implementation,
        // you'd look up the route by name from the router.
        return self::url($name);
    }

    /**
     * Escape HTML entities.
     *
     * @param  string  $value
     * @return string
     */
    public static function e($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Generate a CSRF token field.
     *
     * @return string
     */
    public static function csrf() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_token" value="' . self::e($token) . '">';
    }

    /**
     * Generate a CSRF token.
     *
     * @return string
     */
    protected static function generateCsrfToken() {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }

    /**
     * Check if the current request method matches.
     *
     * @param  string  $method
     * @return bool
     */
    public static function isMethod($method) {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === strtoupper($method);
    }

    /**
     * Get the current request URI.
     *
     * @return string
     */
    public static function currentUri() {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Check if the current URI matches a pattern.
     *
     * @param  string  $pattern
     * @return bool
     */
    public static function isUri($pattern) {
        return strpos(self::currentUri(), $pattern) === 0;
    }

    /**
     * Generate a form method spoofing input.
     *
     * @param  string  $method
     * @return string
     */
    public static function method($method) {
        return '<input type="hidden" name="_method" value="' . self::e(strtoupper($method)) . '">';
    }

    /**
     * Generate a select dropdown.
     *
     * @param  string  $name
     * @param  array  $options
     * @param  mixed  $selected
     * @param  array  $attributes
     * @return string
     */
    public static function select($name, $options = [], $selected = null, $attributes = []) {
        $html = '<select name="' . self::e($name) . '"';

        foreach ($attributes as $key => $value) {
            $html .= ' ' . self::e($key) . '="' . self::e($value) . '"';
        }

        $html .= '>';

        foreach ($options as $value => $label) {
            $isSelected = $selected == $value ? ' selected' : '';
            $html .= '<option value="' . self::e($value) . '"' . $isSelected . '>' . self::e($label) . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Generate a checkbox input.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @param  bool  $checked
     * @param  array  $attributes
     * @return string
     */
    public static function checkbox($name, $value = 1, $checked = false, $attributes = []) {
        $html = '<input type="checkbox" name="' . self::e($name) . '" value="' . self::e($value) . '"';

        if ($checked) {
            $html .= ' checked';
        }

        foreach ($attributes as $key => $val) {
            $html .= ' ' . self::e($key) . '="' . self::e($val) . '"';
        }

        $html .= '>';

        return $html;
    }

    /**
     * Generate a radio input.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @param  bool  $checked
     * @param  array  $attributes
     * @return string
     */
    public static function radio($name, $value, $checked = false, $attributes = []) {
        $html = '<input type="radio" name="' . self::e($name) . '" value="' . self::e($value) . '"';

        if ($checked) {
            $html .= ' checked';
        }

        foreach ($attributes as $key => $val) {
            $html .= ' ' . self::e($key) . '="' . self::e($val) . '"';
        }

        $html .= '>';

        return $html;
    }
}