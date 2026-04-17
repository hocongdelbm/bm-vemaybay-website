<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('custom/include/helpers/api/APINextCloud.php');

class NextCloudUploadAlert
{
    private $folderPath = "bmvmb/modules/alerts/";
    /**
     * Upload alert document to NextCloud after save
     * @param SugarBean $bean
     * @param string $event
     * @param array $arguments
     * 
     * @return void
     */
    public function handleUpload($bean, $event, $arguments)
    {
        $hasUpload = false; // Biến cờ để kiểm tra xem có upload cái gì không

        // 1. Kiểm tra và xử lý Alert Photo
        if (!empty($bean->alert_photo)) {
            $this->handleUploadAlertPhoto($bean, $event, $arguments);
            $hasUpload = true;
        }

        // 2. Kiểm tra và xử lý FileName (Dùng IF mới, không dùng ELSE IF)
        if (!empty($bean->filename)) {
            $this->handleUploadFileName($bean, $event, $arguments);
            $hasUpload = true;
        }

        // 3. Nếu cả 2 đều trống thì mới báo lỗi
        if (!$hasUpload) {
            $GLOBALS['log']->error("NextCloudUploadAlert: No file to upload found");
            return;
        }
    }

    public function handleUploadAlertPhoto($bean, $event, $arguments)
    {
        try {
            //tránh loop save
            if (!empty($bean->in_nextcloud_upload)) {
                $GLOBALS['log']->fatal("NextCloudUploadAlert: Already in upload process, skipping");
                return;
            }

            if (empty($bean->alert_photo)) {
                $GLOBALS['log']->fatal("NextCloudUploadAlert: No alert_photo found");
                return;
            }

            $localFilePath = "upload://{$bean->parent_alert_id}_alert_photo";
            if (!file_exists($localFilePath)) {
                $GLOBALS['log']->error("NextCloudUploadAlert: Local file does not exist - {$localFilePath}");
                return;
            }
            $api = new APINextCloud();

            $cusfolderPath = rtrim($this->folderPath, '/') . '/' . 'alert_photos';
            $part = explode('/', $cusfolderPath);
            $currentPath = '';
            foreach ($part as $folder) {
                $currentPath .= ($currentPath ? '/' : '') . $folder;
                $result = json_decode($api->createFolder($currentPath), true);
                $httpCode = $result['httpCode'] ?? null;
                $status = $result['status'] ?? null;
                if ($status != 1 && $httpCode != 201 && $httpCode != 405) {
                    $GLOBALS['log']->error("NextCloudUploadAlert: Failed to create folder {$currentPath} - " . json_encode($result));
                    return;
                }
            }

            $alert_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $bean->name);
            $remoteFileName = $cusfolderPath . '/' . 'alert_photo_' . $alert_name . '_' . $bean->alert_photo;
            $uploadResult = json_decode($api->uploadFile($localFilePath, $remoteFileName), true);
            if (!isset($uploadResult['status']) || $uploadResult['status'] != 1) {
                $GLOBALS['log']->error("NextCloudUploadAlert: Failed to upload file - " . json_encode($uploadResult));
                return;
            }

            if (file_exists($localFilePath)) {
                unlink($localFilePath);
            }
        } catch (\Throwable $th) {
            $GLOBALS['log']->error("NextCloudUploadAlert: Exception - " . $th->getMessage());
        }
    }
    public function handleUploadFileName($bean, $event, $arguments)
    {
        try {
            //tránh loop save
            if (!empty($bean->in_nextcloud_upload)) {
                $GLOBALS['log']->fatal("NextCloudUploadAlert: Already in upload process, skipping");
                return;
            }

            if (empty($bean->filename)) {
                $GLOBALS['log']->fatal("NextCloudUploadAlert: No file name found");
                return;
            }

            $localFilePath = "upload://{$bean->parent_alert_id}";
            if (!file_exists($localFilePath)) {
                $GLOBALS['log']->error("NextCloudUploadAlert: Local file does not exist - {$localFilePath}");
                return;
            }
            $api = new APINextCloud();

            $cusfolderPath = rtrim($this->folderPath, '/') . '/' . 'filenames';
            $part = explode('/', $cusfolderPath);
            $currentPath = '';
            foreach ($part as $folder) {
                $currentPath .= ($currentPath ? '/' : '') . $folder;
                $result = json_decode($api->createFolder($currentPath), true);
                $httpCode = $result['httpCode'] ?? null;
                $status = $result['status'] ?? null;
                if ($status != 1 && $httpCode != 201 && $httpCode != 405) {
                    $GLOBALS['log']->error("NextCloudUploadAlert: Failed to create folder {$currentPath} - " . json_encode($result));
                    return;
                }
            }

            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $bean->name);
            $remoteFileName = $cusfolderPath . '/' . 'file_' . $filename . '_' . $bean->filename;
            $uploadResult = json_decode($api->uploadFile($localFilePath, $remoteFileName), true);
            if (!isset($uploadResult['status']) || $uploadResult['status'] != 1) {
                $GLOBALS['log']->error("NextCloudUploadAlert: Failed to upload file - " . json_encode($uploadResult));
                return;
            }

            if (file_exists($localFilePath)) {
                unlink($localFilePath);
            }
        } catch (\Throwable $th) {
            $GLOBALS['log']->error("NextCloudUploadAlert: Exception - " . $th->getMessage());
        }
    }



    public function handleDelete($bean, $event, $arguments)
    {
        //performance: only parent alert trigger delete, avoid loop deletion 
        if (!empty($bean->parent_alert_id) && $bean->id != $bean->parent_alert_id) {
            return;
        }
        try {
            $api = new APINextCloud();
            $trashFolder = [
                rtrim($this->folderPath, '/') . '/trash',
                rtrim($this->folderPath, '/') . '/trash/alert_photos',
                rtrim($this->folderPath, '/') . '/trash/filenames',
            ];

            foreach ($trashFolder as $folder) {
                $parts = explode('/', $folder);
                $currentPath = '';

                foreach ($parts as $part) {
                    $currentPath .= ($currentPath ? '/' : '') . $part;
                    $result = json_decode($api->createFolder($currentPath), true);

                    $status = $result['status'] ?? null;
                    $httpCode = $result['httpCode'] ?? null;
                    if ($status != 1 && $httpCode != 201 && $httpCode != 405) {
                        $GLOBALS['log']->error("NextCloudUploadAlert Delete: Failed to create folder '{$currentPath}' - Response: " . json_encode($result));
                        return;
                    }
                }
            }
            // Move file to trash using new naming convention
            $GLOBALS['log']->fatal("NextCloudUploadAlert Delete: debug - Starting to move files to trash");
            if (!empty($bean->alert_photo) && !empty($bean->name)) {
                $GLOBALS['log']->fatal("NextCloudUploadAlert Delete: Moving files to trash for alert {$bean->name}");
                $alert_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $bean->name);
                if (!empty($bean->alert_photo)) {
                    $encodeFilename = rawurlencode($bean->alert_photo);
                    $remoteFileName = rtrim($this->folderPath, '/') . '/alert_photos' . '/' . 'alert_photo_' . $alert_name . '_' . $encodeFilename;
                    $destinationPath = rtrim($this->folderPath, '/') . '/' . 'trash/alert_photos/' . 'trash_alert_photo_' . $alert_name . '_' . $encodeFilename;
                    $GLOBALS['log']->fatal("NextCloud Moving Photo: From $remoteFileName To $destinationPath");
                    $res = json_decode($api->removeFile($remoteFileName, $destinationPath), true);
                    $GLOBALS['log']->fatal("NextCloud Result: " . print_r($res, true));
                }
                if (!empty($bean->filename)) {
                    $encodeFilename = rawurlencode($bean->filename);
                    $remoteFileName = rtrim($this->folderPath, '/') . '/filenames' . '/' . 'file_' . $alert_name . '_' . $encodeFilename;
                    $destinationPath = rtrim($this->folderPath, '/') . '/' . 'trash/filenames/' . 'trash_file_' . $alert_name . '_' . $encodeFilename;
                    $GLOBALS['log']->fatal("NextCloud Moving Photo: From $remoteFileName To $destinationPath");
                    $res = json_decode($api->removeFile($remoteFileName, $destinationPath), true);
                    $GLOBALS['log']->fatal("NextCloud Result: fn " . print_r($res, true));
                }
            }
        } catch (\Throwable $th) {
            $GLOBALS['log']->error("NextCloudUploadAlert Delete: Exception - " . $th->getMessage());
        }
    }
}
