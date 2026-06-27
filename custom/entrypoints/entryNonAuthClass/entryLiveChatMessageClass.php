<?php
require_once 'custom/entrypoints/entryClass.php';

/**
 * Class entryLiveChatMessageClass
 *
 * Saves live chat messages from the client website into the EC_Live_Chat_Messages module.
 * Dispatched through entryGeneralNonAuth.php (class=entryLiveChatMessageClass, method=saveMessage).
 */
class entryLiveChatMessageClass extends entryClass {
    /**
     * Save a live chat message record.
     *
     * @param array $params {
     *     @type int    $src              0: BM -> client, 1: client -> BM (default 1)
     *     @type string $message          Message content (stored in description) - required
     *     @type string $name             Message title (defaults to client_phone or "Live chat")
     *     @type string $client_phone     Client phone number
     *     @type string $client_url       URL the visitor was viewing
     *     @type string $client_user_agent
     *     @type string $client_ip
     *     @type string $sender_type      manual | bot | auto (default manual)
     *     @type string $message_type     text | image | file | link (default text)
     *     @type string $attachment_url
     *     @type string $attachment_name
     *     @type string $contact_id
     *     @type string $booking_id
     * }
     * @return array
     * @author DucPham
     */
    public function saveMessage($params = []) {
        $src            = (int)($params['src'] ?? 2);
        $message        = trim($params['message'] ?? '');
        $attachmentUrl  = trim($params['attachment_url'] ?? '');
        $clientPhone    = $this->cleanInput($params['client_phone'] ?? '');
        $clientIp       = $this->cleanInput($params['client_ip'] ?? '');
        $clientUA       = $this->cleanInput($params['client_user_agent'] ?? '');

        if(!in_array($src, [0, 1], true)) $src = 2;
        if($message === '' && $attachmentUrl === '') {
            return [
                'status'  => 0,
                'message' => 'Invalid message content',
                'data'    => null,
            ];
        }

        try {
            /** @var EC_Live_Chat_Messages **/
            $bean = BeanFactory::newBean('EC_Live_Chat_Messages');
            if(!$bean) {
                return [
                    'status'  => 0,
                    'message' => 'Could not initialize module',
                    'data'    => null,
                ];
            }
            $bean->name               = $this->cleanInput($params['title'] ?? '');
            $bean->description        = $message;
            $bean->src                = $src;
            $bean->client_phone       = $clientPhone;
            $bean->client_url         = $this->cleanInput($params['client_url'] ?? '');
            $bean->client_user_agent  = substr($clientUA, 0, 255);
            $bean->client_ip          = $clientIp;
            $bean->sender_type        = $this->cleanInput($params['sender_type'] ?? 'manual');
            $bean->message_type       = $this->cleanInput($params['message_type'] ?? 'text');
            $bean->attachment_url     = $attachmentUrl;
            $bean->attachment_name    = $this->cleanInput($params['attachment_name'] ?? '');
            $bean->contact_id         = $this->cleanInput($params['contact_id'] ?? '');
            $bean->booking_id         = $this->cleanInput($params['booking_id'] ?? '');
            $id = $bean->save();

            return [
                'status'  => 1,
                'message' => 'Message saved successfully',
                'data'    => [
                    'id' => $id,
                ],
            ];
        }
        catch(Throwable $th) {
            return [
                'status'    => 0,
                'message'   => "Exception error",
                'error'     => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}",
                'data'      => null,
            ];
        }
    }

    /**
     * Update the seen_at field of an existing message to the current time.
     *
     * Uses a direct SQL UPDATE instead of the bean (no relationship/audit
     * overhead) for best performance on this high-frequency call.
     *
     * @param array $params {
     *     @type string $id   Record id - required
     * }
     * @return array
     * @author DucPham
     */
    public function markSeen($params = []) {
        $id = $this->cleanInput($params['id'] ?? '');

        if($id === '') {
            return [
                'status'  => 0,
                'message' => 'Missing record id',
                'data'    => null,
            ];
        }

        try {
            global $db;
            $seenAt    = date('Y-m-d H:i:s');
            $idQuoted  = $db->quoted($id);
            $nowQuoted = $db->quoted($seenAt);

            // Idempotent: only set seen_at the first time (guarded in WHERE).
            $result = $db->query("UPDATE ec_live_chat_messages SET seen_at = $nowQuoted, date_modified = $nowQuoted WHERE id = $idQuoted AND deleted = 0 AND (seen_at IS NULL OR seen_at = '')");
            if($result === false) {
                return [
                    'status'  => 0,
                    'message' => 'Failed to update seen status',
                    'data'    => null,
                ];
            }

            return [
                'status'  => 1,
                'message' => 'Message marked as seen',
                'data'    => [
                    'id'      => $id,
                    'seen_at' => $seenAt
                ],
            ];
        }
        catch(Throwable $th) {
            return [
                'status'    => 0,
                'message'   => "Exception error",
                'error'     => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}",
                'data'      => null,
            ];
        }
    }
}
