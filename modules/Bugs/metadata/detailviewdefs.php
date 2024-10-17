<?php

$viewdefs['Bugs']['DetailView'] = array(
  'templateMeta' => array(
    'form' => array('buttons' => array('EDIT', 'DUPLICATE', 'DELETE', 'FIND_DUPLICATES',)),
    'maxColumns' => '2',
    'widths' => array(
      array('label' => '10', 'field' => '30'),
      array('label' => '10', 'field' => '30')
    ),
  ),

  'panels' => array(
    'lbl_bug_information' => array(
      array(
        'bug_number',
        'priority',
      ),

      array(
        array(
          'name' => 'name',
          'label' => 'LBL_SUBJECT',
        ),
        'status',
      ),

      array(
        'type',
        'source',
      ),

      array(
        'product_category',
        'resolution',
      ),

      array(
        'description',
      ),

      array(
        'work_log',
      ),
      array(
        'name' => 'assigned_user_name',
        'label' => 'LBL_ASSIGNED_TO_NAME',
      ),
      array(
        array(
          'name' => 'date_modified',
          'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
          'label' => 'LBL_DATE_MODIFIED',
        ),
        array(
          'name' => 'date_entered',
          'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
          'label' => 'LBL_DATE_ENTERED',
        ),
      ),
    ),
  )
);
