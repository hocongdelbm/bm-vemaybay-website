<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/Sugar_Smarty.php');

class Viewcalculate_bonus_by_source extends SugarView {
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

        // Admin / manager / accountant can adjust direct bonus from this screen
        $is_manager = (bool) isManagerUser();
        $smarty->assign('IS_MANAGER', $is_manager);

        $source_name = trim((string) ($_GET['source_name'] ?? ''));
        $source_type = $this->getSourceTypeFromName($source_name);

        $smarty->assign('SOURCE_NAME_VALUE', htmlspecialchars($source_name, ENT_QUOTES, 'UTF-8'));

        if (!empty($_GET['btnRun'])) {
            if ($source_name === '') {
                $smarty->assign("ERROR", "Vui lòng nhập nguồn muốn xem: Booking, Phiếu thu, Phiếu hoàn");
            }
            else {
                try {
                    $result = EC_Bonus_Helper::get_source_bonus($source_name, $source_type);

                    if (empty($result)) {
                        $smarty->assign('NOT_FOUND', true);
                    } else {
                        $smarty->assign('SOURCE_BONUS', $this->buildSourceBonus($result));

                        if ($is_manager) {
                            $bonus_users = $this->fetchBonusUsers($result['srcId'] ?? '', $result['srcType'] ?? '');
                            $smarty->assign('BONUS_USERS', $bonus_users);

                            // Shareable pool = current total direct bonus of the source
                            $pool = 0;
                            foreach ($bonus_users as $bu) $pool += $bu['direct_raw'];
                            $smarty->assign('BONUS_POOL', $pool);
                            $smarty->assign('BONUS_POOL_FMT', number_format($pool, 0, $this->dec_sep, $this->grp_sep));
                        }
                    }
                }
                catch (Throwable $th) {
                    if(isDevUser()) $smarty->assign("ERROR", "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
                    else $smarty->assign("ERROR", "Lỗi trong quá trình xử lý, vui lòng liên hệ IT");
                }
            }
        }

        $smarty->display('modules/EC_Bonus/tpls/calculate_bonus_by_source.tpl');
    }

    /**
     * Source type from the code prefix: "PT-" receipt voucher,
     * "HV-" ticket refund, anything else flight booking.
     */
    private function getSourceTypeFromName(string $source_name): string {
        if (stripos($source_name, 'PT-') === 0) return 'EC_Receipt_Voucher';
        if (stripos($source_name, 'HV-') === 0) return 'EC_HoanVe';
        return 'EC_Flight_Bookings';
    }

    /**
     * Users holding an ec_bonus row for this source, for the adjust-bonus
     * modal (id, name, current direct bonus formatted).
     */
    private function fetchBonusUsers(string $source_id, string $source_type): array {
        global $db;

        if ($source_id === '' || $source_type === '') return [];

        $users = [];
        $res = $db->query(
            "SELECT b.assigned_user_id
                ,b.direct_bonus
                ,IFNULL(b.description, '') AS description
                ,TRIM(CONCAT(IFNULL(u.last_name,''), ' ', IFNULL(u.first_name,''))) AS user_name
            FROM ec_bonus b
                INNER JOIN users u ON u.id = b.assigned_user_id AND u.deleted = 0
            WHERE b.source_id = '" . $db->quote($source_id) . "'
                AND b.source_type = '" . $db->quote($source_type) . "'
                AND b.deleted = 0
            ORDER BY b.direct_bonus DESC"
        );

        while ($row = $db->fetchByAssoc($res)) {
            $users[] = [
                'id'          => htmlspecialchars($row['assigned_user_id'], ENT_QUOTES, 'UTF-8'),
                'name'        => htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8'),
                'direct'      => number_format((float) $row['direct_bonus'], 0, $this->dec_sep, $this->grp_sep),
                'direct_raw'  => (int) round((float) $row['direct_bonus']),
                'description' => htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        return $users;
    }

    /**
     * Shape the get_source_bonus() calculation detail for the template:
     * VND-formatted amounts, percent labels, user-friendly bonus time.
     */
    private function buildSourceBonus(array $calc): array {
        global $app_list_strings;

        $fmt = function ($v) { return number_format((float) $v, 0, $this->dec_sep, $this->grp_sep); };
        $esc = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
        $pct = function ($v) use ($fmt) { return $fmt($v * 100) . '%'; };

        $ticket_qty = (float) ($calc['ticketQty'] ?? 0);
        $avg_profit = $ticket_qty > 0 ? ((float) ($calc['profit'] ?? 0)) / $ticket_qty : 0;

        $bonus_time = DatetimeHelper::convert_datetime(
            (string) ($calc['bonusTime'] ?? ''),
            'Y-m-d H:i:s', "$this->date_format $this->time_format"
        ) ?: '';

        $module = (string) ($calc['srcType'] ?? '');

        return [
            'id'                    => $esc($calc['srcId'] ?? ''),
            'name'                  => $esc($calc['srcName'] ?? ''),
            'module'                => $esc($module),
            'module_label'          => $esc($app_list_strings['moduleList'][$module] ?? $module),
            'time'                  => $esc($bonus_time),
            'revenue'               => $fmt($calc['revenue'] ?? 0),
            'cost'                  => $fmt($calc['cost'] ?? 0),
            'profit'                => $fmt($calc['profit'] ?? 0),
            'ticket_qty'            => (int) $ticket_qty,
            'avg_profit'            => $fmt($avg_profit),
            'min_threshold'         => $fmt($calc['minThresholdValue'] ?? 0),
            'extra_threshold'       => $fmt($calc['extraThresholdValue'] ?? 0),
            'bonus_percent'         => $pct($calc['bonusPercent'] ?? 0),
            'extra_bonus_percent'   => $pct($calc['extraBonusPercent'] ?? 0),
            'bonus_per_ticket'       => $fmt($calc['bonusPerTicket'] ?? 0),
            'is_valid_zalo'          => !empty($calc['isValidZalo']),
            'total_bonus_calc'       => $fmt($calc['totalBonus'] ?? (($calc['totalDirectBonus'] ?? 0) + ($calc['totalIndirectBonus'] ?? 0))),
            'total_direct_bonus'     => $fmt($calc['totalDirectBonus'] ?? 0),
            'total_indirect_bonus'   => $fmt($calc['totalIndirectBonus'] ?? 0),
            'total_bonus'            => $fmt(($calc['totalDirectBonus'] ?? 0) + ($calc['totalIndirectBonus'] ?? 0)),
            'total_indirect_kpi'     => (int) ($calc['totalIndirectKPI'] ?? 0),
            'indirect_bonus_per_kpi' => $fmt($calc['indirectBonusPerKPI'] ?? 0),
        ];
    }
}
