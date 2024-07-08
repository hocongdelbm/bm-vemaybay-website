<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class EmployeesViewEdit extends ViewEdit
{
    public $useForSubpanel = true;

    public function __construct()
    {
        parent::__construct();
    }

    function display()
    {
        if (is_admin($GLOBALS['current_user'])) {
            $json           = getJSONobj();
            require_once('include/QuickSearchDefaults.php');
            $qsd            = new QuickSearchDefaults();
            $sqs_objects    = array('EditView_reports_to_name' => $qsd->getQSUser());
            $sqs_objects['EditView_reports_to_name']['populate_list'] = array('reports_to_name', 'reports_to_id');
            $quicksearch_js = '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '; enableQS();</script>';

            $this->ss->assign('REPORTS_TO_JS', $quicksearch_js);
            $this->ss->assign('EDIT_REPORTS_TO', true);
        }

        if ($GLOBALS['current_user']->title == 'QuanLy' || is_admin($GLOBALS['current_user'])) {
            $this->ss->assign('IS_MANAGER', true);
        }

        $this->populateLineItems();
        $this->populateCustomFields();
        $this->displayJS();

        parent::display();
    }

    function populateLineItems()
    {
        global $app_list_strings;
        $html = '';
        $html .= '<table id="tbl_chitiet"  border="0" cellpadding="0" cellspacing="0" class="table-tbl_chitiet table-details__booking">';
        $html .= '<thead><tr id="first-row"> 
                     <th width="15%" align="center">Ngày BĐ</th> 
                     <th width="15%" align="center">Ngày KT</th>
                     <th width="15%" align="center">Tình trạng</th>
                     <th width="10%" align="center">Tính lương</th>
                     <th align="center">Ghi chú</th> 
                     <th width="2%" align="center">&nbsp;</th>
                 </tr></thead>';
        $html .= '';

        $sql = "SELECT * FROM ec_workhistory d WHERE d.deleted = 0 and d.assigned_user_id = '" . $this->bean->id . "' ORDER BY date_start";
        $res = $this->bean->db->query($sql);

        // $row_count = $this->bean->db->getRowCount($res);
        $row_count = $this->bean->db->countRows($res);

        $i = 0;
        while ($row = $this->bean->db->fetchByAssoc($res)) {

            $date_start = !empty($row['date_start']) ? date('d/m/Y', strtotime($row['date_start'])) : '';
            $date_end   = !empty($row['date_end']) ? date('d/m/Y', strtotime($row['date_end'])) : '';
            $checked    = $row['with_salary'] == 1 ? 'checked' : '';

            $html .= '<tr id="ct_line_' . $i . '">';
            $html .= '<td align="center"><input type="text" name="ct_date_start[]" id="ct_date_start' . $i . '" value="' . $date_start . '" class="date-jquery" placeholder="dd/mm/yyyy"/></td>';
            $html .= '<td align="center"><input type="text" name="ct_date_end[]" id="ct_date_end' . $i . '" value="' . $date_end . '" class="date-jquery" placeholder="dd/mm/yyyy"/></td>';
            $html .= '<td align="center"><select name="ct_status[]" id="ct_status' . $i . '">' . get_select_options_with_id($app_list_strings['work_history_status_list'], $row['status']) . '</select></td>';
            $html .= '<td align="center"><input type="checkbox" onclick="checkWithSalary(' . $i . ');" id="ct_chk_with_salary' . $i . '" name="ct_chk_with_salary[]" ' . $checked . ' value="' . $row['with_salary'] . '"> </td>';
            $html .= '<td align="center"><input type="text" name="ct_description[]" id="ct_description' . $i . '" value="' . $row['description'] . '" maxlength="255" /></td>';

            $html .= '<td align="center">
                            <button title="Xóa" class="button-remove-in-edit" type="button" onclick="markRowDeleted(' . $i . ')" >
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
                            </button> 
                             <input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted' . $i . '" />
                             <input type="hidden" name="ct_detail_id[]" id="ct_detail_id' . $i . '" value="' . $row['id'] . '" />
                             <input type="hidden" name="ct_with_salary[]" id="ct_with_salary' . $i . '" value="' . $row['with_salary'] . '" />
                        </td>
                    </tr>';
            $i++;
        }

        $html .= '<tr id="last-row" class="footer-tr">
                    <td align="left" coslpan="4">  
                        <input type="hidden" id="row_count" name="row_count" value="' . $row_count . '" />
                        <input type="button" class="btn btn-primary" id="btnAddRow" value="Thêm dòng" title="Thêm dòng" /> 
                    </td>
                    <td colspan="5"></td>
               </tr>';
        $html .= '</table>';
        $this->ss->assign('WORK_HISTORY', $html);
    }

    function displayJS()
    {
        $js = '';

        $js .= '<script>
         $(document).ready(function() { 
             $("#basic_salary, #efficient_wage, #target_month, #target_quarter, #target_year").attr("class", "allow-number-only");
             $("#gas_allowance, #lunch_allowance, #tele_allowance, #responsible_allowance, #seniority_allowance, #other_allowance1, #other_allowance2").attr("class", "allow-number-only");
             $(".allow-number-only").number(true, 0, dec_sep, num_grp_sep);
         });
     </script>';

        $js .= '<script src="modules/Employees/js/view.detail_edit.js"></script>';

        echo $js;
    }

    function populateCustomFields()
    {
        // ngày vào làm
        $start_date = '<input type="text" name="start_working_date" id="start_working_date" size="30" value="' . $this->bean->start_working_date . '" title="Ngày vào làm" tabindex="122">
            <img src="themes/SuiteP/images/Calendar.svg" alt="Ngày vào làm" id="start_working_date_trigger" class="cursor-pointer" align="absmiddle">
            <script>
                Calendar.setup({
                    inputField : "start_working_date",
                    daFormat : "%d-%m-%Y",
                    button : "start_working_date_trigger",
                    singleClick : true,
                    dateStr : "",
                    step : 1,
                    weekNumbers : false
                });
            </script>';
        $this->ss->assign('CUS_START_WORKING_DATE', $start_date);
    }
}
