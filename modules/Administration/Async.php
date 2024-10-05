<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once("include/entryPoint.php");

if (!is_admin($GLOBALS['current_user'])) {
    sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
}

$json = getJSONObj();
$out = "";

switch ($_REQUEST['adminAction']) {
    ///////////////////////////////////////////////////////////////////////////
    ////	REPAIRXSS
    case "refreshEstimate":
        include("include/modules.php"); // provide $moduleList
        $target = '';
        if (!empty($_REQUEST['bean'])) {
            $target = $_REQUEST['bean'];
        }

        $count = 0;
        $toRepair = array();
        
        if ($target == 'all') {
            $hide = array('Activities', 'Home', 'iFrames', 'Calendar', 'Dashboard');
        
            sort($moduleList);
            $options = array();
            
            foreach ($moduleList as $module) {
                if (!in_array($module, $hide)) {
                    $options[$module] = $module;
                }
            }

            foreach ($options as $module) {
                if (!isset($beanFiles[$beanList[$module]])) {
                    continue;
                }
                
                $file = $beanFiles[$beanList[$module]];
                
                if (!file_exists($file)) {
                    continue;
                }
                    
                require_once($file);
                $bean = new $beanList[$module]();
                
                $q = "SELECT count(*) as count FROM {$bean->table_name}";
                $r = $bean->db->query($q);
                $a = $bean->db->fetchByAssoc($r);
                
                $count += $a['count'];
                
                // populate to_repair array
                $q2 = "SELECT id FROM {$bean->table_name}";
                $r2 = $bean->db->query($q2);
                $ids = [];
                while ($a2 = $bean->db->fetchByAssoc($r2)) {
                    $ids[] = $a2['id'];
                }
                $toRepair[$module] = $ids;
            }
        } elseif (in_array($target, $moduleList)) {
            require_once($beanFiles[$beanList[$target]]);
            $bean = new $beanList[$target]();
            $q = "SELECT count(*) as count FROM {$bean->table_name}";
            $r = $bean->db->query($q);
            $a = $bean->db->fetchByAssoc($r);
            
            $count += $a['count'];
            
            // populate to_repair array
            $q2 = "SELECT id FROM {$bean->table_name}";
            $r2 = $bean->db->query($q2);
            $ids = [];
            while ($a2 = $bean->db->fetchByAssoc($r2)) {
                $ids[] = $a2['id'];
            }
            $toRepair[$target] = $ids;
        }
        
        $out = array('count' => $count, 'target' => $target, 'toRepair' => $toRepair);
    break;
    
    case "repairXssExecute":
        if (isset($_REQUEST['bean']) && !empty($_REQUEST['bean']) && isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            include("include/modules.php"); // provide $moduleList
            $target = $_REQUEST['bean'];
            require_once($beanFiles[$beanList[$target]]);
            
            $ids = $json->decode(from_html($_REQUEST['id']));
            $count = 0;
            foreach ($ids as $id) {
                if (!empty($id)) {
                    $bean = new $beanList[$target]();
                    $bean->retrieve($id, true, false);
                    $bean->new_with_id = false;
                    $bean->save(); // cleanBean() is called on save()
                    $count++;
                }
            }
            
            $out = array('msg' => "success", 'count' => $count);
        } else {
            $out = array('msg' => "failure: bean or ID not defined");
        }
    break;
    ////	END REPAIRXSS
    ///////////////////////////////////////////////////////////////////////////
    
    default:
        die();
    break;
}

$ret = $json->encode($out, true);
echo $ret;
