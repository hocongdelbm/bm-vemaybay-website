<?php

//sleep( 3 );
if (empty($_GET['term'])) exit ;
$q = strtolower($_GET["term"]);
if (get_magic_quotes_gpc()) $q = stripslashes(trim($q));

require_once("config.php");

$con = mysqli_connect($sugar_config['dbconfig']['db_host_name'],$sugar_config['dbconfig']['db_user_name'],$sugar_config['dbconfig']['db_password']);

if (!$con){
  die('Could not connect: ' . mysqli_connect_error());
}

mysqli_select_db($con, $sugar_config['dbconfig']['db_name']);
mysqli_set_charset($con, 'utf8');

$tbl 		= trim(stripslashes($_REQUEST['tbl']));
$fld 		= trim(stripslashes($_REQUEST['fld']));

$fld_arr 		= json_decode($fld, true);
$fld_arr_cnt 	= count($fld_arr);

if($fld_arr_cnt > 0){

	$select 	= "";
	$i 		= 0;

	foreach($fld_arr as $key => $value){
		if($tbl == 'users' && $key == 'name'){
			$select .= "CONCAT(IFNULL(TRIM(last_name),''),' ',IFNULL(TRIM(first_name),'')) AS ".$key;
		} else {
			$select .= $key;
		}
		if($i != ($fld_arr_cnt - 1)){
			$select .= ",";
		}
		$i++;
	}
} else {
	$select = "id,name";
}

$sql = "SELECT ".$select." 
		FROM ".$tbl."
		WHERE deleted = 0 ";
if($tbl == 'users'){		
	$sql .= " AND CONCAT(IFNULL(TRIM(last_name),''),' ',IFNULL(TRIM(first_name),'')) LIKE '%".$q."%'
			  AND status = 'Active' ";
} else {
	$sql .= " AND name LIKE '%".$q."%' ";
}

$res 		= mysqli_query($con, $sql);
$result_array 	= array();
while($row = mysqli_fetch_array($res)){

	$dataArr['label'] = $row['name'];
	$dataArr['value'] = strip_tags($row['name']);
	if($fld_arr_cnt > 0){
		foreach($fld_arr as $key => $value){
			$dataArr[$key] = $row[$key];
		}
	} else {
		$dataArr['id'] = $row['id'];
		$dataArr['name'] = $row['name'];
	}
	
	array_push($result_array, $dataArr);
	if (count($result_array) > 11)
		break;
}

mysqli_close($con);

echo json_encode($result_array);
?>