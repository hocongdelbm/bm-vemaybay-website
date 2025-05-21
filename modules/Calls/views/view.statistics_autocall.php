<?php
require_once("include/Sugar_Smarty.php");

class Viewstatistics_autocall extends SugarView
{
    function display()
    {
        if (ACLController::checkAccess('Calls', 'list', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/Calls/tpls/statistics_autocall.tpl');
        } else {
            header("Location: index.php?module=Calls&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smartyobj)
    {
        global $current_user;

        $from_date = date('d-m-Y');
        $to_date   = date('d-m-Y');

        if (!empty($_POST['from_date'])) {
            $from_date = $_POST['from_date'];
        } else {
            $from_date = date('d-m-Y', strtotime('+7 hours'));
        }

        if (!empty($_POST['to_date'])) {
            $to_date = $_POST['to_date'];
        } else {
            $to_date = date('d-m-Y', strtotime('+7 hours'));
        }

        switch (ceil(date('n') / 3)) {
            case 1:
                $quater_fromdate = '01-01-' . date('Y');
                $quater_todate = '31-03-' . date('Y');
                break;
            case 2:
                $quater_fromdate = '01-04-' . date('Y');
                $quater_todate = '30-06-' . date('Y');
                break;
            case 3:
                $quater_fromdate = '01-07-' . date('Y');
                $quater_todate = '30-09-' . date('Y');
                break;
            case 4:
                $quater_fromdate = '01-10-' . date('Y');
                $quater_todate = '31-12-' . date('Y');
                break;
            default:
                break;
        }
        $arr_date = array(
            '<option value="" fromdate="" todate="">---Trống---</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'yesterday' ? 'selected' : '') . ' value="yesterday" fromdate="' . date('d-m-Y', strtotime('-1 day')) . '" todate="' . date('d-m-Y', strtotime('-1 day')) . '">Hôm qua</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'daybefore' ? 'selected' : '') . ' value="daybefore" fromdate="' . date('d-m-Y', strtotime('-2 day')) . '" todate="' . date('d-m-Y', strtotime('-2 day')) . '">Hôm trước</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'current_week' ? 'selected' : '') . ' value="current_week" fromdate="' . date('d-m-Y', strtotime('monday this week')) . '" todate="' . date('d-m-Y', strtotime('sunday this week')) . '">Tuần này</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_week' ? 'selected' : '') . ' value="previous_week" fromdate="' . date('d-m-Y', strtotime('monday previous week')) . '" todate="' . date('d-m-Y', strtotime('sunday previous week')) . '">Tuần trước</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'this_month' ? 'selected' : '') . ' value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_month' ? 'selected' : '') . ' value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'quater_this_month' ? 'selected' : '') . ' value="quater_this_month" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'quater_previous_month' ? 'selected' : '') . ' value="quater_previous_month" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'this_year' ? 'selected' : '') . ' value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
            '<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_year' ? 'selected' : '') . ' value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
        );
        $smartyobj->assign('DATE_OPTION', implode('', $arr_date));
        $smartyobj->assign('FROM_DATE', $from_date);
        $smartyobj->assign('TO_DATE', $to_date);
        $smartyobj->assign('LIST_AUTOCALL', $this->listAutocall($from_date, $to_date));
    }

    function listAutocall($from_date, $to_date)
    {
        global $db, $current_user, $app_list_strings;
        $html = '';

        $query = "SELECT * 
                    FROM calls 
                    WHERE DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . " 23:59:59'
                    AND type_call_sources = 'autocall'
                    AND deleted = 0
                    ORDER BY date_entered DESC";
        $i   = 1;
        $res = $db->query($query);
        while ($row = $db->fetchByAssoc($res)) {
            $html .= '<tr>
                        <td align="center" class="fw-semibold hide-mobile">' . $i . '</td>
                        <td>' . $row['name'] . '</td>
                        <td>' . date('d-m-Y H:i:s', strtotime($row['date_start'])) . '</td>
                        <td align="center">' . $app_list_strings['call_status_dom'][$row['status']] . '</td>
                        <td>' . $row['description'] . '</td>
                    </tr>';
            $i++;
        }

        return $html;
    }
}
