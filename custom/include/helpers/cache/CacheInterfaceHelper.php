<?php
interface CacheInterfaceHelper {
    public function get($key);
    public function set($key, $value, $ttl = 0);
    public function delete($key);
    public function clear();
}