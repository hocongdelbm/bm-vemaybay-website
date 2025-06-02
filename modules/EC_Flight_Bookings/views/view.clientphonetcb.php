<?php
require_once("include/Sugar_Smarty.php");

class Viewclientphonetcb extends SugarView
{
    function display()
    {
        $this->displayStyle();
        $smarty = new Sugar_Smarty();

        // Helper function to convert dd-mm-yyyy to yyyy-mm-ddS
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
        $toDate   = isset($_GET['to_date']) ? $_GET['to_date'] : '';
        $smarty->assign('from_date', $fromDate);
        $smarty->assign('to_date', $toDate);

        // Convert to Y-m-d for filtering
        $fromYMD = !empty($fromDate) ? convertToYMD($fromDate) : '';
        $toYMD   = !empty($toDate)   ? convertToYMD($toDate)   : '';

        $fromTimestamp = !empty($fromYMD) ? strtotime($fromYMD) : null;
        $toTimestamp   = !empty($toYMD)   ? strtotime($toYMD)   : null;


        $source = isset($_GET['source']) ? $_GET['source'] : 'vietjet.net';
        $smarty->assign('source', $source);
        // Fetch data from API
        $responseData = $this->getDataFromTCB($source);
        $data = [];

        if (isset($responseData['status']) && $responseData['status'] === 'success') {
            $data = $responseData['data'];
        }

        // ✅ Fetch existing phone numbers from calls table
        global $db;
        $callMap = []; // call_to => call_id

        $res = $db->query("SELECT DISTINCT call_to, id FROM calls WHERE deleted = 0");
        while ($row = $db->fetchByAssoc($res)) {
            if (!empty($row['call_to']) && !empty($row['id'])) {
                $callMap[$row['call_to']] = $row['id'];
            }
        }

        foreach ($data as &$entry) {
            $phone = $entry['phone_number'];
            if (isset($callMap[$phone])) {
                $entry['in_calls'] = true;
                $entry['call_id'] = $callMap[$phone];
            } else {
                $entry['in_calls'] = false;
                $entry['call_id'] = '';
            }
        }
        unset($entry);

        if ($fromTimestamp || $toTimestamp) {
            $data = array_filter($data, function ($item) use ($fromTimestamp, $toTimestamp) {
                $itemDate = strtotime(substr($item['date_entered'], 0, 10)); // 'YYYY-MM-DD'
                return (!$fromTimestamp || $itemDate >= $fromTimestamp) &&
                    (!$toTimestamp   || $itemDate <= $toTimestamp);
            });

            $data = array_values($data); // reindex
        }

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
        $perPage = 50;
        $totalRows = count($data);
        $totalPages = ceil($totalRows / $perPage);
        $start = ($page - 1) * $perPage;
        $pagedData = array_slice($data, $start, $perPage);

        $baseUrl = 'index.php?module=EC_Flight_Bookings&action=clientphonetcb';
        if (!empty($fromDate)) $baseUrl .= '&from_date=' . urlencode($fromDate);
        if (!empty($toDate))   $baseUrl .= '&to_date=' . urlencode($toDate);

        // Pagination URLs
        $pageData = [
            'urls' => [
                'startPage' => $page > 1 ? $baseUrl . '&page=1' : '',
                'prevPage'  => $page > 1 ? $baseUrl . '&page=' . ($page - 1) : '',
                'nextPage'  => $page < $totalPages ? $baseUrl . '&page=' . ($page + 1) : '',
                'endPage'   => $page < $totalPages ? $baseUrl . '&page=' . $totalPages : '',
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

        // Navigation labels
        $navStrings = [
            'start' => 'Trang đầu',
            'previous' => 'Trước',
            'next' => 'Tiếp theo',
            'end' => 'Trang cuối',
            'of' => 'trên tổng số',
        ];

        // Assign to Smarty
        $smarty->assign('phone_data', $pagedData);
        $smarty->assign('pageData', $pageData);
        $smarty->assign('navStrings', $navStrings);
        $smarty->assign('action_menu_location', 'bottom');
        $smarty->assign('prerow', false);
        $smarty->assign('colCount', 6);

        // Display the template
        $smarty->display('modules/' . $this->bean->module_dir . '/tpls/view_phone_request_tcb.tpl');
        $this->displayScript();
    }

    function displayStyle()
    {
        $style = '';
        $style .= '<link type="text/css" rel="stylesheet" href="modules/' . $this->bean->module_dir . '/css/phone_request_tcb.css">';
        echo $style;
    }
    function displayScript()
    {
        $script = '';
        $script .= '<script src="modules/' . $this->bean->module_dir . '/js/phone_request_tcb.js"></script>';
        echo $script;
    }
    function getDataFromTCB($source)
    {

        $url = '';
        if ($source === 'timchuyenbay.vn') {
            $url = 'https://timchuyenbay.vn/ajax';
        } elseif ($source === 'vietjet.net') {
            $url = 'https://vietjet.net/ajax';
        } elseif ($source === 'timchuyenbay.com') {
            $url = 'https://timchuyenbay.com/ajax';
        } else {
            $url = 'https://vietjet.net/ajax'; // default fallback
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'action' => 'check_phone_request',
                'api_key' => 'bXj6EzseqvSef16ECeTh0BmWG8muRbCBa2j3EtxiSYkOxIJS9cmsKOQEcUw1qA8r',
            ),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));
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

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON decode error: " . json_last_error_msg();
            return false;
        }

        if (empty($data)) {
            echo "Empty data returned.";
            return false;
        }

        return $data;
    }
}
