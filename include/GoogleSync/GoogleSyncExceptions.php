<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * This class define unique exceptions for GoogleSync
 * When we don't need to support PHP < 7 we can set these to extend the new Exception classes
 * Exception Codes & Messages are set to unique values, but can still be overridden if needed.
 * Standard: Classes should be in separated files..
 */
class GoogleSyncException extends Exception
{
    const UNKNOWN_EXCEPTION = 100;
    const MEETING_NOT_FOUND = 101;
    const EVENT_ID_IS_EMPTY = 102;
    const INVALID_ACTION = 103;
    const INVALID_CLIENT_ID = 104;
    const UNABLE_TO_RETRIEVE_USER = 105;
    const UNABLE_TO_SETUP_GCLIENT = 106;
    const TIMEZONE_SET_FAILURE = 107;
    const GSERVICE_FAILURE = 108;
    const GCALENDAR_FAILURE = 109;
    const NO_REFRESH_TOKEN = 110;
    const UNABLE_TO_RETRIEVE_MEETING = 111;
    const AMBIGUOUS_MEETING_ID = 112;
    const GOOGLE_RECORD_PARSE_FAILURE = 113;
    const INVALID_USER_ID = 114;
    const NO_GRESOURCE_SET = 115;
    const NO_GSERVICE_SET = 116;
    const NO_REMOVE_EVENT_START_IS_NOT_SET = 117;
    const NO_REMOVE_EVENT_START_IS_INCORRECT = 118;
    const INCORRECT_WORKING_USER_TYPE = 119;
    const UNABLE_TO_RETRIEVE_USER_ALL = 120;
    const JSON_CORRUPT = 121;
    const JSON_KEY_MISSING = 122;
    const NO_GCLIENT_SET = 123;
    const GEVENT_INSERT_OR_UPDATE_FAILURE = 124;
    const MEETING_SAVE_FAILURE = 125;
    const MEETING_CREATE_OR_UPDATE_FAILURE = 126;
    const MEETING_ID_IS_EMPTY = 127;
    const RECORD_VALIDATION_FAILURE = 128;
    const SQL_FAILURE = 129;
    const ACCSESS_TOKEN_PARAMETER_MISSING = 130;
}
