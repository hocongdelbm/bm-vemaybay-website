<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/utils/php_zip_utils.php');
require_once('include/upload_file.php');

/**
 * (re)write the .htaccess file to prevent browser access to the log file
 */
function handleHtaccess()
{
    global $mod_strings;
    global $sugar_config;
    $ignoreCase = '';
    if (!empty($_SERVER['SERVER_SOFTWARE']) && (substr_count(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache/2') > 0)) {
        $ignoreCase = '(?i)';
    }
    $htaccess_file = '.htaccess';
    $contents = '';
    $basePath = parse_url((string) $sugar_config['site_url'], PHP_URL_PATH);
    if (empty($basePath)) {
        $basePath = '/';
    }
    $cacheDir = $sugar_config['cache_dir'];

    $restrict_str = <<<EOQ
# BEGIN SUITECRM RESTRICTIONS

EOQ;
    if (ini_get('suhosin.perdir') !== false && strpos(ini_get('suhosin.perdir'), 'e') !== false) {
        $restrict_str .= "php_value suhosin.executor.include.whitelist upload\n";
    }
    $restrict_str .= <<<EOQ
RedirectMatch 403 {$ignoreCase}.*\.log$
RedirectMatch 403 {$ignoreCase}/+not_imported_.*\.txt
RedirectMatch 403 {$ignoreCase}/+(soap|cache|xtemplate|data|examples|include|log4php|metadata|modules|vendor)/+.*\.(php|tpl|phar)
RedirectMatch 403 {$ignoreCase}/+emailmandelivery\.php
RedirectMatch 403 {$ignoreCase}/+.git
RedirectMatch 403 {$ignoreCase}/+.{$cacheDir}
RedirectMatch 403 {$ignoreCase}/+tests
RedirectMatch 403 {$ignoreCase}/+composer\.json
RedirectMatch 403 {$ignoreCase}/+composer\.lock
RedirectMatch 403 {$ignoreCase}/+upload
RedirectMatch 403 {$ignoreCase}/+custom/+blowfish
RedirectMatch 403 {$ignoreCase}/+cache/+diagnostic
RedirectMatch 403 {$ignoreCase}/+files\.md5\$

EOQ;

    $cache_headers = <<<EOQ

<IfModule mod_rewrite.c>
    Options +SymLinksIfOwnerMatch
    Options -Indexes
    Options -MultiViews
    RewriteEngine On
    RewriteBase {$basePath}
    RewriteRule ^cache/jsLanguage/(.._..).js$ index.php?entryPoint=jslang&modulename=app_strings&lang=$1 [L,QSA]
    RewriteRule ^cache/jsLanguage/(\w*)/(.._..).js$ index.php?entryPoint=jslang&modulename=$1&lang=$2 [L,QSA]
    
    RewriteRule ^ep/(.*?)$ index.php?entryPoint=$1 [L,QSA]

    # --------- DEPRECATED --------
    RewriteRule ^api/(.*)$ - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteRule ^api/(.*?)$ lib/API/public/index.php/$1 [L]
    # -----------------------------

    RewriteRule ^Api/(.*)$ - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteRule ^Api/access_token$ Api/index.php [L]
    RewriteRule ^Api/V8/(.*?)$ Api/index.php [L]
</IfModule>
<IfModule mod_headers.c>
    Header unset ETag
    FileETag None
</IfModule>
<IfModule mod_headers.c>
    Header unset X-Powered-By
    Header always unset X-Powered-By
</IfModule>
<IfModule mod_expires.c>
 ExpiresActive on
 ExpiresDefault "access plus 1 month"

 # CSS
 ExpiresByType text/css "access plus 1 year"

 # Data
 ExpiresByType application/atom+xml "access plus 1 hour"
 ExpiresByType application/rdf+xml "access plus 1 hour"
 ExpiresByType application/rss+xml "access plus 1 hour"
 ExpiresByType application/json "access plus 0 seconds"
 ExpiresByType application/ld+json "access plus 0 seconds"
 ExpiresByType application/schema+json "access plus 0 seconds"
 ExpiresByType application/geo+json "access plus 0 seconds"
 ExpiresByType application/xml "access plus 0 seconds"
 ExpiresByType text/calendar "access plus 0 seconds"
 ExpiresByType text/xml "access plus 0 seconds"

 # Favicon
 ExpiresByType image/x-icon "access plus 1 week"

 # HTML
 ExpiresByType text/html "access plus 0 seconds"

 # JavaScript
 ExpiresByType application/javascript "access plus 1 year"
 ExpiresByType application/x-javascript "access plus 1 year"
 ExpiresByType text/javascript "access plus 1 year"

 # Markdown
 ExpiresByType text/markdown "access plus 0 seconds"

 # Media files
 ExpiresByType audio/ogg "access plus 1 month"
 ExpiresByType image/bmp "access plus 1 month"
 ExpiresByType image/gif "access plus 1 month"
 ExpiresByType image/jpeg "access plus 1 month"
 ExpiresByType image/jpg "access plus 1 month"
 ExpiresByType image/png "access plus 1 month"
 ExpiresByType image/svg+xml "access plus 1 month"
 ExpiresByType image/webp "access plus 1 month"
 ExpiresByType video/mp4 "access plus 1 month"
 ExpiresByType video/ogg "access plus 1 month"
 ExpiresByType video/webm "access plus 1 month"

 # Fonts
 ExpiresByType font/eot "access plus 1 month"
 ExpiresByType font/opentype "access plus 1 month"
 ExpiresByType font/otf "access plus 1 month"
 ExpiresByType application/x-font-ttf "access plus 1 month"
 ExpiresByType font/ttf "access plus 1 month"
 ExpiresByType application/font-woff "access plus 1 month"
 ExpiresByType application/x-font-woff "access plus 1 month"
 ExpiresByType font/woff "access plus 1 month"
 ExpiresByType application/font-woff2 "access plus 1 month"
 ExpiresByType font/woff2 "access plus 1 month"

 # Other
 ExpiresByType text/x-cross-domain-policy "access plus 1 week"
</IfModule>
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>
<IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_URI} (.+)/$
        RewriteRule ^ %1 [R=301,L]
</IfModule>
# END SUITECRM RESTRICTIONS
EOQ;

    // add custom content from current '.htaccess' before "# BEGIN SUITECRM RESTRICTIONS"
    $haveBegin = false;
    if (file_exists($htaccess_file)) {
        $fp = fopen($htaccess_file, 'rb');
        while ($line = fgets($fp)) {
            if (preg_match(
                "/\s*#\s*BEGIN\s*SUITECRM\s*RESTRICTIONS/i",
                $line
            ) || preg_match("/\s*#\s*BEGIN\s*SUGARCRM\s*RESTRICTIONS/i", $line)) {
                $haveBegin = true;
                break;
            }
            $contents .= $line;
        }
        fclose($fp);
    }
    // add default content
    $contents .= $restrict_str . $cache_headers;
    // add custom content from current '.htaccess' after "# END SUITECRM RESTRICTIONS"
    if ($haveBegin && file_exists($htaccess_file)) {
        $skip = true;
        $fp = fopen($htaccess_file, 'rb');
        while ($line = fgets($fp)) {
            if (preg_match(
                "/\s*#\s*END\s*SUITECRM\s*RESTRICTIONS/i",
                $line
            ) || preg_match("/\s*#\s*END\s*SUGARCRM\s*RESTRICTIONS/i", $line)) {
                $skip = false;
                $contents .= PHP_EOL;
                continue;
            }
            if (!$skip) {
                $contents .= $line;
            }
        }
        fclose($fp);
    }
    $status = file_put_contents($htaccess_file, $contents);
    if (!$status) {
        echo "<p>{$mod_strings['ERR_PERFORM_HTACCESS_1']}<span class=stop>{$htaccess_file}</span> {$mod_strings['ERR_PERFORM_HTACCESS_2']}</p>\n";
        echo "<p>{$mod_strings['ERR_PERFORM_HTACCESS_3']}</p>\n";
        echo $restrict_str;
    }

    return $status;
}

if (!function_exists('extractFile')) {
    function extractFile($zip_file, $file_in_zip, $base_tmp_upgrade_dir)
    {
        $my_zip_dir = mk_temp_dir($base_tmp_upgrade_dir);
        unzip_file($zip_file, $file_in_zip, $my_zip_dir);
        return ("$my_zip_dir/$file_in_zip");
    }
}

if (!function_exists('extractManifest')) {
    function extractManifest($zip_file, $base_tmp_upgrade_dir)
    {
        return (extractFile($zip_file, "manifest.php", $base_tmp_upgrade_dir));
    }
}

if (!function_exists('validate_manifest')) {
    function validate_manifest($manifest)
    {
        // takes a manifest.php manifest array and validates contents
        global $mod_strings;

        if (!isset($manifest['type'])) {
            die($mod_strings['ERROR_MANIFEST_TYPE']);
        }
        $type = $manifest['type'];
        if (getInstallType("/$type/") == "") {
            die($mod_strings['ERROR_PACKAGE_TYPE'] . ": '" . $type . "'.");
        }

        return true; // making this a bit more relaxed since we updated the language extraction and merge capabilities
    }
}

if (!function_exists('getInstallType')) {
    function getInstallType($type_string)
    {
        // detect file type
        $subdirs = array('full', 'langpack', 'module', 'patch', 'theme', 'temp');
        foreach ($subdirs as $subdir) {
            if (preg_match("#/$subdir/#", (string) $type_string)) {
                return ($subdir);
            }
        }
        // return empty if no match
        return ("");
    }
}

function create_date($year = null, $mnth = null, $day = null)
{
    global $timedate;
    $now = $timedate->getNow();
    if ($day == null) {
        $day = $now->day + mt_rand(0, 365);
    }
    return $timedate->asDbDate($now->get_day_begin($day, $mnth, $year));
}

function create_time($hr = null, $min = null, $sec = null)
{
    global $timedate;
    $date = TimeDate::getInstance()->fromTimestamp(0);
    if ($hr == null) {
        $hr = mt_rand(6, 19);
    }
    if ($min == null) {
        $min = (mt_rand(0, 3) * 15);
    }
    if ($sec == null) {
        $sec = 0;
    }
    return $timedate->asDbTime($date->setDate(2007, 10, 7)->setTime($hr, $min, $sec));
}
