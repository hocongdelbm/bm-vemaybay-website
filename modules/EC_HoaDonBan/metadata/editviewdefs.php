<?php
$module_name = 'EC_HoaDonBan';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'SAVE',
                'CANCEL',
            ),
        ),
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
                'file' => 'themes/SuiteP/js/reset.js',
            )
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'sohoadon',
                    'label' => 'LBL_SOHOADON',
                ),
                array(
                    'name' => 'ngayhoadon',
                    'label' => 'LBL_NGAYHOADON',
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
                    'name' => 'lienhe',
                    'label' => 'LBL_LIENHE',
                ),
                array(
                    'name' => 'loaihoadon',
                    'label' => 'LBL_LOAIHOADON',
                ),
            ),
            array(
                array(
                    'name' => 'tencongty',
                    'label' => 'LBL_TENCONGTY',
                ),
                array(
                    'name' => 'kyhieuhd',
                    'label' => 'LBL_KYHIEUHD',
                ),
            ),
            array(
                array(
                    'name' => 'masothue',
                    'label' => 'LBL_MASOTHUE',
                    'customCode' => '{$CUSTOM_MST}'
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
            ),
            array(
                array(
                    'name' => 'email',
                    'label' => 'LBL_EMAIL',
                ),
                array(
                    'name' => 'hinhthuctt',
                    'label' => 'LBL_HINHTHUCTT',
                ),
            ),
            array(
                array(
                    'name' => 'identity_number',
                    'label' => 'LBL_IDENTITY_NUMBER',
                    'customCode' => '{$CUSTOM_ID_NUMBER}'
                ),
                array(
                    'name' => 'nganhang',
                    'label' => 'LBL_NGANHANG',
                ),
            ),
            array(
                array(),
                array(
                    'name' => 'sotaikhoan',
                    'label' => 'LBL_SOTAIKHOAN',
                ),
            ),
            array(
                array(
                    'name' => 'diachi',
                    'label' => 'LBL_DIACHI',
                    'displayParams' => array(
                        'rows' => 2,
                        'cols' => 32,
                    ),
                ),
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' => array(
                        'rows' => 2,
                        'cols' => 32,
                    ),
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
    ),

);
