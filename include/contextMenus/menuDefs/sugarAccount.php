<?php


global $app_strings;

$menuDef['sugarAccount'] = array(
      array(
            'text' => 'LBL_ADD_TO_FAVORITES',
            'action' => 'SUGAR.contextMenu.actions.addToFavorites'
      ),
      array(
            'text' => 'LBL_CREATE_NOTE',
            'action' => 'SUGAR.contextMenu.actions.createNote',
            'module' => 'Notes',
            'aclAction' => 'edit'
      ),
      array(
            'text' => 'LBL_CREATE_TASK',
            'action' => 'SUGAR.contextMenu.actions.createTask',
            'module' => 'Tasks',
            'aclAction' => 'edit'
      ),
      array(
            'text' => 'LBL_CREATE_CONTACT',
            'action' => 'SUGAR.contextMenu.actions.createContact',
            'module' => 'Contacts',
            'aclAction' => 'edit'
      ),
      array(
            'text' => 'LBL_CREATE_OPPORTUNITY',
            'action' => 'SUGAR.contextMenu.actions.createOpportunity',
            'module' => 'Opportunties',
            'aclAction' => 'edit'
      ),
      array(
            'text' => 'LBL_CREATE_CASE',
            'action' => 'SUGAR.contextMenu.actions.createCase',
            'module' => 'Cases',
            'aclAction' => 'edit'
      ),
      array(
            'text' => 'LBL_SCHEDULE_MEETING',
            'action' => 'SUGAR.contextMenu.actions.scheduleMeeting',
            'module' => 'Meetings',
            'aclAction' => 'edit'
      ),
      array(
            'text' => 'LBL_SCHEDULE_CALL',
            'action' => 'SUGAR.contextMenu.actions.scheduleCall',
            'module' => 'Calls',
            'aclAction' => 'edit'
      ),
);
