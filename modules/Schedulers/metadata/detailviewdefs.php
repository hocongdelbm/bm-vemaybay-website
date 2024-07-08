<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$viewdefs['Schedulers']['DetailView'] = array(
    'templateMeta' => array(
                            'maxColumns' => '2',
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'),
                                            array('label' => '10', 'field' => '30'),
                                           ),
                            'includes'=> array(
                                            array('file'=>'modules/Schedulers/Schedulers.js'),
                                         ),
                           ),

    'panels' => array(
        'default' => array(
            array('name', 'status'),
            array('date_time_start',
                array(
                    'name' => 'time_from',
                    'customCode' => '{$fields.time_from.value|default:$MOD.LBL_ALWAYS}')),
            array('date_time_end',
                array(
                    'name' => 'time_to',
                    'customCode' => '{$fields.time_to.value|default:$MOD.LBL_ALWAYS}')),
            array(
                array(
                    'name' => 'last_run',
                    'customCode' => '{$fields.last_run.value|default:$MOD.LBL_NEVER}'),
                array(
                    'name' => 'job_interval',
                    'customCode' => '{$JOB_INTERVAL}'),
                ),
            array('catch_up', 'job'),
            array(
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}&nbsp;'),
                array(
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}&nbsp;')
           ))
    )

);
