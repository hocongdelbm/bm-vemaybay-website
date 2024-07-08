<?php
$module_name = 'EC_ChuyenTienNoiBo';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                array('customCode' => '{$GHISO}'),
                array('customCode' => '{$PRINT_VOUCHER}')
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array(
                'file' => 'modules/EC_ChuyenTienNoiBo/js/view.detail.js',
            ),
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                'name',
                'description',
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
                    'name' => 'tutknganhang',
                    'studio' => 'visible',
                    'label' => 'LBL_TUTKNGANHANG',
                ),
                array(
                    'name' => 'dentknganhang',
                    'studio' => 'visible',
                    'label' => 'LBL_DENTKNGANHANG',
                ),
            ),

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
                    'name' => 'tk_no',
                    'label' => 'LBL_TK_NO',
                ),
                array(
                    'name' => 'tk_co',
                    'label' => 'LBL_TK_CO',
                ),
            ),

            array(
                array(
                    'name' => 'mucthuchi',
                    'studio' => 'visible',
                    'label' => 'LBL_MUCTHUCHI',
                ),
                'assigned_user_name',
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
