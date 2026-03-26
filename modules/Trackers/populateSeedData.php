<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/Trackers/TrackerUtility.php');
require_once('install/UserDemoData.php');

class populateSeedData
{
    public $monitorIds = 500;
    public $user = 1;
    public $userDemoData;
    public $modules = array('Accounts', 'Calls', 'Contacts', 'Meetings', 'Notes', 'Opportunities', 'Users');
    public $actions = array('authenticate', 'detailview', 'editview', 'index', 'save', 'settimezone');
    public $db;
    public $beanIdMap = array();
    public $userSessions = array();
    public $trackerManager;

    public function start()
    {
        $this->db = DBManagerFactory::getInstance();
        $this->userDemoData = new UserDemoData($this->user, false);
        $this->trackerManager = TrackerManager::getInstance();

        foreach ($this->modules as $mod) {
            $query = "select id from $mod";
            $result = $this->db->limitQuery($query, 0, 50);
            $ids = array();
            while (($row = $this->db->fetchByAssoc($result))) {
                $ids[] = $row['id'];
            } //while
            $this->beanIdMap[$mod] = $ids;
        }

        while ($this->monitorIds-- > 0) {
            $this->monitorId = create_guid();
            $this->trackerManager->setMonitorId($this->monitorId);
            $this->user = $this->userDemoData->guids[array_rand($this->userDemoData->guids)];
            $this->module = $this->modules[array_rand($this->modules)];
            $this->action = $this->actions[array_rand($this->actions)];
            $this->date = $this->randomTimestamp();
            $this->populate_tracker();
            $this->trackerManager->save();
        }
    }

    public function populate_tracker()
    {
        if ($monitor = $this->trackerManager->getMonitor('tracker')) {
            $monitor->setValue('user_id', $this->user);
            $monitor->setValue('module_name', $this->module);
            $monitor->setValue('action', $this->action);
            $monitor->setValue('visible', (($monitor->action == 'detailview') || ($monitor->action == 'editview')) ? 1 : 0);
            $monitor->setValue('date_modified', $this->randomTimestamp());
            $monitor->setValue('session_id', $this->getSessionId());
            if ($this->action != 'settimezone' && isset($this->beanIdMap[$this->module][array_rand($this->beanIdMap[$this->module])])) {
                $monitor->setValue('item_id', $this->beanIdMap[$this->module][array_rand($this->beanIdMap[$this->module])]);
                $monitor->setValue('item_summary', 'random stuff'); //don't really need this
            }
        }
    }


    public function randomTimestamp()
    {
        global $timedate;
        // 1201852800 is 1 Feb 2008
        return $timedate->fromTimestamp(mt_rand(1201852800, $timedate->getNow()->ts))->asDb();
    }

    public function getSessionId()
    {
        if (isset($this->userSessions[$this->user])) {
            return $this->userSessions[$this->user];
        }
        $this->userSessions[$this->user] = $this->monitorId;
        return $this->monitorId;
    }
}

$test = new populateSeedData();
$test->start();
