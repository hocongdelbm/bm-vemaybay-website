<?php
class EC_Flight_BookingsController extends SugarController
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
                $this->action = "ListView";
                break;
            case "employeereport":
                $this->action = "employeereport";
                break;
            case "printeticket":
                $this->action = "printeticket";
                break;
            case "printeticketnew":
                $this->action = "printeticketnew";
                break;
            case "sendeticket":
                $this->action = "sendeticket";
                break;
            case "sendeticketnew":
                $this->action = "sendeticketnew";
                break;
            case "sendconfirm":
                $this->action = "sendconfirm";
                break;
            case "monthlyreport":
                $this->action = "monthlyreport";
                break;
            case "agentreport":
                $this->action = "agentreport";
                break;
            case "checkflydate":
                $this->action = "checkflydate";
                break;
            case "debtopay":
                $this->action = "debtopay";
                break;
            case "recoveryorder":
                $this->action = "recoveryorder";
                break;
            case "airportstatistics":
                $this->action = "airportstatistics";
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
            case "bkreport":
                $this->action = "bkreport";
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
            case "issueticket":
                $this->action = "issueticket";
                break;
            case "updateflightfare":
                $this->action = "updateflightfare";
                break;
            case "clientphonetcb":
                $this->action = "clientphonetcb";
                break;
            case "updateflight":
                $this->action = "updateflight";
                break;
            case "telesaleipmgr":
                $this->action = "telesaleipmgr";
                break;
            case "bookerips":
                $this->action = "bookerips";
                break;
            case "report_route_analysis":
                $this->action = "report_route_analysis";
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
        if ($this->return_action == "printeticket")
            $this->action = "printeticket";
        if ($this->return_action == "sendeticketnew")
            $this->action = "sendeticketnew";
        if ($this->return_action == "printeticketnew")
            $this->action = "printeticketnew";
        if ($this->return_action == "sendeticket")
            $this->action = "sendeticket";
        if ($this->return_action == "sendeticketnew")
            $this->action = "sendeticketnew";
        if ($this->return_action == "employeereport")
            $this->action = "employeereport";
        if ($this->return_action == "monthlyreport")
            $this->action = "monthlyreport";
        if ($this->return_action == "agentreport")
            $this->action = "agentreport";
        if ($this->return_action == "checkflydate")
            $this->action = "checkflydate";
        if ($this->return_action == "debtopay")
            $this->action = "debtopay";
        if ($this->return_action == "sendconfirm")
            $this->action = "sendconfirm";
        if ($this->return_action == "recoveryorder")
            $this->action = "recoveryorder";
        if ($this->return_action == "airportstatistics")
            $this->action = "airportstatistics";
        if ($this->return_action == "issueticket")
            $this->action = "issueticket";
        if ($this->return_action == "updateflight")
            $this->action = "updateflight";
        if ($this->return_action == "telesaleipmgr")
            $this->action = "telesaleipmgr";
        if ($this->return_action == "report_route_analysis")
            $this->action = "report_route_analysis";
    }
}
