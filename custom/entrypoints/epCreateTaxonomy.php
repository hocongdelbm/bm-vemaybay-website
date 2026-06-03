<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $current_user, $db;

header('Content-Type: application/json; charset=utf-8');

// ── Auth check ────────────────────────────────────────────────────────────────
if (empty($current_user) || !is_object($current_user) || empty($current_user->id)) {
    _epLog('not_authenticated');
    echo json_encode(['success' => false, 'error' => 'not_authenticated']);
    exit;
}

// ── Input validation ──────────────────────────────────────────────────────────
$type = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : '');
$name = isset($_POST['name']) ? trim($_POST['name']) : (isset($_GET['name']) ? trim($_GET['name']) : '');

if ($type !== 'categories' && $type !== 'tags') {
    echo json_encode(['success' => false, 'error' => 'invalid_type']);
    exit;
}

if ($name === '') {
    _epLog('empty_name');
    echo json_encode(['success' => false, 'error' => 'empty_name']);
    exit;
}

// ── Module map ────────────────────────────────────────────────────────────────
$moduleMap = [
    'categories' => [
        'module' => 'EC_Post_Categories',
        'file'   => 'modules/EC_Post_Categories/EC_Post_Categories.php',
        'table'  => 'ec_post_categories',
    ],
    'tags' => [
        'module' => 'EC_Post_Tags',
        'file'   => 'modules/EC_Post_Tags/EC_Post_Tags.php',
        'table'  => 'ec_post_tags',
    ],
];
$cfg = $moduleMap[$type];

// ── Save ──────────────────────────────────────────────────────────────────────
try {
    // SugarCRM root (entry point files live at custom/modules/.../entrypoints/)
    $sugarRoot = rtrim(realpath(dirname(__FILE__) . '/../../../..'), '/');

    $moduleFile = $sugarRoot . '/' . $cfg['file'];
    if (file_exists($moduleFile)) {
        require_once($moduleFile);
    }

    $bean  = BeanFactory::newBean($cfg['module']);
    $table = $cfg['table'];

    // Duplicate check (case-insensitive)
    $sql = "SELECT id, name FROM {$table} WHERE deleted = 0 AND LOWER(name) = "
         . $db->quoted(strtolower($name)) . " LIMIT 1";
    $row = $db->fetchByAssoc($db->query($sql));

    if ($row && !empty($row['id'])) {
        _epLog("exists type={$type} name={$name} id={$row['id']}");
        echo json_encode(['success' => true, 'id' => $row['id'], 'name' => $row['name'], 'existing' => true]);
        exit;
    }

    $bean->name = $name;

    if (property_exists($bean, 'slug')) {
        $slug = trim(preg_replace('/[^A-Za-z0-9]+/', '-', strtolower($name)), '-');
        $bean->slug = $slug !== '' ? $slug : 'n-a';
    }

    $bean->assigned_user_id = $current_user->id;
    $bean->save();

    _epLog("created type={$type} name={$name} id={$bean->id}");
    echo json_encode(['success' => true, 'id' => $bean->id, 'name' => $bean->name, 'existing' => false]);
    exit;

} catch (Exception $e) {
    _epLog('exception: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'save_failed', 'message' => $e->getMessage()]);
    exit;
}

// ── Helper ────────────────────────────────────────────────────────────────────
function _epLog($msg) {
    $line = date('[Y-m-d H:i:s] ') . $msg . "\n";
    @file_put_contents('/tmp/epCreateTaxonomy.log', $line, FILE_APPEND);
    if (!empty($GLOBALS['log'])) $GLOBALS['log']->info('epCreateTaxonomy: ' . $msg);
}