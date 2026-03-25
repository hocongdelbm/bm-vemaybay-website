<?php

if (!defined('sugarEntry') || !sugarEntry) {
	die('Not A Valid Entry Point');
}




function smarty_modifier_lookup($value = '', $from = array())
{
	$value = trim($value);
	if (array_key_exists($value, $from)) {
		return $from[$value];
	}
	return '';
}
