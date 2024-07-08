<?php

$module_name = 'EC_Location';
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

    'panels' =>
        array(
            'default' =>
                array(
                    array(
                        'name',
                        array (
                            'name' => 'company',
                            'studio' => 'visible',
                            'label' => 'LBL_COMPANY',
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
                            'name' => 'is_display',
                            'label' => 'LBL_IS_DISPLAY',
                        ),
                        array(
                            'name' => 'description',
                            'label' => 'LBL_DESCRIPTION',
                        ),
                    ),
                )
        )
);
