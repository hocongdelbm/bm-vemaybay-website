<?php
class CallsController extends SugarController
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
               case "statistics":
                    $this->action = "statistics";
                    break;
               case "summary":
                    $this->action = "summary";
                    break;
               case "employee_report":
                    $this->action = "employee_report";
                    break;
               case "manage":
                    $this->action = "manage";
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

          if ($this->return_action == "EditView")
               $this->action = "EditView";
          if ($this->return_action == "DetailView")
               $this->action = "DetailView";
          if ($this->return_action == "index")
               $this->action = "ListView";
          if ($this->return_action == "statistics")
               $this->action = "statistics";
          if ($this->return_action == "summary")
               $this->action = "summary";
          if ($this->return_action == "manage")
               $this->action = "manage";
     }
}
