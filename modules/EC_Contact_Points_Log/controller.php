<?php
class EC_Contact_Points_LogController extends SugarController{
	public function process() {
		switch ($this->action) {
            case "EditView":
                $this->action = "EditView";
                break;
            case "DetailView":
                $this->action = "DetailView";
                break;
            case "index":
                $this->action = "ListView";
                break;
            case "Save":
                $this->action = "Save";
                break;
            case "Delete":
                $this->action = "Delete";
                break;
            case "Error":
                $this->action = "Error";
                break;
            case "dashboard":
                $this->action = "dashboard";
                break;
            default:
                $this->action = "ListView";
                break;
		}		

		parent::process();
	}
}
?>
