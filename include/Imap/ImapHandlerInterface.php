<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


/**
 * ImapHandlerInterface
 *
 * @author gyula
 */
interface ImapHandlerInterface
{

    /**
     *
     * @return boolean
     */
    public function isAvailable();

    /**
     * @param $stream mixed
     * @return bool
     */
    public function isValidStream($stream): bool;

    /**
     *
     * @return string|boolean
     */
    public function getLastError();

    /**
     *
     * @return array
     */
    public function getErrors();

    /**
     *
     * @return array
     */
    public function getAlerts();

    /**
     *
     * @param string $mailbox
     * @param string $username
     * @param string $password
     * @param int $options
     * @param int $n_retries
     * @param array|null $params
     * @return resource|boolean
     */
    public function open($mailbox, $username, $password, $options = 0, $n_retries = 0, $params = null);

    /**
     *
     * @return boolean
     */
    public function close();

    /**
     *
     * @return boolean
     */
    public function ping();

    /**
     *
     * @param string $mailbox
     * @param int $options
     * @param int $n_retries
     * @return boolean
     */
    public function reopen($mailbox, $options = 0, $n_retries = 0);

    /**
     *
     * @return mixed
     */
    public function getConnection();

    /**
     *
     * @param int $timeout_type
     * @param int $timeout
     * @return mixed
     */
    public function setTimeout($timeout_type, $timeout = -1);

    /**
     *
     * @param string $ref
     * @param string $pattern
     * @return array
     */
    public function getMailboxes($ref, $pattern);

    /**
     *
     * @param int $criteria
     * @param int $reverse
     * @param int $options
     * @param string $search_criteria
     * @param string $charset
     * @return array
     */
    public function sort($criteria, $reverse, $options = 0, $search_criteria = null, $charset = null);

    /**
     *
     * @param int $uid
     * @return int
     */
    public function getMessageNo($uid);

    /**
     *
     * @param int $msg_number
     * @param int $fromlength
     * @param int $subjectlength
     * @param string $defaulthost
     * @return bool|object Returns FALSE on error or, if successful, the information in an object
     */
    public function getHeaderInfo($msg_number, $fromlength = 0, $subjectlength = 0, $defaulthost = null);

    /**
     *
     * @param type $msg_number
     * @param type $options
     * @return string
     */
    public function fetchHeader($msg_number, $options = 0);

    /**
     *
     * @param string $mailbox
     * @param string $message
     * @param string $options
     * @param string $internal_date
     * @return bool
     */
    public function append($mailbox, $message, $options = null, $internal_date = null);

    /**
     *
     * @param int $msg_number
     * @return int
     */
    public function getUid($msg_number);

    /**
     * @return bool
     */
    public function expunge();

    /**
     *
     * @param string $old_mbox
     * @param string $new_mbox
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function renameMailbox($old_mbox, $new_mbox);

    /**
     * @return int|bool Return the number of messages in the current mailbox, as an integer, or FALSE on error.
     */
    public function getNumberOfMessages();

    /**
     *
     * @param string $sequence
     * @param int $options
     * @return array
     */
    public function fetchOverview($sequence, $options = 0);

    /**
     *
     * @param int $msg_number
     * @param int $options
     * @return object
     */
    public function fetchStructure($msg_number, $options = 0);

    /**
     *
     * @param int $msg_number
     * @param int $options
     * @return string
     */
    public function getBody($msg_number, $options);

    /**
     *
     * @param string $criteria
     * @param int $options
     * @param string $charset
     * @return array|bool Return FALSE if it does not understand the search criteria or no messages have been found.
     */
    public function search($criteria, $options = SE_FREE, $charset = null);

    /**
     *
     * @param int $msg_number
     * @param int $options
     * @return bool Returns TRUE.
     */
    public function delete($msg_number, $options = 0);

    /**
     *
     * @param string $sequence
     * @param string $flag
     * @param int $options
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function clearFlagFull($sequence, $flag, $options = 0);

    /**
     *
     * @param string $sequence
     * @param string $flag
     * @param int $options
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function setFlagFull($sequence, $flag, $options = NIL);

    /**
     *
     * @param string $mailbox
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function unsubscribe($mailbox);

    /**
     *
     * @param string $data
     * @return string|bool FALSE if text contains invalid modified UTF-7 sequence or text contains a character that is not part of ISO-8859-1 character set.
     */
    public function utf7Encode($data);

    /**
     *
     * @param string $mailbox
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function deleteMailbox($mailbox);

    /**
     *
     * @param string $mailbox
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function createMailbox($mailbox);

    /**
     *
     * @param string $mailbox
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function subscribe($mailbox);

    /**
     *
     * @param string $mailbox
     * @param int $options
     * @return object
     */
    public function getStatus($mailbox, $options);

    /**
     *
     * @param string $mime_encoded_text
     * @return string
     */
    public function utf8($mime_encoded_text);

    /**
     *
     * @param int $msg_number
     * @param string $section
     * @param int $options
     * @return string
     */
    public function fetchBody($msg_number, $section, $options = 0);

    /**
     *
     * @param string $text
     * @return array
     */
    public function mimeHeaderDecode($text);

    /**
     *
     * @param string $headers
     * @param string $defaulthost
     * @return object
     */
    public function rfc822ParseHeaders($headers, $defaulthost = "UNKNOWN");

    /**
     * @return object|bool Returns FALSE on failure.
     */
    public function check();

    /**
     *
     * @param string $msglist
     * @param string $mailbox
     * @param int $options
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function mailCopy($msglist, $mailbox, $options = 0);

    /**
     *
     * @param string $msglist
     * @param string $mailbox
     * @param int $options
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function mailMove($msglist, $mailbox, $options = 0);

    /**
     * @param string|null $filterCriteria
     * @param $sortCriteria
     * @param $sortOrder
     * @param int $offset
     * @param int $pageSize
     * @param array $mailboxInfo
     * @param array $columns
     * @return array
     * @throws ImapHandlerException
     */
    public function getMessageList(
        ?string $filterCriteria,
        $sortCriteria,
        $sortOrder,
        int $offset,
        int $pageSize,
        array &$mailboxInfo,
        array $columns
    ): array;
}
