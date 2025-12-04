<?php

class CacheHelper {

    /** @var CacheInterface */
    private $driver;

    public function __construct($type = 'session') {
        $this->driver = $this->createDriver($type);
    }

    private function createDriver($type) {
        switch (strtolower($type)) {
            case 'file':
                return new FileCacheHelper();
            case 'session':
            default:
                return new SessionCacheHelper();
        }
    }

    public function get($key) {
        return $this->driver->get($key);
    }

    public function set($key, $value, $ttl = 0) {
        return $this->driver->set($key, $value, $ttl);
    }

    public function delete($key) {

        return $this->driver->delete($key);
    }

    public function clear() {

        return $this->driver->clear();
    }
}
