<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $current_user, $mod_strings, $app_strings, $log;

if (!is_admin($current_user)) {
    echo $app_strings['ERR_NOT_ADMIN'];
    return;
}

require_once __DIR__ . '/../../include/Services/NormalizeRecords/NormalizeRecords.php';

$repairStatus = NormalizeRecords::getRepairStatus();

if ($repairStatus === NormalizeRecords::REPAIR_STATUS_REPAIRED || $repairStatus === NormalizeRecords::REPAIR_STATUS_IN_PROGRESS) {
    $mode = NormalizeRecords::getExecutionMode();

    $smarty = new Sugar_Smarty();
    $smarty->assign('MOD', $mod_strings);
    $smarty->assign('status', $repairStatus);
    $smarty->assign('mode', $mode);
    $smarty->display('modules/Administration/templates/RepairUtfEncodingStatus.tpl');

    return;
}


// the initial settings for the template variables to fill
$wasRepaired           = '';
$config_file_ready      = false;
$lbl_rebuild_config     = $mod_strings['LBL_REBUILD_CONFIG'];
$btn_rebuild_config     = $mod_strings['BTN_REBUILD_CONFIG'];
$disable_config_rebuild = 'disabled="disabled"';

// only do the rebuild if config file checks out and user has posted back
if (!empty($_POST['perform_rebuild_utf_encoding'])) {
    $data = [];

    $syncRun = !empty($_POST['syncRun']);
    $keepTrackingTables = !empty($_POST['keepTrackingTables']);

    $repairFrom = $_POST['repairFrom'] ?? null;
    if ($repairFrom === null) {
        $repairFrom = NormalizeRecords::UTF_REPAIR_FROM;
    } elseif (NormalizeRecords::isValidRepairFrom($repairFrom)) {
        $repairFrom .= ' 00:00:01';
    } elseif (!NormalizeRecords::isValidRepairFrom($repairFrom)) {
        $smarty = new Sugar_Smarty();
        $smarty->assign('MOD', $mod_strings);
        $smarty->assign('invalid_repair_from', true);
        $smarty->display('modules/Administration/templates/RepairUtfEncoding.tpl');
        return;
    }

    $data['repair_from'] = $repairFrom;

    if ($syncRun === true) {
        $smarty = new Sugar_Smarty();
        $smarty->assign('MOD', $mod_strings);
        $smarty->assign('status', 'in_progress');
        $smarty->assign('mode', NormalizeRecords::EXECUTION_MODE_SYNC);
        $smarty->display('modules/Administration/templates/RepairUtfEncodingSyncStatus.tpl');
        ob_flush();
        flush();

        $normalize = new NormalizeRecords();
        $result = $normalize->runAll($data, true);

        echo '<h3 class="pt-0">' . $mod_strings['LBL_RESULT'] . '</h3>';

        if ($result['success'] === true) {
            echo '<div>' . $mod_strings['LBL_NORMALIZE_SUCCESS']. '</div>';
        } else {
            echo '<div>' . $mod_strings['LBL_NORMALIZE_FAILURE']. '</div>';
        }

        if (empty($result['messages'])) {
            return;
        }

        foreach ($result['messages'] as $message) {
            echo '<div>' . $message . '</div>';
        }

        return;
    }


    if (!empty($keepTrackingTables)){
        $data['keepTracking'] = true;
    }

    require_once __DIR__ . '/../../include/Services/NormalizeRecords/NormalizeRecordsSchedulerJob.php';
    NormalizeRecordsSchedulerJob::scheduleJob($data);

    NormalizeRecords::setRepairStatus(NormalizeRecords::REPAIR_STATUS_IN_PROGRESS);
    NormalizeRecords::setExecutionMode(NormalizeRecords::EXECUTION_MODE_SYNC);

    $smarty = new Sugar_Smarty();
    $smarty->assign('MOD', $mod_strings);
    $smarty->assign('status', 'in_progress');
    $smarty->assign('mode', NormalizeRecords::EXECUTION_MODE_ASYNC);
    $smarty->display('modules/Administration/templates/RepairUtfEncodingStatus.tpl');

    return;
}

if (!isset($_REQUEST['perform_rebuild_utf_encoding'])) {
    $smarty = new Sugar_Smarty();
    $smarty->assign('MOD', $mod_strings);
    $smarty->assign('invalid_repair_from', false);
    $smarty->display('modules/Administration/templates/RepairUtfEncoding.tpl');
}
