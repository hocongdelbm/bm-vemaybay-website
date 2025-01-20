<?php
$module_name = 'EC_Outbound_Phone';
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
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                ),
            ),
            array(
                array(
                    'name' => 'proxy',
                    'label' => 'LBL_PROXY',
                ),
                array(
                    'name' => 'only_inbound',
                    'label' => 'LBL_ONLY_INBOUND',
                ),
            ),
            array(
                array(
                    'name' => 'network_provider',
                    'label' => 'LBL_NETWORK_PROVIDER',
                ),
                array(
                    'name' => 'brand_name',
                    'label' => 'LBL_BRAND_NAME',
                ),
            ),
            array(
                array(
                    'name' => 'website',
                    'label' => 'LBL_WEBSITE',
                ),
                array(
                    'name' => 'label',
                    'label' => 'LBL_LABEL',
                ),
            ),
            array(
                'description',
                'assigned_user_name',
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
