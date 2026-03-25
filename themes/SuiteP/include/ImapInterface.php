<?php

namespace SuiteCRM;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * ImapInterface
 *
 * @author gyula
 */
interface ImapInterface
{

    /**
     * see more at imap_open()
     *
     * @param string $mailbox
     * @param string $username
     * @param string $password
     * @param int $options
     * @param int $n_retries
     * @param array $params
     *
     * @return resource or <b>FALSE</b> on error.
     */
    public function open($mailbox, $username, $password, $options = 0, $n_retries = 0, array $params = null);
}
