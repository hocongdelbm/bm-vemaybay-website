<?php
require_once("include/Sugar_Smarty.php");

class Viewclientphonetcb extends SugarView
{

    function display()
    {
        $this->displayStyle();
        $smarty = new Sugar_Smarty();

        // Function to convert date format from dd-mm-yyyy to yyyy-mm-dd
        function convertToYMD($dateStr)
        {
            $parts = explode('-', $dateStr); // dd-mm-yyyy
            if (count($parts) === 3) {
                return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
            return '';
        }

        // Get date filters from GET parameters
        $fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
        $toDate = isset($_GET['to_date']) ? $_GET['to_date'] : '';
        $smarty->assign('from_date', $fromDate);
        $smarty->assign('to_date', $toDate);

        // Convert to Y-m-d for filtering
        $fromYMD = !empty($fromDate) ? convertToYMD($fromDate) : '';
        $toYMD = !empty($toDate) ? convertToYMD($toDate) : '';
        $fromTimestamp = !empty($fromYMD) ? strtotime($fromYMD) : null;
        $toTimestamp = !empty($toYMD) ? strtotime($toYMD) : '';

        $source = isset($_GET['source']) ? $_GET['source'] : 'timchuyenbay.com';
        $smarty->assign('source', $source);

        $sources = ['timchuyenbay.vn', 'vietjet.net', 'timchuyenbay.com'];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
        $perPage = 50; 
        $offset = ($page - 1) * $perPage;
        $data = [];

        // Fetch data from the API
        foreach ($sources as $currentSource) {
            if ($currentSource === $source) {
                $responseData = $this->getDataFromTicketWebsite($currentSource, $fromDate, $toDate, $offset, $perPage, 0);  // offset, limit, is_used

                if (isset($responseData['message']) && $responseData['message'] === 'Success') {
                    $data = array_merge($data, $responseData['data']);
                }
            }
        }

        // Zalo ZNS
        global $db;
        $zaloZnsQuery = "
            SELECT send_to, id
            FROM ec_messages
            WHERE type = 'zalo_zns'
            AND category = 'customer_care'
            AND parent_type != 'EC_Flight_Bookings'
        ";
        $zaloZnsRes = $db->query($zaloZnsQuery);
        $zaloZnsMap = [];
        while ($row = $db->fetchByAssoc($zaloZnsRes)) {
            $zaloZnsMap[$row['send_to']] = $row['id'];
        }

        // Get Calls Data
        $minDateEntered = null;
        foreach ($data as $entry) {
            $currentDate = strtotime($entry['date_entered']);
            if ($minDateEntered === null || $currentDate < $minDateEntered) {
                $minDateEntered = $currentDate;
            }
        }
        $minDateAdjusted = $minDateEntered - 7 * 3600;

        $query = "
            SELECT call_to, id, date_modified
            FROM calls
            WHERE deleted = 0
            AND date_modified > '" . date('Y-m-d H:i:s', $minDateAdjusted) . "' 
        ";
        $res = $db->query($query);
        $callMap = [];
        while ($row = $db->fetchByAssoc($res)) {
            if (!empty($row['call_to'])) {
                $phone = $row['call_to'];
                $dateModified = strtotime($row['date_modified']);
                if (!isset($callMap[$phone]) || $dateModified > strtotime($callMap[$phone]['date_modified'])) {
                    $callMap[$phone] = [
                        'id' => $row['id'],
                        'date_modified' => $row['date_modified'],
                    ];
                }
            }
        }
        // check recall
        $callCheck = isset($_GET['call_recheck']) && $_GET['call_recheck'] == 'on';
        $smarty->assign('callCheck', $callCheck);

        // Get Booking Names
        $bookingIds = array_column($data, 'booking_id');
        $bookingIds = array_filter($bookingIds);

        if (!empty($bookingIds)) {
            $bookingIdsStr = "'" . implode("', '", $bookingIds) . "'";

            $query = "
                SELECT id, name
                FROM ec_flight_bookings
                WHERE id IN ($bookingIdsStr)
            ";

            $bookingRes = $db->query($query);
            $bookingMap = [];

            while ($booking = $db->fetchByAssoc($bookingRes)) {
                $bookingMap[$booking['id']] = $booking['name'];
            }

            foreach ($data as &$entry) {
                if (!empty($entry['booking_id']) && isset($bookingMap[$entry['booking_id']])) {
                    $entry['booking_name'] = $bookingMap[$entry['booking_id']];
                } else {
                    $entry['booking_name'] = '';
                }
            }
        }

        // Add additional information to the data
        foreach ($data as &$entry) {
            $entry['is_zns'] = isset($zaloZnsMap[$entry['phone_number']]) ? true : false;

            if ($entry['is_zns']) {
                $entry['zns_id'] = $zaloZnsMap[$entry['phone_number']];
            }

            $entryDateAdjusted = strtotime($entry['date_entered']) - 7 * 3600;
            $phone = $entry['phone_number'];

            if (isset($callMap[$phone])) {
                $callDate = strtotime($callMap[$phone]['date_modified']);
                $entry['in_calls'] = $entryDateAdjusted < $callDate;
                $entry['call_id'] = $entry['in_calls'] ? $callMap[$phone]['id'] : '';
            } else {
                $entry['in_calls'] = false;
                $entry['call_id'] = '';
            }

            $entry['source'] = isset($source) ? $source : 'Unknown';
        }

        // Filter by call recheck checkbox
        if ($callCheck) {
            $data = array_filter($data, function ($entry) {
                return !$entry['in_calls'];
            });
        }

        // Filter by date range
        if ($fromTimestamp || $toTimestamp) {
            $data = array_filter($data, function ($item) use ($fromTimestamp, $toTimestamp) {
                $itemDate = strtotime(substr($item['date_entered'], 0, 10)); // 'YYYY-MM-DD'
                return (!$fromTimestamp || $itemDate >= $fromTimestamp) &&
                    (!$toTimestamp   || $itemDate <= $toTimestamp);
            });
            $data = array_values($data);
        }

        // Pagination calculation
        $totalRows = count($data);
        $totalPages = ceil($totalRows / $perPage);
        $start = ($page - 1) * $perPage;
        $pagedData = array_slice($data, $start, $perPage);

        $baseUrl = 'index.php?module=EC_Flight_Bookings&action=clientphonetcb';

        if ($fromDate !== '' && $fromDate !== null) {
            $baseUrl .= '&from_date=' . urlencode($fromDate);
        }
        if ($toDate !== '' && $toDate !== null) {
            $baseUrl .= '&to_date=' . urlencode($toDate);
        }

        if (!empty($source)) $baseUrl .= '&source=' . urlencode($source);
        if (isset($_GET['call_recheck']) && $_GET['call_recheck'] == 'on') {
            $baseUrl .= '&call_recheck=on';
        }

        $pageData = [
            'urls' => [
                'startPage' => $page > 1 ? $baseUrl . '&page=1' : '',
                'prevPage' => $page > 1 ? $baseUrl . '&page=' . ($page - 1) : '',
                'nextPage' => $page < $totalPages ? $baseUrl . '&page=' . ($page + 1) : '',
                'endPage' => $page < $totalPages ? $baseUrl . '&page=' . $totalPages : '',
            ],
            'offsets' => [
                'current' => $start,
                'lastOffsetOnPage' => $start + count($pagedData),
                'total' => $totalRows,
                'totalCounted' => true,
                'next' => min($start + $perPage, $totalRows),
                'prev' => max(0, $start - $perPage),
            ],
        ];

        // Navigation labels for pagination
        $navStrings = [
            'start' => 'Trang đầu',
            'previous' => 'Trước',
            'next' => 'Tiếp theo',
            'end' => 'Trang cuối',
            'of' => 'trên tổng số',
        ];

        // Assign the processed data and other variables to Smarty
        $smarty->assign('phone_data', $pagedData);
        $smarty->assign('pageData', $pageData);
        $smarty->assign('navStrings', $navStrings);
        $smarty->assign('action_menu_location', 'bottom');
        $smarty->assign('prerow', false);
        $smarty->assign('colCount', 7);

        // Display the template
        $smarty->display('modules/' . $this->bean->module_dir . '/tpls/view_phone_request_tcb.tpl');
        $this->displayScript();
    }
    // Function for displaying styles
    function displayStyle()
    {
        $style = '';
        $style .= '<link type="text/css" rel="stylesheet" href="modules/' . $this->bean->module_dir . '/css/phone_request_tcb.css">';
        echo $style;
    }

    // Function for displaying scripts
    function displayScript()
    {
        $script = '';
        $script .= '<script src="modules/' . $this->bean->module_dir . '/js/phone_request_tcb.js"></script>';
        echo $script;
    }

    function getDataFromTicketWebsite($source, $fromDate, $toDate, $offset = 0, $limit = 50, $isUsed = 0)
    {
        $url = '';
        if ($source === 'timchuyenbay.vn') {
            $url = 'https://timchuyenbay.vn/api';
        } elseif ($source === 'vietjet.net') {
            $url = 'https://vietjet.net/api';
        } elseif ($source === 'timchuyenbay.com') {
            $url = 'https://timchuyenbay.com/api';
        } else {
            $url = 'https://vietjet.net/api'; // Default fallback
        }

        if ($fromDate === '') {
             $fromDate = date('d-m-Y', strtotime('-30 days'));
        }
        if ($toDate === '') {
            $toDate = date('d-m-Y');
        }

        // Prepare the request payload with dynamic parameters
        $data = json_encode([
            'action' => 'get_list_phone_request',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'offset' => $offset,
            'limit' => 50,
            'is_used' => $isUsed
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Api-Key: 4F3yBy83DIRuHaFp6e@alBkS-sb3T3)kvZ8$-MqM', 
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'cURL error: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }

        curl_close($curl);

        if (!$response) {
            echo "No response from API.";
            return false;
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $responseData;
    }
}
