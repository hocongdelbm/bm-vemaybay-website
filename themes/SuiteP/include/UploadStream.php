<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/externalAPI/ExternalAPIFactory.php');

/**
 * @internal
 * Upload file stream handler
 */
class UploadStream
{
    const STREAM_NAME = "upload";
    protected static $upload_dir;

    /**
     * Method checks Suhosin restrictions to use streams in php
     *
     * @static
     * @return bool is allowed stream or not
     */
    public static function getSuhosinStatus()
    {
        // looks like suhosin patch doesn't block protocols, only suhosin extension (tested on FreeBSD)
        // if suhosin is not installed it is okay for us
        if (extension_loaded('suhosin') == false) {
            return true;
        }
        $configuration = ini_get_all('suhosin', false);

        // suhosin simulation is okay for us
        if ($configuration['suhosin.simulation'] == true) {
            return true;
        }

        // checking that UploadStream::STREAM_NAME is allowed by white list
        $streams = $configuration['suhosin.executor.include.whitelist'];
        if ($streams != '') {
            $streams = explode(',', $streams);
            foreach ($streams as $stream) {
                $stream = explode('://', $stream, 2);
                if (count($stream) == 1) {
                    if ($stream[0] == UploadStream::STREAM_NAME) {
                        return true;
                    }
                } elseif ($stream[1] == '' && $stream[0] == UploadStream::STREAM_NAME) {
                    return true;
                }
            }

            $GLOBALS['log']->fatal('Stream ' . UploadStream::STREAM_NAME . ' is not listed in suhosin.executor.include.whitelist and blocked because of it');

            return false;
        }

        // checking that UploadStream::STREAM_NAME is not blocked by black list
        $streams = $configuration['suhosin.executor.include.blacklist'];
        if ($streams != '') {
            $streams = explode(',', $streams);
            foreach ($streams as $stream) {
                $stream = explode('://', $stream, 2);
                if ($stream[0] == UploadStream::STREAM_NAME) {
                    $GLOBALS['log']->fatal('Stream ' . UploadStream::STREAM_NAME . 'is listed in suhosin.executor.include.blacklist and blocked because of it');

                    return false;
                }
            }

            return true;
        }

        $GLOBALS['log']->fatal('Suhosin blocks all streams, please define ' . UploadStream::STREAM_NAME . ' stream in suhosin.executor.include.whitelist');

        return false;
    }

    /**
     * Get upload directory
     * @return string
     */
    public static function getDir()
    {
        if (empty(self::$upload_dir)) {
            self::$upload_dir = rtrim($GLOBALS['sugar_config']['upload_dir'], '/\\');
            if (empty(self::$upload_dir)) {
                self::$upload_dir = "upload";
            }
            if (!file_exists(self::$upload_dir)) {
                sugar_mkdir(self::$upload_dir, 0755, true);
            }
        }

        return self::$upload_dir;
    }

    /**
     * Check if upload dir is writable
     * @return bool
     */
    public static function writable()
    {
        return is_writable(self::getDir());
    }

    /**
     * Register the stream
     */
    public static function register()
    {
        stream_wrapper_register(self::STREAM_NAME, __CLASS__);
    }

    /**
     * Get real FS path of the upload stream file
     * @param string $path Upload stream path (with upload://)
     * @return string FS path
     */
    public static function path($path)
    {
        $path = substr($path, strlen(self::STREAM_NAME) + 3); // cut off upload://
        $path = str_replace("\\", "/", $path); // canonicalize path
        if ($path == ".." || substr($path, 0, 3) == "../" || substr($path, -3, 3) == "/.." || strstr($path, "/../")) {
            return null;
        }

        return self::getDir() . "/" . $path;
    }

    /**
     * Ensure upload subdir exists
     * @param string $path Upload stream path (with upload://)
     * @param bool $writable
     * @return boolean
     */
    public static function ensureDir($path, $writable = true)
    {
        $path = self::path($path);
        if (!is_dir($path)) {
            return sugar_mkdir($path, 0755, true);
        }

        return true;
    }

    public function dir_closedir()
    {
        closedir($this->dirp);
    }

    public function dir_opendir($path, $options)
    {
        $this->dirp = opendir(self::path($path));

        return !empty($this->dirp);
    }

    public function dir_readdir()
    {
        return readdir($this->dirp);
    }

    public function dir_rewinddir()
    {
        return rewinddir($this->dirp);
    }

    public function mkdir($path, $mode, $options)
    {
        return mkdir(self::path($path), $mode, ($options & STREAM_MKDIR_RECURSIVE) != 0);
    }

    public function rename($path_from, $path_to)
    {
        return rename(self::path($path_from), self::path($path_to));
    }

    public function rmdir($path, $options)
    {
        return rmdir(self::path($path));
    }

    public function stream_cast($cast_as)
    {
        return $this->fp;
    }

    public function stream_close()
    {
        fclose($this->fp);

        return true;
    }

    public function stream_eof()
    {
        return feof($this->fp);
    }

    public function stream_flush()
    {
        return fflush($this->fp);
    }

    public function stream_lock($operation)
    {
        return flock($this->fp, $operation);
    }

    public function stream_open($path, $mode)
    {
        $fullpath = self::path($path);
        if (empty($fullpath)) {
            return false;
        }
        if ($mode == 'r') {
            $this->fp = fopen($fullpath, $mode);
        } else {
            // if we will be writing, try to transparently create the directory
            $this->fp = @fopen($fullpath, $mode);
            if (!$this->fp && !file_exists(dirname($fullpath))) {
                if (!mkdir($concurrentDirectory = dirname($fullpath), 0755, true) && !is_dir($concurrentDirectory)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                $this->fp = fopen($fullpath, $mode);
            }
        }

        return !empty($this->fp);
    }

    public function stream_read($count)
    {
        return fread($this->fp, $count);
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return fseek($this->fp, $offset, $whence) == 0;
    }

    public function stream_set_option($option, $arg1, $arg2)
    {
        return true;
    }

    public function stream_stat()
    {
        return fstat($this->fp);
    }

    public function stream_tell()
    {
        return ftell($this->fp);
    }

    public function stream_write($data)
    {
        return fwrite($this->fp, $data);
    }

    public function unlink($path)
    {
        unlink(self::path($path));

        return true;
    }

    public function url_stat($path, $flags)
    {
        return @stat(self::path($path));
    }

    public static function move_uploaded_file($upload, $path)
    {
        return move_uploaded_file($upload, self::path($path));
    }
}
