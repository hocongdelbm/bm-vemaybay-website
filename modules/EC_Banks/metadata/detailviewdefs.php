<?php
$module_name = 'EC_Banks';
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
                    'name' => 'short_name',
                    'label' => 'LBL_SHORT_NAME',
                ),
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
            ),

            array(
                array(
                    'name' => 'english_name',
                    'label' => 'LBL_ENGLISH_NAME',
                ),
                array(
                    'name' => 'image',
                    'label' => 'LBL_IMAGE',
                    'customCode' => '{$HINHANH}',
                ),
            ),

            array(
                array(
                    'name' => 'headquarters',
                    'studio' => 'visible',
                    'label' => 'LBL_HEADQUARTERS',
                ),
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
            ),

            array(
                array(
                    'name' => 'unfollow',
                    'label' => 'LBL_UNFOLLOW',
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
