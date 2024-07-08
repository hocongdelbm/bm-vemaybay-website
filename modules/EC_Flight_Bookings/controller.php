<?php
class EC_Flight_BookingsController extends SugarController{
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
            case "ticketreport":
                $this->action = "ticketreport";
                break;
            case "employeereport":
                $this->action = "employeereport";
                break;
            case "listemreport":
                $this->action = "listemreport";
                break;
            case "printeticket":
                $this->action = "printeticket";
                break;
            case "sendeticket":
                $this->action = "sendeticket";
                break;
            case "sendconfirm":
                $this->action = "sendconfirm";
                break;
            case "monthlyreport":
                $this->action = "monthlyreport";
                break;
            case "yearlyreport":
                $this->action = "yearlyreport";
                break;
            case "currentsales":
                $this->action = "currentsales";
                break;
            case "employeekpi":
                $this->action = "employeekpi";
                break;
            case "comparedebt":
                $this->action = "comparedebt";
                break;
            case "profitreport":
                $this->action = "profitreport";
                break;
            case "agentreport":
                $this->action = "agentreport";
                break;
            case "checkflydate":
                $this->action = "checkflydate";
                break;
            case "checksms":
                $this->action = "checksms";
                break;
            case "debtopay":
                $this->action = "debtopay";
                break;
            case "syncpnr":
                $this->action = "syncpnr";
                break;
            case "addbonus":
                $this->action = "addbonus";
                break;
            case "teamreport":
                $this->action = "teamreport";
                break;
            case "smstool":
                $this->action = "smstool";
                break;
            case "recoveryorder":
                $this->action = "recoveryorder";
                break;
            case "airportstatistics":
                $this->action = "airportstatistics";
                break;
            case "bookingqtyreport":
                $this->action = "bookingqtyreport";
                break;
            case "cashflow":
                $this->action = "cashflow";
                break;
            case "bksalereport":
                $this->action = "bksalereport";
                break;
            case "recheckbk":
                $this->action = "recheckbk";
                break;
            case "assignbk":
                $this->action = "assignbk";
                break;
            case "iplist":
                $this->action = "iplist";
                break;
            case "bkagent":
                $this->action = "bkagent";
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
            case "test":
                $this->action = "test";
                break;
            case "issueticket":
                $this->action = "issueticket";
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
		if( $this->return_action == "ticketreport" )
			$this->action = "ticketreport";
		if( $this->return_action == "printeticket" )
			$this->action = "printeticket";
		if( $this->return_action == "sendeticket" )
			$this->action = "sendeticket";
		if( $this->return_action == "employeereport" )
			$this->action = "employeereport";
		if( $this->return_action == "listemreport" )
			$this->action = "listemreport";
		if( $this->return_action == "monthlyreport" )
			$this->action = "monthlyreport";
		if( $this->return_action == "yearlyreport" )
			$this->action = "yearlyreport";
		if( $this->return_action == "currentsales" )
			$this->action = "currentsales";
		if( $this->return_action == "employeekpi" )
			$this->action = "employeekpi";
		if( $this->return_action == "comparedebt" )
			$this->action = "comparedebt";
		if( $this->return_action == "profitreport" )
			$this->action = "profitreport";
		if( $this->return_action == "agentreport" )
			$this->action = "agentreport";
		if( $this->return_action == "checkflydate" )
			$this->action = "checkflydate";
		if( $this->return_action == "checksms" )
			$this->action = "checksms";
		if( $this->return_action == "debtopay" )
			$this->action = "debtopay";
		if( $this->return_action == "syncpnr" )
			$this->action = "syncpnr";
		if( $this->return_action == "addbonus" )
			$this->action = "addbonus";
		if( $this->return_action == "sendconfirm" )
			$this->action = "sendconfirm";
		if( $this->return_action == "teamreport" )
			$this->action = "teamreport";
        if( $this->return_action == "smstool" )
            $this->action = "smstool";
        if( $this->return_action == "recoveryorder" )
            $this->action = "recoveryorder";
        if( $this->return_action == "airportstatistics" )
            $this->action = "airportstatistics";
     	if( $this->return_action == "bookingqtyreport" )
            $this->action = "bookingqtyreport";
     	if( $this->return_action == "issueticket" )
            $this->action = "issueticket";
	}
}
?>
