<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $theme;
global $sugar_config;
global $current_language;
$currentMax = $sugar_config['list_max_entries_per_page'];
$sugar_config['list_max_entries_per_page'] = 10;

$current_mod_strings = return_module_language($current_language, 'Emails');
$focus			= new Email();
$ListView 		= new ListView();
$display_title	= $current_mod_strings['LBL_LIST_TITLE_MY_INBOX'].': '.$current_mod_strings['LBL_UNREAD_HOME'];
$where			= 'emails.deleted = 0 AND emails.assigned_user_id = \''.$current_user->id.'\' AND emails.type = \'inbound\' AND emails.status = \'unread\'';
$limit			= 10;
///////////////////////////////////////////////////////////////////////////////
////	OUTPUT
///////////////////////////////////////////////////////////////////////////////
echo $focus->rolloverStyle;
$ListView->initNewXTemplate('modules/Emails/ListViewHome.html',$current_mod_strings);
$ListView->xTemplateAssign('ATTACHMENT_HEADER', SugarThemeRegistry::current()->getImage('attachment',"","",""));
$ListView->setHeaderTitle($display_title);
$ListView->setQuery($where, '', 'date_sent, date_entered DESC', "EMAIL");
$ListView->setAdditionalDetails();
$ListView->processListView($focus, 'main', 'EMAIL');

//echo $focus->quickCreateJS();

$sugar_config['list_max_entries_per_page'] = $currentMax;
?>
