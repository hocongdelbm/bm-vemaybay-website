<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/Sugar_Smarty.php');

class Viewcalculate_bonus extends SugarView {
    public string $grp_sep;
    public string $dec_sep;
    public string $timezone;
    public string $date_format;
    public string $time_format;

    public function display() {
        global $current_user, $sugar_config;
        
        if (!isManagerUser()) {
            ACLController::displayNoAccess();
            return;
        }

        list($this->grp_sep, $this->dec_sep) = get_number_separators();
        $this->timezone = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
        $this->date_format = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');
        $this->time_format = $current_user->getPreference('timef') ?: ($sugar_config['timef'] ?? 'H:i');

        $smarty = new Sugar_Smarty();

        // Calendar widget submits "d-m-Y" dates via GET; default to the last 3 days (VN time)
        $from_input = trim($_GET['from_date'] ?? '');
        $to_input   = trim($_GET['to_date'] ?? '');
        if ($from_input === '') $from_input = date($this->date_format, strtotime('-3 days'));
        if ($to_input === '')   $to_input   = date($this->date_format);

        $smarty->assign('FROM_DATE_VALUE', htmlspecialchars($from_input, ENT_QUOTES, 'UTF-8'));
        $smarty->assign('TO_DATE_VALUE', htmlspecialchars($to_input, ENT_QUOTES, 'UTF-8'));

        $is_save = !empty($_GET['save_records']);
        $smarty->assign('SAVE_CHECKED', $is_save ? 'checked' : '');
        $smarty->assign('SAVED', $is_save);

        if (!empty($_GET['btnRun'])) {
            // Date-only parse: the helper zeroes uncovered fields, so from-day gets 00:00:00;
            // the to-day bound carries its literal end-of-day time in the input value
            $from_day = DatetimeHelper::convert_datetime($from_input, $this->date_format, 'Y-m-d H:i:s', $this->timezone, 'Asia/Ho_Chi_Minh');
            $to_day   = DatetimeHelper::convert_datetime("$to_input 23:59:59", "$this->date_format H:i:s", 'Y-m-d H:i:s', $this->timezone, 'Asia/Ho_Chi_Minh');

            if ($from_day === null || $to_day === null) {
                $smarty->assign('ERROR', 'Định dạng ngày không hợp lệ');
            }
            else {
                try {
                    $result = EC_Bonus_Helper::save_bonus_report($from_day, $to_day, $is_save);
                    $smarty->assign('BONUS_REPORT', $this->buildBonusReport($result));
                } catch (Throwable $th) {
                    if(isDevUser()) $smarty->assign("ERROR", "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
                    else $smarty->assign("ERROR", "Lỗi trong quá trình xử lý, vui lòng liên hệ IT");
                }
            }
        }

        $smarty->display('modules/EC_Bonus/tpls/calculate_bonus.tpl');
    }

    /**
     * Shape the helper result (['users' => user_id => source_id => bonus row])
     * for the template: resolved names, per-user subtotals and a grand total,
     * VND-formatted amounts. Source name/type/time come from the row itself.
     */
    private function buildBonusReport(array $result): array {
        $user_rows = $result['users'] ?? [];

        $user_names = $this->fetchNamesById(
            "SELECT id, TRIM(CONCAT(IFNULL(last_name,''), ' ', IFNULL(first_name,''))) AS name FROM users WHERE deleted = 0",
            array_keys($user_rows)
        );

        $fmt = function ($v) { return number_format((float) $v, 0, $this->dec_sep, $this->grp_sep); };
        $esc = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

        $users = [];
        $grand = ['bookings' => 0, 'kpi' => 0, 'direct' => 0.0, 'indirect' => 0.0, 'total' => 0.0];

        foreach ($user_rows as $user_id => $bookings) {
            $u = [
                'name'     => $esc($user_names[$user_id] ?? $user_id),
                'bookings' => [],
                'kpi'      => 0,
                'direct'   => 0.0,
                'indirect' => 0.0,
                'total'    => 0.0,
            ];

            foreach ($bookings as $booking_id => $row) {
                $direct   = (float) ($row['directBonus'] ?? 0);
                $indirect = (float) ($row['indirectBonus'] ?? 0);
                $kpi      = (int) ($row['kpi'] ?? 0);

                $bonus_time = DatetimeHelper::convert_datetime(
                    (string) ($row['bonusTime'] ?? ''),
                    'Y-m-d H:i:s', "$this->date_format $this->time_format"
                ) ?: '';

                $u['bookings'][] = [
                    'id'        => $esc($booking_id),
                    'module'    => $esc($row['srcType'] ?? 'EC_Flight_Bookings'),
                    'name'      => $esc(($row['srcName'] ?? '') !== '' ? $row['srcName'] : $booking_id),
                    'time'      => $esc($bonus_time),
                    'kpi'       => $kpi,
                    'direct'    => $fmt($direct),
                    'indirect'  => $fmt($indirect),
                    'total'     => $fmt($direct + $indirect),
                    'raw_total' => $direct + $indirect,
                ];

                $u['kpi']      += $kpi;
                $u['direct']   += $direct;
                $u['indirect'] += $indirect;
                $u['total']    += $direct + $indirect;
                $grand['bookings']++;
            }

            $grand['kpi']      += $u['kpi'];
            $grand['direct']   += $u['direct'];
            $grand['indirect'] += $u['indirect'];
            $grand['total']    += $u['total'];

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

    /** Run "$sql_base AND id IN (...)" and return an id => name map */
    private function fetchNamesById(string $sql_base, array $ids): array {
        global $db;

        if (empty($ids)) return [];

        $quoted = array_map(function ($id) use ($db) {
            return "'" . $db->quote((string) $id) . "'";
        }, $ids);

        $names = [];
        $res = $db->query($sql_base . ' AND id IN (' . implode(',', $quoted) . ')');
        while ($row = $db->fetchByAssoc($res)) {
            $names[$row['id']] = $row['name'];
        }
        return $names;
    }
}
