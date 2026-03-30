<?php
$module_name = 'EC_Zalo';
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
                'name',
                'id',
            ),
            array(
                'oa_alias',
                'oa_type',
            ),
            array(
                'is_verified',
                'num_follower',
            ),
            array(
                'package_name',
                'package_valid_through_date',
            ),
            array(
                'linked_zca',
                'package_auto_renew_date'
            ),
            array(
                'avatar',
                'cover'
            ),
            array(
                'cate_name',
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
