<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class AssignToCreator
{
    function assign($bean, $event, $arguments)
    {
        global $current_user;

        // Chỉ áp dụng khi tạo mới và chưa có người được gán
        if (empty($bean->fetched_row) && empty($bean->assigned_user_id)) {
            $bean->assigned_user_id = $current_user->id;
        }
    }
}
