<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SuiteEditorSettings
 *
 * store and extends an associative settings for editors
 */
abstract class SuiteEditorSettings
{
    /**
     * @var string
     */
    public $contents;

    /**
     * Target textarea element ID
     *
     * @var string
     */
    public $textareaId;

    /**
     * @var string
     */
    public $elementId;

    /**
     * @var int
     */
    public $width;

    /**
     * @var string
     */
    public $group;

    /**
     * Javascript for body click handling
     *
     * @var string
     */
    public $clickHandler;

    /**
     * SuiteEditorSettings constructor.
     *
     * @param $settings array or object
     */
    public function __construct($settings = null)
    {
        if ($settings) {
            $this->extend($settings);
        }
    }

    /**
     * extends the settings
     *
     * @param $settings array or object
     */
    public function extend($settings)
    {
        foreach ($settings as $key => $value) {
            $this->$key = $value;
        }
    }
}
