<?php
use SuiteCRM\Modules\Administration\Search\Controller;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user;

if (!is_admin($current_user)) {
    sugar_die("Unauthorized access to administration.");
}

$controller = new Controller();

$controller->handle();
