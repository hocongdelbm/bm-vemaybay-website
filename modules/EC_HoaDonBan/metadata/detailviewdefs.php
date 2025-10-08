<?php

$module_name = 'EC_HoaDonBan';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                // 'DUPLICATE',
                // 'DELETE',
                // array('customCode' => '{$INHOADON}'),
                array('customCode' => '{$HUYHOADON}'),
                array('customCode' => '{$VIEW}'),
                array('customCode' => '{$HANDLE}'),
                array('customCode' => '{$REMOVE}'),
                array('customCode' => '{$SIGN}'),
                array('customCode' => '{$SIGNTP}'),
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array('file' => 'themes/SuiteP/js/reset.js'),
        ),
    ),

    'panels' =>
    array(
        'default' =>
        array(
            array(
                array(
                    'name' => 'lienhe',
                    'label' => 'LBL_LIENHE',
                ),
                array(
                    'name' => 'sohoadon',
                    'label' => 'LBL_SOHOADON',
                    'customCode' => '{$CUSTOM_SOHOADON}'
                ),
            ),
            array(
                array(
                    'name' => 'tencongty',
                    'label' => 'LBL_TENCONGTY',
                ),
                array(
                    'name' => 'ngayhoadon',
                    'label' => 'LBL_NGAYHOADON',
                ),
            ),
            array(
                array(
                    'name' => 'masothue',
                    'label' => 'LBL_MASOTHUE',
                ),
                array(
                    'name' => 'loaihoadon',
                    'label' => 'LBL_LOAIHOADON',
                ),
            ),
            array(
                array(
                    'name' => 'diachi',
                    'label' => 'LBL_DIACHI',
                ),
                array(
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ),
            ),
            array(
                array(
                    'name' => 'email',
                    'label' => 'LBL_EMAIL',
                ),
                array(
                    'name' => 'tinhtrang',
                    'label' => 'LBL_TINHTRANG',
                )
            ),
            array(
                array(
                    'name' => 'hinhthuctt',
                    'label' => 'LBL_HINHTHUCTT',
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_ID',
                ),
            ),
            array(
                array(
                    'name' => 'loaikh',
                    'label' => 'LBL_LOAIKH',
                ),
                array(
                    'name' => 'company_unit',
                    'label' => 'LBL_COMPANY_UNIT',
                ),
            ),
            array(
                array(
                    'name' => 'nganhang',
                    'label' => 'LBL_NGANHANG',
                ),
                array(
                    'name' => 'sotaikhoan',
                    'label' => 'LBL_SOTAIKHOAN',
                )
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

        'LBL_LINEITEM_PANEL' => array(
            array(
                array(
                    'name' => 'line_items',
                    'label' => 'LBL_LINE_ITEMS',
                    'customCode' => '{$LINE_ITEMS}',
                ),
            ),
        ),
    )
);
