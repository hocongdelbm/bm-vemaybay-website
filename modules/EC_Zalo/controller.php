<?php
class EC_ZaloController extends SugarController
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
			case "chatzalo":
				$this->action = "chatzalo";
				break;
			default:
				$this->action = "chatzalo";
				break;
		}

		parent::process();

		if ($this->return_action == "EditView")
			$this->action = "EditView";
		if ($this->return_action == "DetailView")
			$this->action = "DetailView";
		if ($this->return_action == "index")
			$this->action = "chatzalo";
		if ($this->return_action == "Delete")
			$this->action = "Delete";
		if ($this->return_action == "Export")
			$this->action = "Export";
		if ($this->return_action == "chatzalo")
            $this->action = "chatzalo";
	}
}
