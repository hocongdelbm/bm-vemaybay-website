<?php
class EC_HoaDonBanController extends SugarController
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
			case "index":
				$this->action = "inputinvoice";
				break;
			case "Save":
				$this->action = "Save";
				break;
			case "inputinvoice":
				$this->action = "inputinvoice";
				break;
			case "requestinvoice":
				$this->action = "requestinvoice";
				break;
			case "outputinvoice":
				$this->action = "outputinvoice";
				break;
			case "invoicereport":
				$this->action = "invoicereport";
				break;
			case "ioinvoice":
				$this->action = "ioinvoice";
				break;
			case "signedinvoice":
				$this->action = "signedinvoice";
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
		if ($this->return_action == "inhoadon")
			$this->action = "inhoadon";
		if ($this->return_action == "signedinvoice")
			$this->action = "signedinvoice";
	}
}
