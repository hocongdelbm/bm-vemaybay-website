<?php

/**
 * Entry Point for saving Google API tokens during account authorization.
 *
 * @license https://raw.githubusercontent.com/salesagility/SuiteCRM/master/LICENSE.txt
 * GNU Affero General Public License version 3
 * @author Benjamin Long <ben@offsite.guru>
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include_once __DIR__ . '/GoogleApiKeySaverEntryPoint.php';

global $current_user, $sugar_config;
$client = new \Google\Client();
new GoogleApiKeySaverEntryPoint($current_user, $sugar_config, $client, $_REQUEST);
