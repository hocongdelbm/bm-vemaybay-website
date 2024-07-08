<?php

$viewdefs ['Notes'] =
array(
  'DetailView' =>
  array(
    'templateMeta' =>
    array(
      'maxColumns' => '2',
      'widths' =>
      array(
        array(
          'label' => '10',
          'field' => '30',
        ),
        array(
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => true,
      'tabDefs' =>
      array(
        'LBL_NOTE_INFORMATION' =>
        array(
          'newTab' => true,
          'panelDefault' => 'expanded',
        ),
        'LBL_PANEL_ASSIGNMENT' =>
        array(
          'newTab' => true,
          'panelDefault' => 'expanded',
        ),
      ),
    ),
    'panels' =>
    array(
      'lbl_note_information' =>
      array(
        array(
          'contact_name',
          array(
            'name' => 'parent_name',
            'customLabel' => '{sugar_translate label=\'LBL_MODULE_NAME\' module=$fields.parent_type.value}',
          ),
        ),
        array(
          array(
            'name' => 'name',
            'label' => 'LBL_SUBJECT',
          ),
        ),
        array(
          array(
            'name' => 'filename',
          ),
        ),
        array(
          array(
            'name' => 'description',
            'label' => 'LBL_NOTE_STATUS',
          ),
        ),
        array(
          'assigned_user_name',
        ),
      ),
      'LBL_PANEL_ASSIGNMENT' =>
      array(
        array(
          array(
            'name' => 'date_modified',
            'label' => 'LBL_DATE_MODIFIED',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
          ),
          array(
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
          ),
        ),
      ),
    ),
  ),
);
