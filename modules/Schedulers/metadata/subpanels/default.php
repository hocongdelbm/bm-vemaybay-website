<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$subpanel_layout = array(
    'top_buttons' => array(
        /*array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Queues'),*/
    ),
    'where' => "",
    'fill_in_additional_fields' => true,
    'list_fields' => array(
        // 'mass_update' => array (),
        'id' => array(
            //		 'widget_class' => 'SubPanelDetailViewLink',
            'vname' => 'LBL_EXECUTE_TIME',
            'width' => '20%',
        ),
    ),
);
