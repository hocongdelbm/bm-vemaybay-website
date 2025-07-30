<?php
$module_name = 'EC_NhomTaiKhoan';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                array('customCode' => '{$CUSTOM_SAVE}'),
                'CANCEL',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array(
                'file' => 'modules/EC_NhomTaiKhoan/js/EC_NhomTaiKhoan.js',
            ),
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'manhom',
                    'label' => 'LBL_MANHOM',
                ),
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
            ),

            array(
                array(
                    'name' => 'tinhchat',
                    'studio' => 'visible',
                    'label' => 'LBL_TINHCHAT',
                ),
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' => array(
                        'cols' => 45,
                        'rows' => 2,
                    ),
                ),
            ),
        ),

        'lbl_chitiettheo_panel' => array(
            array(
                array(
                    'name' => 'chitiettheo',
                    'label' => 'LBL_CHITIETTHEO',
                    'customCode' => '{$ChiTietTheo}',
                ),
            ),
        ),
    ),
);
