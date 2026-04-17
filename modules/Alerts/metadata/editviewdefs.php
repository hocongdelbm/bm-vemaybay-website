<?php
$viewdefs['Alerts'] = array(
    'EditView' => array(
        'templateMeta' => array(
            'form' => array(
                'buttons' => array(
                    'SAVE',
                    'CANCEL',
                ),
            ),
            'maxColumns' => '2',
            'widths' => array(
                array(
                    'label' => '10',
                    'field' => '30',
                ),
                array(
                    'label' => '10',
                    'field' => '30',
                ),
            ),
        ),
        'panels' => array(
            'LBL_ALERT_INFORMATION' => array(
                array(
                    array(
                        'name' => 'name',
                        'label' => 'LBL_NAME',
                        'displayParams' => array(
                            'required' => true,
                        ),
                    ),
                    array(
                        'name' => 'priority',
                        'label' => 'LBL_PRIORITY',
                    ),
                ),
                array(
                    array(
                        'name' => 'type',
                        'label' => 'LBL_TYPE',
                    ),
                    array(
                        'name' => 'assigned_user_name',
                        'label' => 'LBL_ASSIGNED_TO_NAME',
                        'customCode' => '{$EMPLOYEE_NAME}',
                    ),
                ),
                array(
                    array(
                        'name' => 'alert_photo',
                        'label' => 'LBL_ALERT_PHOTO',
                    ),
                    array(
                        'name' => 'filename',
                        'label' => 'LBL_FILENAME',
                    ),
                ),
                array(
                    array(
                        'name' => 'description',
                        'displayParams' => array(
                            'cols' => 32,
                            'rows' => 4,
                        ),
                        'label' => 'LBL_DESCRIPTION',
                    ),
                ),
            ),
        ),
    ),
);
