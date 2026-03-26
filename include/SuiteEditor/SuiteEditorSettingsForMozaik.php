<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SuiteEditorSettingsForMozaik
 *
 * store and extends an associative settings for a mozaik editor
 * in constructor, set the default settings for a mozaik editor
 * and if settings argument exists extends it
 */
class SuiteEditorSettingsForMozaik extends SuiteEditorSettingsForTinyMCE
{

    /**
     * @var int
     */
    public $width = 600;

    /**
     * @var string
     */
    public $group = '';
}
