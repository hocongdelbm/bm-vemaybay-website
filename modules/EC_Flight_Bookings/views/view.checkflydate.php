<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewcheckflydate extends SugarView
{
    private const ASSET_VERSION = '1.3.1';

    private const RECORDS_PER_PAGE = 20;

    private const AIRLINE_GROUPS = [
        'VJ'  => ['VJ', 'VJA'],
        'VJA' => ['VJ', 'VJA'],
        'VN'  => ['VN', 'VNA'],
        'VNA' => ['VN', 'VNA'],
    ];

    private const AIRLINE_ICON_MAP = [
        'VNA' => 'VN',
        'VN'  => 'VNA',
        'VJA' => 'VJ',
        'JET' => 'BL',
        'BBA' => 'QH',
        'VNP' => 'VNP',
        'VTA' => 'VTA',
    ];

    public function display()
    {
        if (!ACLController::checkAccess($this->bean->object_name, 'list', true)) {
            header('Location: index.php?module=' . $this->bean->object_name . '&action=Error&error_string='
                . urlencode('Bạn không được quyền truy cập vào mục này'));
            exit();
        }

        $smarty = new Sugar_Smarty();
        $this->populateContent($smarty);
        $smarty->display('modules/' . $this->bean->object_name . '/tpls/view_checkflydate.tpl');
    }

    public function populateContent($smarty)
    {
        global $app_list_strings;

        $tungay  = !empty($_POST['tungay'])  ? $_POST['tungay']  : date('d-m-Y');
        $denngay = !empty($_POST['denngay']) ? $_POST['denngay'] : date('d-m-Y');
        $this->validateDateRange($tungay, $denngay);

        $page = !empty($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
        $offset = ($page - 1) * self::RECORDS_PER_PAGE;

        $filters = ['phone' => '', 'passenger' => '', 'user_id' => '', 'email' => ''];
        $searchClause = $this->buildSearchClause($tungay, $denngay, $filters);

        $airlineXml = $this->getAirlineData();
        $iconMap    = $this->buildAirlineIconMap($airlineXml);

        [$html, $totalRecords] = $this->renderRows($searchClause, $iconMap, $offset);
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / self::RECORDS_PER_PAGE) : 1;
        $startRecord = ($page - 1) * self::RECORDS_PER_PAGE + 1;
        $endRecord = min($page * self::RECORDS_PER_PAGE, $totalRecords);

        $smarty->assign('DATA', $html);
        $smarty->assign('VERSION', self::ASSET_VERSION);
        $smarty->assign('POST_TUNGAY', $tungay);
        $smarty->assign('POST_DENNGAY', $denngay);
        $smarty->assign('SEARCH_PHONE', $filters['phone']);
        $smarty->assign('SEARCH_PASSENGER', $filters['passenger']);
        $smarty->assign('SEARCH_EMAIL', $filters['email']);
        $smarty->assign('USER_LIST', myGetSelectOptionsWithDb(
            'Users',
            $filters['user_id'],
            'id',
            " AND title IN ('Booker','KeToan','Leader') AND status='Active' ORDER BY first_name ASC "
        ));
        $smarty->assign('AIRLINES', get_select_options_with_id(
            ($app_list_strings['aircode_list'] + $airlineXml),
            $_POST['airlines'] ?? ''
        ));
        $smarty->assign('CURRENT_PAGE', $page);
        $smarty->assign('TOTAL_PAGES', $totalPages);
        $smarty->assign('TOTAL_RECORDS', $totalRecords);
        $smarty->assign('START_RECORD', $startRecord);
        $smarty->assign('END_RECORD', $endRecord);
        $smarty->assign('RECORDS_PER_PAGE', self::RECORDS_PER_PAGE);

        $this->assignDatePresets($smarty);
    }

    private function validateDateRange($tungay, $denngay)
    {
        $days = (strtotime($denngay) - strtotime($tungay)) / 86400;

        if ($days < 0) {
            echo '<p class="error">Đến ngày phải lớn hơn hoặc bằng Từ ngày</p>';
            exit;
        }
    }

    /**
     * Builds the dynamic WHERE fragment from the POSTed filters and writes the
     * sanitized values back into $filters for redisplay.
     */
    private function buildSearchClause($tungay, $denngay, array &$filters)
    {
        global $db;

        $from = date('Y-m-d', strtotime($tungay));
        $to   = date('Y-m-d', strtotime($denngay));
        $clause = " AND i.departure_date >= '{$from} 00:00:00' AND i.departure_date <= '{$to} 23:59:59'";

        if (!empty($_POST['airlines'])) {
            $airline = $_POST['airlines'];
            if (isset(self::AIRLINE_GROUPS[$airline])) {
                $quoted = [];
                foreach (self::AIRLINE_GROUPS[$airline] as $code) {
                    $quoted[] = "'" . $db->quote($code) . "'";
                }
                $clause .= " AND i.airline_code IN (" . implode(',', $quoted) . ")";
            } else {
                $clause .= " AND i.airline_code='" . $db->quote($airline) . "'";
            }
        }

        // Assigned user.
        if (!empty($_POST['user_id'])) {
            $filters['user_id'] = preg_replace('/[^0-9a-zA-Z\-]/', '', $_POST['user_id']);
            $clause .= " AND b.assigned_user_id='" . $db->quote($filters['user_id']) . "'";
        }

        // Booking phone.
        if (!empty($_POST['search_phone'])) {
            $phone = preg_replace('/[^0-9\+\-\(\)\s]/', '', trim($_POST['search_phone']));
            $filters['phone'] = $phone;
            if ($phone !== '') {
                $clause .= " AND b.phone LIKE '" . $db->quote('%' . $phone . '%') . "'";
            }
        }

        // Passenger name.
        if (!empty($_POST['search_passenger'])) {
            $passenger = trim($_POST['search_passenger']);
            $filters['passenger'] = $passenger;
            if ($passenger !== '') {
                $clause .= " AND i.booking_id IN (SELECT DISTINCT booking_id FROM ec_booking_passengers"
                    . " WHERE deleted = 0 AND name LIKE '" . $db->quote('%' . $passenger . '%') . "')";
            }
        }

        // Email.
        if (!empty($_POST['search_email'])) {
            $email = trim($_POST['search_email']);
            $filters['email'] = $email;
            if ($email !== '') {
                $clause .= " AND b.email LIKE '" . $db->quote('%' . $email . '%') . "'";
            }
        }

        return $clause;
    }

    private function buildSql($searchClause, $offset = 0)
    {
        return "SELECT i.id AS itinerary_id,
                    b.id AS booking_id,
                    b.name AS booking,
                    b.contact_name,
                    b.phone,
                    b.email,
                    i.departure,
                    i.arrival,
                    i.departure_date,
                    i.arrival_date,
                    i.airline_code,
                    i.flight_number,
                    i.base_price,
                    i.ticket_class,
                    b.date_ticket_issue,
                    i.checkin_status,
                    i.is_remind,
                    i.description AS notes,
                    COALESCE(d_sum.total_qty, 0) AS total_qty,
                    p_max.complete_time
                FROM ec_booking_itineraries i
                INNER JOIN ec_flight_bookings b ON i.booking_id = b.id AND b.deleted = 0
                LEFT JOIN (
                    SELECT booking_id, direction, SUM(IFNULL(quantity, 0)) AS total_qty
                    FROM ec_booking_details
                    WHERE deleted = 0
                    GROUP BY booking_id, direction
                ) d_sum ON d_sum.booking_id = i.booking_id AND d_sum.direction = i.direction
                LEFT JOIN (
                    SELECT parent_id, MAX(DATE_ADD(date_entered, INTERVAL 7 HOUR)) AS complete_time
                    FROM ec_working_process
                    WHERE completed = 1 AND deleted = 0
                    GROUP BY parent_id
                ) p_max ON p_max.parent_id = b.id
                WHERE b.booking_status IN ('7','8')" . $searchClause . "
                    AND i.deleted = 0
                    AND (i.add_type = 0 OR NOT EXISTS (
                        SELECT 1 FROM ec_booking_itineraries
                        WHERE booking_id = i.booking_id AND add_type = 3 AND deleted = 0
                    ))
                GROUP BY i.id, i.booking_id, b.id, b.name, b.contact_name, b.phone, b.email, i.departure, i.arrival,
                         i.departure_date, i.arrival_date, i.airline_code, i.flight_number, i.base_price,
                         i.ticket_class, b.date_ticket_issue, i.checkin_status, i.is_remind, i.description, d_sum.total_qty, p_max.complete_time
                ORDER BY b.date_ticket_issue DESC, p_max.complete_time DESC
                LIMIT " . self::RECORDS_PER_PAGE . " OFFSET " . (int)$offset;
    }

    private function renderRows($searchClause, array $iconMap, $offset = 0)
    {
        global $db, $app_list_strings;

        $totalRecords = $this->getTotalRecords($searchClause);
        $res  = $db->query($this->buildSql($searchClause, $offset));
        $html = '';
        $stt  = $offset + 1;

        if ($res) {
            while ($row = $db->fetchByAssoc($res)) {
                $html .= $this->renderRow($row, $stt++, $iconMap, $app_list_strings);
            }
        }

        return [$html, $totalRecords];
    }

    private function getTotalRecords($searchClause)
    {
        global $db;

        $sql = "SELECT COUNT(DISTINCT i.id) as total FROM ec_booking_itineraries i
                INNER JOIN ec_flight_bookings b ON i.booking_id = b.id AND b.deleted = 0
                WHERE b.booking_status IN ('7','8')" . $searchClause . "
                    AND i.deleted = 0
                    AND (i.add_type = 0 OR NOT EXISTS (
                        SELECT 1 FROM ec_booking_itineraries
                        WHERE booking_id = i.booking_id AND add_type = 3 AND deleted = 0
                    ))";

        $res = $db->query($sql);
        if (!$res) {
            return 0;
        }
        $row = $db->fetchByAssoc($res);
        return $row ? (int)$row['total'] : 0;
    }

    private function renderRow(array $row, $stt, array $iconMap, array $app_list_strings)
    {
        $complete_time = empty($row['complete_time']) ? '' : date('d/m/Y H:i:s', strtotime($row['complete_time']));

        $row_style = '';
        $row_class = '';
        if ($row['is_remind'] == 1) {
            $row_style = 'background: #cfeafe';
            $row_class = 'remind';
        }

        $checkin_class = '';
        if ($row['checkin_status'] == 1) {
            $checkin_class = 'text-danger';
        } elseif ($row['checkin_status'] == 2) {
            $checkin_class = 'text-success';
        }

        $ticket_class = strpos($row['ticket_class'], '-') !== false
            ? substr($row['ticket_class'], strpos($row['ticket_class'], '-') + 1)
            : $row['ticket_class'];

        $airline_icon = $iconMap[$row['airline_code']] ?? $row['airline_code'];

        $note_html = !empty($row['notes'])
            ? '<div class="note-display min-w-150 text-justify" data-itinerary-id="' . $row['itinerary_id'] . '">' . htmlspecialchars($row['notes']) . '</div>'
            : '<div class="note-display note-empty" data-itinerary-id="' . $row['itinerary_id'] . '"></div>';

        return '<tr class="' . $row_class . '" style="' . $row_style . '" data-itinerary-id="' . $row['itinerary_id'] . '" data-notes="' . htmlspecialchars($row['notes']) . '">
                        <td class="fw-semibold hide-mobile" align="center">' . $stt . '</td>
                        <td class="fw-semibold cell-booking" data-label="Booking" align="center"><a target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '">' . $row['booking'] . '</a></td>
                        <td data-label="Liên hệ" align="left">' . $row['contact_name'] . '</td>
                        <td class="cell-checkin" data-label="Checkin" align="left">
                            <div class="checkin-cell">
                                <span class="fw-semibold ' . $checkin_class . '">' . $app_list_strings['booking_checkin_status_list'][$row['checkin_status']] . '</span>
                                <button type="button" class="btn-notes" data-itinerary-id="' . $row['itinerary_id'] . '" title="Thêm/sửa ghi chú"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/></svg></button>
                            </div>
                            ' . $note_html . '
                        </td>
                        <td data-label="Điện thoại">
                            <div class="lh-lg">
                                <div class="fw-semibold">' . $row['phone'] . '</div>
                                <div class="small text-dark text-break">' . (!empty($row['email']) ? $row['email'] : '') . '</div>
                            </div>
                        </td>
                        <td align="center" class="hide-mobile">
                            <img style="width:40px;" src="custom/themes/default/images/airline-icon-100x100/' . $airline_icon . '.png" border="0" />
                        </td>
                        <td align="center" class="hide-mobile">' . $row['flight_number'] . '</td>
                        <td align="center" class="hide-mobile">' . $row['departure'] . '-' . $row['arrival'] . '</td>
                        <td data-label="Ngày giờ bay" align="center">' . date('d/m/Y', strtotime($row['departure_date'])) . '<br>' . date('H:i', strtotime($row['departure_date'])) . ' - ' . date('H:i', strtotime($row['arrival_date'])) . '</td>
                        <td align="center" class="hide-mobile">' . $ticket_class . '</td>
                        <td align="right" class="hide-mobile">' . format_number($row['base_price']) . '</td>
                        <td align="center" class="hide-mobile">' . format_number($row['total_qty']) . '</td>
                        <td align="center" class="hide-mobile">' . date('d/m/Y', strtotime($row['date_ticket_issue'])) . '</td>
                        <td align="center" class="hide-mobile">' . $complete_time . '</td>
                    </tr>';
    }

    private function assignDatePresets($smarty)
    {
        $smarty->assign('TODAY', date('d-m-Y'));
        $smarty->assign('YESTERDAY', date('d-m-Y', strtotime('-1 day')));
        $smarty->assign('THISWEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
        $smarty->assign('THISWEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
        $smarty->assign('LAST7_FROMDATE', date('d-m-Y', strtotime('-7 days')));
        $smarty->assign('LAST7_TODATE', date('d-m-Y'));
        $smarty->assign('THISMONTH_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
        $smarty->assign('THISMONTH_TODATE', date('d-m-Y', strtotime('last day of this month')));
        $smarty->assign('PREVMONTH_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
        $smarty->assign('PREVMONTH_TODATE', date('d-m-Y', strtotime('last day of last month')));
    }

    private function buildAirlineIconMap(array $airlineXml)
    {
        $xmlCodes = [];
        foreach ($airlineXml as $code => $_) {
            $xmlCodes[$code] = $code;
        }

        return array_merge(self::AIRLINE_ICON_MAP, $xmlCodes);
    }

    private function getAirlineData()
    {
        static $aircode_cache = null;

        if ($aircode_cache === null) {
            $aircode_cache = [];
            if (file_exists('custom/airlines.xml')) {
                $aircode_inter_xml = simplexml_load_file('custom/airlines.xml');
                $records = json_decode(json_encode($aircode_inter_xml), true)['RECORD'] ?? [];
                foreach ($records as $item) {
                    $aircode_cache[$item['code']] = $item['name'] . ' (' . $item['code'] . ')';
                }
            }
        }

        return $aircode_cache;
    }
}
