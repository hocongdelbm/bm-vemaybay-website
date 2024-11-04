<?php
class EC_TongHopController extends SugarController{
	public function process(){
		switch ($this->action) {
          case "DetailView":
               $this->action = "DetailView";
               break;
          case "EditView":
               $this->action = "EditView";
               break;
          case "Error":
               $this->action = "Error";
               break;
            case "index":
                $this->action = "bookingqtyreport";
                break;
          case "bookingqtyreport":
               $this->action = "bookingqtyreport";
               break;
          case "currentsales":
               $this->action = "currentsales";
               break;
          case "ticketreport":
               $this->action = "ticketreport";
               break;
          case "addbonus":
               $this->action = "addbonus";
               break;
          case "employeekpi":
               $this->action = "employeekpi";
               break;
          case "cashflow":
               $this->action = "cashflow";
               break;
          case "yearlyreport":
               $this->action = "yearlyreport";
               break;
          case "profitreport":
               $this->action = "profitreport";
               break;
            case "iplist":
                $this->action = "iplist";
                break;
            case "analytics":
                $this->action = "analytics";
                break;
          case "businessreport":
               $this->action = "businessreport";
               break;
            default:
                $this->action = "bookingqtyreport";
                break;
		}		

		parent::process();

          if( $this->return_action == "EditView" )
               $this->action = "EditView";
          if( $this->return_action == "DetailView" )
               $this->action = "DetailView";
		if( $this->return_action == "index" )
			$this->action = "bookingqtyreport";
          if( $this->return_action == "bookingqtyreport" )
               $this->action = "bookingqtyreport";
          if( $this->return_action == "currentsales" )
			$this->action = "currentsales";
          if( $this->return_action == "ticketreport" )
			$this->action = "ticketreport";
          if( $this->return_action == "addbonus" )
			$this->action = "addbonus";
          if( $this->return_action == "employeekpi" )
			$this->action = "employeekpi";
          if( $this->return_action == "profitreport" )
			$this->action = "profitreport";
          if( $this->return_action == "yearlyreport" )
			$this->action = "yearlyreport";
          if( $this->return_action == "cashflow" )
			$this->action = "cashflow";
          if( $this->return_action == "businessreport" )
			$this->action = "businessreport";
	}
}
?>
