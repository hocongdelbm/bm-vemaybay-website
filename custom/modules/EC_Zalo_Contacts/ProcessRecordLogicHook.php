<?php
class ProcessRecordLogicHook {
    public function custom_column(SugarBean $bean, $event, $arguments) {
        if($bean->alias && !empty($bean->alias)) $bean->name = $bean->alias;
    }
}
