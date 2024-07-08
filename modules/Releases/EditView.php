<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**

 * Description:
 */

$_REQUEST['edit']='true';
include("modules/Releases/index.php");
