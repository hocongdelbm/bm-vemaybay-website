<?php
$viewdefs['Users']['DetailView'] = array(
    'templateMeta' => array('maxColumns' => '2',
                            'widths' => array(
                                array('label' => '10', 'field' => '30'),
                                array('label' => '10', 'field' => '30')
                            ),
                            'form' => array(
                                'headerTpl'=>'modules/Users/tpls/DetailViewHeader.tpl',
                                'footerTpl'=>'modules/Users/tpls/DetailViewFooter.tpl',
                            ),
                      ),
    'panels' => array(
        'LBL_USER_INFORMATION' => array(
            array('user_name',
                  array('name' => 'last_name',
                        'label' => 'LBL_LIST_NAME',
                  ),
            ),
            array('status',
                  '',
            ),
            array(array(
                      'name'=>'UserType',
                      'customCode'=>'{$USER_TYPE_READONLY}',
                  ),
            ),
        ),
    ),
);
