<?php
$module_name = 'EC_TaiKhoan';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                array('customCode' => '{$CUSTOM_SAVE}'),
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
                'file' => 'modules/EC_TaiKhoan/js/EC_TaiKhoan.js',
            ),
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'sotaikhoan',
                    'label' => 'LBL_SOTAIKHOAN',
                ),
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
            ),

            array(
                array(
                    'name' => 'tentienganh',
                    'label' => 'LBL_TENTIENGANH',
                ),
                array(
                    'name' => 'taikhoantonghop',
                    'label' => 'LBL_TAIKHOANTONGHOP',
                    'customCode' => '{$TaiKhoanTongHop}',
                ),
            ),

            array(
                array(
                    'name' => 'nhomtaikhoan',
                    'label' => 'LBL_NHOMTAIKHOAN',
                    'customCode' => '{$NhomTaiKhoan}',
                ),
                array(
                    'name' => 'tinhchat',
                    'studio' => 'visible',
                    'label' => 'LBL_TINHCHAT',
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

        'lbl_panel1' => array(
            array(
                array(
                    'name' => 'chitiettheo',
                    'label' => 'LBL_CHITIETTHEO',
                ),
                array(
                    'name' => 'ngungtheodoi',
                    'label' => 'LBL_NGUNGTHEODOI',
                ),
            ),
        ),

        'lbl_chitiettheo_panel' => array(
            array(
                array(
                    'name' => 'doituong',
                    'label' => 'LBL_DOITUONG',
                    'customCode' => '{$DoiTuong}',
                ),
                array(
                    'name' => 'taphopchiphi',
                    'label' => 'LBL_TAPHOPCHIPHI',
                ),
            ),

            array(
                array(
                    'name' => 'hopdong',
                    'label' => 'LBL_HOPDONG',
                ),
                array(
                    'name' => 'vthh_ccdc',
                    'label' => 'LBL_VTHH_CCDC',
                ),
            ),

            array(
                array(
                    'name' => 'taikhoannganhang',
                    'label' => 'LBL_TAIKHOANNGANHANG',
                ),
                array(
                    'name' => 'khoanmucchiphi',
                    'label' => 'LBL_KHOANMUCCHIPHI',
                ),
            ),

            array(
                array(
                    'name' => 'theongoaite',
                    'label' => 'LBL_THEONGOAITE',
                ),
                array(
                    'name' => 'theophongban',
                    'label' => 'LBL_THEOPHONGBAN',
                ),
            ),

            array(
                array(
                    'name' => 'theothuchi',
                    'label' => 'LBL_THEOTHUCHI',
                ),
            ),
        ),
    ),
);
