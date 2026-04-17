<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Interface SuiteEditorInterface
 *
 * each editor based on same interface to use all of it in same way
 */
interface SuiteEditorInterface
{
    /**
     * use this method after the constructor to tell
     * the settings that apply on editor connector
     *
     * @param SuiteEditorSettings $settings (optional) preferred an associative array or object
     */
    public function setup(SuiteEditorSettings $settings = null);

    /**
     * generate an output which contains the editor
     *
     * @return string (html output)
     */
    public function getHtml();
}
