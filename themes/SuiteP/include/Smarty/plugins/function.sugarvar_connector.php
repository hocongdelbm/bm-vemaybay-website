<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

function smarty_function_sugarvar_connector($params, &$smarty) {
      
      $displayParams = $smarty->get_template_vars('displayParams');
      if(!isset($displayParams['module'])) {
         $smarty->trigger_error("sugarvar_connector: missing 'module' parameter");
         $GLOBALS['log']->error("sugarvar_connector: missing 'module' parameter");
         return;     	
      }
      
      require_once('include/connectors/utils/ConnectorUtils.php');
      echo ConnectorUtils::getConnectorButtonScript($displayParams, $smarty);
}
?>