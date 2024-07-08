<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include 'include/modules.php';

global $mod_strings ;
$db = DBManagerFactory::getInstance();

$log = & $GLOBALS [ 'log' ] ;

$query = 'DELETE FROM relationships';
$db->query($query);

//clear cache before proceeding..
VardefManager::clearVardef();

// loop through all of the modules and create entries in the Relationships table (the relationships metadata) for every standard relationship, that is, relationships defined in the /modules/<module>/vardefs.php
// SugarBean::createRelationshipMeta just takes the relationship definition in a file and inserts it as is into the Relationships table
// It does not override or recreate existing relationships
foreach ($GLOBALS['beanFiles'] as $bean => $file) {
    if (strlen($file) > 0 && file_exists($file)) {
        if (!class_exists($bean)) {
            require $file;
        }
        $focus = new $bean();
        if ($focus instanceof SugarBean) {
            $table_name = $focus->table_name;
            $empty = array();
            if (empty($_REQUEST ['silent'])) {
                echo $mod_strings ['LBL_REBUILD_REL_PROC_META'] . $focus->table_name . '...';
            }
            SugarBean::createRelationshipMeta($focus->getObjectName(), $db, $table_name, $empty, $focus->module_dir);
            SugarBean::createRelationshipMeta(
                $focus->getObjectName(),
                $db,
                $table_name,
                $empty,
                $focus->module_dir,
                true
            );
            if (empty($_REQUEST ['silent'])) {
                echo $mod_strings ['LBL_DONE'] . '<br>';
            }
        }
    }
}

// finally, whip through the list of relationships defined in TableDictionary.php, that is all the relationships in the metadata directory, and install those
$dictionary = array();
require 'modules/TableDictionary.php';
//for module installer in case we already loaded the table dictionary
if (file_exists('custom/application/Ext/TableDictionary/tabledictionary.ext.php')) {
    include 'custom/application/Ext/TableDictionary/tabledictionary.ext.php';
}
$rel_dictionary = $dictionary;
foreach ($rel_dictionary as $rel_name => $rel_data) {
    $table = isset($rel_data ['table']) ? $rel_data ['table'] : '';

    if (empty($_REQUEST ['silent'])) {
        echo $mod_strings ['LBL_REBUILD_REL_PROC_C_META'] . $rel_name . '...';
    }
    SugarBean::createRelationshipMeta($rel_name, $db, $table, $rel_dictionary, '');
    if (empty($_REQUEST ['silent'])) {
        echo $mod_strings ['LBL_DONE'] . '<br>';
    }
}

//clean relationship cache..will be rebuilt upon first access.
if (empty($_REQUEST ['silent'])) {
    echo $mod_strings ['LBL_REBUILD_REL_DEL_CACHE'];
}
Relationship::delete_cache();

//////////////////////////////////////////////////////////////////////////////
// Remove the "Rebuild Relationships" red text message on admin login


if (empty($_REQUEST ['silent'])) {
    echo $mod_strings ['LBL_REBUILD_REL_UPD_WARNING'];
}

$rel = BeanFactory::newBean('Relationships');
Relationship::delete_cache();
$rel->build_relationship_cache();

// unset the session variable so it is not picked up in DisplayWarnings.php
if (isset($_SESSION ['rebuild_relationships'])) {
    unset($_SESSION ['rebuild_relationships']);
}

if (empty($_REQUEST ['silent'])) {
    echo $mod_strings ['LBL_DONE'];
}
