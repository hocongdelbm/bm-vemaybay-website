<?php
class EC_BonusController extends SugarController
{
    public function process()
    {
        switch ($this->action) {
            case "DetailView":
                $this->action = "";
                break;
            case "EditView":
                $this->action = "";
                break;
            case "Popup":
                $this->action = "";
                break;
            case "index":
                $this->action = "bonus_report";
                break;
            case "calculate_bonus":
                $this->action = "calculate_bonus";
                break;
            case "calculate_bonus_by_source":
                $this->action = "calculate_bonus_by_source";
                break;
            default:
                $this->action = "bonus_report";

                break;
        }

        parent::process();
    }
}
