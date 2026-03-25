<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sugar_evalcolumn} function plugin
 *
 * Type:     function<br>
 * Name:     sugar_evalcolumn<br>
 * Purpose:  evaluate a string by substituting values in the rowData parameter. Used for ListViews<br>
 * 
 * @author Wayne Pan {wayne at sugarcrm.com
 * @param array
 * @param Smarty
 */
function smarty_function_sugar_evalcolumn($params, &$smarty)
{
    if (!isset($params['colData']['field']) ) {
        if(empty($params['colData']))  
            $smarty->trigger_error("evalcolumn: missing 'colData' parameter");
        if(!isset($params['colData']['field']))  
            $smarty->trigger_error("evalcolumn: missing 'colData.field' parameter");
        return;
    }

    if(empty($params['colData']['field'])) {
        return;
    }
    $params['var'] = $params['colData']['field'];
    if(isset($params['toJSON'])) {
        $json = getJSONobj();
        $params['var'] = $json->encode($params['var']);
    }

    if (!empty($params['var']['assign'])) {
        return '{$' . $params['colData']['field']['name'] . '}';
    } else {
    	$code = $params['var']['customCode'];
    	if(isset($params['tabindex']) && preg_match_all("'(<[ ]*?)(textarea|input|select)([^>]*?)(>)'si", $code, $matches, PREG_PATTERN_ORDER)) {
    	   $str_replace = array();
    	   $tabindex = ' tabindex="' . $params['tabindex'] . '" ';
    	   foreach($matches[3] as $match) {
    	   	       $str_replace[$match] = $tabindex . $match;
    	   }
    	   $code = str_replace(array_keys($str_replace), array_values($str_replace), $code);
    	}

        if(isset($params['accesskey']) && preg_match_all("'(<[ ]*?)(textarea|input|select)([^>]*?)(>)'si", $code, $matches, PREG_PATTERN_ORDER)) {
    	   $str_replace = array();
    	   $accesskey = ' accesskey="' . $params['accesskey'] . '" ';
    	   foreach($matches[3] as $match) {
    	   	       $str_replace[$match] = $accesskey . $match;
    	   }
    	   $code = str_replace(array_keys($str_replace), array_values($str_replace), $code);
    	}
    	
        // Add a string replace to swap out @@FIELD@@ for the actual field,
        // we can't do this through customCode directly because the sugar_field smarty function returns smarty code to run on the second pass
        if (!empty($code) && strpos($code,'@@FIELD@@') !== FALSE ) {
            // First we need to fetch extra data about the field
            // sfp == SugarFieldParams
            $sfp = $params;
            $sfp['parentFieldArray'] = 'fields';
            $vardefs = $smarty->get_template_vars('fields');
            $sfp['vardef'] = $vardefs[$params['colData']['field']['name']];
            $sfp['displayType'] = 'EditView';
            $sfp['displayParams'] = $params['colData']['field']['displayParams'];
            $sfp['typeOverride'] = $params['colData']['field']['type'];
            $sfp['formName'] = $smarty->get_template_vars('form_name');
            
            $fieldText = smarty_function_sugar_field($sfp, $smarty);

            $code = str_replace('@@FIELD@@',$fieldText,$code);
        }

    	//eggsurplus bug 28321: add support for rendering customCode AND normal field rendering
    	if(!empty($params['var']['displayParams']['enableConnectors']) && empty($params['var']['customCodeRenderField'])) {
    	  require_once('include/connectors/utils/ConnectorUtils.php');
    	  $code .= '&nbsp;' . ConnectorUtils::getConnectorButtonScript($params['var']['displayParams'], $smarty);
    	}
    	return $code;
    }
    
    
}


?>