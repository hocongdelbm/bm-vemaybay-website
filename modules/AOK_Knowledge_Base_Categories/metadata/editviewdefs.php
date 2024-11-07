<?php
$module_name = 'AOK_Knowledge_Base_Categories';
$viewdefs[$module_name] =
  array(
    'EditView' =>
    array(
      'templateMeta' =>
      array(
        'maxColumns' => '2',
        'widths' =>
        array(
          0 =>
          array(
            'label' => '10',
            'field' => '30',
          ),
          1 =>
          array(
            'label' => '10',
            'field' => '30',
          ),
        ),
        'useTabs' => false,
        'tabDefs' =>
        array(
          'DEFAULT' =>
          array(
            'newTab' => false,
            'panelDefault' => 'expanded',
          ),
        ),
        'syncDetailEditViews' => true,
      ),
      'panels' =>
      array(
        'default' =>
        array(
          array(
            0 => 'name',
            1 => '',
          ),
          array(
            0 => 'description',
          ),
        ),
      ),
    ),
  );
