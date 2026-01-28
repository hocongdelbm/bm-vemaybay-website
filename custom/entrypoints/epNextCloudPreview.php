<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * NextCloud Document Proxy Entry Point
 * 
 * This entry point acts as a proxy to fetch documents from NextCloud
 * using stored credentials. This solves the authentication issue where browsers
 * cannot send Basic Auth credentials when loading files via <img> tags.
 * 
 * Usage: index.php?entryPoint=NextCloudPreview&id=<document_id>
 */

require_once('custom/include/helpers/api/APINextCloud.php');

// Get document or revision ID from request
$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';

if (empty($id)) {
    $GLOBALS['log']->error('NextCloudPreview: Missing ID');
    header('HTTP/1.1 400 Bad Request');
    echo 'Missing ID';
    exit;
}

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

// Build the remote file path using new naming convention: {document_name}_v{revision}.{filename}
$documentName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $document->document_name);
$revisionNumber = $revision->revision ?? '1';
$remoteFileName = 'bmvmb/modules/documents/' . $documentName . '_v' . $revisionNumber . '_' . $revision->filename;

try {
    global $sugar_config;

    // Get NextCloud credentials from config
    $username = $sugar_config['next-cloud']['user'];
    $password = $sugar_config['next-cloud']['password'];
    $endpoint = rtrim($sugar_config['next-cloud']['endpoint'], '/') . '/' . $username;

    // Build the full URL to the file
    $fileUrl = $endpoint . '/' . rawurlencode($remoteFileName);

    $GLOBALS['log']->info("NextCloudPreview: Fetching file from: {$fileUrl}");

    // Use cURL to fetch the file with authentication
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($username . ':' . $password)
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $fileData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($fileData === false || $httpCode != 200) {
        $GLOBALS['log']->error("NextCloudPreview: Failed to fetch file - HTTP {$httpCode} - Error: {$error}");
        header('HTTP/1.1 502 Bad Gateway');
        echo 'Failed to fetch file from NextCloud';
        exit;
    }

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

    $GLOBALS['log']->info("NextCloudPreview: Successfully fetched file - Size: " . strlen($fileData) . " bytes, Type: {$contentType}");

    // Set appropriate headers and output the file
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . strlen($fileData));

    // Thêm header để force download thay vì preview trong browser
    if (isset($_REQUEST['download']) && $_REQUEST['download'] == 'yes') {
        header('Content-Disposition: attachment; filename="' . $revision->filename . '"');
    } else {
        header('Content-Disposition: inline; filename="' . $revision->filename . '"');
    }

    header('Cache-Control: public, max-age=3600');
    header('Pragma: cache');

    echo $fileData;
    exit;
} catch (Exception $e) {
    $GLOBALS['log']->error("NextCloudPreview: Exception - " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Internal server error';
    exit;
}
