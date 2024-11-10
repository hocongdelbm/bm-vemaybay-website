<?php
class EC_Receipt_VoucherController extends SugarController{
	public function process() {
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
			case "printrv":
				$this->action = "printrv";
				break;
			case "soquytienmat":
				$this->action = "soquytienmat";
				break;
			case "tienguinganhang":
				$this->action = "tienguinganhang";
				break;
			case "sokyquy":
				$this->action = "sokyquy";
				break;
			case "cashflow":
				$this->action = "cashflow";
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
			default:
				$this->action = "ListView";
				break;
		}		

		parent::process();

		if( $this->return_action == "EditView" )
			$this->action = "EditView";
		if( $this->return_action == "DetailView" )
			$this->action = "DetailView";
		if( $this->return_action == "index" )
			$this->action = "ListView";
		if( $this->return_action == "printrv" )
			$this->action = "printrv";
		if( $this->return_action == "soquytienmat" )
			$this->action = "soquytienmat";
		if( $this->return_action == "tienguinganhang" )
			$this->action = "tienguinganhang";
		if( $this->return_action == "Delete" )
			$this->action = "Delete";
		if( $this->return_action == "sokyquy" )
			$this->action = "sokyquy";
		if( $this->return_action == "cashflow" )
			$this->action = "cashflow";
	}
}
