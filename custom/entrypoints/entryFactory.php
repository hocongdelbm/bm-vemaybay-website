<?php
try {
    $entry_class_dir = (isset($entryAuth) && $entryAuth === false) ? 'custom/entrypoints/entryNonAuthClass/' : 'custom/entrypoints/entryAuthClass/';
    // require_once $entry_class_dir . "entryClass.php";
    foreach (glob("$entry_class_dir*.php") as $filename) {
        if(preg_match('/^entry.+Class\.php$/', str_replace($entry_class_dir, '', $filename))) require_once $filename;
    }
}
catch (Throwable $th) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Throwable: {$th->getMessage()} on line {$th->getLine()} at {$th->getFile()}"
    ]);
    exit();
}

class entryFactory {
    public static function create(string $class_name) {
        if(class_exists($class_name)) $obj = new $class_name();
        return $obj ?? null;
    }
}
