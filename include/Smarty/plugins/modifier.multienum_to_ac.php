<?php
if (!defined('sugarEntry') || !sugarEntry) {
	die('Not A Valid Entry Point');
}




function smarty_modifier_multienum_to_ac($value = '', $field_options = array())
{
	$value = trim($value);
	if (empty($value) || empty($field_options)) {
		return '';
	}

	$expl = explode("^,^", $value);
	if (count($expl) == 1) {
		if (array_key_exists($value, $field_options)) {
			return $field_options[$value] . ", ";
		} else {
			return '';
		}
	} else {
		$final_array = array();
		foreach ($expl as $key_val) {
			if (array_key_exists($key_val, $field_options)) {
				$final_array[] = $field_options[$key_val];
			}
		}
		return implode(", ", $final_array) . ", ";
	}

	return '';
}
