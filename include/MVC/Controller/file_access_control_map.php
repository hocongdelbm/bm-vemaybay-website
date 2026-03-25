<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


/*********************************************************************************

 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
$file_access_control_map = array(
    'modules' => array(
        'administration' => array(
            'actions' => array(
                'backups',
                'updater',
            ),
            'links'    => array(
                'update',
                'backup_management',
                'upgrade_wizard',
                'moduleBuilder',
            ),
        ),
        'upgradewizard' => array(
            'actions' => array(
                'index',
            ),
        ),
        'modulebuilder' => array(
            'actions' => array(
                'index' => array('params' => array('type' => array('mb'))),
            ),
        ),
    )
);
