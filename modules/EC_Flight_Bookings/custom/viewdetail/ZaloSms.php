<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Zalo/SMS journey and history helpers.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait ZaloSmsTrait
{
	public function getJourneysByBooking($bookingId): array {
		// Chuẩn bị dữ liệu hành trình gốc để gửi Zalo/ZBS: mã sân bay, giờ bay, số hiệu, hãng, hạng vé.
		$journeys = [];
		if (is_null($bookingId) || empty($bookingId)) return $journeys;

		$sql = "SELECT 
				iti.id,
				iti.departure,
				iti.arrival,
				iti.departure_date AS departure_date,
				iti.arrival_date AS arrival_date,
				iti.direction,
				iti.airline_code,
				iti.flight_number,
				iti.ticket_class
			FROM ec_booking_itineraries iti
			WHERE iti.booking_id = '$bookingId'
				AND iti.add_type = 0
				AND iti.deleted = 0
			ORDER BY iti.direction, iti.date_entered, iti.departure_date";

		$stt_dep = $stt_ret = 0;
		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$departure_code = $row['departure'] ?? '';
			$departure_name = EC_Airports::getCityName($departure_code) ?: $departure_code;

			$arrival_code = $row['arrival'] ?? '';
			$arrival_name = EC_Airports::getCityName($arrival_code) ?: $arrival_code;

			if ($row['direction'] == '0') {
				$departure_date = explode(' ', $row['departure_date']);
				$airline_code = EC_Airlines::normalizeIataCode($this->bean->airline);
				$airline_name = EC_Airlines::getAirlineName($airline_code) ?: $airline_code;

				if ($stt_dep == 0) {
					$journeys[$row['id']] = array(
						'type' => 'dep',
						'dep_code' => $departure_code,
						'arv_code' => $arrival_code,
						'date' => $departure_date[0],
						'time' => substr($departure_date[1], 0, -3),
						'flightno' => $row['flight_number'],
						'dep_name' => $departure_name . ' (' . $departure_code . ')',
						'arv_name' => $arrival_name . ' (' . $arrival_code . ')',
						'airline' => $airline_name ?? '',
						'datetime' => date('d/m/Y', strtotime($departure_date[0])) . ' ' . substr($departure_date[1], 0, -3),
						'class' => $row['ticket_class'],
					);
				} else {
					$journeys['dep']['arv_code'] = $arrival_code;
					$journeys['dep']['arv_name'] = $arrival_name . ' (' . $arrival_code . ')';
				}

				$stt_dep++;
			}

			// Lượt về
			if ($row['direction'] == '1') {
				$return_date = explode(' ', $row['departure_date']);
				$airline_code = EC_Airlines::normalizeIataCode($this->bean->airline_inbound);
				$airline_name = EC_Airlines::getAirlineName($airline_code) ?: $airline_code;

				if ($stt_ret == 0) {
					$journeys[$row['id']] = array(
						'type' => 'ret',
						'dep_code' => $row['departure'],
						'arv_code' => $arrival_code,
						'date' => $return_date[0],
						'time' => substr($return_date[1], 0, -3),
						'flightno' => $row['flight_number'],
						'dep_name' => $departure_name . ' (' . $departure_code . ')',
						'arv_name' => $arrival_name . ' (' . $arrival_code . ')',
						'airline' => $airline_name ?? '',
						'datetime' => date('d/m/Y', strtotime($return_date[0])) . ' ' . substr($return_date[1], 0, -3),
						'class' => $row['ticket_class'],
					);
				} else {
					$journeys['ret']['arv_code'] = $arrival_code;
					$journeys['ret']['arv_name'] = $arrival_name . ' (' . $arrival_code . ')';
				}

				$stt_ret++;
			}
		}

		return $journeys;
	}

	/**
	 * Get history sending ZBS messages
	 * 
	 * @param string $phoneNumber
	 * @param string $bookingId
	 * @return array
	 */

	public function getHistoryZBS($phoneNumber, $bookingId)
	{
		// Đếm số lần đã gửi từng loại tin Zalo/ZBS cho booking để hiển thị counter trên popup gửi tin.
		$result = [
			'journey' => 0,
			'payment' => 0,
			'code' => 0,
			'callsale' => 0,
			'remind' => 0,
			'delay' => 0,
		];

		if (empty($phoneNumber) || empty($bookingId)) return $result;

		$sql = "SELECT zm.sub_type, COUNT(*) AS count
			FROM ec_zalo_messages zm
			WHERE zm.booking_id = '$bookingId'
				AND (zm.type = 'zns' OR zm.type = 'zbs')
				AND (zm.to_id = '$phoneNumber')
				AND zm.deleted = 0
			GROUP BY zm.sub_type";

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if (stripos($row['sub_type'], 'journey') !== false)
				$result['journey'] += $row['count'];
			elseif (stripos($row['sub_type'], 'code') !== false)
				$result['code'] += $row['count'];
			elseif (stripos($row['sub_type'], 'payment') !== false)
				$result['payment'] += $row['count'];
			elseif (stripos($row['sub_type'], 'after-call-sale') !== false)
				$result['callsale'] += $row['count'];
			elseif (stripos($row['sub_type'], 'remind') !== false)
				$result['remind'] += $row['count'];
			elseif (stripos($row['sub_type'], 'delay') !== false)
				$result['delay'] += $row['count'];
		}

		return $result;
	}
}
