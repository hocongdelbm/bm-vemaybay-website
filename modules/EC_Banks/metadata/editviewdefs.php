<?php
$module_name = 'EC_Banks';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
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
                    'displayParams' => array(
                        'cols' => 45,
                        'rows' => 2,
                    ),
                ),
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' => array(
                        'cols' => 45,
                        'rows' => 2,
                    ),
                ),
            ),

            array(
                array(
                    'name' => 'unfollow',
                    'label' => 'LBL_UNFOLLOW',
                ),
            ),
        ),
    ),
);
