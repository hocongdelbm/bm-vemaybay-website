<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


/**
 * @var DirectoryIterator $node
 * @var User $current_user
 * @var SugarBean $bean
 * @var DBManager $db
 */
global $beanList, $current_user, $db, $mod_strings;
$validBeans = array();
if (!is_admin($current_user)) {
    sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
}
set_time_limit(3600);

foreach ($beanList as $moduleName => $className) {
    $bean = BeanFactory::getBean($moduleName);
    if (!($bean instanceof SugarBean)) {
        continue;
    }
    if (!$bean->haveFiles()) {
        continue;
    }

    $validBeans[] = $bean;
}

echo '<pre>';
$directory = new DirectoryIterator('upload://');
$stat = array(
    'total' => 0,
    'removed' => 0
);
foreach ($directory as $node) {
    if (!$node->isFile()) {
        continue;
    }
    if (!is_guid($node->getFilename())) {
        continue;
    }
    $stat['total'] ++;

    $row = false;
    foreach ($validBeans as $bean) {
        $filter = array('deleted');
        $where = array();
        foreach ($bean->getFilesFields() as $fieldName) {
            $where[] = $fieldName . '=' . $db->quoted($node->getFilename());
            $filter[] = $fieldName;
        }
        $where = '(' . implode(' OR ', $where) . ')';

        $row = $db->fetchOne($bean->create_new_list_query('', $where, $filter, array(), 0));
        if (!empty($row)) {
            break;
        }
        $row = $db->fetchOne($bean->create_new_list_query('', $where, $filter, array(), 1));
        if (!empty($row)) {
            break;
        }
    }

    if ($row == false) {
        if (unlink('upload://' . $node->getFilename())) {
            $stat['removed'] ++;
        }
    } elseif ($row['deleted'] == 1) {
        $bean->populateFromRow($row);
        if ($bean->deleteFiles()) {
            $stat['removed'] ++;
        }
    }

    echo '.';
    if ($stat['total'] % 100 == 0) {
        echo '<br>';
        ob_flush();
        flush();
    }
}
echo '</pre>';

echo $mod_strings['LBL_TOTAL_FILES'] . ': ' . $stat['total'] . '<br>';
echo $mod_strings['LBL_REMOVED_FILES'] . ': ' . $stat['removed'] . '<br>';
