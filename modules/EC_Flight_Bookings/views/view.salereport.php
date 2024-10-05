<?php
require_once("include/Sugar_Smarty.php");

class Viewsalereport extends SugarView {
	function display() {
		if(ACLController::checkAccess('Bugs', 'view', true)){
			$smartyCont= new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_salereport.tpl');
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}
	
	function populateContent($smartyobj) {
		global $sugar_config, $mod_strings, $app_list_strings, $app_strings, $db;
		
		$sql_search = "";
		$sql_search2 = "";
		$sql_search3 = "";
		// từ ngày
		if(isset($_POST['from_date']) && !empty($_POST['from_date'])){
			$sql_search .= " AND hd.ngaychungtu >= '".date('Y-m-d', strtotime($_POST['from_date']))."' ";
			$sql_search2 .= " AND bk.date_ticket_issue >= '".date('Y-m-d', strtotime($_POST['from_date']))."' ";
			$sql_search3 .= " AND DATE(rv.date_entered) >= '".date('Y-m-d', strtotime($_POST['from_date']))."' ";
			$post_from_date = $_POST['from_date'];
		} else {
			$sql_search .= " AND hd.ngaychungtu >= '".date('Y-m-d')."' ";
			$sql_search2 .= " AND bk.date_ticket_issue >= '".date('Y-m-d')."' ";
			$sql_search3 .= " AND DATE(rv.date_entered) >= '".date('Y-m-d')."' ";
			$post_from_date = date('d-m-Y');
		}
		
		// đến ngày
		if(isset($_POST['to_date']) && !empty($_POST['to_date'])){
			$sql_search .= " AND hd.ngaychungtu <= '".date('Y-m-d', strtotime($_POST['to_date']))."' ";
			$sql_search2 .= " AND bk.date_ticket_issue <= '".date('Y-m-d', strtotime($_POST['to_date']))."' ";
			$sql_search3 .= " AND DATE(rv.date_entered) <= '".date('Y-m-d', strtotime($_POST['to_date']))."' ";
			$post_to_date = $_POST['to_date'];
		} else {
			$sql_search .= " AND hd.ngaychungtu <= '".date('Y-m-d')."' ";
			$sql_search2 .= " AND bk.date_ticket_issue <= '".date('Y-m-d')."' ";
			$sql_search3 .= " AND DATE(rv.date_entered) <= '".date('Y-m-d')."' ";
			$post_to_date = date('d-m-Y');
		}
			
			
		if(isset($_POST['btnExcelExport'])){
			
			// thống kê các booking đã lập hóa đơn
			// doanh thu + chi phí
			$sql = "SELECT ct.name AS sove
						  ,ct.hanhtrinh
						  ,ct.soluong
						  ,ct.dongia
						  ,ct.thanhtien
						  ,ct.thuesuat
						  ,ct.thuevat
						  ,ct.phidichvu
						  ,ct.phisanbay
						  ,(SELECT IF(iti.airline_code='JET' AND bkd.passenger_type<>'2', ".$app_list_strings['phiadmin_list']['JET'].", IF(iti.airline_code='VJA' AND bkd.passenger_type<>'2', ".$app_list_strings['phiadmin_list']['VJA'].", 0))  
							FROM ec_booking_itineraries iti INNER JOIN ec_booking_details bkd ON iti.booking_id = bkd.booking_id AND iti.direction = bkd.direction
							WHERE bkd.booking_id = hd.booking_id AND iti.deleted=0 AND bkd.deleted=0 AND bkd.total_price = ct.thanhtien LIMIT 0,1) AS phiadmin
						  ,hd.phihanhly
						  ,hd.phihoandoive
						  ,hd.phikhac
						  ,hd.giamgia
						  ,(SELECT name FROM ec_flight_bookings WHERE id=hd.booking_id) AS booking
						  ,(SELECT name FROM ec_receipt_voucher WHERE id=hd.phieuthu_id) AS phieuthu
					FROM ec_chitiethoadon ct
					LEFT JOIN ec_hoadonban hd ON ct.parent_id = hd.id AND hd.deleted = 0
					WHERE ct.parent_type = 'EC_HoaDonBan' ".$sql_search." AND ct.deleted = 0
					ORDER BY booking ";
			$res = $db->query($sql);
			$i = 0;
			$baseRow = 7;
			while($row = $db->fetchByAssoc($res)){
				
				$laigop = $row['phidichvu']/1.1;
				//$dongiaban = $row['dongia'] + $laigop;
				$dongiaban = $row['dongia'];
				
				$phidichvu = $row['phidichvu'];
				$phisanbay = $row['phisanbay'];
				//$vat_daura = "=G".$baseRow."*0.1";
				$vat_daura = $row['thuevat'];
				
				$phigiaove = ($i == 0 ? $row['phikhac'] : "=IF(D".$baseRow."=D".($baseRow-1).",0,".$row['phikhac'].")");
				$phihanhly = ($i == 0 ? $row['phihanhly'] : "=IF(D".$baseRow."=D".($baseRow-1).",0,".$row['phihanhly'].")");
				$phihoandoive = ($i == 0 ? $row['phihoandoive'] : "=IF(D".$baseRow."=D".($baseRow-1).",0,".$row['phihoandoive'].")");
				$giamgia = ($i == 0 ? $row['giamgia'] : "=IF(D".$baseRow."=D".($baseRow-1).",0,".$row['giamgia'].")");
				$doanhthu = "=((G".$baseRow."+H".$baseRow."+I".$baseRow."+J".$baseRow."+K".$baseRow.")*F".$baseRow.")+L".$baseRow."+M".$baseRow."+N".$baseRow;
				
				$dongiamua = $row['dongia'];
				$phiadmin = $row['phiadmin']; // trẻ sơ sinh không có phí admin
				$vat_dauvao = $row['thuevat'];
				//$chiphi = "=(F".$baseRow."*(O".$baseRow."+P".$baseRow."+Q".$baseRow."+R".$baseRow."))+S".$baseRow."+T".$baseRow."+U".$baseRow."-V".$baseRow;
				$chiphi = "=(F".$baseRow."*(P".$baseRow."+Q".$baseRow."+R".$baseRow."+S".$baseRow."))+T".$baseRow."+U".$baseRow."+V".$baseRow."-W".$baseRow;
				$laigop = "=O".$baseRow."-X".$baseRow;
				
				$html .= "<tr height=19 style='height:14.25pt'>
						  <td height=19 class=xl6510306 style='height:14.25pt;border-top:none'>".($i+1)."</td>
						  <td class=xl6510306 style='border-top:none;border-left:none'>".$row['sove']."</td>
						  <td class=xl6510306 style='border-top:none;border-left:none'>".$row['hanhtrinh']."</td>
						  <td class=xl6510306 style='border-top:none;border-left:none'>".$row['booking']."</td>
						  <td class=xl6510306 style='border-top:none;border-left:none'>".$row['phieuthu']."</td>
						  <td class=xl6810306 style='border-top:none;border-left:none'>".$row['soluong']."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$dongiaban."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$vat_daura."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phiadmin."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phidichvu."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phisanbay."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phigiaove."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phihanhly."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phihoandoive."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$doanhthu."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$dongiamua."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phisanbay."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phiadmin."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$vat_dauvao."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phigiaove."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phihanhly."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phihoandoive."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$giamgia."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$chiphi."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$laigop."</td>
						 </tr>";	 
				$i++;
				$baseRow++;
			}//while
			
			
			$html .= "<tr height=19 style='height:14.25pt'>
			<td height=19 class=xl6510306 style='height:14.25pt;border-top:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6810306 style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($i==0?0:"=SUM(F7:F".($baseRow-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($i==0?0:"=SUM(O7:O".($baseRow-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($i==0?0:"=SUM(X7:X".($baseRow-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($i==0?0:"=SUM(Y7:Y".($baseRow-1).")")."</td>
						 </tr>";
			
			
			// thống kê các booking đã thu tiền
			// chỉ đưa vào chi phí
			$sql = "SELECT IF( bkd.direction = 0, (SELECT eticket_outbound FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bkd.booking_id LIMIT 0,1), (SELECT eticket_inbound FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bkd.booking_id LIMIT 0,1)) AS sove
						, (SELECT CONCAT(departure,IF(arrival IS NOT NULL, ' - ', ''),IF(arrival IS NOT NULL, arrival, '')) FROM ec_booking_itineraries WHERE deleted = 0 AND booking_id = bkd.booking_id AND direction = bkd.direction LIMIT 0,1) AS hanhtrinh
						, bk.name AS booking
						,(SELECT name FROM ec_receipt_voucher WHERE booking_id = bkd.booking_id AND deleted = 0 LIMIT 0,1) AS phieuthu
						, bkd.quantity AS soluong
						, bkd.unit_price AS dongia
						, bkd.tax_and_fee AS thuevat
						, bkd.service_fee AS phidichvu
						, bkd.airport_fee AS phisanbay
						,(SELECT IF(iti.airline_code='JET', ".$app_list_strings['phiadmin_list']['JET'].", IF(iti.airline_code='VJA', ".$app_list_strings['phiadmin_list']['VJA'].", 0))  
							FROM ec_booking_itineraries iti WHERE iti.booking_id = bk.id AND iti.direction = bkd.direction AND iti.deleted=0 LIMIT 0,1) AS phiadmin
						, bkd.total_price AS thanhtien
						, bk.other_fee AS phikhac
						, bk.luggage_fee AS phihanhly
						, bk.ticket_change_fee AS phihoandoive
						, bk.discount_amount AS giamgia
						, bkd.passenger_type AS loaihanhkhach
						, bk.description
						, 'EC_Flight_Bookings' AS parent_type
					FROM ec_booking_details bkd LEFT JOIN ec_flight_bookings bk ON bkd.booking_id = bk.id
					WHERE 
						( bk.booking_status = '7' OR bk.booking_status = '8' )
						AND bk.deleted = 0 AND bkd.deleted = 0 
						".$sql_search2."
					AND bkd.booking_id in (SELECT booking_id FROM ec_receipt_voucher WHERE rv_status = 1 AND deleted = 0)
					
					UNION
					SELECT '' AS sove, '' AS hanhtrinh, bk.name AS booking, rv.name AS phieuthu, 0 AS soluong, 0 AS dongia
					     , 0 AS thuevat, 0 AS phidichvu, 0 AS phisanbay, 0 AS phiadmin, rv.amount AS thanhtien, 0 AS phikhac
						 , 0 AS phihanhly, 0 AS phihoandoive, 0 AS giamgia, '' AS loaihanhkhach, rv.loai_thu AS description, 'EC_Receipt_Voucher' AS parent_type
					FROM ec_receipt_voucher rv LEFT JOIN ec_flight_bookings bk ON rv.booking_id=bk.id AND bk.deleted=0
					WHERE rv.rv_status='1' AND rv.loai_thu IN ('3','4','5') ".$sql_search3." AND rv.deleted=0
					
					ORDER BY booking ";
			$res = $db->query($sql);
			$j = 0;
			$baseRow2 = $baseRow+1;
			while($row = $db->fetchByAssoc($res)){
				
				$html .= "<tr height=19 style='height:14.25pt'>
						  <td height=19 class=xl6510306 style='height:14.25pt;border-top:none'>".($j+1)."</td>";
						  
				if($row['parent_type'] == 'EC_Flight_Bookings'){
			    
				$laigop = $row['phidichvu']/1.1;
				$dongiaban = $row['dongia'];
				
				$phidichvu = $row['phidichvu'];
				$phisanbay = $row['phisanbay'];
				$vat_daura = $row['thuevat'];
				
				$phigiaove = ($j == 0 ? $row['phikhac'] : "=IF(D".$baseRow2."=D".($baseRow2-1).",0,".$row['phikhac'].")");
				$phihanhly = ($j == 0 ? $row['phihanhly'] : "=IF(D".$baseRow2."=D".($baseRow2-1).",0,".$row['phihanhly'].")");
				$phihoandoive = ($j == 0 ? $row['phihoandoive'] : "=IF(D".$baseRow2."=D".($baseRow2-1).",0,".$row['phihoandoive'].")");
				$giamgia = ($j == 0 ? $row['giamgia'] : "=IF(D".$baseRow2."=D".($baseRow2-1).",0,".$row['giamgia'].")");
				$doanhthu = "=((G".$baseRow2."+H".$baseRow2."+I".$baseRow2."+J".$baseRow2."+K".$baseRow2.")*F".$baseRow2.")+L".$baseRow2."+M".$baseRow2."+N".$baseRow2;
				
				$dongiamua = $row['dongia'];
				$phiadmin = $row['loaihanhkhach'] != '2' ? $row['phiadmin'] : 0; // trẻ sơ sinh không có phí admin
				$vat_dauvao = $row['thuevat'];
				$chiphi = "=(F".$baseRow2."*(P".$baseRow2."+Q".$baseRow2."+R".$baseRow2."+S".$baseRow2."))+T".$baseRow2."+U".$baseRow2."+V".$baseRow2."-W".$baseRow2;
				$laigop = "=O".$baseRow2."-X".$baseRow2;

				$html .= "<td class=xl6510306 style='border-top:none;border-left:none'>".$row['sove']."</td>
				<td class=xl6510306 style='border-top:none;border-left:none'>".$row['hanhtrinh']."</td>";
				
				} else {
				
				$laigop = 0;
				$dongiaban = 0;
				
				$phidichvu = 0;
				$phisanbay = 0;
				$vat_daura = 0;
				
				$phigiaove = 0;
				$phihanhly = 0;
				$phihoandoive = 0;
				$giamgia = 0;
				$doanhthu = $row['thanhtien'];
				
				$dongiamua = 0;
				$phiadmin = 0;
				$vat_dauvao = 0;
				$chiphi = $row['thanhtien'];
				$laigop = "=O".$baseRow2."-X".$baseRow2;

			    $html .= "<td colspan='2' class=xl6510306 style='border-top:none;border-left:none'>".$app_list_strings['loai_thu_list'][$row['description']]."</td>";
				 
				}						  
						  
				$html .= "<td class=xl6510306 style='border-top:none;border-left:none'>".$row['booking']."</td>
				<td class=xl6510306 style='border-top:none;border-left:none'>".$row['phieuthu']."</td>
				<td class=xl6810306 style='border-top:none;border-left:none'>".$row['soluong']."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$dongiaban."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$vat_daura."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phiadmin."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phidichvu."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phisanbay."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phigiaove."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phihanhly."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phihoandoive."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$doanhthu."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$dongiamua."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phisanbay."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phiadmin."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$vat_dauvao."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phigiaove."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phihanhly."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$phihoandoive."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$giamgia."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$chiphi."</td>
				<td class=xl6710306 align=right style='border-top:none;border-left:none'>".$laigop."</td>
			   </tr>";
						 
				$j++;
				$baseRow2++;
			}//while
			
			// dòng tính tổng cộng
			$html .= "<tr height=19 style='height:14.25pt'>
			<td height=19 class=xl6510306 style='height:14.25pt;border-top:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6810306 style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($j==0?0:"=SUM(F".($baseRow+1).":F".($baseRow2-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($j==0?0:"=SUM(O".($baseRow+1).":O".($baseRow2-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($j==0?0:"=SUM(X".($baseRow+1).":X".($baseRow2-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($j==0?0:"=SUM(Y".($baseRow+1).":Y".($baseRow2-1).")")."</td>
						 </tr>";
			
			
			
			
			
			
			
			
			
			// ################################# THONG KE CAC PHIEU THU #####################################//
			$sql = "SELECT rv.name, rv.date_entered, rv.amount, rv.loai_thu FROM ec_receipt_voucher rv
					WHERE rv.booking_id IS NULL AND rv.deleted=0 AND rv.rv_status='1' ".$sql_search3;
			$res = $db->query($sql);
			$k = 0;
			$baseRow3 = $baseRow2+1;
			while($row = $db->fetchByAssoc($res)){
				
				$html .= "<tr height=19 style='height:14.25pt'>
						  <td height=19 class=xl6510306 style='height:14.25pt;border-top:none'>".($k+1)."</td>
						  <td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6510306 style='border-top:none;border-left:none'>".$row['name']."</td>
						  <td class=xl6810306 style='border-top:none;border-left:none'>0</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$row['amount']."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>".$row['amount']."</td>
						  <td class=xl6710306 align=right style='border-top:none;border-left:none'>0</td>
						 </tr>";
						 
				$k++;
				$baseRow3++;
			}//while
			
			// dòng tính tổng cộng
			$html .= "<tr height=19 style='height:14.25pt'>
			<td height=19 class=xl6510306 style='height:14.25pt;border-top:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6810306 style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($k==0?0:"=SUM(F".($baseRow2+1).":F".($baseRow3-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($k==0?0:"=SUM(O".($baseRow2+1).":O".($baseRow3-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($k==0?0:"=SUM(X".($baseRow2+1).":X".($baseRow3-1).")")."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:blue'>".($k==0?0:"=SUM(Y".($baseRow2+1).":Y".($baseRow3-1).")")."</td>
						 </tr>";
			
			
			
			
			
			
			
			
			
			
			
			
			
						 
			
			// dòng tính tổng cộng cuối cùng
			$html .= "<tr height=19 style='height:14.25pt'>
			<td height=19 class=xl6510306 style='height:14.25pt;border-top:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6510306 style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6810306 style='border-top:none;border-left:none; font-weight:bold; color:red'>=F".$baseRow."+F".$baseRow2."+F".$baseRow3."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:red'>=O".$baseRow."+O".$baseRow2."+O".$baseRow3."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none'>&nbsp;</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:red'>=X".$baseRow."+X".$baseRow2."+X".$baseRow3."</td>
			<td class=xl6710306 align=right style='border-top:none;border-left:none; font-weight:bold; color:red'>=Y".$baseRow."+Y".$baseRow2."+Y".$baseRow3."</td>
						 </tr>";
			
			
		
			// xuất excel
			ob_clean();
			header("Pragma: cache");
			require_once('modules/EC_Flight_Bookings/views/baocaobanhang.xls.php');
			$xls = generateXLSTemplate($html, $post_from_date, $post_to_date);
			$xls = chr(255).chr(254).mb_convert_encoding($xls, "UTF-16LE", "UTF-8");
			header("Content-type: application/x-msdownload");
			header("Content-disposition: xls; filename=baocaobanhang_".time().".xls; size=".strlen($xls));
			echo $xls;
			exit();
			
		} // end if
		
		
		$smartyobj->assign('FROM_DATE_VALUE', $post_from_date);
		$smartyobj->assign('TO_DATE_VALUE', $post_to_date);
	}	
}
