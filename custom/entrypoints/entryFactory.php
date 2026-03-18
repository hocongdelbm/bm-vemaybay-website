<?php
try {
    foreach(['custom/entrypoints/entryNonAuthClass/', 'custom/entrypoints/entryAuthClass/'] as $entry_class_dir) {
        foreach (glob("$entry_class_dir*.php") as $filename) {
            if(preg_match('/^entry.+Class\.php$/', str_replace($entry_class_dir, '', $filename))) require_once $filename;
        }
    }
}
catch (Throwable $th) {
    $msg = "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}";
    
    if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
        $GLOBALS['log']->fatal($msg);
    } else {
        error_log($msg);
    }
}

class entryFactory {
    public static function create(string $class_name) {
        if(class_exists($class_name)) $obj = new $class_name();
        return $obj ?? null;
    }
}
