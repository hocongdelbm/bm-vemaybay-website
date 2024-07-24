<?php

$module_name = 'EC_HoanVe';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                array('customCode' => '{$PHIEUCHI}'),
                array('customCode' => '{$DOITT}'),
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' =>
        array(
            array(
                'file' => 'themes/SuiteP/js/reset.js',
            ),
            array(
                'file' => 'modules/EC_HoanVe/js/EC_HoanVe_DV.js',
            ),
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
                    'name' => 'ngaychungtu',
                    'label' => 'LBL_NGAYCHUNGTU',
                ),
            ),
            array(
                array(
                    'name' => 'booking',
                    'studio' => 'visible',
                    'label' => 'LBL_BOOKING',
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
                    'name' => 'tinhtrang',
                    'studio' => 'visible',
                    'label' => 'LBL_TINHTRANG',
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
            array(
                array(
                    'name' => 'assigned_user_name',
                    'studio' => 'visible',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
                array(),
            ),
        ),
        'lbl_lineitems_panel' =>
        array(
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
