<?php

namespace custom\services\Webhook;

/**
 * Shared X-Auth-Key / X-Signature verification for the server-to-server webhooks
 * registered by the Chat System. Keys live in $sugar_config['webhook'][<name>] and
 * must never be echoed back or logged.
 *
 * Auth key and signature are verified separately so each endpoint keeps its own
 * ordering (and its own response body shape) around the raw-body read.
 */
final class WebhookAuthenticator
{
    private const KEY_PATTERN = '/^[a-f0-9]{64}$/i';

    /** @var string */
    private $credentials;

    /** @var string */
    private $matchedSignatureKey = null;

    private function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Build from $sugar_config['webhook'][$name]. Returns null when either key is
     * missing or malformed, so the caller can answer 500 in its own format.
     */
    public static function fromConfig(?string $legacyName = null): ?self
    {
        global $sugar_config;

        $webhook = is_array($sugar_config['webhook'] ?? null) ? $sugar_config['webhook'] : [];
        $configs = [$webhook];
        if ($legacyName !== null && is_array($webhook[$legacyName] ?? null)) {
            $configs[] = $webhook[$legacyName];
        }
        $credentials = [];
        foreach ($configs as $config) {
            $authKey = is_string($config['auth_key'] ?? null) ? trim($config['auth_key']) : '';
            $signatureKey = is_string($config['signature_key'] ?? null) ? trim($config['signature_key']) : '';
            if (!preg_match(self::KEY_PATTERN, $authKey) || !preg_match(self::KEY_PATTERN, $signatureKey)) {
                continue;
            }
            $credentials[$authKey . ':' . $signatureKey] = [$authKey, $signatureKey];
        }
        return $credentials ? new self(array_values($credentials)) : null;
    }

    /** @return array<string, mixed> request headers keyed by lowercase name */
    public static function requestHeaders(): array
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        $normalized = [];
        foreach (is_array($headers) ? $headers : [] as $name => $value) {
            $normalized[strtolower($name)] = $value;
        }

        return $normalized;
    }

    /**
     * Read a header, falling back to $_SERVER when getallheaders() is unavailable
     * (CLI-driven tests, some FastCGI setups).
     */
    public static function header(array $headers, string $name): string
    {
        $name = strtolower($name);
        $value = $headers[$name] ?? null;

        if ($value === null) {
            $serverKey = strtoupper(str_replace('-', '_', $name));
            // Content-* headers are exposed by PHP without the HTTP_ prefix.
            if ($name !== 'content-type' && $name !== 'content-length') {
                $serverKey = 'HTTP_' . $serverKey;
            }
            $value = $_SERVER[$serverKey] ?? '';
        }

        return is_string($value) ? $value : '';
    }

    public function verifyAuthKey(array $headers): bool
    {
        $requestKey = self::header($headers, 'x-auth-key');

        foreach ($this->credentials as [$authKey, $signatureKey]) {
            if ($requestKey !== '' && hash_equals($authKey, $requestKey)) {
                $this->matchedSignatureKey = $signatureKey;
                return true;
            }
        }
        return false;
    }

    public function verifySignature(array $headers, string $rawBody): bool
    {
        $signature = strtolower(trim(self::header($headers, 'x-signature')));

        if (!(bool) preg_match('/^[a-f0-9]{64}$/', $signature)) {
            return false;
        }
        $keys = $this->matchedSignatureKey === null
            ? array_column($this->credentials, 1)
            : [$this->matchedSignatureKey];
        foreach ($keys as $signatureKey) {
            if (hash_equals(hash_hmac('sha256', $rawBody, $signatureKey), $signature)) {
                return true;
            }
        }
        return false;
    }
}
