<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class jsLanguage
{

    /**
     * Creates javascript versions of language files
     */
    public function __construct()
    {
    }




    public static function createAppStringsCache($lang = 'en_us')
    {
        // cn: bug 8242 - non-US langpack chokes
        $app_strings = return_application_language($lang);
        $app_list_strings = return_app_list_strings_language($lang);

        $json = getJSONobj();
        $app_list_strings_encoded = $json->encode($app_list_strings);
        $app_strings_encoded = $json->encode($app_strings);

        $str = <<<EOQ
SUGAR.language.setLanguage('app_strings', $app_strings_encoded);
SUGAR.language.setLanguage('app_list_strings', $app_list_strings_encoded);
EOQ;

        $cacheDir = create_cache_directory('jsLanguage/');
        if ($fh = @sugar_fopen($cacheDir . $lang . '.js', "w")) {
            fwrite($fh, $str);
            fclose($fh);
        }
    }

    public static function createModuleStringsCache($moduleDir, $lang = 'en_us', $return = false)
    {
        $json = getJSONobj();

        // cn: bug 8242 - non-US langpack chokes
        $mod_strings = return_module_language($lang, $moduleDir);
        $mod_strings_encoded = $json->encode($mod_strings);
        $str = "SUGAR.language.setLanguage('" . $moduleDir . "', " . $mod_strings_encoded . ");";

        $cacheDir = create_cache_directory('jsLanguage/' . $moduleDir . '/');

        if ($fh = @fopen($cacheDir . $lang . '.js', 'wb')) {
            fwrite($fh, $str);
            fclose($fh);
        }

        if ($return) {
            return $str;
        }
    }
}
