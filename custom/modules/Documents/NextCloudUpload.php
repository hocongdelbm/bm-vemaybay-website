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
        // Simplified - only upload main file to single folder
        $this->handleUploadFileName($bean, $event, $arguments);
    }
    public function handleUploadFileName($bean, $event, $arguments)
    {
        global $sugar_config;

        try {
            $GLOBALS['log']->info("=== NextCloudUpload STARTED for Document ID: {$bean->id}, Revision ID: {$bean->document_revision_id} ===");

            // Skip nếu đang trong quá trình save để tránh loop
            if (!empty($bean->in_nextcloud_upload)) {
                $GLOBALS['log']->fatal("NextCloudUpload: Already in upload process, skipping");
                return;
            }

            // Chỉ xử lý khi có file upload (không phải là edit thông tin)
            if (empty($bean->document_revision_id)) {
                $GLOBALS['log']->fatal("NextCloudUpload: No document_revision_id found");
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

            // Tạo folder trên NextCloud từng cấp - single folder structure
            $folderPath = 'bmvmb/modules/documents';
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
            // New naming convention: v{revision}_{filename}
            // ALWAYS use this simple format for consistency
            // This ensures that editing document_name won't break file access
            $revisionNumber = $revision->revision ?? '1';
            $remoteFileName = $folderPath . '/' . 'v' . $revisionNumber . '_' . $revision->filename;
            
            $GLOBALS['log']->info("NextCloudUpload: Remote filename: {$remoteFileName}");

            // Upload file lên NextCloud
            $uploadResult = json_decode($api->uploadFile($localFilePath, $remoteFileName), true);

            if (!isset($uploadResult['status']) || $uploadResult['status'] != 1) {
                $GLOBALS['log']->error("NextCloudUpload: Failed to upload file - " . json_encode($uploadResult));
                return;
            }

            // Lấy endpoint từ config để tạo URL công khai
            $endpoint = rtrim($sugar_config['next-cloud']['endpoint'], '/') . '/' . $sugar_config['next-cloud']['user'];
            $publicUrl = $endpoint . '/' . rawurlencode($remoteFileName);
            // $publicUrl = $sugar_config['site_url'] . '/index.php?entryPoint=NextCloudPreview&id=' . $bean->id;
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

            // Xóa file local sau khi upload thành công
            if (file_exists($localFilePath)) {
                unlink($localFilePath);
                $GLOBALS['log']->info("NextCloudUpload: Deleted local file {$localFilePath}");
            }
        } catch (Exception $e) {
            $GLOBALS['log']->error("NextCloudUpload: Exception - " . $e->getMessage());
        }
    }

    // Preview image logic removed - we now preview the main file if it's an image
    public function handleDelete($bean, $event, $arguments)
    {
        try {
            // Lấy revision để biết filename
            $revision = BeanFactory::getBean('DocumentRevisions', $bean->document_revision_id);
            if (empty($revision->id)) {
                $GLOBALS['log']->error("NextCloudUpload Delete: Cannot retrieve DocumentRevision {$bean->document_revision_id}");
                // Vẫn tiếp tục để xóa file local
            }

            $api = new APINextCloud();

            // Tạo folder trash - single folder structure
            $trashFolders = [
                'bmvmb/modules/documents/trash'
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

            // Move file to trash using simple naming convention v{revision}_{filename}
            if (!empty($revision->filename)) {
                $revisionNumber = $revision->revision ?? '1';
                $remoteFileName = 'bmvmb/modules/documents/v' . $revisionNumber . '_' . $revision->filename;
                $destinationPath = 'bmvmb/modules/documents/trash/v' . $revisionNumber . '_' . $revision->filename;

                $GLOBALS['log']->info("NextCloudUpload Delete: Moving document file {$remoteFileName} to {$destinationPath}");

                $moveResult = json_decode($api->removeFile($remoteFileName, $destinationPath), true);
                $GLOBALS['log']->info("NextCloudUpload Delete: Move document result - " . json_encode($moveResult));
            }

            // XÓA FILE LOCAL NGAY LẬP TỨC - TRƯỚC KHI SuiteCRM chuyển vào deleted/
            $documentId = $bean->id;
            $revisionId = $bean->document_revision_id;

            $filesToDeleteNow = array(
                $documentId,
                $revisionId
            );

            if (!empty($revision->id)) {
                $filesToDeleteNow[] = $revision->id;
            }

            $deletedCount = 0;
            foreach ($filesToDeleteNow as $fileId) {
                if (empty($fileId)) continue;

                $uploadPath = "upload://{$fileId}";
                if (file_exists($uploadPath)) {
                    if (@unlink($uploadPath)) {
                        $deletedCount++;
                    }
                }
            }
        } catch (Exception $e) {
            $GLOBALS['log']->error("NextCloudUpload Delete: Exception - " . $e->getMessage());
        }
    }
}
