<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$_object_name = '<_object_name>';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'form' => array(),
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),
    'panels' => array(
        'default' => array(

            array(

                array(
                    'name' => 'document_name',
                    'label' => 'LBL_DOC_NAME',
                ),
                array(
                    'name' => 'uploadfile',
                    'displayParams' => array('link' => 'uploadfile', 'id' => 'id'),
                ),

            ),
            array(
                'category_id',
                'subcategory_id',
            ),

            array(

                'status',

            ),
            array(
                'active_date',
                'exp_date',
            ),

            array(
                array('name' => 'assigned_user_name', 'label' => 'LBL_ASSIGNED_TO'),
            ),

            array(

                array(
                    'name' => 'description',
                    'label' => 'LBL_DOC_DESCRIPTION',
                ),
            ),
        ),
    )
);
