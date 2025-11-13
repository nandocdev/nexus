<?php
namespace Nexus\Modules\View;

class Filesystem {
    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path) {
        return file_exists($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string
     */
    public function get($path) {
        return file_get_contents($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @return int
     */
    public function put($path, $contents) {
        return file_put_contents($path, $contents);
    }
}