<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/Sugar_Smarty.php');
require_once('modules/Currencies/Currency.php');

class Viewbonus_report extends SugarView {
    public string $grp_sep;
    public string $dec_sep;
    public string $timezone;
    public string $date_format;
    public string $time_format;

    public function display() {
        global $current_user, $sugar_config;

        if (!ACLController::checkAccess('EC_Bonus', 'list', true)) {
            ACLController::displayNoAccess();
            return;
        }

        list($this->grp_sep, $this->dec_sep) = get_number_separators();
        $this->timezone = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
        $this->date_format = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');
        $this->time_format = $current_user->getPreference('timef') ?: ($sugar_config['timef'] ?? 'H:i');

        $smarty = new Sugar_Smarty();

        // Admin / manager / accountant sees everyone, others only their own rows
        $view_all = (bool) isManagerUser();
        $smarty->assign('VIEW_ALL', $view_all);

        // Calendar widget submits dates in the user's format via GET; default to the current month
        $from_input = trim((string) ($_GET['from_date'] ?? ''));
        $to_input   = trim((string) ($_GET['to_date'] ?? ''));
        if ($from_input === '') $from_input = date($this->date_format, strtotime('first day of this month'));
        if ($to_input === '')   $to_input   = date($this->date_format);

        $smarty->assign('FROM_DATE_VALUE', htmlspecialchars($from_input, ENT_QUOTES, 'UTF-8'));
        $smarty->assign('TO_DATE_VALUE', htmlspecialchars($to_input, ENT_QUOTES, 'UTF-8'));

        // Optional filter on the source name (booking / receipt voucher code)
        $source_input = trim((string) ($_GET['source_name'] ?? ''));
        $smarty->assign('SOURCE_NAME_VALUE', htmlspecialchars($source_input, ENT_QUOTES, 'UTF-8'));

        // ec_bonus.bonus_time is stored in UTC; convert the user-timezone day bounds
        $from_utc = DatetimeHelper::convert_datetime("$from_input 00:00:00", "$this->date_format H:i:s", 'Y-m-d H:i:s', $this->timezone, 'UTC');
        $to_utc   = DatetimeHelper::convert_datetime("$to_input 23:59:59", "$this->date_format H:i:s", 'Y-m-d H:i:s', $this->timezone, 'UTC');

        if ($from_utc === null || $to_utc === null) {
            $smarty->assign('ERROR', 'Định dạng ngày không hợp lệ');
        }
        else {
            try {
                $smarty->assign('BONUS_REPORT', $this->buildReport(
                    $from_utc, $to_utc, $view_all ? '' : $current_user->id, $source_input
                ));
            }
            catch (Throwable $th) {
                if(isDevUser()) $smarty->assign("ERROR", "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
                else $smarty->assign("ERROR", "Lỗi trong quá trình xử lý, vui lòng liên hệ IT");
            }
        }

        $smarty->display('modules/EC_Bonus/tpls/bonus_report.tpl');
    }

    /**
     * Read saved bonus rows from ec_bonus for the period and shape them for
     * the template: rows grouped per user with subtotals and a grand total,
     * VND-formatted amounts, both levels sorted by total bonus descending.
     *
     * @param string $from_utc     Y-m-d H:i:s, UTC lower bound
     * @param string $to_utc       Y-m-d H:i:s, UTC upper bound
     * @param string $only_user_id Restrict to this assigned user; '' = all users
     * @param string $source_name  Filter by source name (partial match); '' = all sources
     */
    private function buildReport(string $from_utc, string $to_utc, string $only_user_id, string $source_name = ''): array {
        global $db;

        $user_cond = $only_user_id !== ''
            ? " AND b.assigned_user_id = '" . $db->quote($only_user_id) . "'"
            : '';

        $source_cond = $source_name !== ''
            ? " AND b.name LIKE '%" . $db->quote($source_name) . "%'"
            : '';

        $sql =
            "SELECT b.name
                ,b.source_id
                ,b.source_type
                ,b.bonus_time
                ,b.kpi
                ,b.direct_bonus
                ,b.indirect_bonus
                ,b.assigned_user_id
                ,TRIM(CONCAT(IFNULL(u.last_name,''), ' ', IFNULL(u.first_name,''))) AS user_name
            FROM ec_bonus b
                INNER JOIN users u ON u.id = b.assigned_user_id AND u.deleted = 0
            WHERE b.deleted = 0
                AND b.bonus_time >= '{$from_utc}'
                AND b.bonus_time <= '{$to_utc}'
                {$user_cond}
                {$source_cond}";

        $fmt = function ($v) { return number_format((float) $v, 0, $this->dec_sep, $this->grp_sep); };
        $esc = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

        $users_map = [];
        $grand = ['bookings' => 0, 'kpi' => 0, 'direct' => 0, 'indirect' => 0, 'total' => 0];

        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $uid = $row['assigned_user_id'];

            if (!isset($users_map[$uid])) {
                $users_map[$uid] = [
                    'name'     => $esc($row['user_name'] !== '' ? $row['user_name'] : $uid),
                    'bookings' => [],
                    'kpi'      => 0,
                    'direct'   => 0,
                    'indirect' => 0,
                    'total'    => 0,
                ];
            }

            $direct   = $row['direct_bonus'];
            $indirect = $row['indirect_bonus'];
            $kpi      = $row['kpi'];

            $bonus_time = DatetimeHelper::convert_datetime(
                (string) $row['bonus_time'],
                'Y-m-d H:i:s', "$this->date_format H:i", 'UTC', $this->timezone
            ) ?: '';

            $users_map[$uid]['bookings'][] = [
                'id'        => $esc($row['source_id']),
                'module'    => $esc($row['source_type'] ?: 'EC_Flight_Bookings'),
                'name'      => $esc($row['name'] !== '' ? $row['name'] : $row['source_id']),
                'time'      => $esc($bonus_time),
                'kpi'       => $kpi,
                'direct'    => $fmt($direct),
                'indirect'  => $fmt($indirect),
                'total'     => $fmt($direct + $indirect),
                'raw_total' => $direct + $indirect,
            ];

            $users_map[$uid]['kpi']      += $kpi;
            $users_map[$uid]['direct']   += $direct;
            $users_map[$uid]['indirect'] += $indirect;
            $users_map[$uid]['total']    += $direct + $indirect;

            $grand['bookings']++;
            $grand['kpi']      += $kpi;
            $grand['direct']   += $direct;
            $grand['indirect'] += $indirect;
            $grand['total']    += $direct + $indirect;
        }

        $users = [];
        foreach ($users_map as $u) {
            // Sources of a user: highest total bonus first
            usort($u['bookings'], function ($a, $b) {
                return $b['raw_total'] <=> $a['raw_total'];
            });

            $u['raw_total'] = $u['total'];
            foreach (['direct', 'indirect', 'total'] as $k) $u[$k] = $fmt($u[$k]);
            $users[] = $u;
        }

        // Users: highest total bonus first
        usort($users, function ($a, $b) {
            return $b['raw_total'] <=> $a['raw_total'];
        });

        $grand['users'] = count($users);
        foreach (['direct', 'indirect', 'total'] as $k) $grand[$k] = $fmt($grand[$k]);

        return ['users' => $users, 'grand' => $grand];
    }
}
