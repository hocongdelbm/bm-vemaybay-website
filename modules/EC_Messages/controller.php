<?php
class EC_MessagesController extends SugarController{
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
                $this->action = "ListView";
                break;
            case "report":
                $this->action = "report";
                break;
            case "Save":
                $this->action = "Save";
                break;
            default:
                $this->action = "ListView";
                break;
		}		

		parent::process();
	}
}
?>
