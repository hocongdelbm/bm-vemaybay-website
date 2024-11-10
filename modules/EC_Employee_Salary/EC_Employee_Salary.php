<?php

class EC_Employee_Salary extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Employee_Salary';
    public $object_name = 'EC_Employee_Salary';
    public $table_name = 'ec_employee_salary';
    public $importable = true;

	public $disable_row_level_security = true ; // to ensure that modules created and deployed under CE will continue to function under team security if the instance is upgraded to PRO

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $SecurityGroups;

    public $currency_id;
    public $month;
    public $year;
    public $basic_salary;
    public $efficient_wage;
    public $gas_allowance;
    public $lunch_allowance;
    public $tele_allowance;
    public $responsible_allowance;
    public $seniority_allowance;
    public $other_allowance1;
    public $other_allowance2;
    public $social_insurance;
    public $com_social_insurance;
    public $emp_social_insurance;
    public $com_health_insurance;
    public $emp_health_insurance;
    public $com_accident_insurance;
    public $emp_accidient_insurance;
    public $minus;
    public $effort;
	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    public function checkApproved($month, $year) {
        $sql = 'SELECT is_approved, approved_date 
                FROM ec_employee_salary 
                WHERE deleted = 0 
                AND month = "' . $month . '" 
                AND year = "' . $year . '"
                LIMIT 1';
        $res = $this->bean->db->query($sql);
        $row = $this->bean->db->fetchByAssoc($res);
        return array(
           'is_approved' => $row['is_approved'], 
           'approved_date' => $row['approved_date']
       );
    }

}
