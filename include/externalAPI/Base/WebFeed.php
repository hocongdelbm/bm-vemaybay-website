<?php



/**
 * External API to news feed system
 * @api
 */
interface WebFeed
{
    public function getLatestUpdates($maxTime, $maxEntries);
}
