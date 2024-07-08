<?php
require_once("include/Sugar_Smarty.php");

class Viewtest extends SugarView
{

     function display()
     {
          $smartyCont = new Sugar_Smarty();
          $this->populateContent($smartyCont);
          $this->checkStatusOnlineUser($smartyCont);
     }

     function populateContent($smartyobj)
     {
          global $app_list_strings, $current_user;
     }

     // Kiểm tra user còn online hay không
     function checkStatusOnlineUser()
     {
          global $db, $app_list_strings, $current_user;

          $json_file      = read_file_logs_online();
          $arr_user       = json_decode($json_file, true);

          pr($arr_user);

          return true;
     }
}
