<?php



/**
 * External API to meeting system
 * @api
 */
interface WebMeeting
{
    public function scheduleMeeting($bean);
    public function unscheduleMeeting($bean);
    public function inviteAttendee($bean, $attendee, $sendInvites = false);
    public function uninviteAttendee($bean, $attendee);
    public function listMyMeetings();
    public function getMeetingDetails($bean);
}
