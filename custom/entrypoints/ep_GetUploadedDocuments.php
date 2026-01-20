<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

header('Content-Type: application/json');

$booking_id = $_POST['booking_id'] ?? '';

if (empty($booking_id)) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing booking_id'
    ]);
    exit;
}

global $db, $app_list_strings;

$query = "
    SELECT 
        d.id,
        d.document_name,
        d.date_entered,
        d.document_revision_id as revision_id,
        d.category_id,
        u.user_name as created_by,
        d.preview_image
    FROM documents d
    LEFT JOIN users u ON d.created_by = u.id
    WHERE d.booking_id = '" . $db->quote($booking_id) . "'
    AND d.deleted = 0
    ORDER BY d.date_entered DESC
";

$result = $db->query($query);
$documents = [];

while ($row = $db->fetchByAssoc($result)) {
    $category = 'Khác';
    if (!empty($row['category_id']) && isset($app_list_strings['document_category_dom'][$row['category_id']])) {
        $category = $app_list_strings['document_category_dom'][$row['category_id']];
    }
    
    // Tạo URL cho preview image
    $preview_url = "Không phải file ảnh";
    if (!empty($row['preview_image'])) {
        $preview_url = "index.php?entryPoint=download&id=" . $row['id'] . "_preview_image&type=Documents";
    }
    
    $documents[] = [
        'id' => $row['id'],
        'document_name' => $row['document_name'],
        'category' => $category,
        'date_entered' => date('d/m/Y H:i', strtotime($row['date_entered'])),
        'created_by_name' => $row['created_by'] ?: 'N/A',
        'preview_image' => $preview_url,
        'revision_id' => $row['revision_id']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $documents,
    'total' => count($documents)
]);
exit;
