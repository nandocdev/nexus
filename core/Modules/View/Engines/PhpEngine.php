<?php
namespace Nexus\Modules\View\Engines;

use Nexus\Modules\View\Engine;

class PhpEngine implements Engine {
    /**
     * The array of shared data.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = []) {
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param  string  $__path
     * @param  array  $__data
     * @return string
     */
    protected function evaluatePath($__path, $__data) {
        $__data = array_merge($this->shared, $__data);
        $__data['__path'] = $__path;
        $__data['__data'] = $__data;

        // Include global helpers
        require_once __DIR__ . '/../helpers.php';

        // Extract data to make variables available in the view
        extract($__data, EXTR_SKIP);

        ob_start();

        try {
            include $__path;
        } catch (\Exception $e) {
            $this->handleViewException($e, $__path);
        } catch (\Throwable $e) {
            $this->handleViewException($e, $__path);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception|\Throwable  $e
     * @param  string  $path
     * @return void
     *
     * @throws $e
     */
    protected function handleViewException($e, $path) {
        ob_end_clean();
        throw $e;
    }

    /**
     * Add a piece of shared data to the engine.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function share($key, $value = null) {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $this->shared[$key] = $value;
        }

        return $value;
    }

    /**
     * Get a piece of shared data from the engine.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function shared($key, $default = null) {
        return $this->shared[$key] ?? $default;
    }

    /**
     * Get all of the shared data from the engine.
     *
     * @return array
     */
    public function getShared() {
        return $this->shared;
    }
}