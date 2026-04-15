<?php
$module_name = 'EC_Zalo_Apps';
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
                'id',
                'name',
            ),
            array(
                'oa_name',
                'secret_key',
            ),
            array(
                'code_verifier',
                'code_challenge',
            ),
            array(
                'description',
                array(),
            ),
        ),
    ),
);
