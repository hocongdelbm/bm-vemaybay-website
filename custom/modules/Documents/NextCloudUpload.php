<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('custom/include/helpers/api/APINextCloud.php');

class NextCloudUpload
{


    /**
     * Upload document to NextCloud after save
     * @param SugarBean $bean
     * @param string $event
     * @param array $arguments
     * 
     * @return void
     * 
     * //Create forlder --> Upload file --> Get public link --> Save link to doc_url field --> Delete local file
     */


    public function handleUpload($bean, $event, $arguments)
    {
        $revision = BeanFactory::getBean('DocumentRevisions', $bean->document_revision_id);
        if (empty($revision->id)) {
            $GLOBALS['log']->error("NextCloudUpload: Cannot retrieve DocumentRevision {$bean->document_revision_id}");
            return;
        }
        if (strpos($revision->file_mime_type, 'image/') === 0) {
            $this->handleUploadFileName($bean, $event, $arguments);
            $this->handleUploadPreviewImage($bean, $event, $arguments);
        } else {
            $this->handleUploadFileName($bean, $event, $arguments);
        }
    }
    public function handleUploadFileName($bean, $event, $arguments)
    {
        global $sugar_config;

        try {
            $GLOBALS['log']->info("=== NextCloudUpload STARTED for Document ID: {$bean->id}, Revision ID: {$bean->document_revision_id} ===");

            // Skip nếu đang trong quá trình save để tránh loop
            if (!empty($bean->in_nextcloud_upload)) {
                $GLOBALS['log']->info("NextCloudUpload: Already in upload process, skipping");
                return;
            }

            // Chỉ xử lý khi có file upload (không phải là edit thông tin)
            if (empty($bean->document_revision_id)) {
                $GLOBALS['log']->info("NextCloudUpload: No document_revision_id found");
                return;
            }

            // Lấy revision để lấy thông tin file
            $revision = BeanFactory::getBean('DocumentRevisions', $bean->document_revision_id);
            if (empty($revision->id)) {
                $GLOBALS['log']->error("NextCloudUpload: Cannot retrieve DocumentRevision {$bean->document_revision_id}");
                return;
            }

            $GLOBALS['log']->info("NextCloudUpload: Revision ID: {$revision->id}, Filename: {$revision->filename}");

            // Kiểm tra đã upload lên NextCloud chưa (nếu đã có doc_url thì skip)
            if (!empty($bean->doc_url)) {
                $GLOBALS['log']->info("NextCloudUpload: Document already has doc_url, skipping");
                return;
            }

            // Check xem revision này đã được upload chưa (dùng custom field hoặc doc_url của revision)
            if (!empty($revision->doc_url)) {
                $GLOBALS['log']->info("NextCloudUpload: Revision already uploaded to NextCloud, copying URL");
                $bean->doc_url = $revision->doc_url;
                $bean->doc_type = 'NextCloud';
                $bean->in_nextcloud_upload = true; // Set flag để tránh loop
                $bean->save();
                return;
            }

            // Đường dẫn file local trong upload://
            $localFilePath = "upload://{$revision->id}";
            if (!file_exists($localFilePath)) {
                $GLOBALS['log']->error("NextCloudUpload: Local file not found: {$localFilePath}");
                return;
            }

            $GLOBALS['log']->info("NextCloudUpload: Local file found: {$localFilePath}");

            // Tạo API NextCloud instance
            $api = new APINextCloud();

            // Tạo folder trên NextCloud từng cấp (bmvmb -> bmvmb/modules -> bmvmb/modules/documents)
            $folderPath = 'bmvmb/modules/documents/document-files';
            $parts = explode('/', $folderPath);
            $currentPath = '';

            foreach ($parts as $part) {
                $currentPath .= ($currentPath ? '/' : '') . $part;
                $result = json_decode($api->createFolder($currentPath), true);
                $httpCode = $result['httpCode'] ?? null;
                $status = $result['status'] ?? null;

                // Check status == 1 (success) hoặc httpCode == 201 (created) hoặc 405 (already exists)
                if ($status == 1 || $httpCode == 201 || $httpCode == 405) {
                    $GLOBALS['log']->info("NextCloudUpload: Folder '{$currentPath}' is ready (status: {$status}, httpCode: {$httpCode})");
                } else {
                    $GLOBALS['log']->error("NextCloudUpload: Failed to create folder '{$currentPath}' - HTTP Code: {$httpCode} - Response: " . json_encode($result));
                    return; // Dừng nếu không tạo được folder
                }
            }
            $GLOBALS['log']->info("NextCloudUpload: All folders are ready on NextCloud");

            // Tạo tên file trên NextCloud: bmvmb/modules/documents/{document_id}_{filename}
            $remoteFileName = $folderPath . '/' . $bean->id . '_' . $revision->filename;

            // Upload file lên NextCloud
            $GLOBALS['log']->info("NextCloudUpload: Uploading file to {$remoteFileName}");
            $uploadResult = json_decode($api->uploadFile($localFilePath, $remoteFileName), true);
            $GLOBALS['log']->info("NextCloudUpload: Upload result - " . json_encode($uploadResult));

            if (!isset($uploadResult['status']) || $uploadResult['status'] != 1) {
                $GLOBALS['log']->error("NextCloudUpload: Failed to upload file - " . json_encode($uploadResult));
                return;
            }

            // Lấy endpoint từ config để tạo URL công khai
            $endpoint = rtrim($sugar_config['next-cloud']['endpoint'], '/') . '/' . $sugar_config['next-cloud']['user'];
            $publicUrl = $endpoint . '/' . rawurlencode($remoteFileName);

            $GLOBALS['log']->info("NextCloudUpload: Successfully uploaded - URL: {$publicUrl}");

            // Lưu URL vào revision trước để tránh upload lại
            $revision->doc_url = $publicUrl;
            $revision->save();

            // Update document trực tiếp vào DB để tránh trigger logic hook lại
            $GLOBALS['db']->query("
                UPDATE documents 
                SET doc_url = " . $GLOBALS['db']->quoted($publicUrl) . ",
                    date_modified = NOW()
                WHERE id = " . $GLOBALS['db']->quoted($bean->id) . "
            ");

            $GLOBALS['log']->info("NextCloudUpload: Successfully uploaded to NextCloud - URL: {$publicUrl}");

            // Xóa file local sau khi upload thành công
            if (file_exists($localFilePath)) {
                unlink($localFilePath);
                $GLOBALS['log']->info("NextCloudUpload: Deleted local file {$localFilePath}");
            }
        } catch (Exception $e) {
            $GLOBALS['log']->error("NextCloudUpload: Exception - " . $e->getMessage());
        }
    }

    public function handleUploadPreviewImage($bean, $event, $arguments)
    {
        global $sugar_config;
        try {
            $GLOBALS['log']->info("=== NextCloudUpload PREVIEW IMAGE UPLOAD STARTED for Document ID: {$bean->id} ===");

            // Skip nếu đang trong quá trình save để tránh loop
            if (!empty($bean->in_nextcloud_upload_preview)) {
                $GLOBALS['log']->info("NextCloudUpload Preview: Already in upload process, skipping");
                return;
            }

            // Chỉ xử lý khi có document ID
            if (empty($bean->id)) {
                $GLOBALS['log']->info("NextCloudUpload Preview: No document ID found");
                return;
            }

            // Kiểm tra xem có preview_image field không
            if (empty($bean->preview_image)) {
                $GLOBALS['log']->info("NextCloudUpload Preview: No preview_image field, skipping");
                return;
            }

            // Đường dẫn file local trong upload://
            $localFilePath = "upload://{$bean->id}_preview_image";
            if (!file_exists($localFilePath)) {
                $GLOBALS['log']->error("NextCloudUpload: Local file not found: {$localFilePath}");
                return;
            }

            $GLOBALS['log']->info("NextCloudUpload: Local file found: {$localFilePath}");

            // Tạo API NextCloud instance
            $api = new APINextCloud();

            // Tạo folder trên NextCloud từng cấp (bmvmb -> bmvmb/modules -> bmvmb/modules/documents)
            $folderPath = 'bmvmb/modules/documents/preview-images';
            $parts = explode('/', $folderPath);
            $currentPath = '';

            foreach ($parts as $part) {
                $currentPath .= ($currentPath ? '/' : '') . $part;
                $result = json_decode($api->createFolder($currentPath), true);
                $httpCode = $result['httpCode'] ?? null;
                $status = $result['status'] ?? null;

                // Check status == 1 (success) hoặc httpCode == 201 (created) hoặc 405 (already exists)
                if ($status == 1 || $httpCode == 201 || $httpCode == 405) {
                    $GLOBALS['log']->info("NextCloudUpload: Folder '{$currentPath}' is ready (status: {$status}, httpCode: {$httpCode})");
                } else {
                    $GLOBALS['log']->error("NextCloudUpload: Failed to create folder '{$currentPath}' - HTTP Code: {$httpCode} - Response: " . json_encode($result));
                    return; // Dừng nếu không tạo được folder
                }
            }
            $GLOBALS['log']->info("NextCloudUpload: All folders are ready on NextCloud");

            // Phát hiện extension từ file local
            $imageInfo = @getimagesize($localFilePath);
            $extension = '.jpg'; // default
            if ($imageInfo !== false) {
                switch ($imageInfo[2]) {
                    case IMAGETYPE_JPEG:
                        $extension = '.jpg';
                        break;
                    case IMAGETYPE_PNG:
                        $extension = '.png';
                        break;
                    case IMAGETYPE_GIF:
                        $extension = '.gif';
                        break;
                    case IMAGETYPE_WEBP:
                        $extension = '.webp';
                        break;
                }
            }
            $GLOBALS['log']->info("NextCloudUpload: Detected image extension: {$extension}");

            // Tạo tên file trên NextCloud: {document_id}_preview_image.extension
            $remoteFileName = $folderPath . '/' . $bean->id . '_preview_image' . $extension;

            // Upload file lên NextCloud
            $GLOBALS['log']->info("NextCloudUpload: Uploading file to {$remoteFileName}");
            $uploadResult = json_decode($api->uploadFile($localFilePath, $remoteFileName), true);
            $GLOBALS['log']->info("NextCloudUpload: Upload result - " . json_encode($uploadResult));

            if (!isset($uploadResult['status']) || $uploadResult['status'] != 1) {
                $GLOBALS['log']->error("NextCloudUpload: Failed to upload file - " . json_encode($uploadResult));
                return;
            }

            // Lấy endpoint từ config để tạo URL công khai
            $endpoint = rtrim($sugar_config['next-cloud']['endpoint'], '/') . '/' . $sugar_config['next-cloud']['user'];
            $publicUrl = $endpoint . '/' . rawurlencode($remoteFileName);

            $GLOBALS['log']->info("NextCloudUpload: Successfully uploaded - URL: {$publicUrl}");

            // LƯU TÊN FILE VÀO DB - Lưu tên file có extension để view.detail.php dùng
            $filename = $bean->id . '_preview_image' . $extension;
            $GLOBALS['db']->query("
                UPDATE documents 
                SET preview_image = " . $GLOBALS['db']->quoted($filename) . ",
                    date_modified = NOW()
                WHERE id = " . $GLOBALS['db']->quoted($bean->id) . "
            ");
            $GLOBALS['log']->info("NextCloudUpload: Saved preview_image filename to DB: {$filename}");

            // Xóa file local sau khi upload thành công
            if (file_exists($localFilePath)) {
                unlink($localFilePath);
                $GLOBALS['log']->info("NextCloudUpload: Deleted local file {$localFilePath}");
            }
        } catch (Exception $e) {
            $GLOBALS['log']->error("NextCloudUpload: Exception - " . $e->getMessage());
        }
    }
    public function handleDelete($bean, $event, $arguments)
    {
        try {
            $GLOBALS['log']->info("=== NextCloudUpload DELETE STARTED for Document ID: {$bean->id} ===");

            // Lấy revision để biết filename
            $revision = BeanFactory::getBean('DocumentRevisions', $bean->document_revision_id);
            if (empty($revision->id)) {
                $GLOBALS['log']->error("NextCloudUpload Delete: Cannot retrieve DocumentRevision {$bean->document_revision_id}");
                // Vẫn tiếp tục để xóa file local
            }

            $GLOBALS['log']->info("NextCloudUpload Delete: Revision ID: {$revision->id}, Filename: {$revision->filename}");

            $api = new APINextCloud();

            // Tạo cấu trúc folder trash với 2 subfolder
            // trash/doc-filename/ - cho file documents
            // trash/preview_image/ - cho preview images
            $trashFolders = [
                'bmvmb/modules/documents/trash',
                'bmvmb/modules/documents/trash/doc-filename',
                'bmvmb/modules/documents/trash/preview_images'
            ];

            foreach ($trashFolders as $folderPath) {
                $parts = explode('/', $folderPath);
                $currentPath = '';

                foreach ($parts as $part) {
                    $currentPath .= ($currentPath ? '/' : '') . $part;
                    $result = json_decode($api->createFolder($currentPath), true);

                    $status = $result['status'] ?? null;
                    $httpCode = $result['httpCode'] ?? null;
                    if ($status == 1 || $httpCode == 201 || $httpCode == 405) {
                        $GLOBALS['log']->info("NextCloudUpload Delete: Folder '{$currentPath}' is ready");
                    } else {
                        $GLOBALS['log']->error("NextCloudUpload Delete: Failed to create folder '{$currentPath}' - Response: " . json_encode($result));
                        return;
                    }
                }
            }

            // 1. Move FILE DOCUMENT vào trash/doc-filename/
            if (!empty($revision->filename)) {
                $remoteFileName = 'bmvmb/modules/documents/document-files/' . $bean->id . '_' . $revision->filename;
                $destinationPath = 'bmvmb/modules/documents/trash/doc-filename/' . $bean->id . '_' . $revision->filename;

                $GLOBALS['log']->info("NextCloudUpload Delete: Moving document file {$remoteFileName} to {$destinationPath}");

                $moveResult = json_decode($api->removeFile($remoteFileName, $destinationPath), true);
                $GLOBALS['log']->info("NextCloudUpload Delete: Move document result - " . json_encode($moveResult));
            }

            // 2. LUÔN THỬ MOVE PREVIEW IMAGE vào trash/preview_images/ (không cần check bean field)
            // Vì nếu là image file thì sẽ có preview, chỉ cần try move
            // IMPORTANT: Phải dùng 'preview-images' (dấu gạch ngang) để match với folder upload
            $previewFileName = 'bmvmb/modules/documents/preview-images/' . $bean->id . '_preview_image';
            $previewDestPath = 'bmvmb/modules/documents/trash/preview_images/' . $bean->id . '_preview_image';

            $GLOBALS['log']->info("NextCloudUpload Delete: Trying to move preview image {$previewFileName}");

            // Try move preview - nếu không tồn tại thì sẽ báo lỗi nhưng không sao
            $previewMoveResult = json_decode($api->removeFile($previewFileName, $previewDestPath), true);
            
            if (isset($previewMoveResult['status']) && $previewMoveResult['status'] == 1) {
                $GLOBALS['log']->info("NextCloudUpload Delete: Moved preview image to trash successfully");
            } else {
                $GLOBALS['log']->info("NextCloudUpload Delete: Preview image not found or already moved - " . json_encode($previewMoveResult));
            }

            // XÓA FILE LOCAL NGAY LẬP TỨC - TRƯỚC KHI SuiteCRM chuyển vào deleted/
            $GLOBALS['log']->info("[BEFORE-DELETE] Deleting local files NOW to prevent storage in source code...");

            $documentId = $bean->id;
            $revisionId = $bean->document_revision_id;

            $filesToDeleteNow = array(
                $documentId,
                $documentId . '_preview_image',
                $revisionId
            );

            if (!empty($revision->id)) {
                $filesToDeleteNow[] = $revision->id;
                $filesToDeleteNow[] = $revision->id . '_preview_image';
            }

            $deletedCount = 0;
            foreach ($filesToDeleteNow as $fileId) {
                if (empty($fileId)) continue;

                $uploadPath = "upload://{$fileId}";
                if (file_exists($uploadPath)) {
                    if (@unlink($uploadPath)) {
                        $deletedCount++;
                        $GLOBALS['log']->info("[BEFORE-DELETE] ✓ Deleted file: {$uploadPath}");
                    }
                }
            }

            $GLOBALS['log']->info("[BEFORE-DELETE] Completed: Deleted {$deletedCount} local files before SuiteCRM move");
        } catch (Exception $e) {
            $GLOBALS['log']->error("NextCloudUpload Delete: Exception - " . $e->getMessage());
        }
    }

    /**
     * Clean up ALL local files after delete
     * SuiteCRM moves BOTH Document ID and Revision ID files to deleted folder
     * This function removes all traces from source code
     */
    public function handleDeleteCleanup($bean, $event, $arguments)
    {
        try {
            $GLOBALS['log']->info("=== NextCloudUpload DELETE CLEANUP STARTED for Document ID: {$bean->id} ===");

            $documentId = $bean->id;
            $revisionId = $bean->document_revision_id;

            $GLOBALS['log']->info("[CLEANUP] Document ID: {$documentId}, Revision ID: {$revisionId}");

            // Danh sách tất cả các ID cần xóa (Document + Revision + preview images)
            $filesToDelete = array(
                $documentId,
                $documentId . '_preview_image'
            );

            // Thêm revision ID nếu có
            if (!empty($revisionId)) {
                $filesToDelete[] = $revisionId;

                // Thử lấy revision để check preview
                $revision = BeanFactory::getBean('DocumentRevisions', $revisionId);
                if (!empty($revision->id)) {
                    $GLOBALS['log']->info("[CLEANUP] Found Revision: {$revision->id}");
                    $filesToDelete[] = $revision->id . '_preview_image';
                }
            }

            // Xóa TẤT CẢ các file
            $totalDeleted = 0;
            foreach ($filesToDelete as $fileId) {
                if (empty($fileId)) continue;

                $GLOBALS['log']->info("[CLEANUP] Processing file ID: {$fileId}");

                // 1. Xóa file trong upload:// (nếu còn)
                $uploadPath = "upload://{$fileId}";
                if (file_exists($uploadPath)) {
                    if (@unlink($uploadPath)) {
                        $totalDeleted++;
                        $GLOBALS['log']->info("[CLEANUP] ✓ Deleted upload:// file: {$uploadPath}");
                    }
                }

                // 2. Xóa file trực tiếp trong upload://deleted/ (flat structure)
                $deletedFlat = "upload://deleted/{$fileId}";
                if (file_exists($deletedFlat)) {
                    if (@unlink($deletedFlat)) {
                        $totalDeleted++;
                        $GLOBALS['log']->info("[CLEANUP] ✓ Deleted flat deleted file: {$deletedFlat}");
                    }
                }

                // 3. Xóa file trong deleted/ với structured path: XX/XX/XX/rest
                if (strlen($fileId) >= 6) {
                    $part1 = substr($fileId, 0, 2);
                    $part2 = substr($fileId, 2, 2);
                    $part3 = substr($fileId, 4, 2);
                    $rest = substr($fileId, 6);

                    $structuredPath = "upload://deleted/{$part1}/{$part2}/{$part3}/{$rest}";

                    if (file_exists($structuredPath)) {
                        if (@unlink($structuredPath)) {
                            $totalDeleted++;
                            $GLOBALS['log']->info("[CLEANUP] ✓ Deleted structured deleted file: {$structuredPath}");
                        }

                        // Xóa thư mục rỗng
                        $this->cleanupEmptyDirectories($part1, $part2, $part3);
                    } else {
                        $GLOBALS['log']->info("[CLEANUP] - File not found in deleted structured path: {$structuredPath}");
                    }
                }
            }

            $GLOBALS['log']->info("=== NextCloudUpload DELETE CLEANUP COMPLETED - Deleted {$totalDeleted} files ===");

            // EXTRA: Scan và xóa tất cả file rác còn sót lại trong deleted folder
            $this->forceCleanupDeletedFolder();
        } catch (Exception $e) {
            $GLOBALS['log']->error("[CLEANUP] Exception: " . $e->getMessage());
        }
    }

    /**
     * FORCE cleanup: Scan deleted folder và xóa TẤT CẢ file cũ
     * Chạy sau khi cleanup thông thường để đảm bảo không có file rác
     */
    private function forceCleanupDeletedFolder()
    {
        try {
            $deletedPath = 'upload://deleted';
            if (!is_dir($deletedPath)) {
                return;
            }

            $GLOBALS['log']->info("[FORCE-CLEANUP] Scanning deleted folder for orphaned files...");

            // Lấy tất cả file trong deleted folder (đệ quy)
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($deletedPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            $filesDeleted = 0;
            $dirsDeleted = 0;

            foreach ($iterator as $fileInfo) {
                $path = $fileInfo->getRealPath();

                if ($fileInfo->isFile()) {
                    // Xóa file nếu cũ hơn 5 phút (tránh xóa file đang được process)
                    $age = time() - $fileInfo->getMTime();
                    if ($age > 300) { // 5 phút
                        if (@unlink($path)) {
                            $filesDeleted++;
                            $GLOBALS['log']->info("[FORCE-CLEANUP] ✓ Deleted old file: {$path}");
                        }
                    }
                } elseif ($fileInfo->isDir()) {
                    // Xóa thư mục rỗng
                    $files = @scandir($path);
                    if ($files && count($files) == 2) { // chỉ có . và ..
                        if (@rmdir($path)) {
                            $dirsDeleted++;
                            $GLOBALS['log']->info("[FORCE-CLEANUP] ✓ Deleted empty dir: {$path}");
                        }
                    }
                }
            }

            if ($filesDeleted > 0 || $dirsDeleted > 0) {
                $GLOBALS['log']->info("[FORCE-CLEANUP] Completed: Deleted {$filesDeleted} files and {$dirsDeleted} directories");
            }
        } catch (Exception $e) {
            $GLOBALS['log']->warn("[FORCE-CLEANUP] Could not scan deleted folder: " . $e->getMessage());
        }
    }

    /**
     * Helper function to clean up empty directories in deleted folder
     */
    private function cleanupEmptyDirectories($part1, $part2, $part3)
    {
        try {
            $dir3 = "upload://deleted/{$part1}/{$part2}/{$part3}";
            $dir2 = "upload://deleted/{$part1}/{$part2}";
            $dir1 = "upload://deleted/{$part1}";

            // Xóa từ trong ra ngoài
            if (is_dir($dir3)) {
                $files = @scandir($dir3);
                if ($files && count($files) == 2) { // chỉ có . và ..
                    @rmdir($dir3);
                    $GLOBALS['log']->info("[CLEANUP] ✓ Removed empty dir: {$dir3}");
                }
            }

            if (is_dir($dir2)) {
                $files = @scandir($dir2);
                if ($files && count($files) == 2) {
                    @rmdir($dir2);
                    $GLOBALS['log']->info("[CLEANUP] ✓ Removed empty dir: {$dir2}");
                }
            }

            if (is_dir($dir1)) {
                $files = @scandir($dir1);
                if ($files && count($files) == 2) {
                    @rmdir($dir1);
                    $GLOBALS['log']->info("[CLEANUP] ✓ Removed empty dir: {$dir1}");
                }
            }
        } catch (Exception $e) {
            $GLOBALS['log']->warn("[CLEANUP] Could not clean directories: " . $e->getMessage());
        }
    }
}
