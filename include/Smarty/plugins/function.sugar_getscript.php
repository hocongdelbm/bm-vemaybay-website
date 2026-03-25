<?php


/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sugar_getscript} function plugin
 *
 * Type:     function<br>
 * Name:     sugar_getscript<br>
 * Purpose:  Creates script tag for filename with caching string
 *
 * @param array
 * @param Smarty
 */
function smarty_function_sugar_getscript($params, &$smarty)
{
	if (!isset($params['file'])) {
		$smarty->trigger_error($GLOBALS['app_strings']['ERR_MISSING_REQUIRED_FIELDS'] . 'file');
	}
	return getVersionedScript($params['file']);
}
