<?php
require_once "custom/entrypoints/entryClass.php";

/**
 * Class entryBonusClass
 *
 * Bonus (ec_bonus) adjustments made from the calculate bonus by source screen
 */
class entryBonusClass extends entryClass {
    private const SOURCE_TYPES = ['EC_Flight_Bookings', 'EC_Receipt_Voucher', 'EC_HoanVe'];

    /**
     * Update the direct bonus of every user of one source at once.
     *
     * bonuses = [[assigned_user_id, direct_bonus (integer VND, plain digits)], ...].
     * The total distributed among all users of the source (submitted values,
     * plus current values of users not submitted) must not exceed the
     * source's current total direct bonus (the shareable pool).
     *
     * @param array $params [source_id, source_type, bonuses]
     * @return array [status, message, data]
     */
    public function updateDirectBonusList($params = []) {
        global $db;

        try {
            // Only admin / manager / accountant can adjust bonuses
            if (!isManagerUser()) {
                return ['status' => false, 'message' => 'Bạn không có quyền điều chỉnh thưởng'];
            }

            // Validate submitted amounts
            $sourceId   = $this->cleanInput($params['source_id'] ?? '');
            $sourceType = $this->cleanInput($params['source_type'] ?? '');
            $bonuses    = $params['bonuses'] ?? [];

            if ($sourceId === '' || !is_array($bonuses) || empty($bonuses)) {
                return ['status' => false, 'message' => 'Thiếu tham số bắt buộc'];
            }
            if (!in_array($sourceType, self::SOURCE_TYPES)) {
                return ['status' => false, 'message' => 'Loại nguồn không hợp lệ'];
            }
           
            $newValues = [];
            $newDescriptions = [];
            foreach ($bonuses as $item) {
                $uid    = $this->cleanInput($item['assigned_user_id'] ?? '');
                $amount = $this->cleanInput($item['direct_bonus'] ?? 0);
                $amount = round(unformat_number($amount));

                if ($uid === '') continue;
                if (!is_numeric($amount)) {
                    return ['status' => false, 'message' => 'Số tiền không hợp lệ'];
                }

                $newValues[$uid] = $amount;
                $newDescriptions[$uid] = trim((string) $this->cleanInput($item['description'] ?? ''));
            }


            // Load every bonus row of this source
            $sourceIdQ   = $db->quote($sourceId);
            $sourceTypeQ = $db->quote($sourceType);

            $rows = [];
            $total = $pool = 0; // Shareable pool = current total direct bonus of the source

            $res = $db->query(
                "SELECT id, assigned_user_id, IFNULL(direct_bonus, 0) AS direct_bonus, IFNULL(description, '') AS description
                FROM ec_bonus
                WHERE source_id = '{$sourceIdQ}'
                    AND source_type = '{$sourceTypeQ}'
                    AND deleted = 0"
            );
            while ($row = $db->fetchByAssoc($res)) {
                $rows[$row['assigned_user_id']] = $row;

                $pool  += $row['direct_bonus'] ?? 0;
                $total += $newValues[$row['assigned_user_id']] ?? 0;
            }

            if (empty($rows)) {
                return ['status' => false, 'message' => 'Không tìm thấy bản ghi thưởng cho nguồn đã chọn'];
            }
            if ($pool <= 0) {
                return ['status' => false, 'message' => 'Nguồn này không có thưởng trực tiếp để chia sẻ'];
            }
            // Max check: submitted values + untouched users' current values <= pool
            if ($total - $pool > 0) {
                return ['status' => false, 'message' => 'Tổng thưởng vượt quá tối đa có thể chia'];
            }

            // Update the rows whose amount or description changed
            $modifiedBy = $db->quote($this->currentUser->id);
            $updated = [];
            foreach ($newValues as $uid => $amount) {
                if (!isset($rows[$uid])) continue;

                $description = $newDescriptions[$uid] ?? '';
                if ($amount == $rows[$uid]['direct_bonus'] && $description === $rows[$uid]['description']) continue;

                $rowIdQ = $db->quote($rows[$uid]['id']);
                $descQ  = $db->quote($description);
                $db->query(
                    "UPDATE ec_bonus
                    SET direct_bonus = {$amount}
                        ,description = '{$descQ}'
                        ,date_modified = NOW()
                        ,modified_user_id = '{$modifiedBy}'
                    WHERE id = '{$rowIdQ}'
                        AND deleted = 0"
                );
                $updated[$uid] = $amount;
            }

            return [
                'status' => true,
                'message' => empty($updated) ? 'Không có thay đổi nào' : 'Đã cập nhật thưởng trực tiếp',
                'data' => [
                    'pool'      => $pool,
                    'total'     => $total,
                    'remaining' => $pool - $total,
                    'updated'   => $updated,
                ],
            ];
        }
        catch (Throwable $th) {
            $GLOBALS['log']->error(__METHOD__  . ": {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cập nhật thưởng'];
        }
    }
}
