<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/SugarFeed/feedLogicBase.php');


class ContactFeed extends FeedLogicBase
{
    public $module = 'Contacts';
    public function pushFeed($bean, $event, $arguments)
    {
        global $locale;
        $text = '';
        if (empty($bean->fetched_row)) {
            $text =  '{SugarFeed.CREATED_CONTACT} [' . $bean->module_dir . ':' . $bean->id . ':' . $locale->getLocaleFormattedName($bean->first_name, $bean->last_name) . ']';
        }
        
        if (!empty($text)) {
            SugarFeed::pushFeed2($text, $bean);
        }
    }
}
