<?php

if (!defined('sugarEntry') || !sugarEntry) {
	die('Not A Valid Entry Point');
}

$module_name = 'EC_HoanVe';
$searchFields[$module_name] = array(
	'name' => array('query_type' => 'default'),
	'current_user_only' => array(
		'query_type' => 'default',
		'db_field' => array('assigned_user_id'),
		'my_items' => true,
		'vname' => 'LBL_CURRENT_USER_FILTER',
		'type' => 'bool'
	),
	'assigned_user_id' => array('query_type' => 'default'),

	'hoten_search' =>
	array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT hoanve_id AS id
							   FROM ec_chitiethoanve
							   WHERE deleted=0 AND name LIKE ',
		'like_char' => '%',
		'db_field' => array('id'),
	),

	'airline_code_search' =>
	array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT hoanve_id AS id
							   FROM ec_chitiethoanve
							   WHERE deleted=0 AND airline_code LIKE ',
		//'like_char' => '%',
		'db_field' => array('id'),
	),

	'noidi_search' =>
	array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT hoanve_id AS id
							   FROM ec_chitiethoanve
							   WHERE deleted=0 AND noidi LIKE ',
		//'like_char' => '%',
		'db_field' => array('id'),
	),

	'noiden_search' =>
	array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT hoanve_id AS id
							   FROM ec_chitiethoanve
							   WHERE deleted=0 AND noiden LIKE ',
		//'like_char' => '%',
		'db_field' => array('id'),
	),

	'sove_search' =>
	array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT hoanve_id AS id
							   FROM ec_chitiethoanve
							   WHERE deleted=0 AND sove LIKE ',
		//'like_char' => '%',
		'db_field' => array('id'),
	),

	'pnr_search' =>
	array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT hoanve_id AS id
							   FROM ec_chitiethoanve
							   WHERE deleted=0 AND pnr LIKE ',
		//'like_char' => '%',
		'db_field' => array('id'),
	),

	'nhacc_search' =>
	array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT c.hoanve_id AS id
							   FROM ec_chitiethoanve c
							   WHERE c.deleted=0 AND c.nhacc_id = ',
		'db_field' => array('id'),
	),

	//Range Search Support
	'range_date_entered' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
	'start_range_date_entered' => array(
		'query_type' => 'default',
		'enable_range_search' => true,
		'is_date_field' => true
	),
	'end_range_date_entered' => array(
		'query_type' => 'default',
		'enable_range_search' => true,
		'is_date_field' => true
	),
	'range_date_modified' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
	'start_range_date_modified' => array(
		'query_type' => 'default',
		'enable_range_search' => true,
		'is_date_field' => true
	),
	'end_range_date_modified' => array(
		'query_type' => 'default',
		'enable_range_search' => true,
		'is_date_field' => true
	),
	//Range Search Support
);
