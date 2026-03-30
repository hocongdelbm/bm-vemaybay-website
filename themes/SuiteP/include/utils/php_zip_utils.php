<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/SugarCache/SugarCache.php';

/**
 * Unzip file to specified directory
 *
 * @param string $zip_archive
 * @param string $zip_dir
 * @return bool
 */
function unzip($zip_archive, $zip_dir)
{
    return unzip_file($zip_archive, null, $zip_dir);
}

/**
 * Unzip files to specified directory
 *
 * @param string $zip_archive
 * @param string|null $archive_file
 * @param string $zip_dir
 * @return bool
 */
function unzip_file($zip_archive, $archive_file, $zip_dir)
{
    if (!is_dir($zip_dir)) {
        LoggerManager::getLogger()->fatal('Specified directory for zip file extraction does not exist');
        if (defined('SUITE_PHPUNIT_RUNNER') || defined('SUGARCRM_INSTALL')) {
            return false;
        }
    }
    $zip = new ZipArchive;
    // We need realpath here for PHP streams support
    $res = $zip->open(UploadFile::realpath($zip_archive));

    if ($res !== true) {
        LoggerManager::getLogger()->fatal(sprintf(sprintf('ZIP Error(%d): Status(%s)', $res, $zip->status)));
        if (defined('SUITE_PHPUNIT_RUNNER') || defined('SUGARCRM_INSTALL')) {
            return false;
        }
    }

    if ($archive_file !== null) {
        $res = $zip->extractTo(UploadFile::realpath($zip_dir), $archive_file);
        if ((new SplFileInfo($archive_file))->getExtension() == 'php') {
            SugarCache::cleanFile(UploadFile::realpath($zip_dir) . '/' . $archive_file);
        }
    } else {
        $res = $zip->extractTo(UploadFile::realpath($zip_dir));
        SugarCache::cleanDir(UploadFile::realpath($zip_dir));
    }

    if ($res !== true) {
        LoggerManager::getLogger()->fatal(sprintf(sprintf('ZIP Error(%d): Status(%s)', $res, $zip->status)));
        if (defined('SUITE_PHPUNIT_RUNNER') || defined('SUGARCRM_INSTALL')) {
            return false;
        }
    }

    return true;
}

/**
 * Zip specified directory
 *
 * @param $zip_dir
 * @param $zip_archive
 * @return bool
 */
function zip_dir($zip_dir, $zip_archive)
{
    if (!is_dir($zip_dir)) {
        LoggerManager::getLogger()->fatal('Specified directory for zip file extraction does not exist.');

        return false;
    }
    $zip = new ZipArchive();
    // We need realpath here for PHP streams support
    $zip->open(
        UploadFile::realpath($zip_archive),
        ZipArchive::CREATE | ZipArchive::OVERWRITE
    );
    $path = UploadFile::realpath($zip_dir);
    $chop = strlen($path) + 1;
    $dir = new RecursiveDirectoryIterator($path);
    foreach (new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST) as $fileinfo) {
        // Bug # 45143
        // ensure that . and .. are not zipped up, otherwise, the
        // CENT OS and others will fail when deploying module
        $fileName = $fileinfo->getFilename();
        if ($fileName === '.' || $fileName === '..') {
            continue;
        }
        $localname = str_replace("\\", '/', substr($fileinfo->getPathname(), $chop));
        if ($fileinfo->isDir()) {
            $zip->addEmptyDir($localname . '/');
        } else {
            $zip->addFile($fileinfo->getPathname(), $localname);
        }
    }

    return true;
}

/**
 * Zip list of files, optionally stripping prefix
 *
 * @param string $zip_file
 * @param array $file_list
 * @param string $prefix
 * @return bool
 */
function zip_files_list($zip_file, $file_list, $prefix = '')
{
    $archive = new ZipArchive();
    // We need realpath here for PHP streams support
    $res = $archive->open(
        UploadFile::realpath($zip_file),
        ZipArchive::CREATE | ZipArchive::OVERWRITE
    );
    if ($res !== true) {
        LoggerManager::getLogger()->fatal("Unable to open zip file, check directory permissions: $zip_file");

        return false;
    }
    foreach ($file_list as $file) {
        if (!empty($prefix) && preg_match($prefix, $file, $matches) > 0) {
            $zipname = substr($file, strlen($matches[0]));
        } else {
            $zipname = $file;
        }
        $archive->addFile($file, $zipname);
    }

    return true;
}
