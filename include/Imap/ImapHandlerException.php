<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * ImapHandlerException
 *
 * @author gyula
 */
class ImapHandlerException extends Exception
{
    const ERR_TEST_SET_NOT_EXISTS = 1;
    const ERR_KEY_NOT_FOUND = 2;
    const ERR_KEY_SAVE_ERROR = 3;
}
