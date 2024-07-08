<?php

if (empty($_GET['term'])) exit ;
$q = strtolower($_GET["term"]);
if (get_magic_quotes_gpc()) $q = trim(stripslashes($q));

require_once("config.php");

$con = mysqli_connect($sugar_config['dbconfig']['db_host_name'],$sugar_config['dbconfig']['db_user_name'],$sugar_config['dbconfig']['db_password']);
if (!$con){
  die('Could not connect: ' . mysql_error());
}

mysql_select_db($sugar_config['dbconfig']['db_name'], $con);
mysql_query("SET NAMES UTF8");

$sql = "SELECT b.id AS booking_id
			  ,b.name AS booking
			  ,b.contact_name
			  ,IFNULL(b.total_amount,0) AS total_amount
			  ,(
				(
					SELECT SUM(IFNULL(d.total_bought_price,0))
					FROM ec_booking_details d
					WHERE d.deleted = 0
					AND d.booking_id = b.id
				) + (
					SELECT IF(b.flight_type = '0'
							 ,SUM(
							 	IF(p.luggage_price > 0, IFNULL(p.luggage_purchase,0), 0) 
							 	+ IF(p.luggage_price_inbound > 0, IFNULL(p.luggage_purchase_inbound,0), 0)
							  )
							 ,SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase,0), 0))
							)
					FROM ec_booking_passengers p
					WHERE p.deleted = 0
					AND p.booking_id = b.id
				)		  	
			  ) AS total_purchase
			  ,(IFNULL(b.total_amount,0) - (SELECT total_purchase)) AS total_profit
		FROM ec_flight_bookings b
		WHERE b.deleted = 0 
		AND b.booking_status IN ('7','8')
		AND b.name = '".$q."' ";

$res = mysql_query($sql);
$result_array = array();
while($row = mysql_fetch_array($res)){
	array_push($result_array, 
		array('booking_id' => $row['booking_id']
			 ,'booking' => $row['booking']
			 ,'value' => strip_tags($row['booking'])
			 ,'contact_name' => $row['contact_name']
			 ,'total_amount' => $row['total_amount']
			 ,'total_purchase' => $row['total_purchase']
			 ,'total_profit' => $row['total_profit']
		)
	);
	if(count($result_array) > 11) break;
}
mysql_close($con);

echo json_encode($result_array);
?>