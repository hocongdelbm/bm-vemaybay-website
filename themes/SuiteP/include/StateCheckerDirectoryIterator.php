<?php

namespace SuiteCRM;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;

/**
 * StateCheckerDirectoryIterator
 *
 * Readable directory iterator
 *
 * @author gyula
 */
class StateCheckerDirectoryIterator extends RecursiveFilterIterator
{
    public function __construct($path)
    {
        if (!$path instanceof RecursiveDirectoryIterator) {
            if (! is_readable($path) || ! is_dir($path)) {
                throw new InvalidArgumentException("$path is not a valid directory or not readable");
            }
            $path = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        }
        parent::__construct($path);
    }

    public function accept()
    {
        return $this->current()->isReadable() && $this->current()->isDir();
    }
}
