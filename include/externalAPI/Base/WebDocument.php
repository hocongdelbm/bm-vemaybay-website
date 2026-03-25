<?php



/**
 * External API to document storage
 * @api
 */
interface WebDocument
{
    public function uploadDoc($bean, $fileToUpload, $docName, $mineType);
    public function downloadDoc($documentId, $documentFormat);
    public function shareDoc($documentId, $emails);
    public function deleteDoc($documentId);
    public function searchDoc($keywords, $flushDocCache = false);
}
