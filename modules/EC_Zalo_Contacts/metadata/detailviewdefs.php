<?php
$module_name = 'EC_Zalo_Contacts';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                // 'DELETE',
                // 'DUPLICATE',
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
                array(
                    'name' => 'zalo_id',
                    'label' => 'LBL_ZALO_ID',
                    'customCode' => '{$CUSTOM_ZALO_ID}',
                ),
            ),

            array(
                'alias',
                array(
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                    'customCode' => '{$CUSTOM_STATUS}',
                ),
            ),

            array(
                'contact_name',
                array(
                    'name' => 'is_follower',
                    'label' => 'LBL_IS_FOLLOWER',
                    'customCode' => '{$CUSTOM_FOLLOWER}',
                ),
            ),

            array(
                array(
                    'name' => 'avatar',
                    'label' => 'LBL_AVATAR',
                    'customCode' => '{$CUSTOM_AVATAR}',
                ),
                array(
                    'name' => 'address',
                    'label' => 'LBL_ADDRESS',
                    'customCode' => '{$CUSTOM_ADDRESS}',
                ),
            ),

            array(
                'last_interaction',
                'birth_date',
            ),

            array(
                array(
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ),
                array(
                    'name' => 'tags',
                    'label' => 'LBL_TAGS',
                    'customCode' => '{$CUSTOM_TAGS}',
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
    )
);
