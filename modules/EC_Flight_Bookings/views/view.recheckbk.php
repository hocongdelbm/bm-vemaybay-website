<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewrecheckbk extends SugarView {
    function display() {
        $smartyCont = new Sugar_Smarty();
        $this->populateContent($smartyCont);
        $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_recheckbk.tpl');
    }

    function populateContent($smarty) {
        global $app_list_strings;
        $params = array();
        // từ ngày
        if(!empty($_POST['from_date'])) {
            $from_date = date('Y-m-d', strtotime($_POST['from_date']));
        // } else $from_date = date('Y-m-01'); 
        } else $from_date = date('Y-m-d'); 
        // đến ngày
        if (!empty($_POST['to_date'])) {
            $to_date = date('Y-m-d', strtotime($_POST['to_date']));
        // } else $to_date = date('Y-m-t');
        } else $to_date = date('Y-m-d');
        // hãng 
        if(!empty($_POST['rc_airline'])) {
            $params['airline'] = $_POST['rc_airline'];
        }
        // thông tin recheck
        if (!empty($_POST['rc_inf'])) {
            $params['recheck'] = $_POST['rc_inf'];
        }
        // tìm những bk chưa đủ số lần recheck tối thiểu
        if (isset($_POST['multi_airline_recheck'])) {
            $params['multi_airline_recheck'] = $_POST['multi_airline_recheck'];
            $multi_check = 'checked';
            $other_search_disabled = 'disabled';
        } else {
            $multi_check = '';
            $other_search_disabled = '';
        }
        $smarty->assign('RECHECK_TBL', $this->populateRecheckTable($from_date, $to_date, $params));
        $smarty->assign('FROM_DATE', date('d-m-Y', strtotime($from_date)));
        $smarty->assign('TO_DATE', date('d-m-Y', strtotime($to_date)));

        $airline_arr = $app_list_strings['aircode_list'] + array('OTH' => 'Các hãng khác');

        $smarty->assign('RC_AIRLINE', get_select_options_with_id($airline_arr, $_POST['rc_airline']));

        $rc_inf = array(0 => 'Tất cả', 1 => 'Chưa có');

        $smarty->assign('RC_INF', get_select_options_with_id($rc_inf, $_POST['rc_inf']));

        $smarty->assign('RC_MULTI_MISSING', $multi_check);
        $smarty->assign('OTH_DISABLED', $other_search_disabled);
        $smarty->assign('TODAY', date('d-m-Y'));

        $smarty->assign('YESTERDAY', date('d-m-Y', strtotime('-1 day')));
        $smarty->assign('THISWEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
        $smarty->assign('THISWEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
        $smarty->assign('PREVWEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
        $smarty->assign('PREVWEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));
        $smarty->assign('THISMONTH_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
        $smarty->assign('THISMONTH_TODATE', date('d-m-Y', strtotime('last day of this month')));
        $smarty->assign('PREVMONTH_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
        $smarty->assign('PREVMONTH_TODATE', date('d-m-Y', strtotime('last day of last month')));
    }

    function populateRecheckTable($from_date, $to_date, $params = array()) {
        global $app_list_strings;
        $sql_having = ''; $sql_con = '';
        $sql_airline = '';
        $airline_arr = array(
            'VNA' => array('VNA', 'VN'),
            'VJA' => array('VJA', 'VJ')
        );

        // tìm hãng
        if(!empty($params['airline'])) {
            if($params['airline'] != 'OTH') {
                $sql_airline .= ' AND ';
                if(in_array($params['airline'], array_keys($airline_arr))) {
                    $new_airlinearr = $airline_arr[$params['airline']];
                    for($a = 0; $a < count($new_airlinearr); $a++) {
                        if($a != 0) {
                            $sql_airline .= ' OR ';
                        } else $sql_airline .= '(';
                        $sql_airline .= ' airline_code = "' . $new_airlinearr[$a] . '"';
                        if ($a == (count($new_airlinearr) - 1)) {
                            $sql_airline .= ')';
                        }
                    }
                } else {
                    $sql_airline .= ' airline_code = "' . $params['airline'] . '"';
                }
            } else {
                $aircode = array_keys($app_list_strings['aircode_list']);
                for($a = 0; $a < count($aircode); $a++) {
                    if(!empty($aircode[$a])) {
                        $new_airlinearr = $airline_arr[$aircode[$a]];
                        if(count($new_airlinearr)) {
                            for($b = 0; $b < count($new_airlinearr); $b++) {
                                $sql_airline .= ' AND airline_code <> "' . $new_airlinearr[$b] . '"';
                            }
                        } else {
                            $sql_airline .= ' AND airline_code <> "' . $aircode[$a] . '"';
                        }
                    }
                }
            }

            if (empty($sql_having)) $sql_having .= 'HAVING';
            else $sql_having .= ' AND ';
            $sql_having .= ' bk_airline IS NOT NULL';
        }

        // tìm thông tin recheck
        if (!empty($params['recheck'])) {
            if (empty($sql_having)) $sql_having .= 'HAVING';
            else $sql_having .= ' AND ';
            $sql_having .= ' recheck_inf IS NULL';
        }

        // tìm bk chưa đủ số lần recheck
        if(!empty($params['multi_airline_recheck'])) {
            $sql_recheck_cnt = '
                , (
                    SELECT COUNT(p.id)
                    FROM ec_working_process p 
                    INNER JOIN users u ON u.id = p.assigned_user_id
                    WHERE p.deleted = 0 AND p.recheck > 0
                    AND p.parent_id = b.id
                ) AS recheck_cnt
                , (
                    SELECT COUNT(DISTINCT IF(airline_code = "VNP", "VNA", airline_code))
                    FROM ec_booking_itineraries
                    WHERE deleted = 0 AND booking_id = b.id
                ) AS bk_airline_cnt
                ';
            if (empty($sql_having)) $sql_having .= 'HAVING';
            else $sql_having .= ' AND ';
            $sql_having .= ' recheck_cnt < bk_airline_cnt AND bk_airline_cnt > 1';
        }

        $sql = '
            SELECT 
                b.id AS booking_id, b.name AS booking
                , (
                    SELECT DATE_ADD(date_created, INTERVAL 7 HOUR)
                    FROM ec_flight_bookings_audit
                    WHERE parent_id = b.id
                        AND after_value_string = 7
                        AND before_value_string = 3
                    GROUP BY parent_id
                ) AS expticket_time
                , (
                    SELECT GROUP_CONCAT(DISTINCT airline_code)
                    FROM ec_booking_itineraries
                    WHERE booking_id = b.id AND deleted = 0
                    ' . $sql_airline . '
                ) AS bk_airline
                , (
                    SELECT GROUP_CONCAT(
                        CONCAT_WS("///", p.description, u.user_name, u.id, DATE_ADD(p.date_entered, INTERVAL 7 HOUR)) 
                        ORDER BY p.date_entered 
                        SEPARATOR "|||" 
                    )
                    FROM ec_working_process p 
                    INNER JOIN users u ON u.id = p.assigned_user_id
                    WHERE p.parent_id = b.id AND p.deleted = 0 AND p.recheck > 0
                ) AS recheck_inf
            ' . $sql_recheck_cnt . '
            FROM ec_flight_bookings b 
            WHERE
                ((
                    b.date_ticket_issue >= "' . $from_date . '"
                AND b.date_ticket_issue <= "' . $to_date . '"
                ) OR (
                    b.date_ticket_inbound_issue >= "' . $from_date . '"
                AND b.date_ticket_inbound_issue <= "' . $to_date . '"
                )) 
                AND b.deleted = 0
                ' . $sql_con . '
            GROUP BY b.id
        ' . $sql_having;

        $res    = $this->bean->db->query($sql);
        $html   = ''; 
        $bk_arr = array();

        while($row = $this->bean->db->fetchByAssoc($res)) {
            $bk_arr[] = $row;
            $sort_col[] = $row['expticket_time'];
        }

        array_multisort($sort_col, SORT_ASC, $bk_arr);
        for($i = 0; $i < count($bk_arr); $i++) {
            $bk_airline = explode(',', $bk_arr[$i]['bk_airline']);

            $color_airline = '';
            if($bk_airline[0] == 'VJA'){
                $color_airline = 'text-danger';
            } elseif($bk_airline[0] == 'BBA'){
                $color_airline = 'text-success';
            } elseif($bk_airline[0] == 'VNA'){
                $color_airline = 'text-warning';
            } elseif($bk_airline[0] == 'VTA'){
                $color_airline = 'text-primary';
            }  else{
                $color_airline = 'text-secondary';
            }

            $rw_span    = count($bk_airline);
            $html .= '
            <tr class="line' . $i . '">
                <td class="text-center fw-bold" rowspan="' . $rw_span  . '">' . ($i + 1) . '</td>
                <td class="text-center" rowspan="' . $rw_span  . '">' . date('d-m-Y', strtotime($bk_arr[$i]['expticket_time'])) . '    ' . date('H:i', strtotime($bk_arr[$i]['expticket_time'])) . '</td>
                <td class="text-center" rowspan="' . $rw_span  . '"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $bk_arr[$i]['booking_id'] . '" target="_blank">' . $bk_arr[$i]['booking'] . '</a></td>
                <td class="text-center"><span class="label_badge badge '.$color_airline.'">'.$bk_airline[0].'</span></td>
                <td class="recheck_col p-0" rowspan="' . $rw_span  . '" colspan="3">
                    ' . $this->genRecheck($bk_arr[$i]['recheck_inf']) . '
                </td>
            </tr>';

            for ($k = 1; $k < count($bk_airline); $k++) {
                $color_airline_k = '';
                if($bk_airline[$k] == 'VJA'){
                    $color_airline_k = 'text-danger';
                } elseif($bk_airline[$k] == 'BBA'){
                    $color_airline_k = 'text-success';
                } elseif($bk_airline[$k] == 'VNA'){
                    $color_airline_k = 'text-warning';
                } elseif($bk_airline[$k] == 'VTA'){
                    $color_airline_k = 'text-primary';
                } else{
                    $color_airline_k = 'text-secondary';
                }

                $html .= '
                <tr class="line' . $i . '">
                    <td class="text-center"><span class="label_badge badge '.$color_airline_k.'">'.$bk_airline[$k].'</span></td>
                </tr>
                ';
            }
        }
        return $html;
    }

    function genRecheck($recheck_inf) {
        $html           = '';
        $recheck_row    = explode('|||', $recheck_inf);

        for($i = 0; $i < count($recheck_row); $i++) {
            if(!empty($recheck_row[$i])) {
                $recheck_cell = explode('///', $recheck_row[$i]);
                
                $html .= '
                    <div class="d-flex align-items-center '.(($i > 0) ? 'border-top__table' : '') .'">
                        <div class="p-2 border-right__table w-60 text-start"><span class="text-primary fw-bold">Lần ' . ($i + 1) . ': </span> ' . $recheck_cell[0] . '</div>
                        <div class="p-2 w-20 border-right__table text-center"><a href="index.php?module=Users&return_module=Users&action=DetailView&record=' . $recheck_cell[2] . '">' . $recheck_cell[1] . '</a></div>
                        <div class="p-2 w-20 text-center">' . date('d-m-Y H:i', strtotime($recheck_cell[3])) . '</div>
                    </div>
                ';
                
            } else {
                    $html .= '<p class="p-2">(Chưa có)</p>';
            }
        }
        return $html;
    }

}
