<?php
require_once 'custom/entrypoints/entryClass.php';
require_once 'custom/include/helpers/api/APIZaloOA.php';

/**
 * Class entryZaloPostClass
 * 
 * Đăng bài Zalo
 */
class entryZaloPostClass extends entryClass {
    
    /**
     * Create Zalo Article (Auto Post)
     * 
     * @param array $params
     * @return array
     */
    public function createArticle($params = []) {
        return $this->executeZaloAction($params, function($api, $params) {
            if(empty($params['type']) || !in_array($params['type'], ['normal', 'video'])) {
                return $this->formatResponse(0, "Tham số type không hợp lệ (cần normal hoặc video)");
            }
            
            if(empty($params['title'])) {
                return $this->formatResponse(0, "Thiếu tiêu đề bài viết (title)");
            }

            return $api->create_article($params);
        }, "Gửi yêu cầu tạo bài viết thành công");
    }

    /**
     * Upload Video Article
     * 
     * @param array $params
     * @return array
     */
    public function uploadVideoArticle($params = []) {
        return $this->executeZaloAction($params, function($api, $params) {
            $file_path = $params['file_path'] ?? '';
            
            if (empty($file_path) || !file_exists($file_path)) {
                return $this->formatResponse(0, "Thiếu đường dẫn file video (file_path) hoặc file không tồn tại.");
            }

            $file_size = filesize($file_path);
            if ($file_size > 50 * 1024 * 1024) { // 50MB
                return $this->formatResponse(0, "Kích thước video vượt quá 50MB.");
            }

            $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            if (!in_array($extension, ['avi', 'mp4'])) {
                return $this->formatResponse(0, "Định dạng file không hợp lệ (chỉ hỗ trợ avi, mp4).");
            }

            return $api->upload_video_article($file_path);
        }, "Tải video lên thành công.");
    }

    /**
     * Verify Video Article Status
     * 
     * @param array $params
     * @return array
     */
    public function verifyVideoArticleStatus($params = []) {
        return $this->executeZaloAction($params, function($api, $params) {
            $token = $params['token'] ?? '';
            
            if (empty($token)) {
                return $this->formatResponse(0, "Thiếu tham số token của video.");
            }

            return $api->verify_video_article($token);
        }, "Kiểm tra trạng thái video thành công.");
    }

    /**
     * Helper to execute Zalo API calls with standard error handling
     * 
     * @param array $params
     * @param callable $action
     * @param string $successMessage
     * @return array
     */
    private function executeZaloAction($params, callable $action, $successMessage) {
        try {
            $app_id = $params['app_id'] ?? '';
            $oa_id = $params['oa_id'] ?? '';
            $api = new APIZaloOA($app_id, $oa_id);
            
            if (empty($api->app) || empty($api->app->id)) {
                return $this->formatResponse(0, "Không tìm thấy cấu hình Zalo App. Vui lòng truyền tham số app_id hợp lệ.");
            }

            unset($params['app_id'], $params['oa_id']);

            // Execute the specific action
            $response = $action($api, $params);

            // Handle early returns for validation errors
            if (is_array($response)) {
                return $response;
            }

            // Handle API response
            $responseArr = json_decode($response, true);
            
            if (isset($responseArr['error']) && $responseArr['error'] == 0) {
                return $this->formatResponse(1, $successMessage, $responseArr['data'] ?? null);
            }
            
            return $this->formatResponse(0, $responseArr['message'] ?? $api->get_error_description($responseArr['error'] ?? -200), $responseArr);
        }
        catch(Throwable $th) {
            $logId = LoggerHelper::generateLogId();
            $GLOBALS['log']->fatal("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return $this->formatResponse(0, "Exception error $logId", null, ['errorId' => $logId]);
        }
    }

    /**
     * Standard response formatter
     * 
     * @param int $status
     * @param string $message
     * @param mixed $data
     * @param array $extra
     * @return array
     */
    private function formatResponse($status, $message, $data = null, $extra = []) {
        $response = [
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];
        return array_merge($response, $extra);
    }
}