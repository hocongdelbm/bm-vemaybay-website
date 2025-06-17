<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (empty($_REQUEST['record'])) {
    sugar_die('No record provided.');
}

$focus = BeanFactory::newBean('MergeRecords');
$focus->load_merge_bean($_REQUEST['merge_module'], true, $_REQUEST['record']);
if (empty($focus->merge_bean->id)) {
    sugar_die('No record provided.');
}
if (empty($dictionary[$focus->merge_bean->object_name]['duplicate_merge'])) {
    sugar_die('You do not have access to merge this module.');
}
if (!$focus->merge_bean->ACLAccess('edit')) {
    sugar_die('You do not have access to merge this module.');
}

if (empty($_REQUEST['merged_ids'])) {
    sugar_die('No merge records provided.');
}
require_once $focus->merge_bean_file_path;

foreach ($_REQUEST['merged_ids'] as $mergeId) {
    $mergeSource = new $focus->merge_bean_class();
    if (!$mergeSource->retrieve($mergeId)) {
        continue;
    }
    if (!$mergeSource->ACLAccess('edit')) {
        sugar_die('You do not have access to merge this module.');
    }
    if (!$mergeSource->ACLAccess('delete')) {
        sugar_die('You do not have access to merge this module.');
    }
}

foreach ($focus->merge_bean->column_fields as $field) {
    if (isset($_POST[$field])) {
        $value = $_POST[$field];
        if (is_array($value) && !empty($focus->merge_bean->field_defs[$field]['isMultiSelect'])) {
            if (empty($value[0])) {
                unset($value[0]);
            }
            $value = encodeMultienumValue($value);
        }
        $focus->merge_bean->$field = $value;
    } elseif (isset($focus->merge_bean->field_name_map[$field]['type']) && $focus->merge_bean->field_name_map[$field]['type'] == 'bool') {
        $focus->merge_bean->$field = 0;
    }
}

foreach ($focus->merge_bean->additional_column_fields as $field) {
    if (isset($_POST[$field])) {
        $value = $_POST[$field];
        if (is_array($value) && !empty($focus->merge_bean->field_defs[$field]->properties['isMultiSelect'])) {
            if (empty($value[0])) {
                unset($value[0]);
            }
            $value = encodeMultienumValue($value);
        }
        $focus->merge_bean->$field = $value;
    }
}

global $check_notify;

$_REQUEST['useEmailWidget'] = true;
if (isset($_POST['date_entered'])) {
    // set this to true so we won't unset date_entered when saving
    $focus->merge_bean->update_date_entered = true;
}
$focus->merge_bean->save($check_notify);
unset($_REQUEST['useEmailWidget']);

$return_id = $focus->merge_bean->id;
$return_module = $focus->merge_module;
$return_action = 'DetailView';

//handle related data.

$linked_fields = $focus->merge_bean->get_linked_fields();

$exclude = explode(',', $_REQUEST['merged_links']);

if (is_array($_POST['merged_ids'])) {
    foreach ($_POST['merged_ids'] as $id) {
        require_once $focus->merge_bean_file_path;
        $mergeSource = new $focus->merge_bean_class();
        if (!$mergeSource->retrieve($id)) {
            continue;
        }
        foreach ($linked_fields as $name => $properties) {
            if ($properties['name'] == 'modified_user_link' || $properties['name'] == 'created_by_link' || in_array($properties['name'], $exclude)) {
                continue;
            }
            if (isset($properties['duplicate_merge']) &&
                ($properties['duplicate_merge'] == 'disabled' ||
                    $properties['duplicate_merge'] == 'false' ||
                    $properties['name'] == 'assigned_user_link')
            ) {
                continue;
            }
            if ($name == 'accounts' && $focus->merge_bean->module_dir == 'Opportunities') {
                continue;
            }

            if ($mergeSource->load_relationship($name)) {
                //check to see if loaded relationship is with email address
                $relName = $mergeSource->$name->getRelatedModuleName();
                if (!empty($relName) && strtolower($relName) == 'emailaddresses') {
                    //handle email address merge
                    handleEmailMerge($focus, $name, $mergeSource->$name->get());
                } else {
                    $data = $mergeSource->$name->get();
                    if (is_array($data) && $focus->merge_bean->load_relationship($name)) {
                        foreach ($data as $related_id) {
                            //add to primary bean
                            $focus->merge_bean->$name->add($related_id);
                        }
                    }
                }
            }
        }
        //END Bug #13826
        //delete the child bean, this action will cascade into related data too.
        $mergeSource->mark_deleted($mergeSource->id);
    }
}
$GLOBALS['log']->debug('Merged record with id of '.$return_id);

//do not redirect if noRedirect flag is set.  This is mostly used by Unit tests
if (empty($_REQUEST['noRedirect'])) {
    header("Location: index.php?action=$return_action&module=$return_module&record=$return_id");
}

/**
 * This function will compare the email addresses to be merged and only add the email id's
 * of the email addresses that are not duplicates.
 *
 * @param MergeRecord $focus - Merge Bean
 * @param string      $name  name of relationship (email_addresses)
 * @param array       $data  array of email id's that will be merged into existing bean.
 */
function handleEmailMerge($focus, $name, $data)
{
    $mrgArray = array();
    //get the email id's to merge
    $existingData = $data;

    //make sure id's to merge exist and are in array format

    //get the existing email id's
    $focus->merge_bean->load_relationship($name);
    $exData = $focus->merge_bean->$name->get();

    if (!is_array($existingData) || empty($existingData)) {
        return;
    }
    //query email and retrieve existing email address
    $exEmailQuery = 'Select id, email_address from email_addresses where id in (';
    $first = true;
    foreach ($exData as $id) {
        if ($first) {
            $exEmailQuery .= " '$id' ";
            $first = false;
        } else {
            $exEmailQuery .= ", '$id' ";
            $first = false;
        }
    }
    $exEmailQuery .= ')';

    $exResult = $focus->merge_bean->db->query($exEmailQuery);
    $existingEmails = array();
    while (($row = $focus->merge_bean->db->fetchByAssoc($exResult)) != null) {
        $existingEmails[$row['id']] = $row['email_address'];
    }

    //query email and retrieve email address to be linked.
    $newEmailQuery = 'Select id, email_address from email_addresses where id in (';
    $first = true;
    foreach ($existingData as $id) {
        if ($first) {
            $newEmailQuery .= " '$id' ";
            $first = false;
        } else {
            $newEmailQuery .= ", '$id' ";
            $first = false;
        }
    }
    $newEmailQuery .= ')';

    $newResult = $focus->merge_bean->db->query($newEmailQuery);
    $newEmails = array();
    while (($row = $focus->merge_bean->db->fetchByAssoc($newResult)) != null) {
        $newEmails[$row['id']] = $row['email_address'];
    }

    //compare the two arrays and remove duplicates
    foreach ($newEmails as $k => $n) {
        if (!in_array($n, $existingEmails)) {
            $mrgArray[$k] = $n;
        }
    }

    //add email id's.
    foreach ($mrgArray as $related_id => $related_val) {
        //add to primary bean
        $focus->merge_bean->$name->add($related_id);
    }
}
