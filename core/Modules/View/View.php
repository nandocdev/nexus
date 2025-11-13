<?php
namespace Nexus\Modules\View;

use Nexus\Modules\View\Engines\PhpEngine;

class View {
    /**
     * The view factory instance.
     *
     * @var ViewFactory
     */
    protected $factory;

    /**
     * The engine implementation.
     *
     * @var Engine
     */
    protected $engine;

    /**
     * The name of the view.
     *
     * @var string
     */
    protected $view;

    /**
     * The array of view data.
     *
     * @var array
     */
    protected $data;

    /**
     * The path to the view file.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new view instance.
     *
     * @param  ViewFactory  $factory
     * @param  Engine  $engine
     * @param  string  $view
     * @param  array  $data
     * @param  string|null  $path
     * @return void
     */
    public function __construct(ViewFactory $factory, Engine $engine, $view, $data = [], $path = null) {
        $this->view = $view;
        $this->data = $data;
        $this->path = $path;
        $this->engine = $engine;
        $this->factory = $factory;
    }

    /**
     * Get the string contents of the view.
     *
     * @param  callable|null  $callback
     * @return string
     */
    public function render(callable $callback = null) {
        $contents = $this->renderContents();

        $response = isset($callback) ? $callback($this, $contents) : null;

        return $response ?? $contents;
    }

    /**
     * Get the contents of the view instance.
     *
     * @return string
     */
    protected function renderContents() {
        return $this->engine->get($this->path ?: $this->factory->getFinder()->find($this->view), $this->data);
    }

    /**
     * Get the data bound to the view instance.
     *
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function getName() {
        return $this->view;
    }

    /**
     * Set the path to the view.
     *
     * @param  string  $path
     * @return $this
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null) {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the view factory instance.
     *
     * @return ViewFactory
     */
    public function getFactory() {
        return $this->factory;
    }
}