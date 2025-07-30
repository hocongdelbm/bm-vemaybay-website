<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


global $current_user;

if (!is_admin($current_user)) {
    die('Unauthorized Access. Aborting.');
}

//find  modules that have a full-text index and rebuild it.
global $beanFiles;
foreach ($beanFiles as $beanname=>$beanpath) {
    require_once($beanpath);
    $focus= new $beanname();

    //skips beans based on same tables. user, employee and group are an example.
    if (empty($focus->table_name) || isset($processed_tables[$focus->table_name])) {
        continue;
    } else {
        $processed_tables[$focus->table_name]=$focus->table_name;
    }

    //skips beans based on same tables. user, employee and group are an example.
    if (empty($focus->table_name) || isset($processed_tables[$focus->table_name])) {
        continue;
    }
    $processed_tables[$focus->table_name]=$focus->table_name;

    if (!empty($dictionary[$focus->object_name]['indices'])) {
        $indices=$dictionary[$focus->object_name]['indices'];
    } else {
        $indices=array();
    }

    //clean vardef definitions.. removed indexes not value for this dbtype.
    //set index name as the key.
    $var_indices=array();
    foreach ($indices as $definition) {
        //database helpers do not know how to handle full text indices
        if ($definition['type']=='fulltext') {
            if (isset($definition['db']) and $definition['db'] != DBManagerFactory::getInstance()->dbType) {
                continue;
            }

            echo "Rebuilding Index {$definition['name']} <BR/>";
            DBManagerFactory::getInstance()->query('alter index ' .$definition['name'] . " REBUILD");
        }
    }
}
