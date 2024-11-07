<?php

$module_name = 'EC_LoaiChungTu';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
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
                    'name' => 'maloai',
                    'label' => 'LBL_MALOAI',
                ),
            ),
            array(
                array(
                    'name' => 'taikhoanno',
                    'label' => 'LBL_TAIKHOANNO',
                    'customCode' => '{$TAIKHOANNO}',
                ),
                array(
                    'name' => 'taikhoanco',
                    'label' => 'LBL_TAIKHOANCO',
                    'customCode' => '{$TAIKHOANCO}',
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' => array(
                        'rows' => 2,
                        'cols' => 45,
                    ),
                ),
            ),
        ),
    ),
);
