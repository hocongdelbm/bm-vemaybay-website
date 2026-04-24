<?php
/**
 * View to migrate N-N relationship: ec_hoadonban <-> ec_receipt_voucher
 *
 * Đọc cặp (parent_id, receipt_voucher_id) từ ec_chitiethoadon
 * rồi insert vào bảng junction hoadonban_receiptvouchers nếu chưa tồn tại.
 *
 * Access: index.php?module=EC_HoaDonBan&action=migrate
 * Chỉ dành cho Admin.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class Viewmigrate extends SugarView
{
    private $stats = [
        'total'   => 0,
        'success' => 0,
        'failed'  => 0,
        'skipped' => 0,
        'errors'  => [],
    ];

    public function display()
    {
        global $current_user;

        if (!$current_user->isAdmin()) {
            sugar_die('Unauthorized access. Admin privileges required.');
        }

        if (!empty($_REQUEST['run_migration'])) {
            $this->runMigration();
            return;
        }

        $this->renderUI();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UI
    // ─────────────────────────────────────────────────────────────────────────

    private function renderUI()
    {
        $count = $this->getPendingCount();

        echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Migrate HoaDonBan ↔ PhiếuThu</title>
    <style>
        body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5}
        .container{max-width:900px;margin:0 auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,.1)}
        h1{color:#333;border-bottom:3px solid #4CAF50;padding-bottom:10px}
        .box{border-radius:4px;padding:15px;margin:20px 0}
        .info   {background:#d9edf7;border:1px solid #bce8f1}
        .warning{background:#fcf8e3;border:1px solid #faebcc}
        .btn{padding:12px 24px;margin:10px 5px;font-size:15px;cursor:pointer;border:none;border-radius:4px;text-decoration:none;display:inline-block}
        .btn-primary  {background:#4CAF50;color:#fff}
        .btn-warning  {background:#f0ad4e;color:#fff}
        .btn-secondary{background:#6c757d;color:#fff}
        .stat{font-size:48px;font-weight:700;color:#4CAF50;text-align:center;margin:20px 0}
        .form-group{margin:20px 0}
        label{display:block;margin-bottom:8px;font-weight:bold}
        input[type=number]{padding:10px;font-size:15px;border:1px solid #ddd;border-radius:4px;width:200px}
        ul{line-height:1.8} ol{line-height:1.8}
    </style>
</head>
<body>
<div class="container">
    <h1>🔄 Migrate HoaDonBan ↔ PhiếuThu (N-N)</h1>

    <div class="box info">
        <strong>📋 Mục đích:</strong><br>
        Đọc các cặp <code>(parent_id, receipt_voucher_id)</code> trong bảng
        <code>ec_chitiethoadon</code> rồi tạo liên kết tương ứng trong bảng junction
        <code>hoadonban_receiptvouchers</code> nếu chưa tồn tại.
    </div>

    <div class="stat">' . $count . '</div>
    <p style="text-align:center;font-size:18px;color:#666">cặp cần migrate (chưa có trong junction table)</p>

    <div class="box warning">
        <strong>⚠️ Chú ý:</strong>
        <ul>
            <li>Chạy <strong>DRY RUN</strong> trước để kiểm tra kết quả</li>
            <li><strong>Backup database</strong> trước khi chạy thật</li>
            <li>Script chỉ INSERT, không UPDATE hay DELETE dữ liệu hiện có</li>
            <li>Các cặp đã tồn tại sẽ bị <em>bỏ qua</em> (skipped)</li>
        </ul>
    </div>

    <form method="GET" action="">
        <input type="hidden" name="module" value="EC_HoaDonBan">
        <input type="hidden" name="action" value="migrate">

        <div class="form-group">
            <label for="limit">Giới hạn số cặp xử lý (để trống = tất cả):</label>
            <input type="number" id="limit" name="limit" min="1" placeholder="Tất cả">
            <small style="color:#666;display:block;margin-top:5px">
                Nên thử với 5–10 bản ghi trước
            </small>
        </div>

        <div class="form-group">
            <button type="submit" name="run_migration" value="dry_run"
                onclick="return confirm(\'Chạy DRY RUN?\n\nKhông có thay đổi nào được lưu vào database.\')"
                class="btn btn-warning">
                🔍 DRY RUN (xem trước, không thay đổi DB)
            </button>

            <button type="submit" name="run_migration" value="execute"
                onclick="return confirm(\'⚠️ CẢNH BÁO: Sẽ INSERT vào database!\n\nBạn đã backup chưa?\n\nNhấn OK để tiếp tục.\')"
                class="btn btn-primary">
                ✅ CHẠY MIGRATE (INSERT vào DB)
            </button>

            <a href="index.php?module=EC_HoaDonBan&action=index" class="btn btn-secondary">
                ← Quay lại
            </a>
        </div>
    </form>

    <div class="box info">
        <strong>💡 Quy trình khuyến nghị:</strong>
        <ol>
            <li>DRY RUN với limit nhỏ (5–10) để kiểm tra</li>
            <li>DRY RUN toàn bộ nếu kết quả ổn</li>
            <li>Backup database</li>
            <li>Chạy MIGRATE thật</li>
        </ol>
    </div>
</div>
</body>
</html>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MIGRATE
    // ─────────────────────────────────────────────────────────────────────────

    private function runMigration()
    {
        $dryRun = ($_REQUEST['run_migration'] === 'dry_run');
        $limit  = !empty($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : null;

        echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Migrate — Kết quả</title>
    <style>
        body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5}
        .container{max-width:1000px;margin:0 auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,.1)}
        h1{color:#333;border-bottom:3px solid #4CAF50;padding-bottom:10px}
        .dry-run-banner{background:#fcf8e3;border:1px solid #faebcc;padding:15px;margin:20px 0;border-radius:4px}
        .log{background:#f9f9f9;border:1px solid #ddd;border-radius:4px;padding:15px;margin:20px 0;
             max-height:500px;overflow-y:auto;font-family:"Courier New",monospace;font-size:13px;line-height:1.6}
        .log-entry{margin:4px 0;padding:2px 0}
        .log-info   {color:#333}
        .log-success{color:#4CAF50;font-weight:bold}
        .log-error  {color:#d9534f;font-weight:bold}
        .log-warning{color:#f0ad4e;font-weight:bold}
        .stats{display:flex;justify-content:space-around;margin:20px 0;flex-wrap:wrap;gap:12px}
        .stat-box{text-align:center;padding:20px;border-radius:4px;min-width:110px}
        .stat-total  {background:#d9edf7;border:2px solid #31708f}
        .stat-success{background:#dff0d8;border:2px solid #3c763d}
        .stat-failed {background:#f2dede;border:2px solid #a94442}
        .stat-skipped{background:#fcf8e3;border:2px solid #8a6d3b}
        .stat-number{font-size:36px;font-weight:bold;margin:10px 0}
        .stat-label {font-size:13px;text-transform:uppercase}
        .btn{padding:12px 24px;margin:10px 5px;font-size:15px;cursor:pointer;border:none;
             border-radius:4px;text-decoration:none;display:inline-block;background:#6c757d;color:#fff}
        .error-list{margin-top:20px;padding:15px;background:#f2dede;border:1px solid #ebccd1;border-radius:4px}
    </style>
</head>
<body>
<div class="container">
    <h1>🔄 ' . ($dryRun ? 'DRY RUN — Preview kết quả' : 'Migrate — Đang xử lý') . '</h1>';

        if ($dryRun) {
            echo '<div class="dry-run-banner">
                    <strong>⚠️ DRY RUN MODE</strong> —
                    Không có thay đổi nào được lưu vào database.
                  </div>';
        }

        echo '<div class="log" id="log">';

        if (ob_get_level() == 0) ob_start();

        $this->log('=== Bắt đầu migration ===');
        $this->log('Chế độ: ' . ($dryRun ? 'DRY RUN' : 'EXECUTE'), 'warning');
        if ($limit) {
            $this->log("Giới hạn: {$limit} cặp");
        }

        $pairs = $this->findPendingPairs($limit);
        $this->stats['total'] = count($pairs);
        $this->log("Tìm thấy {$this->stats['total']} cặp cần xử lý");

        foreach ($pairs as $pair) {
            $this->processPair($pair, $dryRun);
            flush();
            ob_flush();
        }

        $this->log('=== Hoàn thành ===', 'success');

        echo '</div>';

        $this->renderStats();

        echo '
    <div style="margin-top:30px">
        <a href="index.php?module=EC_HoaDonBan&action=migrate" class="btn">← Quay lại</a>
        <a href="index.php?module=EC_HoaDonBan&action=index"   class="btn">Về danh sách HĐ</a>
    </div>
</div>
</body>
</html>';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // QUERIES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Đếm số cặp trong ec_chitiethoadon chưa có trong junction table.
     */
    private function getPendingCount()
    {
        global $db;

        $sql = "
            SELECT COUNT(DISTINCT ct.parent_id, ct.receipt_voucher_id) AS cnt
            FROM ec_chitiethoadon ct
            INNER JOIN ec_hoadonban hdb
                ON hdb.id = ct.parent_id AND hdb.deleted = 0
            INNER JOIN ec_receipt_voucher rv
                ON rv.id = ct.receipt_voucher_id AND rv.deleted = 0
            LEFT JOIN hoadonban_receiptvouchers hrv
                ON hrv.hoadon_id  = ct.parent_id
               AND hrv.receipt_id = ct.receipt_voucher_id
               AND hrv.deleted    = 0
            WHERE ct.deleted            = 0
              AND ct.parent_type        = 'EC_HoaDonBan'
              AND ct.parent_id          IS NOT NULL AND ct.parent_id          != ''
              AND ct.receipt_voucher_id IS NOT NULL AND ct.receipt_voucher_id != ''
              AND hrv.id IS NULL
        ";

        $result = $db->query($sql, true, 'getPendingCount failed');
        $row    = $db->fetchByAssoc($result);

        return (int)$row['cnt'];
    }

    /**
     * Lấy danh sách các cặp cần migrate (có tên để dễ log).
     *
     * @return array [ ['hoadon_id'=>, 'receipt_id'=>, 'hoadon_name'=>, 'rv_name'=>, 'ct_id'=>], ... ]
     */
    private function findPendingPairs($limit = null)
    {
        global $db;

        $limitClause = $limit ? 'LIMIT ' . (int)$limit : '';

        $sql = "
            SELECT
                ct.id                 AS ct_id,
                ct.parent_id          AS hoadon_id,
                hdb.name              AS hoadon_name,
                ct.receipt_voucher_id AS receipt_id,
                rv.name               AS rv_name
            FROM ec_chitiethoadon ct
            INNER JOIN ec_hoadonban hdb
                ON hdb.id = ct.parent_id AND hdb.deleted = 0
            INNER JOIN ec_receipt_voucher rv
                ON rv.id = ct.receipt_voucher_id AND rv.deleted = 0
            LEFT JOIN hoadonban_receiptvouchers hrv
                ON hrv.hoadon_id  = ct.parent_id
               AND hrv.receipt_id = ct.receipt_voucher_id
               AND hrv.deleted    = 0
            WHERE ct.deleted            = 0
              AND ct.parent_type        = 'EC_HoaDonBan'
              AND ct.parent_id          IS NOT NULL AND ct.parent_id          != ''
              AND ct.receipt_voucher_id IS NOT NULL AND ct.receipt_voucher_id != ''
              AND hrv.id IS NULL
            GROUP BY ct.parent_id, ct.receipt_voucher_id
            ORDER BY hdb.name
            {$limitClause}
        ";

        $result = $db->query($sql, true, 'findPendingPairs failed');
        $rows   = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROCESS ONE PAIR
    // ─────────────────────────────────────────────────────────────────────────

    private function processPair(array $pair, bool $dryRun)
    {
        global $db;

        $hoadonId  = $pair['hoadon_id'];
        $receiptId = $pair['receipt_id'];
        $label     = "{$pair['hoadon_name']} ↔ {$pair['rv_name']}";

        $this->log("Xử lý: {$label}");

        try {
            // Double-check: đã tồn tại chưa (tránh race condition khi chạy nhiều lần)
            $checkSql = "
                SELECT id FROM hoadonban_receiptvouchers
                WHERE hoadon_id  = '{$db->quote($hoadonId)}'
                  AND receipt_id = '{$db->quote($receiptId)}'
                  AND deleted    = 0
                LIMIT 1
            ";
            $checkResult = $db->query($checkSql, true, 'check existing pair failed');

            if ($db->fetchByAssoc($checkResult)) {
                $this->log("  → Đã tồn tại, bỏ qua", 'warning');
                $this->stats['skipped']++;
                return;
            }

            if ($dryRun) {
                $this->log("  [DRY RUN] Sẽ INSERT cặp này vào hoadonban_receiptvouchers", 'warning');
                $this->stats['success']++;
                return;
            }

            // INSERT
            $newId = create_guid();
            $now   = date('Y-m-d H:i:s');

            $insertSql = "
                INSERT INTO hoadonban_receiptvouchers
                    (id, hoadon_id, receipt_id, date_modified, deleted)
                VALUES (
                    '{$db->quote($newId)}',
                    '{$db->quote($hoadonId)}',
                    '{$db->quote($receiptId)}',
                    '{$now}',
                    0
                )
            ";

            $ok = $db->query($insertSql, false);

            if ($ok) {
                $this->log("  ✓ Đã INSERT thành công (id: {$newId})", 'success');
                $this->stats['success']++;
            } else {
                throw new Exception('DB query trả về false: ' . $db->lastError());
            }

        } catch (Exception $e) {
            $this->log("  ✗ Lỗi: " . $e->getMessage(), 'error');
            $this->stats['failed']++;
            $this->stats['errors'][] = [
                'label' => $label,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function renderStats()
    {
        echo '
        <div class="stats">
            <div class="stat-box stat-total">
                <div class="stat-label">Tổng</div>
                <div class="stat-number">' . $this->stats['total']   . '</div>
            </div>
            <div class="stat-box stat-success">
                <div class="stat-label">Thành công</div>
                <div class="stat-number">' . $this->stats['success'] . '</div>
            </div>
            <div class="stat-box stat-failed">
                <div class="stat-label">Thất bại</div>
                <div class="stat-number">' . $this->stats['failed']  . '</div>
            </div>
            <div class="stat-box stat-skipped">
                <div class="stat-label">Bỏ qua</div>
                <div class="stat-number">' . $this->stats['skipped'] . '</div>
            </div>
        </div>';

        if (!empty($this->stats['errors'])) {
            echo '<div class="error-list"><h3>Chi tiết lỗi:</h3><ul>';
            foreach ($this->stats['errors'] as $err) {
                echo '<li><strong>' . htmlspecialchars($err['label']) . '</strong><br>'
                   . 'Lỗi: ' . htmlspecialchars($err['error']) . '</li>';
            }
            echo '</ul></div>';
        }
    }

    private function log(string $message, string $level = 'info')
    {
        $ts    = date('Y-m-d H:i:s');
        $class = 'log-' . $level;

        echo '<div class="log-entry ' . $class . '">[' . $ts . '] '
           . htmlspecialchars($message) . '</div>';

        if (!empty($GLOBALS['log'])) {
            $level === 'error'
                ? $GLOBALS['log']->error("[MigrateHoadonRV] {$message}")
                : $GLOBALS['log']->info("[MigrateHoadonRV] {$message}");
        }
    }
}
