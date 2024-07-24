<?php
class EC_Employee_SalaryController extends SugarController{
	public function process(){
		switch ($this->action) {
		case "DetailView":
			$this->action = "DetailView";
			break;
		case "EditView":
			$this->action = "EditView";
			break;
		case "Popup":
			$this->action = "Popup";
			break;
		case "index":
			$this->action = "employeesalary";
			break;
		// case "timesheets":
		// 	$this->action = "timesheets";
		// 	break;
		default:
			break;
		}		

		parent::process();

		if( $this->return_action == "index" )
			$this->action = "employeesalary";
	}
}
?>