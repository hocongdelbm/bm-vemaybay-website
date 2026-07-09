<?php
require_once 'custom/entrypoints/entryClass.php';

/**
 * Class entryContactLookupClass
 *
 * Batch-resolves Contacts by phone number, returning {name, email, contact_type}
 * per phone. Results are cached per calling user (ACL/SecurityGroup filtered),
 * so a repeat lookup for a number already resolved skips the DB.
 */
class entryContactLookupClass extends entryClass
{
    private const MAX_PHONES_PER_REQUEST = 50;

    // Positive results carry PII gated by ACL/SecurityGroup; shorter TTL bounds
    // the window where a revoked group membership still serves stale cached access.
    private const CACHE_TTL_HIT = 21600; // 6h

    private const CACHE_TTL_MISS = 3600; // 1h

    private const PHONE_COLUMNS = ['phone_mobile', 'phone_work', 'phone_home', 'phone_other'];

    /** @var CacheHelper */
    private $cache;

    public function __construct()
    {
        parent::__construct();
        $this->cache = new CacheHelper('file');
    }

    /**
     * Resolve a list of phone numbers to Contacts data.
     *
     * @param  array $params  ['phones' => string[]]
     * @return array<string, array{name:string,email:string,contact_type:string,salutation:string,description:string}|null>|array{error:string}
     */
    public function getContactsByPhones($params = [])
    {
        try {
            $phones = $params['phones'] ?? [];
            if (!is_array($phones)) {
                return ['error' => 'phones must be an array'];
            }

            if (count($phones) > self::MAX_PHONES_PER_REQUEST) {
                return ['error' => 'Only a maximum of ' . self::MAX_PHONES_PER_REQUEST . ' phones are supported per request'];
            }

            // original input phone => normalized phone
            $normalizedByOriginal = [];
            foreach ($phones as $phone) {
                if (!is_string($phone) && !is_int($phone)) {
                    continue;
                }

                $normalized = $this->normalizePhone((string) $phone);
                if ($normalized === '') {
                    continue;
                }

                $normalizedByOriginal[(string) $phone] = $normalized;
            }

            $results = $this->resolveNormalizedPhones(array_unique(array_values($normalizedByOriginal)));

            $output = [];
            foreach ($normalizedByOriginal as $original => $normalized) {
                $output[$original] = $results[$normalized] ?? null;
            }

            return $output;
        } catch (Throwable $th) {
            return ['error' => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}"];
        }
    }

    // -------------------------------------------------------------------------
    // Private methods
    // -------------------------------------------------------------------------

    /**
     * Resolve a deduped list of normalized phones via cache, falling back to DB for misses.
     *
     * @param  string[] $normalizedPhones
     * @return array<string, array{name:string,email:string,contact_type:string,salutation:string,description:string}|null>
     */
    private function resolveNormalizedPhones(array $normalizedPhones): array
    {
        $results = [];
        $misses  = [];

        foreach ($normalizedPhones as $phone) {
            $cached = $this->cache->get($this->cacheKey($phone));
            if ($cached !== null) {
                $results[$phone] = $cached === false ? null : $cached;
                continue;
            }

            $misses[] = $phone;
        }

        if (empty($misses)) {
            return $results;
        }

        $candidatesByPhone = $this->findCandidatesByPhones($misses);

        foreach ($misses as $phone) {
            $resolved = $this->resolveWithAcl($candidatesByPhone[$phone] ?? []);

            $results[$phone] = $resolved;
            $this->cache->set(
                $this->cacheKey($phone),
                $resolved === null ? false : $resolved, // cache driver treats null as "not found in cache"
                $resolved === null ? self::CACHE_TTL_MISS : self::CACHE_TTL_HIT
            );
        }

        return $results;
    }

    /**
     * Return the first candidate row the current user has ACL/SecurityGroup access to.
     *
     * @param  array<int, array<string, mixed>> $candidates
     * @return array{name:string,email:string,contact_type:string,salutation:string,description:string}|null
     */
    private function resolveWithAcl(array $candidates): ?array
    {
        foreach ($candidates as $row) {
            $bean = BeanFactory::getBean('Contacts', $row['id']);
            if (empty($bean->id) || !$bean->ACLAccess('view')) {
                continue;
            }

            return [
                'name'         => trim(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? '')),
                'email'        => $row['email'] ?? '',
                'contact_type' => $row['contact_type'] ?? '',
                'salutation'   => $row['salutation'] ?? '',
                'description'  => $row['description'] ?? '',
            ];
        }

        return null;
    }

    /**
     * Batch-query contacts matching any of the given normalized phones across all phone columns,
     * joined to their primary email. Returns a map of normalizedPhone => list of candidate rows.
     *
     * @param  string[] $normalizedPhones
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function findCandidatesByPhones(array $normalizedPhones): array
    {
        global $db;

        $quoted = array_map(function ($phone) use ($db) {
            return "'" . $db->quote($phone) . "'";
        }, $normalizedPhones);
        $inList = implode(', ', $quoted);

        $orConditions = implode(' OR ', array_map(function ($column) use ($inList) {
            return "c.{$column} IN ({$inList})";
        }, self::PHONE_COLUMNS));

        $sql = "SELECT c.id, c.first_name, c.last_name, c.contact_type, c.salutation, c.description,
                       c.phone_mobile, c.phone_work, c.phone_home, c.phone_other,
                       e.email_address AS email
                FROM contacts c
                LEFT JOIN email_addr_bean_rel eb ON eb.bean_id = c.id
                    AND eb.bean_module = 'Contacts' AND eb.primary_address = 1 AND eb.deleted = 0
                LEFT JOIN email_addresses e ON e.id = eb.email_address_id
                WHERE c.deleted = 0 AND ({$orConditions})";

        $res = $db->query($sql);
        if (!$res) {
            $this->sendSQLErrorNotification($sql);
            return [];
        }

        $candidatesByPhone = [];
        while ($row = $db->fetchByAssoc($res)) {
            foreach (self::PHONE_COLUMNS as $column) {
                $rowPhone = $this->normalizePhone((string) ($row[$column] ?? ''));
                if ($rowPhone !== '' && in_array($rowPhone, $normalizedPhones, true)) {
                    $candidatesByPhone[$rowPhone][] = $row;
                }
            }
        }

        return $candidatesByPhone;
    }

    /**
     * Normalize a phone number to a canonical digits-only form (leading country code
     * or trunk prefix collapsed to a single leading zero), used both as the cache key
     * and as the DB match value.
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (strpos($digits, '84') === 0 && strlen($digits) > 9) {
            $digits = '0' . substr($digits, 2);
        } elseif (strpos($digits, '0') !== 0) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    private function cacheKey(string $normalizedPhone): string
    {
        return sha1("contact_lookup:{$normalizedPhone}:{$this->currentUser->id}");
    }
}
