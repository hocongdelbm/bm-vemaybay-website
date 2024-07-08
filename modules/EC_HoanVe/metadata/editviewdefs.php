<?php

$module_name = 'EC_HoanVe';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' =>
        array(
            array(
                'file' => 'custom/jqueryui/plugins/formatNumber.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/fromPopupReturn.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/jquery.number.min.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/mcautocomplete.js',
            ),
            array(
                'file' => 'themes/SuiteP/js/reset.js',
            ),
            array(
                'file' => 'modules/EC_HoanVe/js/EC_HoanVe.js',
            ),
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'booking',
                    'studio' => 'visible',
                    'label' => 'LBL_BOOKING',
                    'displayParams' =>
                    array(
                        'initial_filter' => '&booking_status=8',
                    ),
                ),
                array(
                    'name' => 'ngaychungtu',
                    'label' => 'LBL_NGAYCHUNGTU',
                ),
            ),
            array(
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
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
                    'displayParams' => array(
                        'rows' => 2,
                        'cols' => 45,
                    ),
                ),
                array()
            ),
        ),
        'lbl_lineitems_panel' => array(
            array(
                array(
                    'name' => 'line_items',
                    'label' => 'LBL_LINE_ITEMS',
                    'customCode' => '{$LINE_ITEMS}',
                )
            ),
        ),
    ),

);
