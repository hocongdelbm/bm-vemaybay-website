<?php

$module_name = 'EC_LoaiChungTu';
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
                'name',
                array(
                    'name' => 'maloai',
                    'label' => 'LBL_MALOAI',
                ),
            ),
            array(
                array(
                    'name' => 'taikhoanno',
                    'label' => 'LBL_TAIKHOANNO',
                ),
                array(
                    'name' => 'taikhoanco',
                    'label' => 'LBL_TAIKHOANCO',
                ),
            ),
            array(
                'description',
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
    ),
);
