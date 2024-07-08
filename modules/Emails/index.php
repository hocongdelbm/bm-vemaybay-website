<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
$focus = new Email();
$focus->email2init();
$focus->et->preflightUser($current_user);
$out = $focus->et->displayEmailFrame();
echo $out;
echo "<script>var composePackage = null;</script>";

$skipFooters = true;

