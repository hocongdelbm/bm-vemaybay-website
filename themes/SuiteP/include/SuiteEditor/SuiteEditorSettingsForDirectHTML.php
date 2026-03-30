<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class SuiteEditorSettingsForDirectHTML
 *
 * store and extends an associative settings for a simple textarea editor
 */
class SuiteEditorSettingsForDirectHTML extends SuiteEditorSettings
{

    /**
     * Editor contents
     * @var string
     */
    public $contents = '';

    /**
     * target element, original textarea ID
     * @var string
     */
    public $textareaId = 'text';

    /**
     * Editor element ID
     * @var string
     */
    public $elementId = 'editor';

    /**
     * SuiteEditorSettingsForDirectHTML constructor.
     *
     * set the default settings for a simple textarea editor
     * and if settings argument exists extends it
     * @param null $settings (optional)
     */
    public function __construct($settings = null)
    {
        parent::__construct($settings);
    }
}
