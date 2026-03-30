<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class AlertsViewEdit extends ViewEdit
{
    function __construct()
    {
        parent::__construct();
    }

    function display()
    {
        global $current_user;

        $this->displayJS();
        $this->displayCSS();

        $this->populateCustomFields();
        parent::display();
    }

    function displayCSS() {
		$css = '';
		$css .= '<link type="text/css" rel="stylesheet" href="themes/SuiteP/libs/css/select2.min.css">';
		echo $css;
	}

    function displayJS() {
		$js = '';
		$js .= '<script>
                    $(document).ready(function() {
                        $("#employee-select").select2();
                    });
                </script>';

		echo $js;
	}

    /* TRƯỜNG DỮ LIỆU */
    function populateCustomFields()
    {
		$employee_arr = $this->getEmployeeList();
		$employee_list = '<select id="employee-select" required multiple name="list_employee_id[]"><option value="all">Tất cả</option>'.get_select_options_with_id($employee_arr, $this->bean->assigned_user_id).'</select>';
		$this->ss->assign('EMPLOYEE_NAME', $employee_list);
    }

    // Lấy danh sách nhân viên
	function getEmployeeList() {
		$sql = 'SELECT id, CONCAT(last_name, " ", IFNULL(first_name, "")) AS full_name 
				FROM users 
				WHERE deleted = 0 
				AND status = "Active" 
				AND title NOT IN ("Admin", "Bot")';
		$res = $this->bean->db->query($sql);
		while($row = $this->bean->db->fetchByAssoc($res)) {
			$employee_list[$row['id']] = $row['full_name'];
		}

		return $employee_list;
	}
}
