<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
include_once __DIR__ . '/../../include/utils.php';

global $app_strings;

?>
<br><br>
<span class='error'><?php if (isset($_REQUEST['error_string'])) {
                        LoggerManager::getLogger()->warn('Passing error string in request is deprecated. Please update your code.');
                        echo getAppString($_REQUEST['error_string']);
                    } else {
                        LoggerManager::getLogger()->warn('Passing error string in request is deprecated. Please update your code.');
                        echo isset($request) ? getAppString($request['error_string']) : null;
                    } ?>
    <br><br>
    <?php echo $app_strings['NTC_CLICK_BACK']; ?>
</span>