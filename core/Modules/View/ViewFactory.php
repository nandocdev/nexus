<?php
namespace Nexus\Modules\View;

use Nexus\Modules\View\Engines\PhpEngine;

class ViewFactory {
    /**
     * The engine implementation.
     *
     * @var Engine
     */
    protected $engine;

    /**
     * The view finder implementation.
     *
     * @var ViewFinder
     */
    protected $finder;

    /**
     * The event dispatcher instance.
     *
     * @var \Nexus\Modules\View\ViewDispatcher
     */
    protected $events;

    /**
     * The view composer events.
     *
     * @var array
     */
    protected $composers = [];

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected $renderCount = 0;

    /**
     * Create a new view factory instance.
     *
     * @param  Engine  $engine
     * @param  ViewFinder  $finder
     * @param  ViewDispatcher|null  $events
     * @return void
     */
    public function __construct(Engine $engine, ViewFinder $finder, ViewDispatcher $events = null) {
        $this->engine = $engine;
        $this->finder = $finder;
        $this->events = $events ?: new ViewDispatcher();

        $this->share('__env', $this);
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function make($view, $data = []) {
        $data = array_merge($this->parseData($data), $data);

        return $this->viewInstance($view, $data)->render();
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Nexus\Modules\View\View
     */
    public function viewInstance($view, $data = [], $mergeData = []) {
        return new View($this, $this->getEngine(), $view, $data, null);
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param  mixed  $data
     * @return array
     */
    protected function parseData($data) {
        return $data instanceof Arrayable ? $data->toArray() : (array) $data;
    }

    /**
     * Determine if a given view exists.
     *
     * @param  string  $view
     * @return bool
     */
    public function exists($view) {
        try {
            $this->finder->find($view);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get the view finder instance.
     *
     * @return ViewFinder
     */
    public function getFinder() {
        return $this->finder;
    }

    /**
     * Set the view finder instance.
     *
     * @param  ViewFinder  $finder
     * @return void
     */
    public function setFinder(ViewFinder $finder) {
        $this->finder = $finder;
    }

    /**
     * Get the engine implementation.
     *
     * @return Engine
     */
    public function getEngine() {
        return $this->engine;
    }

    /**
     * Set the engine implementation.
     *
     * @param  Engine  $engine
     * @return void
     */
    public function setEngine(Engine $engine) {
        $this->engine = $engine;
    }

    /**
     * Add a piece of shared data to the environment.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function share($key, $value = null) {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $this->engine->share($key, $value);
        }

        return $value;
    }

    /**
     * Get an item from the shared data.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function shared($key, $default = null) {
        return $this->engine->shared($key, $default);
    }

    /**
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared() {
        return $this->engine->getShared();
    }

    /**
     * Start a component rendering process.
     *
     * @param  string  $name
     * @param  array  $data
     * @return void
     */
    public function startComponent($name, array $data = []) {
        if (ob_start()) {
            $this->componentStack[] = $name;

            $this->componentData[$this->currentComponent()] = $data;

            $this->slots[$this->currentComponent()] = [];
        }
    }

    /**
     * Render the current component.
     *
     * @return string
     */
    public function renderComponent() {
        $name = array_pop($this->componentStack);

        return $this->make($name, $this->componentData[$name]);
    }
}