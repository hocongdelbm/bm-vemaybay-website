<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/Administration/UpgradeWizardCommon.php');
require_once('ModuleInstall/PackageManager/PackageManagerDisplay.php');
require_once('ModuleInstall/ModuleScanner.php');
global $mod_strings;
$uh = new UpgradeHistory();

function unlinkTempFiles()
{
    global $sugar_config;
    @unlink($_FILES['upgrade_zip']['tmp_name']);
    @unlink("upload://" . $_FILES['upgrade_zip']['name']);
}

$base_upgrade_dir       = "upload://upgrades";
$base_tmp_upgrade_dir   = sugar_cached('upgrades/temp');

// make sure dirs exist
foreach ($GLOBALS['subdirs'] as $subdir) {
    if (!file_exists("$base_upgrade_dir/$subdir")) {
        sugar_mkdir("$base_upgrade_dir/$subdir", 0770, true);
    }
}

// get labels and text that are specific to either Module Loader or Upgrade Wizard
if ($view == "module") {
    $uploaddLabel = $mod_strings['LBL_UW_UPLOAD_MODULE'];
    $descItemsQueued = $mod_strings['LBL_UW_DESC_MODULES_QUEUED'];
    $descItemsInstalled = $mod_strings['LBL_UW_DESC_MODULES_INSTALLED'];
} else {
    $uploaddLabel = $mod_strings['LBL_UPLOAD_UPGRADE'];
    $descItemsQueued = $mod_strings['DESC_FILES_QUEUED'];
    $descItemsInstalled = $mod_strings['DESC_FILES_INSTALLED'];
}

//
// check that the upload limit is set to 6M or greater
//

define('SUGARCRM_MIN_UPLOAD_MAX_FILESIZE_BYTES', 6 * 1024 * 1024);  // 6 Megabytes

$upload_max_filesize = ini_get('upload_max_filesize');
$upload_max_filesize_bytes = return_bytes($upload_max_filesize);
if ($upload_max_filesize_bytes < constant('SUGARCRM_MIN_UPLOAD_MAX_FILESIZE_BYTES')) {
    $GLOBALS['log']->debug("detected upload_max_filesize: $upload_max_filesize");
    print('<p class="error">' . $mod_strings['MSG_INCREASE_UPLOAD_MAX_FILESIZE'] . ' '
        . get_cfg_var('cfg_file_path') . "</p>\n");
}

//
// process "run" commands
//

if (isset($_REQUEST['run']) && ($_REQUEST['run'] != "")) {
    $run = $_REQUEST['run'];

    if ($run === 'upload') {
        $perform = false;
        if (isset($_REQUEST['release_id']) && $_REQUEST['release_id'] != "") {
            require_once('ModuleInstall/PackageManager.php');
            $pm = new PackageManager();
            $tempFile = $pm->download('', '', $_REQUEST['release_id']);
            $perform = true;
            $base_filename = urldecode($tempFile);
        } elseif (!empty($_REQUEST['load_module_from_dir'])) {
            $moduleDir = $_REQUEST['load_module_from_dir'] ?? '';
            if (stripos($moduleDir, 'phar://') !== false) {
                LoggerManager::getLogger()->fatal("UpgradeWizard - invalid load_module_from_dir: " . $moduleDir);
                throw new RuntimeException('Invalid request');
            }

            if (strtolower(pathinfo(urldecode($_REQUEST['upgrade_zip_escaped'] ?? ''), PATHINFO_EXTENSION)) !== 'zip') {
                LoggerManager::getLogger()->fatal("UpgradeWizard - invalid upgrade_zip_escaped: " . $_REQUEST['upgrade_zip_escaped'] ?? '');
                throw new RuntimeException("Invalid request");
            }
            //copy file to proper location then call performSetup
            copy($moduleDir . '/' . $_REQUEST['upgrade_zip_escaped'], "upload://" . $_REQUEST['upgrade_zip_escaped']);

            $perform = true;
            $base_filename = urldecode($_REQUEST['upgrade_zip_escaped']);
        } else {
            if (empty($_FILES['upgrade_zip']['tmp_name'])) {
                echo $mod_strings['ERR_UW_NO_UPLOAD_FILE'];
            } else {
                $upload = new UploadFile('upgrade_zip');
                if (
                    !$upload->confirm_upload() ||
                    strtolower(pathinfo($upload->get_stored_file_name(), PATHINFO_EXTENSION)) != 'zip' ||
                    !$upload->final_move($upload->get_stored_file_name())
                ) {
                    unlinkTempFiles();
                    sugar_die("Invalid Package");
                } else {
                    $tempFile = "upload://" . $upload->get_stored_file_name();
                    $perform = true;
                    $base_filename = urldecode($_REQUEST['upgrade_zip_escaped']);
                }
            }
        }
        if ($perform) {
            $manifest_file = extractManifest($tempFile);
            if (is_file($manifest_file)) {
                //SCAN THE MANIFEST FILE TO MAKE SURE NO COPIES OR ANYTHING ARE HAPPENING IN IT
                $ms = new ModuleScanner();
                $ms->lockConfig();
                $fileIssues = $ms->scanFile($manifest_file);
                if (!empty($fileIssues)) {
                    echo '<h2>' . $mod_strings['ML_MANIFEST_ISSUE'] . '</h2><br>';
                    $ms->displayIssues();
                    die();
                }
                list($manifest, $installdefs) = MSLoadManifest($manifest_file);
                if ($ms->checkConfig($manifest_file)) {
                    echo '<h2>' . $mod_strings['ML_MANIFEST_ISSUE'] . '</h2><br>';
                    $ms->displayIssues();
                    die();
                }
                validate_manifest($manifest);

                $upgrade_zip_type = $manifest['type'];

                // exclude the bad permutations
                if ($view == "module") {
                    if ($upgrade_zip_type != "module" && $upgrade_zip_type != "theme" && $upgrade_zip_type != "langpack") {
                        unlinkTempFiles();
                        die($mod_strings['ERR_UW_NOT_ACCEPTIBLE_TYPE']);
                    }
                } elseif ($view == "default") {
                    if ($upgrade_zip_type != "patch") {
                        unlinkTempFiles();
                        die($mod_strings['ERR_UW_ONLY_PATCHES']);
                    }
                }

                $base_filename = pathinfo($tempFile, PATHINFO_BASENAME);

                mkdir_recursive("$base_upgrade_dir/$upgrade_zip_type");
                $target_path = "$base_upgrade_dir/$upgrade_zip_type/$base_filename";
                $target_manifest = remove_file_extension($target_path) . "-manifest.php";

                if (isset($manifest['icon']) && $manifest['icon'] != "") {
                    $icon_location = extractFile($tempFile, $manifest['icon']);
                    copy($icon_location, remove_file_extension($target_path) . "-icon." . pathinfo($icon_location, PATHINFO_EXTENSION));
                }

                if (rename($tempFile, $target_path)) {
                    copy($manifest_file, $target_manifest);
                    $GLOBALS['ML_STATUS_MESSAGE'] = $base_filename . $mod_strings['LBL_UW_UPLOAD_SUCCESS'];
                } else {
                    $GLOBALS['ML_STATUS_MESSAGE'] = $mod_strings['ERR_UW_UPLOAD_ERROR'];
                }
            } else {
                unlinkTempFiles();
                die($mod_strings['ERR_UW_NO_MANIFEST']);
            }
        }
    } else {
        if ($run == $mod_strings['LBL_UW_BTN_DELETE_PACKAGE']) {
            if (!empty($_REQUEST['install_file'])) {
                die($mod_strings['ERR_UW_NO_UPLOAD_FILE']);
            }

            $delete_me = hashToFile($delete_me);

            $checkFile = strtolower($delete_me);

            if (
                substr($delete_me, -4) != ".zip" || substr($delete_me, 0, 9) != "upload://" ||
                strpos($checkFile, "..") !== false || !file_exists($checkFile)
            ) {
                die("<span class='error'>File is not a zipped archive.</span>");
            }
            if (unlink($delete_me)) { // successful deletion?
                echo "Package $delete_me has been removed.<br>";
            } else {
                die("Problem removing package $delete_me.");
            }
        }
    }
}

if ($view == "module") {
    print(getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_MODULE_LOADER_TITLE']), false));
} else {
    print(getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_UPGRADE_WIZARD_TITLE']), false));
}

// upload link
if (!empty($GLOBALS['sugar_config']['use_common_ml_dir']) && $GLOBALS['sugar_config']['use_common_ml_dir'] && !empty($GLOBALS['sugar_config']['common_ml_dir'])) {
    //rrs
    $form = '<form name="move_form" action="index.php?module=Administration&view=module&action=UpgradeWizard" method="post"  ><input type=hidden name="run" value="upload" /><input type=hidden name="load_module_from_dir" id="load_module_from_dir" value="' . $GLOBALS['sugar_config']['common_ml_dir'] . '" /><input type=hidden name="upgrade_zip_escaped" value="" />';
    $form .= '<br>' . $mod_strings['LBL_MODULE_UPLOAD_DISABLE_HELP_TEXT'] . '</br>';
    $form .= '<table class="edit view"><tr><th align="left">' . $mod_strings['LBL_ML_NAME'] . '</th><th align="left">' . $mod_strings['LBL_ML_ACTION'] . '</th></tr>';
    if ($handle = opendir($GLOBALS['sugar_config']['common_ml_dir'])) {
        while (false !== ($filename = readdir($handle))) {
            if ($filename == '.' || $filename == '..' || !preg_match("#.*\.zip\$#", $filename)) {
                continue;
            }
            $form .= '<tr><td>' . $filename . '</td><td><input type=button class="button" value="' . $mod_strings['LBL_UW_BTN_UPLOAD'] . '" onClick="document.move_form.upgrade_zip_escaped.value = escape( \'' . $filename . '\');document.move_form.submit();" /></td></tr>';
        }
    }
    $form .= '</table></form>';
    //rrs
} else {
    $form = <<<eoq
        <div class="box-section">
        <form name="the_form" enctype="multipart/form-data" action="{$form_action}" method="post">
        <table border="0" cellspacing="0" cellpadding="0" class="edit view">
        <tr><td>
        <table width="450" border="0" cellspacing="0" cellpadding="0">
        <tr><td>
        <div class="d-flex gap-2 align-items-center">
        {$uploaddLabel}
        <input type="file" name="upgrade_zip" size="40" />
        <input type="button" class="btn btn-primary" value="{$mod_strings['LBL_UW_BTN_UPLOAD']}" onClick="document.the_form.upgrade_zip_escaped.value = escape( document.the_form.upgrade_zip.value );document.the_form.submit();" />
        <input type="hidden" name="run" value="upload" />
        <input type="hidden" name="upgrade_zip_escaped" value="" />
        </div>
        </td>
        </tr>
        </table></td></tr></table>
        </form>
        </div>
        eoq;
}

$hidden_fields = "<input type=hidden name=\"run\" value=\"upload\" />";
$hidden_fields .= "<input type=hidden name=\"mode\"/>";

$form2 = PackageManagerDisplay::buildPackageDisplay($form, $hidden_fields, $form_action, array('module'));
$form3 = <<<eoq3

eoq3;

echo $form2 . $form3;

$GLOBALS['log']->info("Upgrade Wizard view");
?>
</td>
</tr>
</table>
</td>
</tr>
</table>