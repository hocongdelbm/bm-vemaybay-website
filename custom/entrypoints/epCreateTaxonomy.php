<?php
if (!defined('sugarEntry')) define('sugarEntry', true);
require_once('include/entryPoint.php');
global $current_user, $db;

header('Content-Type: application/json');

if (empty($current_user) || !is_object($current_user) || empty($current_user->id)) {
    echo json_encode(['success' => false, 'error' => 'not_authenticated']);
    exit;
}

$type = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : 'categories');
$name = isset($_POST['name']) ? trim($_POST['name']) : (isset($_GET['name']) ? trim($_GET['name']) : '');

if ($type !== 'categories' && $type !== 'tags') {
    echo json_encode(['success' => false, 'error' => 'invalid_type']);
    exit;
}
if ($name === '') {
    echo json_encode(['success' => false, 'error' => 'empty_name']);
    exit;
}

try {
    if ($type === 'categories') {
        require_once('modules/EC_Post_Categories/EC_Post_Categories.php');
        $bean = BeanFactory::newBean('EC_Post_Categories');
        $table = 'ec_post_categories';
    } else {
        require_once('modules/EC_Post_Tags/EC_Post_Tags.php');
        $bean = BeanFactory::newBean('EC_Post_Tags');
        $table = 'ec_post_tags';
    }

    // check if already exists (case-insensitive)
    $sql = "SELECT id, name FROM {$table} WHERE deleted = 0 AND LOWER(name) = " . $db->quoted(strtolower($name)) . " LIMIT 1";
    $row = $db->fetchByAssoc($db->query($sql));
    if ($row && !empty($row['id'])) {
        echo json_encode(['success' => true, 'id' => $row['id'], 'name' => $row['name'], 'existing' => true]);
        exit;
    }

    $bean->name = $name;
    if (property_exists($bean, 'slug')) {
        // simple slugify; you can replace with shared slug code later
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', trim($name)));
        $slug = trim($slug, '-');
        if ($slug === '') $slug = 'n-a';
        $bean->slug = $slug;
    }
    $bean->assigned_user_id = $current_user->id;
    $bean->save();

    echo json_encode(['success' => true, 'id' => $bean->id, 'name' => $bean->name, 'existing' => false]);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'save_failed', 'message' => $e->getMessage()]);
    exit;
}
