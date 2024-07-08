<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewemployeereport extends SugarView {
    private $_receiptTypeIDs = "'4', '5', '10', '11', '12', '13', '14', '16'";

    function display() {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'view', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_employeereport.tpl');
        } else {
            header('Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=' . urlencode('Bạn không được quyền truy cập vào mục này.'));
            exit;
        }
    }

    function populateContent($smartyObj) {
        global $db;

        $paymentTypeId = '3f9f8060-1866-2b2e-8322-52e36b8f58d5'; // Discount for customer

        $sqlSearch = "";
        if (!empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
            $sqlSearch .= " AND p.date_ticket_issue >= '" . date('Y-m-d', strtotime($_REQUEST['from_date'])) . "' ";
            $fromDate = $_REQUEST['from_date'];
        } else {
            $sqlSearch .= " AND p.date_ticket_issue >= '" . date('Y-m-01') . "' ";
            $fromDate = date('01-m-Y');
        }

        if (!empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
            $sqlSearch .= " AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($_REQUEST['to_date'])) . "' ";
            $toDate = $_REQUEST['to_date'];
        } else {
            $sqlSearch .= " AND p.date_ticket_issue <= '" . date('Y-m-d') . "' ";
            $toDate = date('d-m-Y');
        }

        $sqlSearchAssign = str_replace('p.date_ticket_issue', 'DATE(DATE_ADD(p2.date_entered, INTERVAL 7 HOUR))', $sqlSearch);
        $sqlSearchReceipt = str_replace('p.date_ticket_issue', 'DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR))', $sqlSearch);
        $sqlSearchRefund = str_replace('p.date_ticket_issue', 'p.ngaychungtu', $sqlSearch);
        $sqlSearchWorking = str_replace('p.date_ticket_issue', 'DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR))', $sqlSearch);

        $sql = "
               SELECT p.id
                     ,p.assigned_user_id AS user_id
                     ,1 AS complete_count
					 ,SUM(IFNULL(d.quantity, 0)) AS quantity
                     ,IFNULL(p.total_amount, 0) AS sell_amt
                     ,(
                            SUM(IFNULL(d.total_bought_price, 0)) 
                            +
                            IFNULL((
                                SELECT 
                                  IF(
                                      p.flight_type = '0',
                                      SUM(
                                        IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0) + 
                                        IF(px.luggage_price_inbound > 0, IFNULL(px.luggage_purchase_inbound, 0), 0)
                                      ),
                                      SUM(IF(px.luggage_price > 0, IFNULL(px.luggage_purchase, 0), 0))
                                  )
                                FROM ec_booking_passengers px
                                WHERE px.deleted = 0
                                AND px.booking_id = p.id
                            ), 0)
                      ) AS purchase_amt
                     ,IFNULL((
                          SELECT SUM(IFNULL(amount, 0))
                          FROM ec_payment_voucher
                          WHERE deleted = 0
                          AND booking_id = d.booking_id 
                          AND pv_status = '3'
                          AND ec_payment_types_id_c = '" . $paymentTypeId . "'
                      ), 0) AS discount_amt
			   FROM ec_booking_details d
			   LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0 
			   WHERE d.deleted = 0
			   AND p.booking_status = '8'
			   " . $sqlSearch . "
			   GROUP BY p.id
			   
			   UNION
			   SELECT p.id
			         ,p.assigned_user_id AS user_id
			         ,0 AS complete_count
			   		 ,0 AS quantity
			   		 ,SUM(IFNULL(p.amount, 0))  AS sell_amt
					 ,SUM(IFNULL(p.bought_amount, 0) + IFNULL(p.bought_amount2, 0) + IFNULL(p.bought_amount3, 0)) AS purchase_amt
					 ,0 AS discount_amt
			   FROM ec_receipt_voucher p
			   WHERE p.deleted = 0
			   AND p.rv_status = '1' 
			   AND p.loai_thu IN (" . $this->_receiptTypeIDs . ")
			   " . $sqlSearchReceipt . "
			   GROUP BY p.id
			   
			   UNION
			   SELECT p.id
			         ,p.assigned_user_id AS user_id
			         ,0 AS complete_count
			   		 ,0 AS quantity
			   		 ,SUM(IFNULL(p.tongtienhang, 0))  AS sell_amt
					 ,SUM(IFNULL(p.tongtienkhach, 0)) AS purchase_amt
					 ,0 AS discount_amt
			   FROM ec_hoanve p
			   WHERE p.deleted = 0
			   AND p.tinhtrang = '1'
			   AND p.tongtiendv > 0
			   " . $sqlSearchRefund . "
			   GROUP BY p.id ";

        $sqlFinal = "SELECT tmp.user_id
                           ,u.user_name 
                           ,TRIM(CONCAT(TRIM(IFNULL(u.last_name, '')), ' ', TRIM(IFNULL(u.first_name, '')))) AS full_name
                           ,u.title
                           ,SUM(IFNULL(tmp.quantity, 0)) AS total_qty
                           ,(SUM(IFNULL(tmp.sell_amt, 0))
                              - SUM(IFNULL(tmp.purchase_amt, 0))
                              - SUM(IFNULL(tmp.discount_amt, 0))
                            ) AS total_profit
                           ,(
                              SELECT SUM(
                                IFNULL(w.called, 0) 
                                + IFNULL(w.confirmed, 0) 
                                + IFNULL(w.completed, 0) 
                                + IFNULL(w.paid, 0) 
                                + IFNULL(w.recheck, 0) 
                                + (IFNULL(w.invoice_issued, 0) * 3) 
                                + IFNULL(w.ticket_delivery, 0) 
                                + IFNULL(w.recall, 0) 
                                + IFNULL(w.bonus, 0) 
                                + IFNULL(w.check_debt, 0) 
                                + IFNULL(w.create_repaid, 0) 
                                + IFNULL(w.process_repaid, 0) 
                                + IFNULL(w.create_payment, 0) 
                                + IFNULL(w.create_transfer, 0)
                                + IFNULL(w.invoice_input_issued, 0)
                              ) FROM ec_working_process w
                              WHERE w.deleted = 0
                              AND w.assigned_user_id = tmp.user_id
                              " . $sqlSearchWorking . "
                              GROUP BY w.assigned_user_id
                           ) AS total_kpi
                           ,(
                                SELECT COUNT(p2.id)
                                FROM ec_flight_bookings p2
                                WHERE p2.booking_status <> '8'
                                AND p2.assigned_user_id = tmp.user_id
                                " . $sqlSearchAssign . "
                                GROUP BY p2.assigned_user_id
                           ) AS total_assign
                           ,SUM(IFNULL(tmp.complete_count, 0)) AS total_complete
                     FROM (
                        " . $sql . "
                     ) AS tmp
                     LEFT JOIN users u ON tmp.user_id = u.id AND u.deleted = 0
                     WHERE u.status = 'Active'
                     AND u.title IN ('Booker', 'KeToan')
                     GROUP BY tmp.user_id
                     ORDER BY total_profit DESC ";

        $res = $db->query($sqlFinal);
        $html = '';
        $excelData = '';
        $i = 0;
        $totalKpi = 0;
        $totalQty = 0;
        $totalProfit = 0;
        $totalAssign = 0;
        $totalComplete = 0;
        while ($row = $db->fetchByAssoc($res)) {
            $performance = $row['total_assign'] != 0 ? $row['total_complete'] * 100 / $row['total_assign'] : 0;

            $html .= '<tr>
                <td style="text-align: center;">' . ($i + 1) . '</td>
                <td>' . $row['full_name'] . '</td>
                <td>' . $row['title'] . '</td>
                <td class="number">' . format_number($row['total_assign']) . '</td>
                <td class="number">' . format_number($row['total_complete']) . '</td>
                <td class="number">' . round($performance, 2) . ' %</td>
                <td class="number">' . format_number($row['total_kpi']) . '</td>
                <td class="number">' . format_number($row['total_qty']) . '</td>
                <td class="number">' . format_number($row['total_profit']) . '</td>
            </tr>';

            $excelData .= "<tr height=17 style='height:12.75pt'>
            <td height=17 class=xl6424612 style='height:12.75pt'>" . ($i + 1) . "</td>
            <td class=xl6524612 style='border-left:none'>" . $row['full_name'] . "</td>
            <td class=xl6524612 style='border-left:none'>" . $row['title'] . "</td>
            <td class=xl6524612 style='border-left:none'>" . $row['user_name'] . "</td>
            <td class=xl6524612 style='border-left:none'>" . $row['user_id'] . "</td>
            <td class=xl6624612 style='border-left:none'><span style='mso-spacerun:yes'>            </span>" . $row['total_assign'] . " </td>
            <td class=xl6624612 style='border-left:none'><span style='mso-spacerun:yes'>         </span>" . $row['total_complete'] . " </td>
            <td class=xl7524612 style='border-left:none'><span style='mso-spacerun:yes'>           </span>" . $performance . " </td>
            <td class=xl6624612 style='border-left:none'><span style='mso-spacerun:yes'>       </span>" . $row['total_kpi'] . " </td>
            <td class=xl6624612 style='border-left:none'><span style='mso-spacerun:yes'>             </span>" . $row['total_qty'] . " </td>
            <td class=xl6624612 style='border-left:none'><span style='mso-spacerun:yes'>        </span>" . $row['total_profit'] . " </td>
            </tr>";

            $totalKpi += $row['total_kpi'];
            $totalQty += $row['total_qty'];
            $totalProfit += $row['total_profit'];
            $totalAssign += $row['total_assign'];
            $totalComplete += $row['total_complete'];
            $i++;
        }

        $totalPerformance = $totalAssign != 0 ? $totalComplete * 100 / $totalAssign : 0;

        $html .= '<tr>
            <td colspan="3" style="text-align: center;">Tổng cộng</td>
            <td class="number">' . format_number($totalAssign) . '</td>
            <td class="number">' . format_number($totalComplete) . '</td>
            <td class="number">' . round($totalPerformance, 2) . ' %</td>
            <td class="number">' . format_number($totalKpi) . '</td>
            <td class="number">' . format_number($totalQty) . '</td>
            <td class="number">' . format_number($totalProfit) . '</td>
        </tr>';

        $excelData .= "<tr height=17 style='height:12.75pt'>
            <td colspan=5 height=17 class=xl7224612 style='border-right:.5pt solid black; height:12.75pt'>Tổng cộng</td>
            <td class=xl7824612 style='border-left:none'><span style='mso-spacerun:yes'>            </span>" . $totalAssign . " </td>
            <td class=xl7824612 style='border-left:none'><span style='mso-spacerun:yes'>         </span>" . $totalComplete . " </td>
            <td class=xl7724612 style='border-left:none'><span style='mso-spacerun:yes'>           </span>" . $totalPerformance . " </td>
            <td class=xl7824612 style='border-left:none'><span style='mso-spacerun:yes'>       </span>" . $totalKpi . " </td>
            <td class=xl7824612 style='border-left:none'><span style='mso-spacerun:yes'>            </span>" . $totalQty . " </td>
            <td class=xl7824612 style='border-left:none'><span style='mso-spacerun:yes'>       </span>" . $totalProfit . " </td>
        </tr>";

        /**
         * Export Excel
         */
        if (isset($_REQUEST['btnExportExcel'])) {
            ob_clean();
            header('Pragma: cache');
            $excelTpl = file_get_contents('modules/EC_Flight_Bookings/tpls/excel_employeereport.tpl');
            $excelTpl = str_replace(array('{$FROM_DATE}', '{$TO_DATE}', '{$DATA}'), array($fromDate, $toDate, $excelData), $excelTpl);
            $excelTpl = chr(255) . chr(254) . mb_convert_encoding($excelTpl, 'UTF-16LE', 'UTF-8');
            header('Content-type: application/x-msdownload');
            header('Content-disposition: xls; filename=BXHNV_' . time() . '.xls; size=' . strlen($excelTpl));
            echo $excelTpl;
            exit;
        }

        $smartyObj->assign('FROM_DATE', $fromDate);
        $smartyObj->assign('TO_DATE', $toDate);
        $smartyObj->assign('DATA', $html);
    }
}
