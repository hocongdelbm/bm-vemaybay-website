<?php
global $current_user, $db;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = isset($_POST['type']) ? trim($_POST['type']) : "";

    if (strtoupper($type) == 'GET_TOTAL_OF_WEEK') {
        $current_date       = date('Y-m-d');
        $start_of_week      = date('Y-m-d', strtotime('monday this week', strtotime($current_date)));
        $start_of_last_week = date('Y-m-d', strtotime('-1 week', strtotime($start_of_week)));

        // CALLS
        $query_calls = "
                SELECT 
                    WEEKDAY(date_entered) AS day_of_week,
                    COUNT(*) AS total_calls,
                    IF(date_entered >= '{$start_of_week}', 'this_week', 'last_week') AS week_type
                FROM calls
                WHERE date_entered >= '{$start_of_last_week}'
                AND deleted = 0
                GROUP BY week_type, day_of_week
                ORDER BY week_type, day_of_week
            ";

        $result_calls = $db->query($query_calls);
        $data_calls = [
            'this_week' => array_fill(0, 7, 0), // [Thứ 2 -> Chủ nhật]
            'last_week' => array_fill(0, 7, 0),
        ];
        while ($row = $db->fetchByAssoc($result_calls)) {
            $data_calls[$row['week_type']][(int)$row['day_of_week']] = (int)$row['total_calls'];
        }

        // BOOKINGS
        $query_bookings = "
            SELECT 
                WEEKDAY(date_entered) AS day_of_week,
                COUNT(*) AS total_bookings,
                IF(date_entered >= '{$start_of_week}', 'this_week', 'last_week') AS week_type
            FROM ec_flight_bookings
            WHERE date_entered >= '{$start_of_last_week}'
            AND deleted = 0
            GROUP BY week_type, day_of_week
            ORDER BY week_type, day_of_week
        ";

        $result_bookings = $db->query($query_bookings);

        $data_bookings = [
            'this_week' => array_fill(0, 7, 0),
            'last_week' => array_fill(0, 7, 0),
        ];
        while ($row = $db->fetchByAssoc($result_bookings)) {
            $data_bookings[$row['week_type']][(int)$row['day_of_week']] = (int)$row['total_bookings'];
        }

        // KPI
        $query_kpi = "SELECT w.assigned_user_id,
                        CONCAT(IFNULL(u.last_name,''), 
                                IF(u.first_name IS NOT NULL,' ',''), 
                                IFNULL(u.first_name,'')) AS full_name,
                        w.total_kpi
                    FROM (
                        SELECT assigned_user_id,
                            SUM(
                                IFNULL(called, 0) 
                                + IFNULL(completed, 0) 
                                + IFNULL(paid, 0) 
                                + IFNULL(recheck, 0) 
                                + IFNULL(support, 0) 
                                + (IFNULL(invoice_issued, 0) * 3) 
                                + IFNULL(ticket_delivery, 0) 
                                + IFNULL(recall, 0) 
                                + IFNULL(remind, 0) 
                                + IFNULL(check_debt, 0) 
                                + IFNULL(create_repaid, 0) 
                                + IFNULL(process_repaid, 0) 
                                + IFNULL(create_payment, 0) 
                                + IFNULL(create_receipt, 0) 
                                + IFNULL(create_transfer, 0) 
                                + IFNULL(invoice_input_issued, 0)
                            ) AS total_kpi
                        FROM ec_working_process
                        WHERE deleted = 0
                        AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = '{$current_date}'
                        GROUP BY assigned_user_id
                    ) w
                    JOIN users u ON w.assigned_user_id = u.id
                    WHERE u.is_admin = 0 
                    AND u.title <> 'QuanLy' 
                    AND u.start_working_date IS NOT NULL
                    AND u.deleted = 0
                    ORDER BY w.total_kpi DESC
                    LIMIT 1";

        $result_kpi = $db->query($query_kpi);
        $data_kpi = [
            'full_name' => '',
            'total_kpi' => 0,
        ];

        while ($row = $db->fetchByAssoc($result_kpi)) {
            $data_kpi = [
                'full_name' => $row['full_name'],
                'total_kpi' => (int)$row['total_kpi']
            ];
        }

        echo json_encode([
            'calls' => $data_calls,
            'bookings' => $data_bookings,
            'kpi' => $data_kpi,
        ]);
        exit;
    } elseif (strtoupper($type) == 'GET_DATA_CDR_CALLS') {
        $calls = BeanFactory::getBean('Calls');
        $cdr_stats = $calls->getCDRStatistics();
        
        $result_cdr = [
            'total' => $cdr_stats['total'] ?? [],
            'failed' => $cdr_stats['failed'] ?? [],
            'answered' => $cdr_stats['answered'] ?? [],
            'minutes' => $cdr_stats['minutes'] ?? [],
            'call_per_min' => $cdr_stats['call_per_min'] ?? [],
            'asr' => $cdr_stats['asr'] ?? [],
            'aloc' => $cdr_stats['aloc'] ?? [],
        ];

        echo json_encode($result_cdr);
        exit;
    }
}
