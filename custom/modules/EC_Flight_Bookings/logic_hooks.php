<?php
$hook_version = 1;
$hook_array = array();
$hook_array["process_record"] = array();
$hook_array["process_record"][] = array(
    1,
    "customDisplay",
    "custom/modules/EC_Flight_Bookings/EC_Flight_BookingsLogicHook.php",
    "EC_Flight_BookingsLogicHook",
    "customDisplay"
);
$hook_array['process_record'][] = array(
    2,
    'getRecallValue',
    'custom/modules/EC_Flight_Bookings/EC_Flight_BookingsLogicHook.php',
    'EC_Flight_BookingsLogicHook',
    'getRecallValue'
);

$hook_array["before_delete"] = array();
$hook_array["before_delete"][] = array(
    1,
    "checkBeforeDelete",
    "custom/modules/EC_Flight_Bookings/EC_Flight_BookingsLogicHook.php",
    "EC_Flight_BookingsLogicHook",
    "checkBeforeDelete"
);

$hook_array["before_save"] = array();
$hook_array["before_save"][] = array(
    1,
    "checkBeforeSave",
    "custom/modules/EC_Flight_Bookings/EC_Flight_BookingsLogicHook.php",
    "EC_Flight_BookingsLogicHook",
    "checkBeforeSave"
);

$hook_array["after_save"] = array();
$hook_array["after_save"][] = array(
    1,
    "autoAssignBooking",
    "custom/modules/EC_Flight_Bookings/EC_Flight_BookingsLogicHook.php",
    "EC_Flight_BookingsLogicHook",
    "autoAssignBooking"
);
$hook_array["after_save"][] = array(
    2,
    "updateKPI",
    "custom/modules/EC_Flight_Bookings/EC_Flight_BookingsLogicHook.php",
    "EC_Flight_BookingsLogicHook",
    "updateKPI"
);
$hook_array["after_save"][] = array(
    3,
    "updateVoucher",
    "custom/modules/EC_Flight_Bookings/EC_Flight_BookingsLogicHook.php",
    "EC_Flight_BookingsLogicHook",
    "updateVoucher"
);
$hook_array["after_save"][] = array(
    4,
    "getVoucher",
    "custom/modules/EC_Flight_Bookings/EC_Flight_BookingsLogicHook.php",
    "EC_Flight_BookingsLogicHook",
    "getVoucher"
);
