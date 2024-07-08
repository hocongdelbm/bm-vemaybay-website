<?php

$module_name = 'EC_Customer';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                // 'DUPLICATE',
                // 'DELETE',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
          array(
              'file' => 'themes/SuiteP/js/reset.js',
          ),
      ),
    ),

    'panels' =>
    array(
      'default' => array(
        array(
          array(
            'name' => 'name',
            'label' => 'LBL_NAME',
            'displayParams' => array(
              'required' => true,
            ),
          ),
          array(
            'name' => 'phone',
            'label' => 'LBL_PHONE',
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
            'name' => 'gender',
            'label' => 'LBL_GENDER',
          ),
          array(
            'name' => 'source',
            'label' => 'LBL_SOURCE',
          ),
        ),
        array(
          array(
            'name' => 'type',
            'studio' => 'visible',
            'label' => 'LBL_TYPE',
          ),
          array(),
        ),
      ),
      'lbl_lineitems_panel' => array(
        array(
          array(
            'name' => 'line_items',
            'label' => 'LBL_LINE_ITEMS',
            'customCode' => '{$LINE_ITEMS}',
          ),
        )
      ),
    )
);
