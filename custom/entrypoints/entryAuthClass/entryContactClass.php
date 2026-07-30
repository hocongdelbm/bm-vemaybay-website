<?php
require_once 'custom/entrypoints/entryClass.php';

/**
 * Class entryContactClass
 *
 * Batch-resolves Contacts by phone number (cached, ACL-filtered per user), and creates/updates
 * Contacts. Writes are serialized per phone/contact via MySQL named locks (GET_LOCK/RELEASE_LOCK)
 * to avoid duplicate-create races and overlapping edits. A shared, non-PII "touch" marker per
 * phone lets every admin's next lookup see a write immediately instead of waiting out the
 * per-user cache TTL. Supersedes entryContactLookupClass.
 */
class entryContactClass extends entryClass
{
    private const MAX_PHONES_PER_REQUEST = 50;

    // Positive results carry PII gated by ACL/SecurityGroup; shorter TTL bounds
    // the window where a revoked group membership still serves stale cached access.
    private const CACHE_TTL_HIT = 21600; // 6h

    private const CACHE_TTL_MISS = 3600; // 1h

    private const LOCK_TIMEOUT = 5; // seconds

    private const PHONE_COLUMNS = ['phone_mobile', 'phone_work', 'phone_home', 'phone_other'];

    private const UPDATABLE_FIELDS = ['first_name', 'last_name', 'phone_mobile', 'email', 'contact_type', 'salutation', 'description'];

    /** @var CacheHelper */
    private $cache;

    public function __construct()
    {
        parent::__construct();
        $this->cache = new CacheHelper('file');
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Resolve a list of phone numbers to Contacts data.
     *
     * @param  array $params  ['phones' => string[]]
     * @return array<string, array{id:string,name:string,email:string,contact_type:string,salutation:string,description:string}|null>|array{error:string}
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

    /**
     * TEMPORARY debug helper — clears the file cache backing getContactsByPhones(), so a retest
     * isn't served a stale cached result from an earlier test run. Remove once testing is done.
     */
    public function clearCache($params = [])
    {
        $this->cache->clear();

        return ['status' => 'cleared'];
    }

    /**
     * Create a new Contact, guarded against concurrent duplicate-create races on the same phone.
     *
     * @param  array $params ['phone'=>string (required), 'first_name'=>string, 'last_name'=>string,
     *                        'email'=>string, 'contact_type'=>string, 'salutation'=>string, 'description'=>string]
     * @return array{id:string,name:string,email:string,contact_type:string,salutation:string,description:string}|array{error:string}
     */
    public function createContact($params = [])
    {
        $lockName = null;

        try {
            $rawPhone = $params['phone'] ?? '';
            if (!is_string($rawPhone) && !is_int($rawPhone)) {
                return ['error' => 'phone is required'];
            }

            $normalized = $this->normalizePhone((string) $rawPhone);
            if ($normalized === '') {
                return ['error' => 'phone is required'];
            }

            if (!BeanFactory::newBean('Contacts')->ACLAccess('save')) {
                return ['error' => 'Access denied'];
            }

            // Safe to interpolate directly (normalizePhone() guarantees digits-only), but acquireLock()
            // quotes it anyway as defense in depth.
            $lockName = 'contact_phone:' . $normalized;
            if (!$this->acquireLock($lockName)) {
                $lockName = null; // never acquired, nothing to release
                return ['error' => 'Another request is updating this contact, please retry'];
            }

            // Race-free now: no other request can be inside this same phone's lock. Note this only
            // ever sees a match the *caller* has view-ACL to (reuses resolveWithAcl()) — a contact
            // that exists but is invisible to this user due to SecurityGroup scoping will not be
            // found here, so a duplicate can still be created across security groups. This mirrors
            // the lookup's deliberate "don't leak existence of inaccessible contacts" behavior.
            $candidates = $this->findCandidatesByPhones([$normalized]);
            $existing = $this->resolveWithAcl($candidates[$normalized] ?? []);
            if ($existing !== null) {
                return ['error' => 'Contact already exists for this phone', 'existing' => $existing];
            }

            $con = BeanFactory::newBean('Contacts');
            $con->phone_mobile = trim((string) $rawPhone); // raw human-entered format; normalization is match-only
            $con->first_name = (string) ($params['first_name'] ?? '');
            $con->last_name = (string) ($params['last_name'] ?? '');
            if (!empty($params['email'])) {
                $con->email1 = (string) $params['email']; // Contact bean's email-relationship convention
            }
            $con->contact_type = (string) ($params['contact_type'] ?? '');
            $con->salutation = (string) ($params['salutation'] ?? '');
            $con->description = (string) ($params['description'] ?? '');
            $con->assigned_user_id = $this->currentUser->id ?? '';
            $con->save();

            $dto = $this->toDto($con);

            // Touch before releasing the lock (not after) to minimize the window where another
            // admin's stale cache entry still looks valid despite the DB already being updated.
            $this->touchPhone($normalized);
            $this->cache->set($this->cacheKey($normalized), $this->wrapCacheValue($dto), self::CACHE_TTL_HIT);

            return $dto;
        } catch (Throwable $th) {
            return ['error' => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}"];
        } finally {
            if ($lockName !== null) {
                $this->releaseLock($lockName);
            }
        }
    }

    /**
     * Update an existing Contact's whitelisted fields, guarded against overlapping concurrent edits.
     *
     * @param  array $params ['id'=>string (required), 'fields'=>array<string,mixed>]
     * @return array{id:string,name:string,email:string,contact_type:string,salutation:string,description:string}|array{error:string}
     */
    public function updateContact($params = [])
    {
        $lockName = null;

        try {
            $id = $params['id'] ?? '';
            // GUID-shape validation is required here (unlike createContact's digits-only phone lock)
            // because $id is otherwise raw caller input about to be interpolated into a lock name.
            if (!is_string($id) || !preg_match('/^[0-9a-f-]{36}$/i', $id)) {
                return ['error' => 'id is required'];
            }

            $bean = BeanFactory::getBean('Contacts', $id);
            if (empty($bean->id)) {
                return ['error' => 'Contact not found'];
            }

            if (!$bean->ACLAccess('edit')) {
                return ['error' => 'Access denied'];
            }

            $lockName = 'contact_edit:' . $id;
            if (!$this->acquireLock($lockName)) {
                $lockName = null;
                return ['error' => 'Another request is updating this contact, please retry'];
            }

            // Re-retrieve after acquiring the lock so we mutate the latest saved row, not one read
            // before a concurrent updater's save completed.
            $bean = BeanFactory::getBean('Contacts', $id);
            if (empty($bean->id)) {
                return ['error' => 'Contact not found'];
            }

            $oldNormalized = $this->normalizePhone((string) ($bean->phone_mobile ?? ''));

            $fields = is_array($params['fields'] ?? null) ? $params['fields'] : [];
            foreach (self::UPDATABLE_FIELDS as $field) {
                if (!array_key_exists($field, $fields)) {
                    continue;
                }

                if ($field === 'email') {
                    $bean->email1 = (string) $fields['email'];
                    continue;
                }

                $bean->$field = (string) $fields[$field];
            }

            $bean->save();

            $newNormalized = $this->normalizePhone((string) ($bean->phone_mobile ?? ''));
            $dto = $this->toDto($bean);

            foreach (array_unique(array_filter([$oldNormalized, $newNormalized])) as $phone) {
                $this->touchPhone($phone);
            }
            if ($newNormalized !== '') {
                $this->cache->set($this->cacheKey($newNormalized), $this->wrapCacheValue($dto), self::CACHE_TTL_HIT);
            }

            return $dto;
        } catch (Throwable $th) {
            return ['error' => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}"];
        } finally {
            if ($lockName !== null) {
                $this->releaseLock($lockName);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Private methods
    // -------------------------------------------------------------------------

    /**
     * Resolve a deduped list of normalized phones via cache, falling back to DB for misses.
     * A per-user cache hit is only trusted if it's newer than the phone's shared "touch" marker —
     * otherwise a write (create/update) elsewhere would stay invisible until the TTL expired.
     *
     * @param  string[] $normalizedPhones
     * @return array<string, array{name:string,email:string,contact_type:string,salutation:string,description:string}|null>
     */
    private function resolveNormalizedPhones(array $normalizedPhones): array
    {
        $results = [];
        $misses  = [];

        foreach ($normalizedPhones as $phone) {
            $cached = $this->cache->get($this->cacheKey($phone)); // null | ['cached_at'=>int,'data'=>array|false]
            if ($cached !== null) {
                $touchedAt = $this->cache->get($this->touchKey($phone)); // null | int
                if ($touchedAt === null || $touchedAt <= $cached['cached_at']) {
                    $results[$phone] = $cached['data'] === false ? null : $cached['data'];
                    continue;
                }
                // else: touched after this entry was cached — fall through, treat as a miss
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
                $this->wrapCacheValue($resolved === null ? false : $resolved), // cache driver treats null as "not found in cache"
                $resolved === null ? self::CACHE_TTL_MISS : self::CACHE_TTL_HIT
            );
        }

        return $results;
    }

    /**
     * Return the first candidate row the current user has ACL/SecurityGroup access to.
     *
     * @param  array<int, array<string, mixed>> $candidates
     * @return array{id:string,name:string,email:string,contact_type:string,salutation:string,description:string}|null
     */
    private function resolveWithAcl(array $candidates): ?array
    {
        foreach ($candidates as $row) {
            $bean = BeanFactory::getBean('Contacts', $row['id']);
            if (empty($bean->id) || !$bean->ACLAccess('view')) {
                continue;
            }

            return [
                // Exposed so the caller can offer edit (updateContact needs id) — previously
                // omitted here (only createContact's own response carried it), which meant only
                // contacts created in the same browser session were editable. See
                // CONTACT_ENTRY_MODAL_PLAN.md's "Open Questions" #4 in the chat_websocket repo.
                'id'           => $bean->id,
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

    private function toDto($bean): array
    {
        return [
            'id'           => $bean->id,
            'name'         => trim(($bean->last_name ?? '') . ' ' . ($bean->first_name ?? '')),
            'email'        => $bean->email1 ?? '',
            'contact_type' => $bean->contact_type ?? '',
            'salutation'   => $bean->salutation ?? '',
            'description'  => $bean->description ?? '',
        ];
    }

    private function wrapCacheValue($data): array
    {
        return ['cached_at' => time(), 'data' => $data];
    }

    private function cacheKey(string $normalizedPhone): string
    {
        // Distinct prefix from entryContactLookupClass's cache key ("contact_lookup:...") is
        // intentional: that class's cache values are raw (unwrapped), while this class wraps
        // values as {cached_at, data}. Reusing the same key would make this class misread the
        // other's entries (and vice versa) while both coexist during the migration period.
        return sha1("contact_v2_lookup:{$normalizedPhone}:{$this->currentUser->id}");
    }

    private function touchKey(string $normalizedPhone): string
    {
        return sha1("contact_v2_touch:{$normalizedPhone}");
    }

    private function touchPhone(string $normalizedPhone): void
    {
        if ($normalizedPhone === '') {
            return;
        }

        $this->cache->set($this->touchKey($normalizedPhone), time(), self::CACHE_TTL_HIT);
    }

    /**
     * Acquire a MySQL session-scoped named lock, quoting $lockName regardless of the caller's own
     * validation (defense in depth — createContact's phone-derived name is digits-only by
     * construction, updateContact's id-derived name is GUID-validated before reaching here, but
     * this method never trusts that alone).
     */
    private function acquireLock(string $lockName): bool
    {
        global $db;

        $quoted = $db->quote($lockName);
        $result = $db->getOne("SELECT GET_LOCK('{$quoted}', " . self::LOCK_TIMEOUT . ')');

        // GET_LOCK returns 1 (acquired), 0 (timeout), or NULL (error) — only 1 counts as success.
        return $result === '1' || $result === 1;
    }

    private function releaseLock(string $lockName): void
    {
        global $db;

        $quoted = $db->quote($lockName);
        $db->query("SELECT RELEASE_LOCK('{$quoted}')");
    }
}
