<?php
namespace Nexus\Modules\View;

class ViewDispatcher {
    /**
     * The registered event listeners.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Register an event listener for the view system.
     *
     * @param  string  $event
     * @param  \Closure|string  $listener
     * @return void
     */
    public function listen($event, $listener) {
        $this->listeners[$event][] = $listener;
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function fire($event, array $payload = []) {
        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {
            if ($listener instanceof \Closure) {
                $listener(...$payload);
            } else {
                // Handle string listeners if needed
            }
        }
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string  $event
     * @return void
     */
    public function forget($event) {
        unset($this->listeners[$event]);
    }
}