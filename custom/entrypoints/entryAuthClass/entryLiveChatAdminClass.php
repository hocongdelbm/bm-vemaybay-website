<?php
require_once 'custom/entrypoints/entryClass.php';

/**
 * Admin-side live chat read APIs.
 *
 * Dispatched through entryGeneral.php:
 * - class=entryLiveChatAdminClass, method=getConversations
 * - class=entryLiveChatAdminClass, method=getMessages
 */
class entryLiveChatAdminClass extends entryClass {
    private $table = 'ec_live_chat_messages';

    /**
     * Get latest conversations grouped by client_phone.
     *
     * @param array $params {
     *     @type int $limit  Default 15, max 50
     *     @type int $offset Default 0
     * }
     * @return array
     */
    public function getConversations($params = []) {
        global $db;

        $limit  = $this->normalizeLimit($params['limit'] ?? 15);
        $offset = max(0, (int)($params['offset'] ?? 0));

        try {
            $phones = [];
            $sql = "SELECT client_phone, MAX(date_entered) AS last_date
                FROM {$this->table}
                WHERE deleted = 0 AND client_phone IS NOT NULL AND client_phone != ''
                GROUP BY client_phone
                ORDER BY last_date DESC
                LIMIT {$offset}, {$limit}";
            $result = $db->query($sql);

            while($row = $db->fetchByAssoc($result)) {
                if(!empty($row['client_phone'])) $phones[] = $row['client_phone'];
            }

            $conversations = [];
            foreach($phones as $phone) {
                $latest = $this->fetchLatestMessageByPhone($phone);
                if(empty($latest)) continue;

                $unreadCount = $this->countUnreadCustomerMessages($phone);
                $lastMessage = $this->getMessagePreview($latest);

                $conversations[] = [
                    'id'             => $phone,
                    'conversationId' => $phone,
                    'customerName'   => $phone,
                    'phone'          => $phone,
                    'lastMessage'    => $lastMessage,
                    'updatedAt'      => $this->formatTime($latest['date_entered'] ?? ''),
                    'updatedAtRaw'   => $latest['date_entered'] ?? '',
                    'unreadCount'    => $unreadCount,
                    'latestMessage'  => $this->normalizeMessageRow($latest),
                ];
            }

            return [
                'status'  => 1,
                'message' => 'Success',
                'data'    => [
                    'conversations' => $conversations,
                    'limit'         => $limit,
                    'offset'        => $offset,
                ],
            ];
        }
        catch(Throwable $th) {
            return $this->exceptionResponse($th);
        }
    }

    /**
     * Get latest messages of one conversation identified by client_phone.
     *
     * @param array $params {
     *     @type string $conversation_id|client_phone|phone
     *     @type int    $limit  Default 15, max 50
     *     @type int    $offset Default 0
     * }
     * @return array
     */
    public function getMessages($params = []) {
        global $db;

        $phone = $this->cleanPhone($params['conversation_id'] ?? $params['client_phone'] ?? $params['phone'] ?? '');
        if($phone === '') {
            return [
                'status'  => 0,
                'message' => 'Missing conversation id',
                'data'    => null,
            ];
        }

        $limit  = $this->normalizeLimit($params['limit'] ?? 15);
        $offset = max(0, (int)($params['offset'] ?? 0));

        try {
            $phoneQuoted = $db->quoted($phone);
            $sql = "SELECT m.*, u.user_name AS assigned_user_name, u.first_name AS assigned_first_name, u.last_name AS assigned_last_name
                FROM {$this->table} m
                LEFT JOIN users u ON u.id = m.assigned_user_id AND u.deleted = 0
                WHERE m.deleted = 0 AND m.client_phone = {$phoneQuoted}
                ORDER BY m.date_entered DESC, m.id DESC
                LIMIT {$offset}, {$limit}";
            $result = $db->query($sql);

            $messages = [];
            while($row = $db->fetchByAssoc($result)) {
                $messages[] = $this->normalizeMessageRow($row);
            }
            $messages = array_reverse($messages);

            return [
                'status'  => 1,
                'message' => 'Success',
                'data'    => [
                    'conversationId' => $phone,
                    'messages'       => $messages,
                    'limit'          => $limit,
                    'offset'         => $offset,
                ],
            ];
        }
        catch(Throwable $th) {
            return $this->exceptionResponse($th);
        }
    }

    private function fetchLatestMessageByPhone($phone) {
        global $db;

        $phoneQuoted = $db->quoted($this->cleanPhone($phone));
        $sql = "SELECT m.*, u.user_name AS assigned_user_name, u.first_name AS assigned_first_name, u.last_name AS assigned_last_name
            FROM {$this->table} m
            LEFT JOIN users u ON u.id = m.assigned_user_id AND u.deleted = 0
            WHERE m.deleted = 0 AND m.client_phone = {$phoneQuoted}
            ORDER BY m.date_entered DESC, m.id DESC
            LIMIT 1";
        $result = $db->query($sql);
        return $db->fetchByAssoc($result) ?: [];
    }

    private function countUnreadCustomerMessages($phone) {
        global $db;

        $phoneQuoted = $db->quoted($this->cleanPhone($phone));
        $sql = "SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE deleted = 0
                AND client_phone = {$phoneQuoted}
                AND src = 1
                AND (seen_at IS NULL OR seen_at = '')";
        $result = $db->query($sql);
        $row = $db->fetchByAssoc($result);
        return (int)($row['total'] ?? 0);
    }

    private function normalizeMessageRow($row) {
        $phone = $this->cleanPhone($row['client_phone'] ?? '');
        $src = (int)($row['src'] ?? 1);
        $senderType = $src === 1 ? 'customer' : 'staff';
        if($src === 0 && in_array(($row['sender_type'] ?? ''), ['bot', 'auto'], true)) {
            $senderType = 'ai';
        }

        return [
            'id'             => $row['id'] ?? '',
            'conversationId' => $phone,
            'senderType'     => $senderType,
            'adminName'      => $senderType === 'staff' ? $this->getAdminNameFromRow($row) : '',
            'content'        => html_entity_decode($row['description'] ?? '', ENT_QUOTES, 'UTF-8'),
            'messageType'    => $row['message_type'] ?? 'text',
            'imageUrl'       => ($row['message_type'] ?? '') === 'image' ? ($row['attachment_url'] ?? '') : '',
            'attachmentUrl'  => $row['attachment_url'] ?? '',
            'attachmentName' => $row['attachment_name'] ?? '',
            'createdAt'      => $this->formatTime($row['date_entered'] ?? ''),
            'createdAtRaw'   => $row['date_entered'] ?? '',
            'seenAt'         => $this->formatTime($row['seen_at'] ?? ''),
            'seenAtRaw'      => $row['seen_at'] ?? '',
        ];
    }

    private function getMessagePreview($row) {
        $message = trim(html_entity_decode($row['description'] ?? '', ENT_QUOTES, 'UTF-8'));
        if($message !== '') return $message;
        if(!empty($row['attachment_name'])) return $row['attachment_name'];
        if(!empty($row['attachment_url'])) return 'Đã gửi tệp đính kèm';
        return '';
    }

    private function getAdminNameFromRow($row) {
        $fullName = trim(($row['assigned_first_name'] ?? '') . ' ' . ($row['assigned_last_name'] ?? ''));
        if($fullName !== '') return $fullName;
        if(!empty($row['assigned_user_name'])) return $row['assigned_user_name'];
        return 'Admin';
    }

    private function cleanPhone($value) {
        return preg_replace('/[^\d+]/', '', $this->cleanInput((string)$value));
    }

    private function normalizeLimit($value) {
        $limit = (int)$value;
        if($limit <= 0) return 15;
        return min($limit, 50);
    }

    private function formatTime($value) {
        if(empty($value)) return '';
        $timestamp = strtotime($value);
        if(!$timestamp) return '';
        return date('H:i', $timestamp);
    }

    private function exceptionResponse($th) {
        return [
            'status'  => 0,
            'message' => 'Exception error',
            'error'   => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}",
            'data'    => null,
        ];
    }
}
