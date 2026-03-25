<?php
if (!defined('sugarEntry') || !sugarEntry) {
	die('Not A Valid Entry Point');
}

/**
 * Smarty modifier to return default date value
 *
 * Type:     modifier<br>
 * Name:     default_date_value<br>
 * Purpose:  Utility to return a default date value given the field's default value settings
 * @author   Collin Lee <clee at sugarcrm dot com>
 * @param defaultValue The date field's default value setting
 * @return String representing date value
 */
function smarty_modifier_default_date_value($defaultValue)
{
	global $timedate;
	require_once('modules/DynamicFields/templates/Fields/TemplateDate.php');
	$td = new TemplateDate();
	return $timedate->asUser(new SugarDateTime($td->dateStrings[$defaultValue]));
}
