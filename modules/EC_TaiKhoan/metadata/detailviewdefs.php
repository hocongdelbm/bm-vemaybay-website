<?php
$module_name = 'EC_TaiKhoan';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                'FIND_DUPLICATES',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'sotaikhoan',
                    'label' => 'LBL_SOTAIKHOAN',
                ),
                'name',
            ),

            array(
                array(
                    'name' => 'tentienganh',
                    'label' => 'LBL_TENTIENGANH',
                ),
                array(
                    'name' => 'taikhoantonghop',
                    'label' => 'LBL_TAIKHOANTONGHOP',
                ),
            ),

            array(
                array(
                    'name' => 'nhomtaikhoan',
                    'label' => 'LBL_NHOMTAIKHOAN',
                ),
                array(
                    'name' => 'tinhchat',
                    'studio' => 'visible',
                    'label' => 'LBL_TINHCHAT',
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
    )
);
