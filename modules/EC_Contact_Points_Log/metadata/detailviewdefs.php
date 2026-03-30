<?php
$module_name = 'EC_Contact_Points_Log';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                // 'EDIT',
                // 'DUPLICATE',
                // 'DELETE',
                // 'FIND_DUPLICATES',
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
                'name',
                'contact',
            ),
            array(
                'contact_phone',
                array(
                    'name' => 'parent_id',
                    'label' => 'LBL_PARENT_TYPE',
                    'customCode' => '<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record={$fields.parent_id.value}" target="_blank">Booking</a>'
                )
            ),
            array(
                'up',
                'down',
            ),
            array(
                'current_point',
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
        )
    )
);
