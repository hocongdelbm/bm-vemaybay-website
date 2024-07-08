<?php


$module_name = 'EC_ChiTietTaiKhoan';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' =>
    array(
        'default' =>
        array(
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
                array(
                    'name' => 'sotaikhoan',
                    'label' => 'LBL_SOTAIKHOAN',
                ),
            ),
            array(
                array(
                    'name' => 'dunodau',
                    'label' => 'LBL_DUNODAU',
                ),
                array(
                    'name' => 'ducodau',
                    'label' => 'LBL_DUCODAU',
                ),
            ),
            array (
                array (
                  'name' => 'company',
                  'studio' => 'visible',
                  'label' => 'LBL_COMPANY',
                ),
                array (
                  'name' => 'location',
                  'studio' => 'visible',
                  'label' => 'LBL_LOCATION',
                ),
              ),
            array(
                array(
                    'name' => 'parent_name',
                    'studio' => 'visible',
                    'label' => 'LBL_FLEX_RELATE',
                ),
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
            ),
            array(
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array(
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
            ),
        ),
    )
);
