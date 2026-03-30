<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SuiteEditorSettingsForTinyMCE
 *
 * It's a non-extended class of SuiteEditorSettingsForDirectHTML
 * the TinyMCE need exactly same default settings as a Direct HTML editor
 * but class is extended for correct name convection
 *
 * see: class SuiteEditorSettingsForDirectHTML
 *
 */
class SuiteEditorSettingsForTinyMCE extends SuiteEditorSettingsForDirectHTML
{

    /**
     * JSON setting for TinyMCE initializer script
     *
     * @var string
     */
    public $tinyMCESetup = '{}';
}
