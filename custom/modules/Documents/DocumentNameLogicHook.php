<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'custom/modules/Documents/DocumentNameService.php';

/**
 * Logic hook xử lý việc đặt tên tự động cho Document
 */
class DocumentNameLogicHook
{
    /**
     * Hàm bắt sự kiện before_save
     * @param SugarBean $bean Đối tượng đang được lưu
     * @param string $event Tên sự kiện (before_save)
     * @param array $arguments Các tham số khác
     */
    public function handleDocumentName($bean, $event, $arguments)
    {
        try {
            $service = new DocumentNameService();
            $newName = $service->generateDocumentName($bean);
            
            if (!empty($newName)) {
                $bean->document_name = $newName;
                $GLOBALS['log']->info("DocumentNameLogicHook: Auto-generated document name to '{$newName}' for Document ID: {$bean->id}");
            }
        } catch (Exception $e) {
            $GLOBALS['log']->error("DocumentNameLogicHook: Exception - " . $e->getMessage());
        }
    }
}
