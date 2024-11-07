<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/MySettings/TabController.php');
require_once('include/SugarFields/SugarFieldHandler.php');

$tabs_def = urldecode(isset($_REQUEST['display_tabs_def']) ? $_REQUEST['display_tabs_def'] : '');
$DISPLAY_ARR = array();
parse_str($tabs_def, $DISPLAY_ARR);

//there was an issue where a non-admin user could use a proxy tool to intercept the save on their own Employee
//record and swap out their record_id with the admin employee_id which would cause the email address
//of the non-admin user to be associated with the admin user thereby allowing the non-admin to reset the password
//of the admin user.
if (isset($_POST['record']) && !is_admin($GLOBALS['current_user']) && !$GLOBALS['current_user']->isAdminForModule('Employees') && ($_POST['record'] != $GLOBALS['current_user']->id)) {
    sugar_die("Unauthorized access to administration.");
} elseif (!isset($_POST['record']) && !is_admin($GLOBALS['current_user']) && !$GLOBALS['current_user']->isAdminForModule('Employees')) {
    sugar_die("Unauthorized access to user administration.");
}

$focus = BeanFactory::newBean('Employees');

$focus->retrieve($_POST['record']);

//rrs bug: 30035 - I am not sure how this ever worked b/c old_reports_to_id was not populated.
$old_reports_to_id = $focus->reports_to_id;

populateFromRow($focus, $_POST);

$focus->save();
$return_id = $focus->id;

// save work history 
save_list_work_history();

if (isset($_POST['return_module']) && $_POST['return_module'] != "") {
    $return_module = $_POST['return_module'];
} else {
    $return_module = "Employees";
}
if (isset($_POST['return_action']) && $_POST['return_action'] != "") {
    $return_action = $_POST['return_action'];
} else {
    $return_action = "DetailView";
}
if (isset($_POST['return_id']) && $_POST['return_id'] != "") {
    $return_id = $_POST['return_id'];
}

$GLOBALS['log']->debug("Saved record with id of " . $return_id);

header("Location: index.php?action=$return_action&module=$return_module&record=$return_id");
function populateFromRow(&$focus, $row)
{
    //only employee specific field values need to be copied.
    $e_fields = array (
		'employee_status', 'first_name', 'last_name', 'reports_to_id',
		'description', 'phone_home', 'phone_mobile', 'phone_work', 'phone_other',
		'phone_fax', 'address_street', 'address_city', 'address_state', 'address_country',
		'address_country', 'address_postalcode', 'messenger_id', 'messenger_type', 
		'basic_salary', 'efficient_wage', 'gas_allowance', 'lunch_allowance', 'tele_allowance',
		'responsible_allowance', 'seniority_allowance', 'other_allowance1', 'other_allowance2',
		'workday', 'leaveday', 'target_month', 'target_quarter', 'target_year', 
		'start_working_date', 'remain_leave_days', 'employee_type', 'leader_id', 'init_exp_mark'
	);
    if (is_admin($GLOBALS['current_user']) || $GLOBALS['current_user']->title == 'QuanLy') {
        $e_fields = array_merge($e_fields, array('title', 'department', 'employee_status'));
    }
    // Also add custom fields
    $sfh = new SugarFieldHandler();
    foreach ($focus->field_defs as $fieldName => $field) {
        if (isset($field['source']) && $field['source'] == 'custom_fields') {
            $type = !empty($field['custom_type']) ? $field['custom_type'] : $field['type'];
            $sf = $sfh->getSugarField($type);
            if ($sf != null) {
                $sf->save($focus, $_POST, $fieldName, $field, '');
            } else {
                $GLOBALS['log']->fatal("Field '$fieldName' does not have a SugarField handler");
            }
        }
    }
    $nullvalue = '';
    foreach ($e_fields as $field) {
        $rfield = $field; // fetch returns it in lowercase only
        if (isset($row[$rfield])) {
            $focus->$field = $row[$rfield];
        }
    }
}

/**
 * Save list work history
 */
function save_list_work_history()
{
    global $current_user;

    $count = count($_POST['ct_date_start']);

    for ($i = 0; $i < $count; $i++) {
        // bổ sung ngày kết thúc cho các dòng khác dòng cuối
        if ($i < ($count - 1) && empty($_POST['ct_date_end'][$i])) {
            $_POST['ct_date_end'][$i] = date('d-m-Y', strtotime('-1 day', strtotime($_POST['ct_date_start'][$i + 1])));
        }
        $detail = new EC_WorkHistory();
        $detail->id = $_POST['ct_detail_id'][$i];
        $detail->name = $_POST['last_name'] . ' ' .  $_POST['first_name'];
        $detail->deleted =  $_POST['ct_deleted'][$i];
        $detail->assigned_user_id = $_POST['record'];

        $date_start = $_POST['ct_date_start'][$i] ? date("d-m-Y", strtotime(str_replace('/', '-', $_POST['ct_date_start'][$i]))) : '';
        $date_end = $_POST['ct_date_end'][$i] ? date("d-m-Y", strtotime(str_replace('/', '-', $_POST['ct_date_end'][$i]))) : '';
        $detail->date_start = $date_start;
        $detail->date_end = $date_end;
        $detail->with_salary = $_POST['ct_with_salary'][$i];
        $detail->status = $_POST['ct_status'][$i];
        $detail->description = $_POST['ct_description'][$i];

        if ($detail->deleted == 1) {
            $detail->mark_deleted($detail->id);
        } else {
            if (trim($detail->date_start) != '') {
                $detail->save();
            }
        }
    }
}
