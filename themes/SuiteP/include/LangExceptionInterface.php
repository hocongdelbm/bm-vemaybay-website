<?php

namespace SuiteCRM;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LangInterface
 *
 * @author gyula
 */
// implement this interface in any exception to make it translatable
interface LangExceptionInterface
{ // extends Throwable { // extending Throwable only in PHP7+

    /**
     *
     * @return string
     */
    public function getLangMessage();
}
