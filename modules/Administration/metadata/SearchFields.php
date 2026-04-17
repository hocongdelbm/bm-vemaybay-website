<?php
$searchFields['Administration'] =
    array(
        'user_name' => array(
        'query_type'=>'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT users.id FROM users WHERE users.deleted=0 and users.user_name LIKE',
        'db_field'=>array('user_id')),
    );
