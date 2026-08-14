<?php
require_once 'custom/entrypoints/entryClass.php';
require_once 'custom/include/helpers/api/APINextCloud.php';

/**
 * NextCloud Document Public Share Entry Point
 * 
 * This entry point provides access to NextCloud documents via public share links.
 * Instead of using authentication, it creates or retrieves existing public shares
 * and redirects/proxies the file through the public URL.
 * 
 * Usage: index.php?entryPoint=NextCloudPreview&id=<document_id>
 * Optional: &download=yes (force download instead of inline preview)
 */
class entryNextCloudPreviewClass extends entryClass
{
    private $ocsApi;
    public function __construct()
    {
        parent::__construct();
        $this->ocsApi = new APINextCloud();
    }

    public function getPublicLinkOCS($params = [])
    {
        $this->handleCors();

        try {
            // Check if it's a POST request for uploading
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $isMultipartUpload = is_string($contentType)
                && stripos($contentType, 'multipart/form-data') === 0;
            if ($_SERVER['REQUEST_METHOD'] === 'POST'
                && (array_key_exists('images', $_FILES) || $isMultipartUpload)
            ) {
                return $this->handleImageUpload();
            }

            // Step 1: Get and validate document ID from params or request
            $id = $params['id'] ?? $_REQUEST['id'] ?? '';
            $id = trim($id);
            if (!$this->validateDocumentId($id)) {
                throw new Exception('Invalid document ID');
            }

            // Step 2: Load document and revision entities
            $entities = $this->loadDocumentAndRevision($id);
            if (empty($entities)) {
                throw new Exception('Document not found');
            }
            $document = $entities['document'];
            $revision = $entities['revision'];

            // Step 3: Build remote file path
            $remoteFilePath = $this->buildRemoteFilePath($document, $revision);

            // Step 4: Get or create public share
            $shareInfo = $this->getOrCreatePublicShare($remoteFilePath, $revision);
            if (empty($shareInfo)) {
                throw new Exception('Failed to get or create public share');
            }

            // Step 5: Check debug mode
            $debugMode = $params['debug'] ?? $_REQUEST['debug'] ?? '';
            if ($debugMode == 'yes') {
                $debugInfo = $this->prepareDebugInfo(
                    $shareInfo['publicShareUrl'],
                    $shareInfo['shareToken'],
                    $remoteFilePath,
                    $document,
                    $revision
                );
                
                // Check if output mode (default for HTTP requests)
                $outputMode = $params['outputMode'] ?? true;
                if ($outputMode) {
                    return $this->outputDebugInfo($debugInfo);
                } else {
                    return $debugInfo;
                }
            }

            // Step 6: Fetch file from public URL
            $fileResult = $this->fetchFileFromPublicUrl($shareInfo['publicShareUrl']);
            if (empty($fileResult)) {
                throw new Exception('Failed to fetch file from public URL');
            }

            // Step 7: Determine MIME type
            $contentType = $this->determineMineType($fileResult['contentType'], $revision);

            // Step 8: Prepare result
            $result = [
                'success' => true,
                'fileData' => $fileResult['fileData'],
                'contentType' => $contentType,
                'filename' => $revision->filename,
                'publicShareUrl' => $shareInfo['publicShareUrl'],
                'shareToken' => $shareInfo['shareToken']
            ];

            // Check if output mode (default for HTTP requests)
            $outputMode = $params['outputMode'] ?? true;
            if ($outputMode) {
                // Output for HTTP requests
                $downloadMode = $params['download'] ?? $_REQUEST['download'] ?? '';
                return $this->outputFile($result['fileData'], $result['contentType'], $revision, $downloadMode);
            }
            
            return $result;
        } catch (Exception $e) {
            $GLOBALS['log']->error("NextCloudPreview: Exception - " . $e->getMessage());
            
            // Check if output mode (default for HTTP requests)
            $outputMode = $params['outputMode'] ?? true;
            if ($outputMode) {
                return $this->outputError('Internal server error', 500);
            } else {
                return [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    /**
     * Validate document ID
     * @param string $id
     * @return bool True if valid, false otherwise
     */
    private function validateDocumentId($id)
    {
        if (empty($id)) {
            $GLOBALS['log']->error('NextCloudPreview: Missing ID');
            return false;
        }
        return true;
    }

    /**
     * Load document and revision beans
     * @param string $id Document or Revision ID
     * @return array|null ['document' => SugarBean, 'revision' => SugarBean] or null on error
     */
    private function loadDocumentAndRevision($id)
    {
        // Try to load as DocumentRevision first (if coming from filename link)
        $revision = BeanFactory::getBean('DocumentRevisions', $id);

        if (!empty($revision->id)) {
            // This is a revision ID - get the parent document
            $document = BeanFactory::getBean('Documents', $revision->document_id);
            if (empty($document->id)) {
                $GLOBALS['log']->error("NextCloudPreview: Document not found for revision - ID: {$id}");
                return null;
            }
        } else {
            // Try to load as Document (if coming from document ID)
            $document = BeanFactory::getBean('Documents', $id);

            if (empty($document->id)) {
                $GLOBALS['log']->error("NextCloudPreview: Document not found - ID: {$id}");
                return null;
            }

            // Get revision from document
            $revision = BeanFactory::getBean('DocumentRevisions', $document->document_revision_id);

            if (empty($revision->id) || empty($revision->filename)) {
                $GLOBALS['log']->error("NextCloudPreview: Cannot retrieve document revision or filename");
                return null;
            }
        }

        return ['document' => $document, 'revision' => $revision];
    }

    /**
     * Build remote file path for NextCloud
     * @param SugarBean $document
     * @param SugarBean $revision
     * @return string Remote file path
     */
    private function buildRemoteFilePath($document, $revision)
    {
        // ALWAYS use revision filename as base to ensure consistency
        // When document is edited, document_name may change but filename stays the same
        // This ensures we always point to the correct file on NextCloud
        $revisionNumber = $revision->revision ?? '1';
        $remoteFilePath = '/bmvmb/modules/documents/' . 'v' . $revisionNumber . '_' . $revision->filename;
        
        $GLOBALS['log']->info("NextCloudPreview: Built remote path: {$remoteFilePath}");
        return $remoteFilePath;
    }

    /**
     * Get or create public share for file
     * @param string $remoteFilePath Remote file path on NextCloud
     * @param SugarBean $revision Document revision bean
     * @return array|null ['publicShareUrl' => string, 'shareToken' => string] or null on error
     */
    private function getOrCreatePublicShare($remoteFilePath, $revision)
    {
        $publicShareUrl = null;
        $shareToken = null;

        // Check if a share already exists for this path via OCS API
        $existingSharesJson = $this->ocsApi->getShares($remoteFilePath);
        $existingShares = json_decode($existingSharesJson, true);

        if ($existingShares['status'] == 1 && !empty($existingShares['data'])) {
            // Use existing share
            $shareData = $existingShares['data'][0];
            $shareToken = $shareData['token'];
            $publicShareUrl = $shareData['url'] . '/preview';
            $GLOBALS['log']->info("NextCloudPreview: Found existing share token: {$shareToken}");
        } else {
            // Create new share (read-only permission = 1)
            $createShareJson = $this->ocsApi->createShare($remoteFilePath, 1);
            $createShareResult = json_decode($createShareJson, true);

            if ($createShareResult['status'] != 1) {
                $GLOBALS['log']->error("NextCloudPreview: Failed to create share - " . json_encode($createShareResult));
                return null;
            }

            $shareData = $createShareResult['data'];
            $shareToken = $shareData['token'];
            $publicShareUrl = $shareData['url'] . '/preview';
            $GLOBALS['log']->info("NextCloudPreview: Created new share token: {$shareToken}");
        }

        if (empty($publicShareUrl)) {
            $GLOBALS['log']->error("NextCloudPreview: Could not obtain public share URL");
            return null;
        }

        return [
            'publicShareUrl' => $publicShareUrl,
            'shareToken' => $shareToken
        ];
    }

    /**
     * Fetch file from public share URL
     * @param string $publicShareUrl Public share download URL
     * @return array|null ['fileData' => string, 'contentType' => string] or null on error
     */
    private function fetchFileFromPublicUrl($publicShareUrl)
    {
        $GLOBALS['log']->info("NextCloudPreview: Fetching file from PUBLIC URL (no auth): {$publicShareUrl}");

        // Fetch file from public URL using APIOCS (no authentication needed)
        $result = $this->ocsApi->fetchPublicFile($publicShareUrl);

        if (!$result['success']) {
            $GLOBALS['log']->error("NextCloudPreview: Failed to fetch file from public URL - HTTP {$result['httpCode']} - Error: {$result['error']}");
            return null;
        }

        return [
            'fileData' => $result['data'],
            'contentType' => $result['contentType']
        ];
    }

    /**
     * Determine MIME type from content type or file extension
     * @param string $contentType Content type from server
     * @param SugarBean $revision Document revision bean
     * @return string MIME type
     */
    private function determineMineType($contentType, $revision)
    {
        // Determine MIME type based on file extension if not provided by server
        if (empty($contentType) || $contentType === 'application/octet-stream') {
            $extension = strtolower(pathinfo($revision->filename, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'webp':
                    $contentType = 'image/webp';
                    break;
                case 'pdf':
                    $contentType = 'application/pdf';
                    break;
                default:
                    // Use the mime type from revision if available
                    $contentType = !empty($revision->file_mime_type) ? $revision->file_mime_type : 'application/octet-stream';
            }
        }

        return $contentType;
    }

    /**
     * Output file to browser with appropriate headers
     * @param string $fileData File content
     * @param string $contentType MIME type
     * @param SugarBean $revision Document revision bean
     * @param string $downloadMode Download mode ('yes' to force download)
     */
    private function outputFile($fileData, $contentType, $revision, $downloadMode = '')
    {
        // Set appropriate headers and output the file
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . strlen($fileData));

        // Force download or inline preview
        if ($downloadMode == 'yes') {
            header('Content-Disposition: attachment; filename="' . $revision->filename . '"');
        } else {
            header('Content-Disposition: inline; filename="' . $revision->filename . '"');
        }

        return $fileData;
    }

    /**
     * Prepare debug information
     * @param string $publicShareUrl Public share URL
     * @param string $shareToken Share token
     * @param string $remoteFilePath Remote file path
     * @param SugarBean $document Document bean
     * @param SugarBean $revision Revision bean
     * @return array Debug information
     */
    private function prepareDebugInfo($publicShareUrl, $shareToken, $remoteFilePath, $document, $revision)
    {
        $revisionNumber = $revision->revision ?? '1';

        return [
            'mode' => 'PUBLIC_SHARE',
            'auth_required' => false,
            'public_share_url' => $publicShareUrl,
            'share_token' => $shareToken,
            'remote_file_path' => $remoteFilePath,
            'document_name' => $document->document_name,
            'revision' => $revisionNumber,
            'filename' => $revision->filename,
            'message' => 'This URL is PUBLIC and does NOT require authentication'
        ];
    }

    /**
     * Output debug information as JSON
     * @param array $debugInfo Debug information
     */
    private function outputDebugInfo($debugInfo)
    {
        header('Content-Type: application/json');
        return json_encode($debugInfo, JSON_PRETTY_PRINT);
    }

    /**
     * Output error message with HTTP status code
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    private function outputError($message, $statusCode = 500)
    {
        $statusMessages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            413 => 'Payload Too Large',
            415 => 'Unsupported Media Type',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            504 => 'Gateway Timeout',
        ];
        
        $statusText = $statusMessages[$statusCode] ?? 'Error';
        header("HTTP/1.1 {$statusCode} {$statusText}");
        header('Content-Type: application/json');
        return json_encode(['success' => false, 'error' => $message]);
    }

    /**
     * Handle multiple image uploads via POST
     */
    private function handleImageUpload()
    {
        try {
            // Load the reusable upload classes from the standalone webhook
            // without executing that webhook's HTTP receiver in this request.
            $entryUploadWebhookLibraryOnly = true;
            require_once 'custom/entrypoints/entryUploadWebhook.php';
            unset($entryUploadWebhookLibraryOnly);

            $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
            if (!array_key_exists('images', $_FILES)
                && $contentLength > NextCloudImageUploadService::MAX_BATCH_SIZE
            ) {
                return $this->outputError('Image batch exceeds 25 MiB', 413);
            }

            $service = new NextCloudImageUploadService();
            $files = $service->uploadFiles($_FILES['images'] ?? null);
            $result = [
                'success' => true,
                'urls' => array_column($files, 'url'),
            ];
            header('Content-Type: application/json');
            return json_encode($result);
        } catch (NextCloudImageUploadException $exception) {
            $GLOBALS['log']->error(
                'NextCloudPreview Upload: '
                . $exception->getErrorCode()
                . ' - '
                . $exception->getMessage()
            );
            return $this->outputError($exception->getMessage(), $exception->getHttpStatus());
        } catch (Throwable $throwable) {
            $GLOBALS['log']->error('NextCloudPreview Upload: Exception - ' . $throwable->getMessage());
            return $this->outputError('Internal server error', 500);
        }
    }


    /**
     * Handle CORS for local development/testing across different ports
     */
    private function handleCors()
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            echo json_encode(['success' => true]);
            exit;
        }
    }
}
