<?php
class FileCacheHelper implements CacheInterfaceHelper {
    private $path;

    public function __construct() {
        $this->path = 'cache/cachehelper/';
        if (!file_exists($this->path)) {
            mkdir($this->path, 0700, true);
        }
    }

    private function filePath($key) {
        return $this->path . sha1($key) . '.cache';
    }

    public function get($key) {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $file = $this->filePath($key);
        if (!file_exists($file)) {
            return null;
        }

        $raw = file_get_contents($file);
        if ($raw === false) {
            return null;
        }

        $data = @unserialize($raw);
        // Old entries or corrupted file
        if (!is_array($data) || !array_key_exists('value', $data) || !array_key_exists('expires_at', $data)) {
            return null;
        }

        // Check expiration
        if ($data['expires_at'] !== 0 && $data['expires_at'] < time()) {
            // Expired: delete file and return null
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = 0) {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $expiresAt = 0; // 0 = never expire
        if ($ttl > 0) {
            $expiresAt = time() + $ttl;
        }

        $payload = [
            'value'      => $value,
            'expires_at' => $expiresAt,
        ];

        file_put_contents($this->filePath($key), serialize($payload), LOCK_EX);
    }

    public function delete($key) {
        $file = $this->filePath($key);
        if (file_exists($file)) unlink($file);
    }

    public function clear() {
        array_map('unlink', glob($this->path.'*.cache'));
    }
}