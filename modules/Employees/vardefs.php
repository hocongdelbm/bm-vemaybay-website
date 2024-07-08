<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $dictionary;
if (empty($dictionary['User'])) {
    include('modules/Users/vardefs.php');
}
$dictionary['Employee']=$dictionary['User'];
//users of employees modules are not allowed to change the employee/user status.
$dictionary['Employee']['fields']['status']['massupdate']=false;
$dictionary['Employee']['fields']['is_admin']['massupdate']=false;

//begin bug 48033
$dictionary['Employee']['fields']['UserType']['massupdate']=false;
$dictionary['Employee']['fields']['messenger_type']['massupdate']=false;
$dictionary['Employee']['fields']['email_link_type']['massupdate']=false;
//end bug 48033
$dictionary['Employee']['fields']['email1']['required']=false;
$dictionary['Employee']['fields']['email_addresses']['required']=false;
$dictionary['Employee']['fields']['email_addresses_primary']['required']=false;
// bugs 47553 & 49716
$dictionary['Employee']['fields']['status']['studio']=false;
$dictionary['Employee']['fields']['status']['required']=false;
