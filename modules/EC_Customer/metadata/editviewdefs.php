<?php
$module_name = 'EC_Customer';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' =>
        array(
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'displayParams' => array(
                        'required' => true,
                    ),
                ),
                array(
                    'name' => 'gender',
                    'label' => 'LBL_GENDER',
                ),
            ),
            array(
                array(
                    'name' => 'email',
                    'label' => 'LBL_EMAIL',
                ),
                array(
                    'name' => 'birthday',
                    'label' => 'LBL_BIRTHDAY',
                ),
            ),
            array(
                array(
                    'name' => 'source',
                    'label' => 'LBL_SOURCE',
                ),
                array(),
            ),
        ),
    )

);
