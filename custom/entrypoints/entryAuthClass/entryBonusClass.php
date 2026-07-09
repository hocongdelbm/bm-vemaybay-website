<?php
require_once "custom/entrypoints/entryClass.php";

/**
 * Class entryBonusClass
 *
 * Bonus (ec_bonus) adjustments made from the calculate bonus by source screen
 */
class entryBonusClass extends entryClass
{
    private const SOURCE_TYPES = ['EC_Flight_Bookings', 'EC_Receipt_Voucher'];

    /**
     * Update the direct bonus of every user of one source at once.
     *
     * bonuses = [[assigned_user_id, direct_bonus (integer VND, plain digits)], ...].
     * The total distributed among all users of the source (submitted values,
     * plus current values of users not submitted) must not exceed the
     * source's current total direct bonus (the shareable pool).
     *
     * @param array $params [source_id, source_type, bonuses]
     * @return array [error, message, data]
     */
    public function updateDirectBonusList($params = []) {
        global $db;

        try {
            // Only admin / manager / accountant can adjust bonuses
            if (!isManagerUser($this->currentUser->id)) {
                return ['error' => 1, 'message' => 'Bạn không có quyền điều chỉnh thưởng'];
            }

            $sourceId   = $this->cleanInput($params['source_id'] ?? '');
            $sourceType = $this->cleanInput($params['source_type'] ?? '');
            $bonuses    = $params['bonuses'] ?? [];

            if ($sourceId === '' || !is_array($bonuses) || empty($bonuses)) {
                return ['error' => 1, 'message' => 'Thiếu tham số bắt buộc'];
            }
            if (!in_array($sourceType, self::SOURCE_TYPES)) {
                return ['error' => 1, 'message' => 'Loại nguồn không hợp lệ'];
            }

            // Load every bonus row of this source
            $sourceIdQ   = $db->quote($sourceId);
            $sourceTypeQ = $db->quote($sourceType);

            $rows = [];
            $res = $db->query(
                "SELECT id, assigned_user_id, direct_bonus
                FROM ec_bonus
                WHERE source_id = '{$sourceIdQ}'
                    AND source_type = '{$sourceTypeQ}'
                    AND deleted = 0"
            );
            while ($row = $db->fetchByAssoc($res)) {
                $rows[$row['assigned_user_id']] = $row;
            }

            if (empty($rows)) {
                return ['error' => 1, 'message' => 'Không tìm thấy bản ghi thưởng cho nguồn đã chọn'];
            }

            // Shareable pool = current total direct bonus of the source
            $pool = 0.0;
            foreach ($rows as $r) $pool += (float) $r['direct_bonus'];
            if ($pool <= 0) {
                return ['error' => 1, 'message' => 'Nguồn này không có thưởng trực tiếp để chia sẻ'];
            }

            // Validate submitted amounts
            $newValues = [];
            foreach ($bonuses as $item) {
                $uid    = $this->cleanInput($item['assigned_user_id'] ?? '');
                $amount = trim((string) ($item['direct_bonus'] ?? ''));

                if ($uid === '' || !ctype_digit($amount)) {
                    return ['error' => 1, 'message' => 'Số tiền không hợp lệ, nhập số nguyên không âm cho từng nhân viên'];
                }
                if (!isset($rows[$uid])) {
                    return ['error' => 1, 'message' => 'Có nhân viên không thuộc nguồn này'];
                }
                if (isset($newValues[$uid])) {
                    return ['error' => 1, 'message' => 'Nhân viên bị trùng trong danh sách'];
                }
                $newValues[$uid] = (float) $amount;
            }

            // Max check: submitted values + untouched users' current values <= pool
            $total = 0.0;
            foreach ($rows as $uid => $r) {
                $total += $newValues[$uid] ?? (float) $r['direct_bonus'];
            }
            if ($total - $pool > 0.01) {
                return [
                    'error' => 1,
                    'message' => 'Tổng thưởng đã chia (' . number_format($total, 0, ',', '.')
                        . ') vượt quá tối đa có thể chia (' . number_format($pool, 0, ',', '.') . ')',
                ];
            }

            // Update the rows whose amount changed
            $modifiedBy = $db->quote($this->currentUser->id);
            $updated = [];
            foreach ($newValues as $uid => $amount) {
                if (abs($amount - (float) $rows[$uid]['direct_bonus']) < 0.01) continue;

                $rowIdQ = $db->quote($rows[$uid]['id']);
                $db->query(
                    "UPDATE ec_bonus
                    SET direct_bonus = {$amount}
                        ,date_modified = NOW()
                        ,modified_user_id = '{$modifiedBy}'
                    WHERE id = '{$rowIdQ}' AND deleted = 0"
                );
                $updated[$uid] = $amount;
            }

            return [
                'error' => 0,
                'message' => empty($updated) ? 'Không có thay đổi nào' : 'Đã cập nhật thưởng trực tiếp',
                'data' => [
                    'pool'      => round($pool, 2),
                    'total'     => round($total, 2),
                    'remaining' => round($pool - $total, 2),
                    'updated'   => $updated,
                ],
            ];
        }
        catch (Throwable $th) {
            $GLOBALS['log']->error("entryBonusClass::updateDirectBonusList: {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ['error' => 1, 'message' => 'Có lỗi xảy ra khi cập nhật thưởng'];
        }
    }
}
