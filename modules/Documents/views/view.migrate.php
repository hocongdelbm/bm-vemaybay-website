<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('custom/include/helpers/api/APINextCloud.php');

/**
 * View to migrate old documents to use public share links
 * 
 * This view provides a UI to:
 * 1. Find all documents that have files on NextCloud but don't have public share links
 * 2. Create public share links for those files
 * 3. Update doc_url in both documents and document_revisions tables
 * 
 * Access: index.php?module=Documents&action=Migrate
 */
class DocumentsViewMigrate extends SugarView
{
    private $api;
    private $stats = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'skipped' => 0,
        'errors' => []
    ];

    public function __construct()
    {
        parent::__construct();
        $this->api = new APINextCloud();
    }

    /**
     * Display the migration interface
     */
    public function display()
    {
        global $mod_strings, $app_strings, $current_user;

        // Check admin permission
        if (!$current_user->isAdmin()) {
            sugar_die("Unauthorized access. Admin privileges required.");
        }

        // Check if migration is requested
        if (!empty($_REQUEST['run_migration'])) {
            $this->runMigration();
            return;
        }

        // Show the UI
        $this->renderUI();
    }

    /**
     * Render the migration UI
     */
    private function renderUI()
    {
        global $mod_strings;

        // Get count of documents that need migration
        $count = $this->getDocumentsToMigrateCount();

        echo '<!DOCTYPE html>
<html>
<head>
    <title>Migrate Documents to Public Share Links</title>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333; 
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .info-box {
            background: #d9edf7;
            border: 1px solid #bce8f1;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box {
            background: #fcf8e3;
            border: 1px solid #faebcc;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .success-box {
            background: #dff0d8;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            padding: 12px 24px;
            margin: 10px 5px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        .btn-primary:hover {
            background: #45a049;
        }
        .btn-warning {
            background: #f0ad4e;
            color: white;
        }
        .btn-warning:hover {
            background: #ec971f;
        }
        .btn-danger {
            background: #d9534f;
            color: white;
        }
        .btn-danger:hover {
            background: #c9302c;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        input[type="number"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 200px;
        }
        .stat {
            font-size: 48px;
            font-weight: bold;
            color: #4CAF50;
            text-align: center;
            margin: 20px 0;
        }
        ul {
            line-height: 1.8;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Migrate Documents to Public Share Links</h1>
        
        <div class="info-box">
            <strong>📋 Mục đích:</strong><br>
            Script này sẽ tạo public share link cho các document cũ đã được upload lên NextCloud nhưng chưa có public link.<br>
            Điều này cho phép files được truy cập trực tiếp qua URL công khai.
        </div>

        <div class="stat">' . $count . '</div>
        <p style="text-align: center; font-size: 18px; color: #666;">
            document(s) cần migration
        </p>

        <div class="warning-box">
            <strong>⚠️ Chú ý quan trọng:</strong><br>
            <ul>
                <li>Nên chạy <strong>DRY RUN</strong> trước để kiểm tra</li>
                <li><strong>Backup database</strong> trước khi chạy migration thực tế</li>
                <li>Script sẽ update cả bảng <code>documents</code> và <code>document_revisions</code></li>
                <li>Quá trình có thể mất vài phút tùy số lượng documents</li>
            </ul>
        </div>

        <form method="GET" action="">
            <input type="hidden" name="module" value="Documents">
            <input type="hidden" name="action" value="Migrate">
            
            <div class="form-group">
                <label for="limit">Giới hạn số lượng (để trống = xử lý tất cả):</label>
                <input type="number" id="limit" name="limit" min="1" placeholder="Tất cả documents">
                <small style="color: #666; display: block; margin-top: 5px;">
                    Nên test với số nhỏ (VD: 5-10) trước khi chạy toàn bộ
                </small>
            </div>

            <div class="form-group">
                <button type="submit" name="run_migration" value="dry_run" 
                        onclick="return confirm(\'Chạy DRY RUN để kiểm tra?\n\nKhông có thay đổi nào sẽ được lưu vào database.\')" 
                        class="button btn-warning">
                    🔍 DRY RUN (Kiểm tra không thay đổi)
                </button>
                
                <button type="submit" name="run_migration" value="execute" 
                        onclick="return confirm(\'⚠️ CẢNH BÁO: Script sẽ thay đổi database!\n\nBạn đã backup database chưa?\n\nNhấn OK để tiếp tục.\')" 
                        class="button btn-primary">
                    ✅ CHẠY MIGRATION (Thay đổi database)
                </button>
                
                <a href="index.php?module=Documents&action=index" class="button btn-secondary">
                    ← Quay lại
                </a>
            </div>
        </form>

        <div class="info-box">
            <strong>💡 Gợi ý:</strong><br>
            <ol>
                <li>Chạy DRY RUN với limit nhỏ (5-10) để xem kết quả</li>
                <li>Nếu OK, chạy DRY RUN toàn bộ</li>
                <li>Backup database</li>
                <li>Chạy MIGRATION thực tế</li>
            </ol>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Run the migration
     */
    private function runMigration()
    {
        global $db;
        
        $dryRun = ($_REQUEST['run_migration'] === 'dry_run');
        $limit = !empty($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : null;

        echo '<!DOCTYPE html>
<html>
<head>
    <title>Migration Progress</title>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333; 
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .log {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            max-height: 500px;
            overflow-y: auto;
            font-family: "Courier New", monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        .log-entry {
            margin: 5px 0;
            padding: 3px 0;
        }
        .log-info { color: #333; }
        .log-success { color: #4CAF50; font-weight: bold; }
        .log-error { color: #d9534f; font-weight: bold; }
        .log-warning { color: #f0ad4e; font-weight: bold; }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat-box {
            text-align: center;
            padding: 20px;
            border-radius: 4px;
            min-width: 120px;
        }
        .stat-total { background: #d9edf7; border: 2px solid #31708f; }
        .stat-success { background: #dff0d8; border: 2px solid #3c763d; }
        .stat-failed { background: #f2dede; border: 2px solid #a94442; }
        .stat-skipped { background: #fcf8e3; border: 2px solid #8a6d3b; }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 14px;
            text-transform: uppercase;
        }
        .button {
            padding: 12px 24px;
            margin: 10px 5px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            background: #6c757d;
            color: white;
        }
        .button:hover {
            background: #5a6268;
        }
        .error-list {
            margin-top: 20px;
            padding: 15px;
            background: #f2dede;
            border: 1px solid #ebccd1;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 ' . ($dryRun ? 'DRY RUN - Migration Preview' : 'Migration Progress') . '</h1>';

        if ($dryRun) {
            echo '<div style="background: #fcf8e3; border: 1px solid #faebcc; padding: 15px; margin: 20px 0; border-radius: 4px;">
                    <strong>⚠️ DRY RUN MODE</strong><br>
                    Không có thay đổi nào sẽ được lưu vào database. Đây chỉ là preview.
                  </div>';
        }

        echo '<div class="log" id="log">';
        
        // Flush output buffer để hiển thị real-time
        if (ob_get_level() == 0) ob_start();

        $this->log("=== Bắt đầu migration ===");
        if ($dryRun) {
            $this->log("Chế độ: DRY RUN (không thay đổi database)", 'warning');
        } else {
            $this->log("Chế độ: EXECUTE (sẽ thay đổi database)", 'warning');
        }

        // Find documents that need migration
        $documents = $this->findDocumentsToMigrate($limit);
        $this->stats['total'] = count($documents);
        
        $this->log("Tìm thấy {$this->stats['total']} documents cần xử lý");

        // Process each document
        foreach ($documents as $doc) {
            $this->processDocument($doc, $dryRun);
            flush();
            ob_flush();
        }

        $this->log("=== Hoàn thành migration ===", 'success');

        echo '</div>';

        // Display statistics
        $this->renderStats();

        echo '
        <div style="margin-top: 30px;">
            <a href="index.php?module=Documents&action=Migrate" class="button">
                ← Quay lại
            </a>
            <a href="index.php?module=Documents&action=index" class="button">
                Về trang Documents
            </a>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Get count of documents that need migration
     */
    private function getDocumentsToMigrateCount()
    {
        global $db;
        
        $query = "
            SELECT COUNT(*) as count
            FROM documents d
            WHERE d.deleted = 0
                AND d.document_revision_id IS NOT NULL
                AND d.document_revision_id != ''
                AND (
                    d.doc_url IS NULL 
                    OR d.doc_url = '' 
                    OR d.doc_url NOT LIKE '%/s/%'
                    OR d.doc_url LIKE '%/download'
                )
        ";

        $result = $db->query($query);
        $row = $db->fetchByAssoc($result);
        
        return (int)$row['count'];
    }

    /**
     * Find documents that need migration
     */
    private function findDocumentsToMigrate($limit = null)
    {
        global $db;
        
        $limitClause = $limit ? "LIMIT " . (int)$limit : "";
        
        $query = "
            SELECT 
                d.id, 
                d.document_name,
                d.document_revision_id,
                d.doc_url,
                d.doc_type
            FROM documents d
            WHERE d.deleted = 0
                AND d.document_revision_id IS NOT NULL
                AND d.document_revision_id != ''
                AND (
                    d.doc_url IS NULL 
                    OR d.doc_url = '' 
                    OR d.doc_url NOT LIKE '%/s/%'
                    OR d.doc_url LIKE '%/download'
                )
            ORDER BY d.date_entered DESC
            {$limitClause}
        ";

        $result = $db->query($query);
        $documents = [];
        
        while ($row = $db->fetchByAssoc($result)) {
            $documents[] = $row;
        }

        return $documents;
    }

    /**
     * Process a single document
     */
    private function processDocument($doc, $dryRun)
    {
        global $db;
        
        $docId = $doc['id'];
        $docName = $doc['document_name'];
        $revisionId = $doc['document_revision_id'];

        $this->log("Đang xử lý: {$docName} (ID: {$docId})");

        try {
            // Get revision info
            $revision = BeanFactory::getBean('DocumentRevisions', $revisionId);
            if (empty($revision->id)) {
                throw new Exception("Không tìm thấy revision {$revisionId}");
            }

            $filename = $revision->filename;
            $revisionNumber = $revision->revision ?? '1';

            // Case 1: doc_url already has public link with /download suffix -> strip it
            $currentDocUrl = $doc['doc_url'] ?? '';
            if (!empty($currentDocUrl) && strpos($currentDocUrl, '/s/') !== false && preg_match('#/download$#', $currentDocUrl)) {
                $cleanUrl = preg_replace('#/download$#', '', $currentDocUrl);
                $this->log("  Phát hiện link cũ có /download: {$currentDocUrl}");
                $this->log("  → Sẽ sửa thành: {$cleanUrl}", 'warning');

                if (!$dryRun) {
                    $db->query("
                        UPDATE documents 
                        SET doc_url = " . $db->quoted($cleanUrl) . ",
                            doc_type = 'NextCloud'
                        WHERE id = " . $db->quoted($docId) . "
                    ");
                    $db->query("
                        UPDATE document_revisions 
                        SET doc_url = " . $db->quoted($cleanUrl) . "
                        WHERE id = " . $db->quoted($revisionId) . "
                    ");
                    $this->log("  ✓ Đã xóa /download khỏi URL", 'success');
                }

                $this->stats['success']++;
                return;
            }

            // Case 2: Revision already has public link (without /download) -> copy to document
            if (!empty($revision->doc_url) && strpos($revision->doc_url, '/s/') !== false && !preg_match('#/download$#', $revision->doc_url)) {
                $this->log("  ✓ Revision đã có public link: {$revision->doc_url}", 'success');
                
                if (!$dryRun) {
                    // Copy the link to document
                    $db->query("
                        UPDATE documents 
                        SET doc_url = " . $db->quoted($revision->doc_url) . ",
                            doc_type = 'NextCloud'
                        WHERE id = " . $db->quoted($docId) . "
                    ");
                }
                
                $this->stats['success']++;
                return;
            }

            // Build remote file path using naming convention: v{revision}_{filename}
            $remotePath = 'bmvmb/modules/documents/v' . $revisionNumber . '_' . $filename;
            
            $this->log("  Remote path: {$remotePath}");

            // Check if share already exists for this path
            $existingShare = $this->findExistingShare($remotePath);
            
            if ($existingShare) {
                $publicShareUrl = $existingShare['url'];
                
                $this->log("  ✓ Tìm thấy share link có sẵn: {$publicShareUrl}", 'success');
            } else {
                // Create public share
                $this->log("  Đang tạo public share...");
                
                if ($dryRun) {
                    $this->log("  [DRY RUN] Sẽ tạo share cho: {$remotePath}", 'warning');
                    $this->stats['success']++;
                    return;
                }

                $shareResp = $this->api->createShare($remotePath);
                $shareResult = json_decode($shareResp, true);

                if (!isset($shareResult['status']) || $shareResult['status'] != 1 || !isset($shareResult['data']['url'])) {
                    throw new Exception("Không tạo được share: " . json_encode($shareResult));
                }

                $publicShareUrl = $shareResult['data']['url'];
                
                $this->log("  ✓ Đã tạo public share: {$publicShareUrl}", 'success');
            }

            if (!$dryRun) {
                // Update revision
                $db->query("
                    UPDATE document_revisions 
                    SET doc_url = " . $db->quoted($publicShareUrl) . "
                    WHERE id = " . $db->quoted($revisionId) . "
                ");

                // Update document
                $db->query("
                    UPDATE documents 
                    SET doc_url = " . $db->quoted($publicShareUrl) . ",
                        doc_type = 'NextCloud'
                    WHERE id = " . $db->quoted($docId) . "
                ");

                $this->log("  ✓ Đã update database", 'success');
            }

            $this->stats['success']++;

        } catch (Exception $e) {
            $this->log("  ✗ Lỗi: " . $e->getMessage(), 'error');
            $this->stats['failed']++;
            $this->stats['errors'][] = [
                'doc_id' => $docId,
                'doc_name' => $docName,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find existing share for a path
     */
    private function findExistingShare($remotePath)
    {
        try {
            $shareResp = $this->api->getShares($remotePath, false, false);
            $shareResult = json_decode($shareResp, true);

            if (isset($shareResult['status']) && $shareResult['status'] == 1 && 
                isset($shareResult['data']) && is_array($shareResult['data']) && 
                count($shareResult['data']) > 0) {
                
                // Get first public share (shareType = 3)
                foreach ($shareResult['data'] as $share) {
                    if (isset($share['share_type']) && $share['share_type'] == 3) {
                        return [
                            'id' => $share['id'],
                            'url' => $share['url']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // No existing share found
        }

        return null;
    }

    /**
     * Render statistics
     */
    private function renderStats()
    {
        echo '
        <div class="stats">
            <div class="stat-box stat-total">
                <div class="stat-label">Tổng số</div>
                <div class="stat-number">' . $this->stats['total'] . '</div>
            </div>
            <div class="stat-box stat-success">
                <div class="stat-label">Thành công</div>
                <div class="stat-number">' . $this->stats['success'] . '</div>
            </div>
            <div class="stat-box stat-failed">
                <div class="stat-label">Thất bại</div>
                <div class="stat-number">' . $this->stats['failed'] . '</div>
            </div>
            <div class="stat-box stat-skipped">
                <div class="stat-label">Bỏ qua</div>
                <div class="stat-number">' . $this->stats['skipped'] . '</div>
            </div>
        </div>';

        if (!empty($this->stats['errors'])) {
            echo '<div class="error-list">
                    <h3>Chi tiết lỗi:</h3>
                    <ul>';
            foreach ($this->stats['errors'] as $error) {
                echo '<li><strong>' . htmlspecialchars($error['doc_name']) . '</strong> (ID: ' . htmlspecialchars($error['doc_id']) . ')<br>';
                echo 'Lỗi: ' . htmlspecialchars($error['error']) . '</li>';
            }
            echo '</ul></div>';
        }
    }

    /**
     * Log message
     */
    private function log($message, $level = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $class = 'log-' . $level;
        
        echo '<div class="log-entry ' . $class . '">[' . $timestamp . '] ' . htmlspecialchars($message) . '</div>';
        
        if (isset($GLOBALS['log'])) {
            if ($level == 'error') {
                $GLOBALS['log']->error($message);
            } else {
                $GLOBALS['log']->info($message);
            }
        }
    }
}
