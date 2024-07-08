<?php
class EC_SMS_LogsController extends SugarController
{
	public function process()
	{
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
			case "Save":
				$this->action = "Save";
				break;
			case "Delete":
				$this->action = "Delete";
				break;
			case "Error":
				$this->action = "Error";
				break;
			case "Export":
				$this->action = "Export";
				break;
			default:
				$this->action = "ListView";
				break;
		}

		parent::process();

		if ($this->return_action == "EditView")
			$this->action = "EditView";
		if ($this->return_action == "DetailView")
			$this->action = "DetailView";
		if ($this->return_action == "index")
			$this->action = "ListView";
		if ($this->return_action == "Delete")
			$this->action = "Delete";
		if ($this->return_action == "Export")
			$this->action = "Export";
	}
}
