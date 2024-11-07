<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class EmployeesViewDetail extends ViewDetail
{
    function display()
    {
        if (is_admin($GLOBALS['current_user']) || $_REQUEST['record'] == $GLOBALS['current_user']->id || $GLOBALS['current_user']->title == 'QuanLy') {
            $this->ss->assign('DISPLAY_EDIT', true);
        }
        if (is_admin($GLOBALS['current_user'])) {
            $this->ss->assign('DISPLAY_DUPLICATE', true);
        }

        // leader 
        $leader = new User;
        $leader->retrieve($this->bean->leader_id);
        $this->ss->assign('LEADER_NAME', $leader->last_name . ' ' . $leader->first_name);
        $this->populateLineItems();
        $this->displayJS();
        parent::display();
    }

    function displayJS()
    {
        echo '';

        $js = '';

        $js .= '<script>
        $(document).ready(function() {
            $(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
           $("#detailpanel_4 > tbody > tr:nth-child(1) > td:nth-child(1)").remove();
           $("#detailpanel_4 > tbody > tr:nth-child(1) > td").attr("colspan","4");
        });
    </script>';

        $js .= '<script src="modules/Employees/js/view.detail_edit.js"></script>';

        echo $js;
    }

    function populateLineItems()
    {
        global $app_list_strings;

        $sql = "SELECT * FROM ec_workhistory d WHERE d.deleted = 0 and d.assigned_user_id = '" . $this->bean->id . "' ORDER BY date_start";

        $res = $this->bean->db->query($sql);
        // $row_count = $this->bean->db->getRowCount($res);
        $row_count = $this->bean->db->countRows($res);

        $html = '';
        $html .= '<table id="tbl_chitiet" cellpadding="0" cellspacing="0" border="0" class="table-tbl_chitiet table-details__booking">';
        $html .= '<thead><tr> 
                    <th width="2%">STT</th> 
                     <th width="15%">Ngày BĐ</th> 
                     <th width="15%">Ngày KT</th>
                     <th width="15%">Tình trạng</th>
                     <th width="10%">Tính lương</th>
                     <th>Ghi chú</th>  
                 </tr></thead>';
        $html .= '';

        $i = 0;
        while ($row = $this->bean->db->fetchByAssoc($res)) {
            $odd_or_even = $i % 2 > 0 ? 'even' : 'odd';

            $date_start = !empty($row['date_start']) ? date('d/m/Y', strtotime($row['date_start'])) : '';
            $date_end   = !empty($row['date_end']) ? date('d/m/Y', strtotime($row['date_end'])) : '';
            $checked    = $row['with_salary'] == 1 ? 'checked' : '';

            $html .= '<tr class="' . $odd_or_even . '">';
            $html .= '<td class="text-center">' . ($i + 1) . '</td>';
            $html .= '<td class="text-center">' . $date_start . '</td>';
            $html .= '<td class="text-center">' . $date_end . '</td>';
            $html .= '<td class="text-center">' . $app_list_strings['work_history_status_list'][$row['status']] . '</td>';
            $html .= '<td class="text-center"><input type="checkbox" onclick="checkWithSalary(' . $i . ');" id="ct_with_salary' . $i . '" name="ct_with_salary[]" ' . $checked . ' value="' . $row['with_salary'] . '"> </td>';
            $html .= '<td class="text-start">' . $row['description'] . '</td>';
            $i++;
        }
        $html .= '</table>';
        $this->ss->assign('WORK_HISTORY', $html);
    }
}
