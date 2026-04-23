<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/SugarView.php');

class Viewtypereports extends SugarView
{
    public function display()
    {
        global $current_user;

        $con_return = $this->populateCondition();

        $smartyCont = new Sugar_Smarty();
        $this->assignFields($con_return, $smartyCont);
        $smartyCont->display('modules/Calls/tpls/typereports.tpl');
    }

    function populateCondition()
    {
        $currYear      = date('Y');
        $reportTime    = 'năm ' . date('Y');
        $type_call = '';

        if (isset($_REQUEST['type_call']) && !empty($_REQUEST['type_call'])) {
            $type_call = $_REQUEST['type_call'];
        }

        if (isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
            $from_date = date('d-m-Y', strtotime($_REQUEST['from_date']));
        } else {
            $from_date = date('d-m') . '-' . ($currYear - 1);
            $_REQUEST['from_date'] = $from_date;
        }

        if (isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
            $to_date = date('d-m-Y', strtotime($_REQUEST['to_date']));
        } else {
            $to_date = date('d-m-Y', strtotime($currYear));
            $_REQUEST['to_date'] =  $to_date;
        }

        return [
            'curr_year'    => $currYear,
            'report_time'  => $reportTime,
            'type_call'    => $type_call,
            'from_date'    => $from_date,
            'to_date'      => $to_date,
        ];
    }

    function assignFields($con, $smarty)
    {
        global $app_list_strings;

        $smarty->assign('MODULE_NAME', $this->bean->module_dir);
        $smarty->assign('FROM_DATE', $con['from_date']);
        $smarty->assign('TO_DATE', $con['to_date']);
        $smarty->assign('TYPE_CALL', $con['type_call']);

        $data_call_reason = $this->getCallReason($con);
        if(empty($data_call_reason)) {
            $smarty->assign('LIST_CALL_DATA', '');
            $smarty->assign('LIST_CALL_REASON_LABEL', '');
            $smarty->assign('LIST_CALL_REASON_DATA', '');
            return;
        }
        
        $smarty->assign('LIST_CALL_DATA', $this->renderDataListCallReason($data_call_reason['data']));

        $reason_keys = array_keys($data_call_reason['summary']);
        $reason_labels = [];
        foreach ($reason_keys as $key) {
            $reason_labels[] = $app_list_strings['call_reason_list'][$key] ?? 'Chưa phân loại'; // fallback nếu không tồn tại label
        }

        $smarty->assign('LIST_CALL_REASON_LABEL', implode('|', $reason_labels));
        $smarty->assign('LIST_CALL_REASON_DATA', implode('|', array_values($data_call_reason['summary'])));
    }

    function getCallReason($con)
    {
        $direction = $con['type_call'];
        $from_date = $con['from_date'];
        $to_date = $con['to_date'];

        if (empty($direction) || empty($from_date) || empty($to_date)) return array();

        $result = [];
        $reason_count = [];

        $query = "SELECT id, name, status, call_from, call_to, call_duration, call_talk, date_start, call_reason, description
                FROM calls 
                WHERE direction = '$direction' AND deleted = 0
                AND DATE_ADD(date_entered, INTERVAL 7 HOUR) BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . " 23:59:59'";

        $rs = $this->bean->db->query($query);
        while ($row = $this->bean->db->fetchByAssoc($rs)) {
            if (!empty($row['call_reason'])) {
                $result[$row['call_reason']][] = $row;
            } else {
                $result['empty'][] = $row;
                $row['call_reason'] = 'in_empty';
            }

            // Đếm số lượng theo từng loại
            if (!isset($reason_count[$row['call_reason']])) {
                $reason_count[$row['call_reason']] = 0;
            }
            $reason_count[$row['call_reason']]++;
        }

        return [
            'data' => $result,
            'summary' => $reason_count,
        ];
    }

    function renderDataListCallReason($data)
    {
        global $app_list_strings;

        $html = '<table class="table-list__customer table-details__booking table__sticky text-nowrap" cellpadding="0" cellspacing="0" width="100%">
                        <thead>
                                <tr>
                                    <th width="3%" align="center">#</th>
                                    <th width="10%" align="left">Cuộc gọi</th>
                                    <th width="10%" align="center">Trạng thái</th>
                                    <th width="8%" align="center">Gọi từ</th>
                                    <th width="8%" align="center">Gọi đến</th>
                                    <th width="10%" align="center">Thời gian gọi</th>
                                    <th width="10%" align="center">Thời lượng</th>
                                    <th width="10%" align="center">Hội thoại</th>
                                    <th align="left" class="text-wrap">Ghi chú</th>
                                </tr>
                        </thead>
                        <tbody>';

        if (is_array($data) && count($data) > 0) {
            foreach ($data as $reason => $item) {
                $text_reason = $app_list_strings['call_reason_list'][$reason] ?? 'Chưa phân loại';
                $html .= '<tr>
                            <td colspan="9" class="text-left bg-warning"><strong>' . $text_reason . '</strong> - Tổng: <strong>' . count($item) . '</strong></td>
                        </tr>';
                foreach ($item as $key => $value) {
                    // Class status
                    $status_class = 'text-normal';
                    if ($value['status'] === 'processing') {
                        $status_class = 'text-warning';
                    } else if ($value['status'] === 'done') {
                        $status_class = 'text-success';
                    }
                                           
                    $html .= '<tr>
                                <td align="center" class="fw-semibold">' . ($key + 1) . '</td>
                                <td align="left"><a href="index.php?module=Calls&action=DetailView&record=' . $value['id'] . '" target="_blank">' . $value['name'] . '</a></td>
                                <td align="center" class="fw-semibold ' . $status_class . '">' . $app_list_strings['call_status_dom'][$value['status']] . '</td>
                                <td align="center">' . $value['call_from'] . '</td>
                                <td align="center">' . $value['call_to'] . '</td>
                                <td align="center">' . $value['date_start'] . '</td>
                                <td align="center">' . global_secondsToTimeFormat($value['call_duration']) . '</td>
                                <td align="center">' . global_secondsToTimeFormat($value['call_talk']) . '</td>
                                <td align="left" class="text-wrap">' . $value['description'] . '</td>
                            </tr>';
                }
            }
        } else {
            $html .= '<tr><td colspan="9" class="text-center">Không có dữ liệu</td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
