<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/Sugar_Smarty.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');

class Viewcalculate_bonus extends SugarView
{
    public function display()
    {
        if (!ACLController::checkAccess('EC_Bonus', 'edit', true)) {
            ACLController::displayNoAccess();
            return;
        }

        $smarty = new Sugar_Smarty();

        // Calendar widget submits "d-m-Y" dates via GET; default to the last 3 days (VN time)
        $from_input = trim((string) ($_GET['from_date'] ?? ''));
        $to_input   = trim((string) ($_GET['to_date'] ?? ''));
        if ($from_input === '') $from_input = date('d-m-Y', strtotime('-3 days'));
        if ($to_input === '')   $to_input   = date('d-m-Y');

        $smarty->assign('FROM_DATE_VALUE', htmlspecialchars($from_input, ENT_QUOTES, 'UTF-8'));
        $smarty->assign('TO_DATE_VALUE', htmlspecialchars($to_input, ENT_QUOTES, 'UTF-8'));

        $is_save = !empty($_GET['save_records']);
        $smarty->assign('SAVE_CHECKED', $is_save ? 'checked' : '');
        $smarty->assign('SAVED', $is_save);

        if (!empty($_GET['btnRun'])) {
            $from_day = DatetimeHelper::convert_datetime($from_input, 'd-m-Y', 'Y-m-d');
            $to_day   = DatetimeHelper::convert_datetime($to_input, 'd-m-Y', 'Y-m-d');

            if ($from_day === null || $to_day === null) {
                $smarty->assign('ERROR', 'Định dạng ngày không hợp lệ');
            }
            else {
                try {
                    $result = EC_Bonus_Helper::save_bonus_report("$from_day 00:00:00", "$to_day 23:59:59", $is_save);
                    $smarty->assign('BONUS_REPORT', self::buildBonusReport($result));
                } catch (Throwable $th) {
                    $smarty->assign('ERROR', htmlspecialchars($th->getMessage(), ENT_QUOTES, 'UTF-8'));
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
    private static function buildBonusReport(array $result): array
    {
        $user_rows = $result['users'] ?? [];

        $user_names = self::fetchNamesById(
            "SELECT id, TRIM(CONCAT(IFNULL(last_name,''), ' ', IFNULL(first_name,''))) AS name FROM users WHERE deleted = 0",
            array_keys($user_rows)
        );

        list($grp_sep, $dec_sep) = EC_Bonus_Helper::get_number_seps();
        $fmt = function ($v) use ($grp_sep, $dec_sep) { return number_format((float) $v, 0, $dec_sep, $grp_sep); };
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
                    'Y-m-d H:i:s', 'd-m-Y H:i'
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
    private static function fetchNamesById(string $sql_base, array $ids): array
    {
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
