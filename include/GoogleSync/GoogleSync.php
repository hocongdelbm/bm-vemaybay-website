<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once __DIR__ . '/GoogleSyncBase.php';
require_once __DIR__ . '/GoogleSyncHelper.php';

/**
 * Implements Google Calendar Syncing
 *
 * @license https://raw.githubusercontent.com/salesagility/SuiteCRM/master/LICENSE.txt
 * GNU Affero General Public License version 3
 * @author Benjamin Long <ben@offsite.guru>
 */

class GoogleSync extends GoogleSyncBase
{

    /** @var array An array of user id's we are going to sync for */
    protected $users = array();

    /**
     * Gets the combined titles of a Meeting/Event pair for Logging
     *
     * @param Meeting $meeting The CRM Meeting
     * @param \Google\Service\Calendar\Event $event The Google Event
     *
     * @return string The combined title
     */
    protected function getTitle(Meeting $meeting = null, Google\Service\Calendar\Event $event = null)
    {
        $meetingTitle = isset($meeting) ? $meeting->name : null;
        $eventTitle = isset($event) ? $event->getSummary() : null;

        if (!empty($meetingTitle) && !empty($eventTitle)) {
            $title = $meetingTitle . " / " . $eventTitle;
        }
        if (empty($meetingTitle) || empty($eventTitle)) {
            $title = $meetingTitle . $eventTitle;
        }
        if (empty($meetingTitle) && empty($eventTitle)) {
            $title = "UNNAMED RECORD";
        }
        return $title;
    }

    /**
     * Helper method for doSync
     *
     * @param string $action The action to take with the two events
     * @param Meeting $meeting The CRM Meeting
     * @param \Google\Service\Calendar\Event $event The Google Event
     *
     * @return bool Success/Failure
     * @throws GoogleSyncException if $action is invalid.
     * @throws GoogleSyncException if something else fails.
     */
    protected function doAction($action, Meeting $meeting = null, Google\Service\Calendar\Event $event = null)
    {
        $title = $this->getTitle($meeting, $event);

        switch ($action) {
            case "push":
                $this->logger->info(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - ' . 'Pushing Record: ' . $title);
                $ret = $this->pushEvent($meeting, $event);
                break;
            case "pull":
                $this->logger->info(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - ' . 'Pulling Record: ' . $title);
                $ret = $this->pullEvent($event, $meeting);
                break;
            case "skip":
                $this->logger->info(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - ' . 'Skipping Record: ' . $title);
                $ret = true;
                break;
            case "push_delete":
                $this->logger->info(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - ' . 'Push Deleting Record: ' . $title);
                $ret = $this->delEvent($event, $meeting->id);
                break;
            case "pull_delete":
                $this->logger->info(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - ' . 'Pull Deleting Record: ' . $title);
                $ret = $this->delMeeting($meeting);
                break;
            default:
                throw new GoogleSyncException('Unknown Action: ' . $action . ' for record: ' . $title, GoogleSyncException::INVALID_ACTION);
        }

        if ($ret) {
            $this->syncedList[] = $ret;
            return true;
        }
        //else
        throw new GoogleSyncException('Something went wrong with the requested action');
    }

    /**
     * Perform the sync for a user
     *
     * @param string $id The SuiteCRM user id
     *
     * @return bool true, unless an exception is thrown by called function
     */
    public function doSync($id)
    {
        $this->initUserService($id);

        $meetings = $this->getUserMeetings($id);

        // First, we look for SuiteCRM meetings that are not on Google
        foreach ($meetings as $meeting) {
            $gevent = null;
            if (!empty($meeting->gsync_id)) {
                $gevent = $this->getGoogleEventById($meeting->gsync_id);
            }
            $action = $this->pushPullSkip($meeting, $gevent);
            $actionResult = $this->doAction($action, $meeting, $gevent);
        }

        // Now, we look at the Google Calendar
        $googleEvents = $this->getUserGoogleEvents();

        foreach ($googleEvents as $gevent) {
            $meeting = $this->getMeetingByEventId($gevent->getId());
            $action = $this->pushPullSkip($meeting, $gevent);
            $actionResult = $this->doAction($action, $meeting, $gevent);
        }
        return true;
    }

    /**
     * Add a user to the list of users to sync
     *
     * The user id is used as the key
     *
     * @param string $id : the SuiteCRM user id
     * @param string $name : the SuiteCRM user name.
     *  Not really used for anything other than reference.
     *
     * @return bool Success/Failure
     */
    protected function addUser($id, $name)
    {
        if (array_key_exists($id, $this->users)) {
            $this->logger->warn(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - ' . $id . ' already set');
            return false;
        }
        $this->users[$id] = $name;
        $this->logger->info(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - ' . $id . ' set to ' . $this->users[$id]);
        return true;
    }

    /**
     * Figure out if we need to push/pull an update, or do nothing.
     *
     * Used when an event w/ a matching ID is on both ends of the sync.
     * At least one of the params is required.
     *
     * @param Meeting|null $meeting (optional) Meeting Bean or Google\Service\Calendar\Event Object
     * @param \Google\Service\Calendar\Event|null $event (optional) Google\Service\Calendar\Event Object
     *
     * @return string|bool 'push(_delete)', 'pull(_delete)', 'skip', false (on error)
     */
    protected function pushPullSkip(Meeting $meeting = null, Google\Service\Calendar\Event $event = null)
    {
        if (empty($meeting) && empty($event)) {
            throw new GoogleSyncException('Missing Parameter, You must pass at least one event');
        }

        $helper =  new GoogleSyncHelper;

        // Did we only get one event?
        if (empty($meeting) || empty($event)) {
            // If we only got one event, figure out which kind it is, and pass the return from the helper method
            return $helper->singleEventAction($meeting, $event);
        }

        // Get array of timestamps for this event
        $timeArray = $helper->getTimeStrings($meeting, $event);

        // Can we skip this event?
        if ($helper->isSkippable($meeting, $event, $timeArray, $this->syncedList)) {
            return "skip";
        }

        // Event was modified since last sync
        return $helper->getNewestMeetingResponse($meeting, $event, $timeArray);
    }

    /**
     * Setup array of users to sync
     *
     * Fills the $users array with users that are configured to sync
     *
     * @param array $tempData Debug info
     * @return int added users
     * @throws GoogleSyncException if unable to get user bean
     */
    protected function setSyncUsers(&$tempData = [])
    {
        $query = "SELECT id FROM users WHERE deleted = '0'";
        $result = $this->db->query($query);
        if (!$result) {
            throw new GoogleSyncException('Unable to get any User bean to sync Google.', GoogleSyncException::UNABLE_TO_RETRIEVE_USER_ALL);
        }

        $counter = 0;
        $tempData['founds'] = 0;
        while ($row = $this->db->fetchByAssoc($result)) {
            $tempData['founds']++;
            $tmp = [];

            $user = BeanFactory::getBean('Users', $row['id']);
            if (!$user) {
                throw new GoogleSyncException('Unable to get User bean. ID was: ' . $row['id'], GoogleSyncException::UNABLE_TO_RETRIEVE_USER);
            }

            if ($tmp['notEmpty'] = !empty($user->getPreference('GoogleApiToken', 'GoogleSync')) &&
                $tmp['decoded'] = json_decode(base64_decode($user->getPreference('GoogleApiToken', 'GoogleSync'))) &&
                $tmp['syncPref'] = $user->getPreference('syncGCal', 'GoogleSync')
            ) {
                if ($tmp['added'] = $this->addUser($user->id, $user->full_name)) {
                    $counter++;
                }
            }
            $tempData['results'][] = $tmp;
        }

        return $counter;
    }

    /**
     * Sync All Configured Users
     *
     * Running this method will collect all users who
     * have Calendar Sync Configured and Enabled and
     * Sync them one by one.
     *
     * @return bool Success/Failure
     */
    public function syncAllUsers()
    {
        // First we populate the array of syncable users
        $ret = $this->setSyncUsers();

        if (!$ret) {
            $this->logger->info(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - There is no user to sync..');
            return true; // No users to sync, so we just return. This is not an error.
        }

        // We count failures here
        $failures = 0;

        // Then we go though the array and sync the users with doSync()
        if (isset($this->users) && !empty($this->users)) {
            foreach (array_keys($this->users) as $key) {
                try {
                    $this->doSync($key);
                } catch (Exception $e) { // We need to catch any exception here, otherwise the foreach loop cannot continue to the next user.
                    // FUTURE: We'll inform the user that the sync failed.
                    $this->logger->fatal('Caught Exception While Syncing User:' . $key);
                    $this->logger->error($e->getTraceAsString());
                    $failures++;
                }
            }
        }
        if ($failures == 0) {
            return true;
        }
        $this->logger->warn(__FILE__ . ':' . __LINE__ . ' ' . __METHOD__ . ' - ' . $failures . ' failure(s) found at syncAllUsers method.');
        return false;
    }
}
