<?php

class SessionCacheHelper implements CacheInterfaceHelper {
    public function get($key) {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        if (!isset($_SESSION[$key])) {
            return null;
        }

        $item = $_SESSION[$key];

        // Backward compatibility: old plain values without TTL
        if (!is_array($item) || !array_key_exists('value', $item) || !array_key_exists('expires_at', $item)) {
            return $item;
        }

        // Check expiration
        if ($item['expires_at'] !== 0 && $item['expires_at'] < time()) {
            // Expired: remove and return null
            unset($_SESSION[$key]);
            return null;
        }

        return $item['value'];
    }

    public function set($key, $value, $ttl = 0) {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $expiresAt = 0; // 0 = never expire
        if ($ttl > 0) {
            $expiresAt = time() + (int) $ttl; // $ttl in seconds
        }

        $_SESSION[$key] = [
            'value'      => $value,
            'expires_at' => $expiresAt,
        ];
    }

    public function delete($key) {
        unset($_SESSION[$key]);
    }

    public function clear() {
        session_unset();
    }
}
