<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Pure helpers for EC_Flight_Bookings detail view.
 */
class ECFlightBookingViewDetailSupportHelpers
{
	public static function normalizeAirlineCode($raw)
	{
		$map = [
			'VNA' => ['VN',  'VN',  'style="width:45px"'],
			'VJA' => ['VJ',  'VJ',  'style="width:45px"'],
			'VNP' => ['BL',  'VNP', 'style="width:45px"'],
			'BBA' => ['QH',  'QH',  'style="width:45px"'],
			'VTA' => ['VU',  'VTA', 'style="width:55px"'],
		];
		if (isset($map[$raw])) {
			return ['code' => $map[$raw][0], 'logo' => $map[$raw][1], 'img_style' => $map[$raw][2]];
		}
		return ['code' => $raw, 'logo' => $raw, 'img_style' => 'style="width:45px"'];
	}

	/**
	 * Extract the first two integers from a baggage text string.
	 * Returns [pack_count, weight_kg].
	 */

	public static function parseBaggageNumbers($text)
	{
		preg_match_all('/\d+/', $text, $matches);
		return [
			(int)($matches[0][0] ?? 0),
			(int)($matches[0][1] ?? 0),
		];
	}

	/**
	 * Walk the parent_detail_id chain upward to collect all ancestor IDs.
	 * Needed because assigned_user_id in itinerary changes may reference
	 * any version in the rename chain, not necessarily the latest one.
	 */
}
