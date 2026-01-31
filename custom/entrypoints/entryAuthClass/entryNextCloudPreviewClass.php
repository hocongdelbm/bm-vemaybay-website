<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'custom/entrypoints/entryClass.php';
require_once 'custom/include/helpers/api/APIOCS.php';

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
        $this->ocsApi = new APIOCS();
    }

    public function getPublicLinkOCS($params = [])
    {
        try {
            // Step 1: Get and validate document ID from params or request
            $id = $params['id'] ?? $_REQUEST['id'] ?? '';
            $id = trim($id);
            $this->validateDocumentId($id);
            
            // Step 2: Load document and revision entities
            $entities = $this->loadDocumentAndRevision($id);
            $document = $entities['document'];
            $revision = $entities['revision'];
            
            // Step 3: Build remote file path
            $remoteFilePath = $this->buildRemoteFilePath($document, $revision);
            
            // Step 4: Get or create public share
            $shareInfo = $this->getOrCreatePublicShare($remoteFilePath, $revision);
            
            // Step 5: Check debug mode
            $debugMode = $params['debug'] ?? $_REQUEST['debug'] ?? '';
            if ($debugMode == 'yes') {
                $this->outputDebugInfo(
                    $shareInfo['publicShareUrl'],
                    $shareInfo['shareToken'],
                    $remoteFilePath,
                    $document,
                    $revision
                );
                return;
            }
            
            // Step 6: Fetch file from public URL
            $fileResult = $this->fetchFileFromPublicUrl($shareInfo['publicShareUrl']);
            
            // Step 7: Determine MIME type
            $contentType = $this->determineMineType($fileResult['contentType'], $revision);
            
            // Step 8: Output file to browser
            $downloadMode = $params['download'] ?? $_REQUEST['download'] ?? '';
            $this->outputFile($fileResult['fileData'], $contentType, $revision, $downloadMode);
            
        } catch (Exception $e) {
            $GLOBALS['log']->error("NextCloudPreview: Exception - " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Internal server error';
            exit;
        }
    }

    /**
     * Validate document ID
     * @param string $id
     * @throws Exception if invalid
     */
    private function validateDocumentId($id)
    {
        if (empty($id)) {
            $GLOBALS['log']->error('NextCloudPreview: Missing ID');
            header('HTTP/1.1 400 Bad Request');
            echo 'Missing ID';
            exit;
        }
        // Valid - continue execution
    }

    /**
     * Load document and revision beans
     * @param string $id Document or Revision ID
     * @return array ['document' => SugarBean, 'revision' => SugarBean]
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
                header('HTTP/1.1 404 Not Found');
                echo 'Document not found';
                exit;
            }
        } else {
            // Try to load as Document (if coming from document ID)
            $document = BeanFactory::getBean('Documents', $id);

            if (empty($document->id)) {
                $GLOBALS['log']->error("NextCloudPreview: Document not found - ID: {$id}");
                header('HTTP/1.1 404 Not Found');
                echo 'Document not found';
                exit;
            }

            // Get revision from document
            $revision = BeanFactory::getBean('DocumentRevisions', $document->document_revision_id);

            if (empty($revision->id) || empty($revision->filename)) {
                $GLOBALS['log']->error("NextCloudPreview: Cannot retrieve document revision or filename");
                header('HTTP/1.1 404 Not Found');
                echo 'Document revision not found';
                exit;
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
        // Build the remote file path using new naming convention: {document_name}_v{revision}.{filename}
        $documentName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $document->document_name);
        $revisionNumber = $revision->revision ?? '1';
        $remoteFilePath = '/bmvmb/modules/documents/' . $documentName . '_v' . $revisionNumber . '_' . $revision->filename;

        return $remoteFilePath;
    }

    /**
     * Get or create public share for file
     * @param string $remoteFilePath Remote file path on NextCloud
     * @param SugarBean $revision Document revision bean
     * @return array ['publicShareUrl' => string, 'shareToken' => string]
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
            $publicShareUrl = $shareData['url'] . '/download';
            $GLOBALS['log']->info("NextCloudPreview: Found existing share token: {$shareToken}");
        } else {
            // Create new share (read-only permission = 1)
            $createShareJson = $this->ocsApi->createShare($remoteFilePath, 1);
            $createShareResult = json_decode($createShareJson, true);

            if ($createShareResult['status'] != 1) {
                $GLOBALS['log']->error("NextCloudPreview: Failed to create share - " . json_encode($createShareResult));
                header('HTTP/1.1 502 Bad Gateway');
                echo 'Failed to create public share for file';
                exit;
            }

            $shareData = $createShareResult['data'];
            $shareToken = $shareData['token'];
            $publicShareUrl = $shareData['url'] . '/download';
            $GLOBALS['log']->info("NextCloudPreview: Created new share token: {$shareToken}");
        }

        if (empty($publicShareUrl)) {
            $GLOBALS['log']->error("NextCloudPreview: Could not obtain public share URL");
            header('HTTP/1.1 502 Bad Gateway');
            echo 'Could not obtain public share URL';
            exit;
        }

        return [
            'publicShareUrl' => $publicShareUrl,
            'shareToken' => $shareToken
        ];
    }

    /**
     * Fetch file from public share URL
     * @param string $publicShareUrl Public share download URL
     * @return array ['fileData' => string, 'contentType' => string]
     */
    private function fetchFileFromPublicUrl($publicShareUrl)
    {
        $GLOBALS['log']->info("NextCloudPreview: Fetching file from PUBLIC URL (no auth): {$publicShareUrl}");
        
        // Fetch file from public URL using APIOCS (no authentication needed)
        $result = $this->ocsApi->fetchPublicFile($publicShareUrl);

        if (!$result['success']) {
            $GLOBALS['log']->error("NextCloudPreview: Failed to fetch file from public URL - HTTP {$result['httpCode']} - Error: {$result['error']}");
            header('HTTP/1.1 502 Bad Gateway');
            echo 'Failed to fetch file from NextCloud';
            exit;
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

        header('Cache-Control: public, max-age=3600');
        header('Pragma: cache');

        echo $fileData;
        exit;
    }
             
    /**
     * Output debug information as JSON
     * @param string $publicShareUrl Public share URL
     * @param string $shareToken Share token
     * @param string $remoteFilePath Remote file path
     * @param SugarBean $document Document bean
     * @param SugarBean $revision Revision bean
     */
    private function outputDebugInfo($publicShareUrl, $shareToken, $remoteFilePath, $document, $revision)
    {
        $revisionNumber = $revision->revision ?? '1';
        
        header('Content-Type: application/json');
        echo json_encode([
            'mode' => 'PUBLIC_SHARE',
            'auth_required' => false,
            'public_share_url' => $publicShareUrl,
            'share_token' => $shareToken,
            'remote_file_path' => $remoteFilePath,
            'document_name' => $document->document_name,
            'revision' => $revisionNumber,
            'filename' => $revision->filename,
            'message' => 'This URL is PUBLIC and does NOT require authentication'
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
