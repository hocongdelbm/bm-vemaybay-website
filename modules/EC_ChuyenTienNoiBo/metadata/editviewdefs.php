<?php
$module_name = 'EC_ChuyenTienNoiBo';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array(
                'file' => 'custom/jqueryui/plugins/jquery.number.min.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/mcautocomplete.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/formatNumber.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/fromPopupReturn.js',
            ),
            array(
                'file' => 'modules/EC_ChuyenTienNoiBo/js/EC_ChuyenTienNoiBo.js',
            ),
        )
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'sotien',
                    'label' => 'LBL_SOTIEN',
                ),
                array(
                    'name' => 'loaitien',
                    'studio' => 'visible',
                    'label' => 'LBL_LOAITIEN',
                ),
            ),
            array(
                array(
                    'name' => 'tutknganhang',
                    'studio' => 'visible',
                    'label' => 'LBL_TUTKNGANHANG',
                    'customCode' => '{$TUTKNGANHANG}',
                ),
                array(
                    'name' => 'dentknganhang',
                    'studio' => 'visible',
                    'label' => 'LBL_DENTKNGANHANG',
                    'customCode' => '{$DENTKNGANHANG}',
                ),
            ),
            array(
                array(
                    'name' => 'ngaychungtu',
                    'label' => 'LBL_NGAYCHUNGTU',
                ),
                array(
                    'name' => 'ngayhachtoan',
                    'label' => 'LBL_NGAYHACHTOAN',
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
            ),
        ),
    ),
);
