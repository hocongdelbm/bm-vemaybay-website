<?php
$module_name = 'EC_NhomTaiKhoan';
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

        'lbl_chitiettheo_panel' => array(
            array(
                array(
                    'name' => 'chitiettheo',
                    'label' => 'LBL_CHITIETTHEO',
                    'customCode' => '{$ChiTietTheo}',
                ),
            ),
        ),
    )
);
