<?php

$viewdefs['Alerts'] =
    array(
        'DetailView' =>
        array(
            'templateMeta' =>
            array(
                'form' =>
                array(
                    'buttons' =>
                    array(
                        // 0 => 'EDIT',
                        1 => 'DELETE',
                    ),
                ),
                'maxColumns' => '2',
                'widths' =>
                array(
                    0 =>
                    array(
                        'label' => '10',
                        'field' => '30',
                    ),
                    1 =>
                    array(
                        'label' => '10',
                        'field' => '30',
                    ),
                ),
            ),
            'panels' =>
            array(
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
                            'name' => 'filename',
                            'label' => 'LBL_FILENAME',
                            'customCode' => '{$CUS_ALERT_FILENAME}',
                        ),
                    ),
                    array(
                        array(
                            'name' => 'priority',
                            'label' => 'LBL_PRIORITY',
                        ),
                        array(
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO',
                        ),
                    ),
                    array(
                        array(
                            'name' => 'alert_photo',
                            'label' => 'LBL_ALERT_PHOTO',
                            'customCode' => '{$CUS_ALERT_PHOTO}',
                        ),
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
