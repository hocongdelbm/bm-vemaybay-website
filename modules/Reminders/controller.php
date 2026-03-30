<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
class RemindersController extends SugarController
{
    public function action_getInviteesPersonName()
    {
        $personModules = array('Users', 'Contacts');
        $ret = array();
        $invitees = $_REQUEST['invitees'];
        foreach ($invitees as $invitee) {
            if (!empty($invitee['personModule']) && !empty($invitee['personModuleId']) && in_array($invitee['personModule'], $personModules)) {
                if (empty($invitee['personName'])) {
                    $person = BeanFactory::getBean($invitee['personModule'], $invitee['personModuleId']);
                    if (empty($person->name)) {
                        continue;
                    }
                    $invitee['personName'] = $person->name;
                }
                $ret[] = $invitee;
            }
        }

        $inviteeJson = json_encode($ret);
        echo $inviteeJson;
        die();
    }

    public function action_getUserPreferencesForReminders()
    {
        echo Reminder::loadRemindersDefaultValuesDataJson();
        die();
    }
}
