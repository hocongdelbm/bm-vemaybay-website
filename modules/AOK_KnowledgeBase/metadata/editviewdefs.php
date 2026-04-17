<?php
if (!defined('sugarEntry')) {
  define('sugarEntry', true);
}

$module_name = 'AOK_KnowledgeBase';
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
        'includes' =>
        array(
          0 =>
          array(
            'file' => 'include/javascript/tiny_mce/tiny_mce.js',
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
          ),
          array(
            array(
              'name' => 'status',
              'studio' => 'visible',
              'label' => 'LBL_STATUS',
            ),
            array(
              'name' => 'revision',
              'label' => 'LBL_REVISION',
            ),
          ),
          array(
            0 => 'description',
          ),
          array(
            'photo',
            'photo_sub',
          ),
          array(
            array(
              'name' => 'additional_info',
              'comment' => 'Full text of the note',
              'studio' => 'visible',
              'label' => 'LBL_ADDITIONAL_INFO',
            ),
          ),
          array(
            array(
              'name' => 'author',
              'studio' => 'visible',
              'label' => 'LBL_AUTHOR',
            ),
          ),
          array(
            array(
              'name' => 'approver',
              'studio' => 'visible',
              'label' => 'LBL_APPROVER',
            ),
          ),
        ),
      ),
    ),
  );
