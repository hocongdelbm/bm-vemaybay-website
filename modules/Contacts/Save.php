<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/Contacts/ContactFormBase.php');
$contactForm = new ContactFormBase();
$prefix = empty($_REQUEST['dup_checked']) ? '' : 'Contacts';
$contactForm->handleSave($prefix, true, false);
