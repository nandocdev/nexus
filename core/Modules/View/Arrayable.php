<?php
namespace Nexus\Modules\View;

interface Arrayable {
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}