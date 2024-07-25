<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarFeedFlush
{
    public function flushStaleEntries($bean, $event, $arguments)
    {
        $admin = BeanFactory::newBean('Administration');
        $admin->retrieveSettings();

        $timedate = TimeDate::getInstance();

        $currDate = $timedate->nowDbDate();
        if (isset($admin->settings['sugarfeed_flushdate']) && $admin->settings['sugarfeed_flushdate'] != $currDate) {
            $db = DBManagerFactory::getInstance();
            if (! isset($db)) {
                $db = DBManagerFactory::getInstance();
            }

            $tmpTime = time();
            $tmpSF = BeanFactory::newBean('SugarFeed');
            $flushBefore = $timedate->asDbDate($timedate->getNow()->modify("-14 days")->setTime(0, 0));
            $db->query("DELETE FROM ".$tmpSF->table_name." WHERE date_entered < '".$db->quote($flushBefore)."'");
            $admin->saveSetting('sugarfeed', 'flushdate', $currDate);
            // Flush the cache
            $admin->retrieveSettings(false, true);
        }
    }
}
